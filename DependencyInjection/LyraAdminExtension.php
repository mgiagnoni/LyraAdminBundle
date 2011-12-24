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
    private $modelNames;

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
        $this->modelNames = array_keys($config['models']);
        $routes = array('route_pattern_prefix' => $config['route_pattern_prefix']);
        $menu = array();

        foreach ($config['models'] as $name => $options)
        {
            foreach ($config['actions'] as $key => $action) {
                if (isset($options['actions'][$key])) {
                    $options['actions'][$key] = array_merge($action, $options['actions'][$key]);
                } else {
                    $options['actions'][$key] = $action;
                }
            }

            if (!isset($options['route_prefix'])) {
                $options['route_prefix'] = 'lyra_admin_'.$name;
            }

            if (!isset($options['route_pattern_prefix'])) {
                $options['route_pattern_prefix'] = $name;
            }

            // Options for route loader

            $routes['models'][$name] = array(
                'controller' => $options['controller'],
                'route_pattern_prefix' => $options['route_pattern_prefix'],
                'route_prefix' => $options['route_prefix']
            );

            foreach ($options['actions'] as $action => $attrs) {
                if (isset($attrs['route_pattern'])) {
                    $routes['models'][$name]['actions'][$action] = array(
                        'route_pattern' => $attrs['route_pattern'],
                        'route_defaults' => $attrs['route_defaults']
                    );
                }
            }

            // Options for menu

            $menu[$name] = array(
                'route' => $options['route_prefix'].'_index',
                'title' => isset($options['title']) ? $options['title'] : ucfirst($name),
                'trans_domain' => $options['trans_domain']
            );

            // Default columns options

            $columns = &$options['list']['columns'];

            foreach ($columns as $col => $attrs) {
                $columns[$col]['sorted'] = false;
                $columns[$col]['name'] = $col;

                if (!isset($attrs['label'])) {
                    $columns[$col]['label'] = $options['list']['auto_labels'] ? Util::humanize($col) : $name.'.list.'.$col;
                }

                if (!isset($attrs['property_name'])) {
                    $columns[$col]['property_name'] = $col;
                }

                if (isset($attrs['template'])) {
                    $columns[$col]['type'] = 'template';
                }
            }

            $options['theme'] = $config['theme'];
            $container->setParameter(sprintf('lyra_admin.%s.options', $name), $options);
            $container->setParameter(sprintf('lyra_admin.%s.class', $name), $options['class']);

            // Services

            $container->setDefinition(sprintf('lyra_admin.%s.model_manager', $name), new DefinitionDecorator($options['services']['model_manager']))
                ->setArguments(array(new Reference('doctrine.orm.entity_manager'), new Parameter(sprintf('lyra_admin.%s.class', $name))));

            $container->setDefinition(sprintf('lyra_admin.%s.list_renderer', $name), new DefinitionDecorator('lyra_admin.list_renderer.abstract'))
                ->setArguments(array(new Parameter(sprintf('lyra_admin.%s.list.options', $name))))
                ->addMethodCall('setName', array($name));

            $container->setDefinition(sprintf('lyra_admin.%s.form_renderer', $name), new DefinitionDecorator('lyra_admin.form_renderer.abstract'))
                ->setArguments(array(new Reference('lyra_admin.form_factory'), new Parameter(sprintf('lyra_admin.%s.form.options', $name))))
                ->addMethodCall('setName', array($name));

            $container->setDefinition(sprintf('lyra_admin.%s.filter_renderer', $name), new DefinitionDecorator('lyra_admin.filter_renderer.abstract'))
                ->setArguments(array(new Reference('lyra_admin.form_factory'), new Parameter(sprintf('lyra_admin.%s.filter.options', $name))))
                ->addMethodCall('setName', array($name));

             $container->setDefinition(sprintf('lyra_admin.%s.dialog_renderer', $name), new DefinitionDecorator('lyra_admin.dialog_renderer.abstract'))
                 ->setArguments(array(new Parameter(sprintf('lyra_admin.%s.actions.options', $name))))
                 ->addMethodCall('setName', array($name));;
        }
        $container->setParameter('lyra_admin.routes', $routes);
        $container->setParameter('lyra_admin.menu', $menu);
        $resources = array();

        if ($container->has('twig.form.resources')) {
            $resources = $container->getParameter('twig.form.resources');
        }
        $resources[] = 'LyraAdminBundle:Admin:fields.html.twig';
        $container->setParameter('twig.form.resources', $resources);
    }

    public function configureFromMetadata(ContainerBuilder $container)
    {
        $this->setFieldsDefaults($container);
        $this->updateColumnsDefaults($container);
        $this->setModelOptions($container);
    }

    private function setFieldsDefaults(ContainerBuilder $container)
    {
        $models = array();
        $config = $container->getParameter('lyra_admin.options');

        foreach ($config['models'] as $name => $options) {
            $models[$name]['class'] = $options['class'];
            $refl = new \ReflectionClass($options['class']);
            $models[$name]['nspace'] = $refl->getNamespaceName();
        }

        $managers = $container->getParameter('doctrine.entity_managers');

        foreach (array_keys($managers) as $manager) {
            $definition = $container->getDefinition(sprintf('doctrine.orm.%s_entity_manager', $manager));
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
                        if ($this->checkNamespace($models, $nspace)) {
                            $driver = $this->createDriver($container, $ref);
                            $driverChain->addDriver($driver, $nspace);
                        }
                        break;
                }
            }

            $config->setMetadataDriverImpl($driverChain);
            $em = EntityManager::create($connectionOptions, $config);

            foreach ($models as $name => $model) {
                $metadata = $em->getClassMetadata($model['class']);
                $this->setFieldsDefaultsFromMetadata($container, $name, $metadata);
            }
        }
    }

    private function setModelOptions(ContainerBuilder $container)
    {
        foreach ($this->modelNames as $name) {
            $options = $container->getParameter(sprintf('lyra_admin.%s.options', $name));
            $container->setParameter(sprintf('lyra_admin.%s.actions.options', $name), array_diff_key($options, array('form' => null,'list'=>null)));
            $container->setParameter(sprintf('lyra_admin.%s.list.options', $name), array_diff_key($options, array('form' => null)));
            $container->setParameter(sprintf('lyra_admin.%s.form.options', $name), array_diff_key($options, array('list' => null)));
            $container->setParameter(sprintf('lyra_admin.%s.filter.options', $name), array_diff_key($options, array('form' => null,'list' => null)));

        }
    }

    private function updateColumnsDefaults(ContainerBuilder $container)
    {
        foreach ($this->modelNames as $name) {
            $options = $container->getParameter(sprintf('lyra_admin.%s.options', $name));
            $fields = $options['fields'];
            $columns = $options['list']['columns'];

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

            $options['list']['columns'] = $columns;
            $container->setParameter(sprintf('lyra_admin.%s.options', $name), $options);
        }
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

    private function setFieldsDefaultsFromMetadata(ContainerBuilder $container, $model, $metadata)
    {
        $modelOptions = $container->getParameter(sprintf('lyra_admin.%s.options', $model));
        $fields = $modelOptions['fields'];

        foreach ($metadata->fieldMappings as $name => $attrs) {
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

        foreach ($metadata->associationMappings as $name => $attrs) {
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

        $modelOptions['fields'] = $fields;
        $fields = $modelOptions['filter']['fields'];
        $mappings = $metadata->fieldMappings;

        foreach ($fields as $field => $attrs) {
            $fields[$field]['type'] = $mappings[$field]['type'];
        }

        $modelOptions['filter']['fields'] = $fields;
        $container->setParameter(sprintf('lyra_admin.%s.options', $model) ,$modelOptions);
    }

    private function checkNamespace($models, $nspace)
    {
        foreach ($models as $model) {
            if ($model['nspace'] == $nspace) {
                return true;
            }
        }

        return false;
    }
}
