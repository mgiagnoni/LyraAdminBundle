<?php

/*
 * This file is part of the LyraAdminBundle package.
 *
 * Copyright 2011 Massimo Giagnoni <gimassimo@gmail.com>
 *
 * This source file is subject to the MIT license. Full copyright and license
 * information are in the LICENSE file distributed with this source code.
 */

namespace Lyra\AdminBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Processor;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\DefinitionDecorator;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\DependencyInjection\Parameter;
use Symfony\Component\Config\FileLocator;
use Doctrine\ORM\Mapping\ClassMetadataInfo;
use Doctrine\Common\Cache\ArrayCache;
use Doctrine\ORM\Configuration as ORMConfig;
use Doctrine\ORM\EntityManager;
use Doctrine\Common\Annotations\AnnotationReader;

use Lyra\AdminBundle\Util\Util;

/**
 * Bundle extension class.
 */
class LyraAdminExtension extends Extension
{
    private $config;
    private $modelNames;
    private $metadata;

    public function load(array $configs, ContainerBuilder $container)
    {
        $processor = new Processor();
        $configuration = new Configuration();

        $config = $processor->processConfiguration($configuration, $configs);
        $loader = new XmlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));

        foreach (array($config['db_driver'], 'services', 'routing_loader', 'form') as $basename) {
            $loader->load(sprintf('%s.xml', $basename));
        }

        $container->setParameter('lyra_admin.options', $config);
        $this->config = $config;
        $this->modelNames = array_keys($config['models']);

        foreach ($this->modelNames as $model)
        {
            $this->setActionsDefaults($model);
            $this->setRouteDefaults($model);
            $this->setColumnsDefaults($model);
        }

        $this->createServiceDefinitions($container);
        $this->setRouteLoaderOptions($container);
        $this->setMenuOptions($container);

        $resources = array();

        if ($container->has('twig.form.resources')) {
            $resources = $container->getParameter('twig.form.resources');
        }
        $resources[] = 'LyraAdminBundle:Admin:fields.html.twig';
        $container->setParameter('twig.form.resources', $resources);
    }

    public function configureFromMetadata(ContainerBuilder $container)
    {
        $this->readMetadata($container);
        $this->setFieldsDefaultsFromMetadata();
        $this->setFilterFieldsDefaults();
        $this->setAssocFieldsOptions();
        $this->updateColumnsDefaults();
        $this->setModelOptions($container);
    }


    private function setActionsDefaults($model)
    {
        $options =& $this->config['models'][$model]['actions'];

        foreach ($this->config['actions'] as $action => $attrs) {
            if (isset($options[$action])) {
                if (count($options[$action]['route_defaults']) == 0) {
                    unset($options[$action]['route_defaults']);
                }
                $options[$action] = array_merge($attrs, $options[$action]);
            } else {
                $options[$action] = $attrs;
            }
        }
    }

    private function setRouteDefaults($model)
    {
        $options =& $this->config['models'][$model];

        if (!isset($options['route_prefix'])) {
            $options['route_prefix'] = 'lyra_admin_'.$model;
        }

        if (!isset($options['route_pattern_prefix'])) {
            $options['route_pattern_prefix'] = $model;
        }
    }

    private function setColumnsDefaults($model)
    {
        $options =& $this->config['models'][$model]['list'];
        $columns =& $options['columns'];

        foreach ($columns as $col => $attrs) {
            $columns[$col]['sorted'] = false;
            $columns[$col]['name'] = $col;

            if (!isset($attrs['label'])) {
                $columns[$col]['label'] = $options['auto_labels'] ? Util::humanize($col) : $model.'.list.'.$col;
            }

            if (!isset($attrs['field'])) {
                $columns[$col]['field'] = $col;
            }

            if (isset($attrs['template'])) {
                $columns[$col]['type'] = 'template';
            }
        }
    }

    private function setModelOptions(ContainerBuilder $container)
    {
        foreach ($this->modelNames as $model) {
            $options = $this->config['models'][$model];
            $options['theme'] = $this->config['theme'];
            $container->setParameter(sprintf('lyra_admin.%s.options', $model), $options);
            $container->setParameter(sprintf('lyra_admin.%s.actions.options', $model), array_diff_key($options, array('form' => null,'list'=>null)));
            $container->setParameter(sprintf('lyra_admin.%s.list.options', $model), array_diff_key($options, array('form' => null)));
            $container->setParameter(sprintf('lyra_admin.%s.form.options', $model), array_diff_key($options, array('list' => null)));
            $container->setParameter(sprintf('lyra_admin.%s.filter.options', $model), array_diff_key($options, array('form' => null,'list' => null)));
            $container->setParameter(sprintf('lyra_admin.%s.class', $model), $options['class']);
        }
    }

    private function updateColumnsDefaults()
    {
        foreach ($this->modelNames as $model) {
            $fields = $this->config['models'][$model]['fields'];
            $columns =& $this->config['models'][$model]['list']['columns'];

            foreach ($columns as $key => $attrs) {
                $type = $attrs['type'];
                if(null === $type && isset($fields[$key])) {
                    $type = $fields[$key]['type'];
                    $columns[$key]['type'] = $type;
                }

                if ('date' == $type || 'datetime' == $type && !isset($attrs['format'])) {
                    //TODO: make default date format configurable
                    $columns[$key]['format'] = 'j/M/Y';
                }

                $columns[$key]['class'] = 'class="'.$type.'"';;
                $class = $columns[$key]['sortable'] ? 'sortable' : '';
                $class .= ' col-'.$key.' '.$type;
                $columns[$key]['th_class'] = 'class="'.trim($class).'"';
            }
        }
    }

    private function setFieldsDefaultsFromMetadata()
    {
        foreach ($this->modelNames as $model) {

            if (!isset($this->metadata[$model])) {
                continue;
            }

            $fields =& $this->config['models'][$model]['fields'];

            foreach ($this->metadata[$model]->fieldMappings as $name => $attrs) {
                if (isset($attrs['id']) && $attrs['id'] === true) {
                    continue;
                }
                $defaults = array('name' => $name, 'options' => array());
                if (!isset($fields[$name])) {
                    $fields[$name] = $defaults;
                }

                $fields[$name]['type'] = $attrs['type'];

                if (isset($attrs['length'])) {
                    $fields[$name]['length'] = $attrs['length'];
                }

                if (isset($attrs['options'])) {
                    $options = $attrs['options'];
                } else {
                    $options = array();
                }

                $options = array_merge($options, $fields[$name]['options']);
                unset($fields[$name]['options']);
                $fields[$name] = array_merge($attrs, $defaults, $fields[$name]);
                $fields[$name]['options'] = $options;
            }

            foreach ($this->metadata[$model]->associationMappings as $name => $attrs) {
                if (ClassMetadataInfo::MANY_TO_ONE == $attrs['type'] || ClassMetadataInfo::MANY_TO_MANY == $attrs['type']) {
                    $fields[$name]['type'] = 'entity';
                    $fields[$name]['options'] = array(
                        'class' => $attrs['targetEntity'],
                        'multiple' => ClassMetadataInfo::MANY_TO_MANY == $attrs['type']
                    );
                }
            }

            foreach ($fields as $field => $attrs) {
                if (!isset($attrs['get_method'])) {
                    $fields[$field]['get_method'] = 'get'.Util::camelize($field);
                }
                $fields[$field]['tag'] = Util::underscore($field);
            }
        }
    }

    private function setFilterFieldsDefaults()
    {
        foreach ($this->modelNames as $model) {
            $fields = $this->config['models'][$model]['fields'];
            $filters =& $this->config['models'][$model]['filter']['fields'];

            foreach ($filters as $field => $attrs) {
                $filters[$field]['type'] = $fields[$field]['type'];
                $filters[$field]['options'] = $fields[$field]['options'];
            }
        }
    }

    private function setAssocFieldsOptions()
    {
        $classes = array();
        foreach ($this->modelNames as $model) {
            $options = $this->config['models'][$model];
            $classes[$options['class']] = array('model' => $model, 'fields' => $options['fields']);
        }

        foreach ($this->modelNames as $model) {
            $fields =& $this->config['models'][$model]['fields'];
            foreach ($fields as $name => $attrs) {
                if ('entity' == $attrs['type']) {
                    $class = $attrs['options']['class'];
                    if (isset($classes[$class])) {
                        $fields[$name]['assoc'] = $classes[$class];
                    }
                }
            }
        }
    }

    private function createServiceDefinitions(ContainerBuilder $container)
    {
        foreach ($this->config['models'] as $model => $options) {

            $container->setDefinition(sprintf('lyra_admin.%s.configuration', $model), new DefinitionDecorator('lyra_admin.configuration.abstract'))
                ->setArguments(array(new Parameter(sprintf('lyra_admin.%s.options', $model))));

            $container->setDefinition(sprintf('lyra_admin.%s.model_manager', $model), new DefinitionDecorator($options['services']['model_manager']))
                ->setArguments(array(new Reference('doctrine.orm.entity_manager'), new Reference(sprintf('lyra_admin.%s.configuration', $model))));

            $container->setDefinition(sprintf('lyra_admin.%s.list_renderer', $model), new DefinitionDecorator('lyra_admin.list_renderer.abstract'))
                ->setArguments(array(new Reference(sprintf('lyra_admin.%s.configuration', $model))))
                ->addMethodCall('setName', array($model));

            $container->setDefinition(sprintf('lyra_admin.%s.form_renderer', $model), new DefinitionDecorator('lyra_admin.form_renderer.abstract'))
                ->replaceArgument(1, new Reference(sprintf('lyra_admin.%s.configuration', $model)))
                ->addMethodCall('setName', array($model));

            $container->setDefinition(sprintf('lyra_admin.%s.filter_renderer', $model), new DefinitionDecorator('lyra_admin.filter_renderer.abstract'))
                ->replaceArgument(1, new Reference(sprintf('lyra_admin.%s.configuration', $model)))
                ->addMethodCall('setName', array($model));

            $container->setDefinition(sprintf('lyra_admin.%s.dialog_renderer', $model), new DefinitionDecorator('lyra_admin.dialog_renderer.abstract'))
                ->setArguments(array(new Reference(sprintf('lyra_admin.%s.configuration', $model))))
                ->addMethodCall('setName', array($model));
        }
    }

    private function setRouteLoaderOptions(ContainerBuilder $container)
    {
        $routes = array('route_pattern_prefix' => $this->config['route_pattern_prefix']);

        foreach ($this->modelNames as $model) {
            $options = $this->config['models'][$model];
            $routes['models'][$model] = array(
                'controller' => $options['controller'],
                'route_pattern_prefix' => $options['route_pattern_prefix'],
                'route_prefix' => $options['route_prefix']
            );

            foreach ($options['actions'] as $action => $attrs) {
                if (isset($attrs['route_pattern'])) {
                    $routes['models'][$model]['actions'][$action] = array(
                        'route_pattern' => $attrs['route_pattern'],
                        'route_defaults' => $attrs['route_defaults']
                    );
                }
            }
        }

        $container->setParameter('lyra_admin.routes', $routes);
    }

    private function setMenuOptions(ContainerBuilder $container)
    {
        $menu = array();
        foreach ($this->modelNames as $model) {
            $options = $this->config['models'][$model];

            if (false !== $options['title']) {
                $menu[$model] = array(
                    'route' => $options['route_prefix'].'_index',
                    'title' => null !== $options['title'] ? $options['title'] : ucfirst($model),
                    'trans_domain' => $options['trans_domain']
                );
            }
        }

        $container->setParameter('lyra_admin.menu', $menu);
    }

    private function readMetadata(ContainerBuilder $container)
    {
        foreach ($this->config['models'] as $model => $options) {
            if ($em = $this->createEntitymanager($container)) {
                $this->metadata[$model] = $em->getClassMetadata($options['class']);
            }
        }
    }

    private function createEntityManager(ContainerBuilder $container, $manager = 'default')
    {
        $id = sprintf('doctrine.orm.%s_entity_manager', $manager);
        if (!$container->hasDefinition($id)) {
            return false;
        }

        $definition = $container->getDefinition($id);
        // Connection
        $definition = $container->getDefinition($definition->getArgument(0));
        $connectionOptions = $definition->getArgument(0);

        $config = new ORMConfig();
        $cache = new ArrayCache();
        $config->setMetadataCacheImpl($cache);
        $config->setQueryCacheImpl($cache);

        $definition = $container->getDefinition(sprintf('doctrine.orm.%s_configuration', $manager));
        $methods = $definition->getMethodCalls();
        foreach ($methods as $method) {
            switch ($method[0]) {
                case 'setProxyDir':
                    $config->setProxyDir($method[1][0]);
                    break;
                case 'setProxyNamespace':
                    $config->setProxyNamespace($method[1][0]);
                    break;
            }
        }

        // Configure driver chain
        $definition = $container->getDefinition(sprintf('doctrine.orm.%s_metadata_driver', $manager));
        $class = $definition->getClass();
        $methods = $definition->getMethodCalls();
        $driverChain = new $class;

        foreach ($methods as $method) {
            switch ($method[0]) {
                case 'addDriver':
                    $ref = $method[1][0];
                    $nspace = $method[1][1];
                    $driver = $this->createDriver($container, $ref);
                    $driverChain->addDriver($driver, $nspace);
                    break;
            }
        }

        $config->setMetadataDriverImpl($driverChain);

        return EntityManager::create($connectionOptions, $config);
    }

    private function createDriver(ContainerBuilder $container, $reference)
    {
        $definition = $container->getDefinition($reference);

        if (false !== strpos($reference, 'annotation_metadata_driver')) {
            return $this->createAnnotationDriver($definition);
        }

        return $this->createFileDriver($definition);
    }

    private function createFileDriver(Definition $definition)
    {
        $class = $definition->getClass();
        $driver = new $class($definition->getArgument(0));
        $methods = $definition->getMethodCalls();

        foreach ($methods as $method) {
            switch ($method[0]) {
                case 'setNamespacePrefixes':
                    $driver->setNamespacePrefixes($method[1][0]);
                    break;
                case 'setGlobalBasename':
                    $driver->setGlobalBasename($method[1][0]);
                    break;
            }
        }

        return $driver;
    }

    private function createAnnotationDriver(Definition $definition)
    {
        $driverClass = $definition->getClass();
        $reader = new AnnotationReader();
        $driver = new $driverClass($reader, $definition->getArgument(1));

        return $driver;
    }
}
