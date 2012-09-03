<?php

/*
 * This file is part of the LyraAdminBundle package.
 *
 * Copyright 2011-2012 Massimo Giagnoni <gimassimo@gmail.com>
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

        foreach (array($config['db_driver'], 'services', 'routing_loader', 'form', 'twig') as $basename) {
            $loader->load(sprintf('%s.xml', $basename));
        }

        $container->setParameter('lyra_admin.options', $config);
        $this->config = $config;
        $this->modelNames = array_keys($config['models']);

        $this->setRouteDefaults();
        $this->setActionsDefaults();

        foreach ($this->modelNames as $model)
        {
            $this->setListDefaults($model);
            $this->setFormDefaults($model);
            $this->setFilterDefaults($model);
            $this->setColumnsDefaults($model);
        }

        $this->createServiceDefinitions($container);
        $this->setRouteLoaderOptions($container);
        $this->setSecurityManagerOptions($container);
        $this->setMenuOptions($container);

        $resources = array();

        if ($container->has('twig.form.resources')) {
            $resources = $container->getParameter('twig.form.resources');
        }
        $resources[] = 'LyraAdminBundle:Form:fields.html.twig';
        $container->setParameter('twig.form.resources', $resources);
    }

    public function configureFromMetadata(ContainerBuilder $container)
    {
        $this->readMetadata($container);
        $this->setFieldsDefaultsFromMetadata($container);
        $this->setFilterFieldsDefaults();
        $this->setShowFieldsDefaults();
        $this->updateColumnsDefaults();
        $this->setModelOptions($container);
        $this->updateServiceDefinitions($container);
    }


    private function setActionsDefaults()
    {
        foreach ($this->modelNames as $model) {
            $actions =& $this->config['models'][$model]['actions'];

            foreach ($this->config['actions'] as $action => $attrs) {
                if (isset($actions[$action])) {
                    if (count($actions[$action]['route_defaults']) == 0) {
                        unset($actions[$action]['route_defaults']);
                    }
                    $actions[$action] = array_merge($attrs, $actions[$action]);
                } else {
                    $actions[$action] = $attrs;
                }
            }

            foreach ($actions as $action => $attrs) {
                if (isset($attrs['route_pattern']) && !isset($attrs['route_name'])) {
                    $actions[$action]['route_name'] = $this->config['models'][$model]['route_prefix'].'_'.$action;
                }
            }
        }

        // Action alias

        foreach ($this->modelNames as $model) {
            $actions =& $this->config['models'][$model]['actions'];

            foreach ($actions as $action => $attrs) {
                if (isset($attrs['alias'])) {
                    if (false === strpos($attrs['alias'], '.')) {
                        $attrs['alias'] = $model.'.'.$attrs['alias'];
                    }

                    list($aliasModel, $aliasAction) = explode('.', $attrs['alias']);

                    if (isset($this->config['models'][$aliasModel]['actions'][$aliasAction])) {
                        $actions[$action] = $this->config['models'][$aliasModel]['actions'][$aliasAction];
                        $actions[$action]['alias'] = $attrs['alias'];
                    }
                }
            }
        }
    }

    private function setRouteDefaults()
    {
        foreach ($this->modelNames as $model) {
            $options =& $this->config['models'][$model];

            if (!isset($options['route_prefix'])) {
                $options['route_prefix'] = 'lyra_admin_'.$model;
            }

            if (!isset($options['route_pattern_prefix'])) {
                $options['route_pattern_prefix'] = $model;
            }
        }
    }

    private function setListDefaults($model)
    {
        $modelOpts = $this->config['models'][$model];
        $options =& $this->config['models'][$model]['list'];
        $options['other_actions'] = array('index' => array(), 'object' => array());

        foreach (array('list_actions', 'object_actions', 'batch_actions', 'other_actions') as $type) {
            $listActions =& $options[$type];
            $actions = array();
            foreach ($listActions as $action => $attrs) {
                $actions[$action] = $this->setActionOptions($action, $modelOpts['actions'], $attrs);
            }
            $listActions = $actions;
        }

        $options['trans_domain'] = $modelOpts['trans_domain'];
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

    private function setFormDefaults($model)
    {
        $modelOpts = $this->config['models'][$model];
        $options =& $this->config['models'][$model]['form'];

        $groups = $options['groups'];
        $new = $options['new']['groups'];
        $edit = $options['edit']['groups'];

        if (count($new) || count($edit)) {
            $groups = array(
                '_new' => array_merge($groups, $new),
                '_edit' => array_merge($groups, $edit)
            );
        }

        $options['groups'] = $groups;

        foreach (array('new', 'edit') as $key) {
            foreach ($options[$key]['actions'] as $action => $attrs) {
                $options[$key]['actions'][$action] = $this->setActionOptions($action, $modelOpts['actions'], $attrs);
            }
        }

        $actions = array();

        foreach (array('new', 'edit') as $action) {
            $actions[$action] = $this->setActionOptions($action, $modelOpts['actions']);
        }

        $options['actions'] = $actions;
        $options['trans_domain'] = $modelOpts['trans_domain'];
        $options['data_class'] = $modelOpts['class'];
    }

    private function setFilterDefaults($model)
    {
        $options =& $this->config['models'][$model]['filter'];
        $actions = array();
        foreach (array('filter', 'index') as $action) {
            $actions[$action] = $this->setActionOptions($action, $this->config['models'][$model]['actions']);
        }

        $options['actions'] = $actions;
        $options['trans_domain'] = $this->config['models'][$model]['trans_domain'];
    }

    private function setActionOptions($action, $actions, $curOpts = array())
    {
        $options['name'] = $action;
        $keys = array('route_name', 'route_pattern', 'route_params', 'text', 'icon', 'style', 'dialog', 'trans_domain', 'template', 'roles');
        foreach ($keys as $key) {
            if (isset($curOpts[$key])) {
                $options[$key] = $curOpts[$key];
            } elseif (isset($actions[$action][$key])) {
                $options[$key] = $actions[$action][$key];
            }
        }

        return $options;
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

                if (('date' == $type || 'datetime' == $type) && !isset($attrs['format'])) {
                    //TODO: make default date format configurable
                    $columns[$key]['format'] = 'j/M/Y';
                }

                $getMethods = array();
                $colFields = explode('.', $columns[$key]['field']);
                $field = array_shift($colFields);

                if (isset($fields[$field])) {
                    $getMethods[] = $fields[$field]['get_method'];
                }

                $assocFields = $fields;

                foreach ($colFields as $colField) {
                    if (!isset($assocFields[$field]['assoc'])) {
                        break;
                    }

                    $assocModel = $assocFields[$field]['assoc']['model'];
                    $assocFields = $this->config['models'][$assocModel]['fields'];

                    if (isset($assocFields[$colField])) {
                        $getMethods[] = $assocFields[$colField]['get_method'];
                    }

                    $field = $colField;
                }

                $columns[$key]['get_methods'] = $getMethods;
            }
        }
    }

    private function setFieldsDefaultsFromMetadata(ContainerBuilder $container)
    {
        $classes = array();
        foreach ($this->modelNames as $model) {
            $options = $this->config['models'][$model];
            $classes[$options['class']] = array('model' => $model, 'fields' => $options['fields']);
        }

        foreach ($this->modelNames as $model) {

            if (!isset($this->metadata[$model])) {
                continue;
            }

            $fields =& $this->config['models'][$model]['fields'];

            foreach ($this->metadata[$model]->fieldMappings as $name => $attrs) {
                if (isset($attrs['id']) && $attrs['id'] === true) {
                    continue;
                }

                $fields[$name]['name'] = $name;
                $fields[$name]['type'] = $attrs['type'];

                if (!isset($fields[$name]['widget'])) {
                    switch($attrs['type']) {
                        case 'text':
                            $widget = 'textarea';
                            break;
                        case 'boolean':
                            $widget ='checkbox';
                            break;
                        case 'date':
                            $widget = 'date';
                            break;
                        case 'datetime':
                            $widget = 'datetime';
                            break;
                        default:
                            $widget = 'text';
                    }

                    $fields[$name]['widget'] = $widget;
                }

                if (isset($attrs['length'])) {
                    $fields[$name]['length'] = $attrs['length'];
                }

                if (!isset($fields[$name]['options']['required'])) {
                    $fields[$name]['options']['required'] = !isset($attrs['nullable']) || false === $attrs['nullable'];
                }

                if ('checkbox' == $fields[$name]['widget']) {
                    $fields[$name]['options']['required'] = false;
                }
            }

            foreach ($this->metadata[$model]->associationMappings as $name => $attrs) {
                if (ClassMetadataInfo::MANY_TO_ONE == $attrs['type'] || ClassMetadataInfo::MANY_TO_MANY == $attrs['type']) {
                    $fields[$name]['name'] = $name;
                    $fields[$name]['type'] = 'entity';
                    $fields[$name]['widget'] = 'entity';
                    $class = $attrs['targetEntity'];
                    $info = $this->getEntityManagerInfoForClass($container, $class);
                    $fields[$name]['options'] = array_merge(
                        isset($fields[$name]['options']) ? $fields[$name]['options'] : array(),
                        array(
                            'class' => $class,
                            'multiple' => ClassMetadataInfo::MANY_TO_MANY == $attrs['type'],
                            'em' => $info['name']
                        )
                    );

                    if (isset($classes[$class])) {
                        $fields[$name]['assoc'] = $classes[$class];
                    }
                }
            }

            foreach ($fields as $field => $attrs) {
                if (!isset($attrs['get_method'])) {
                    $fields[$field]['get_method'] = 'get'.Util::camelize($field);
                }
                $class = isset($attrs['options']['attr']['class']) ? $attrs['options']['attr']['class'].' ' : '';
                $fields[$field]['options']['attr']['class'] = $class.'widget-'.$attrs['widget'];
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
                $filters[$field]['name'] = $fields[$field]['name'];
                $filters[$field]['type'] = $fields[$field]['type'];
                $filters[$field]['options'] = array_replace($attrs['options'], array('required' => false));
                if (!isset($filters[$field]['widget'])) {
                    switch($filters[$field]['type']) {
                        case 'boolean':
                            $filters[$field]['widget'] = 'choice';
                            break;
                        case 'date':
                        case 'datetime':
                            $filters[$field]['widget'] = 'daterange';
                            break;
                        default:
                             $filters[$field]['widget'] = $fields[$field]['widget'];
                    }
                }

                switch($filters[$field]['widget']) {
                    case 'choice':
                        $filters[$field]['options'] = array_merge(
                            $filters[$field]['options'], array(
                                'choices' => array(1 => 'Yes', 0 => 'No', null => 'Both'),
                                'expanded' => true
                            )
                        );
                        break;
                    case 'entity':
                        $filters[$field]['options'] = array_merge(
                            $filters[$field]['options'],
                            array_intersect_key($fields[$field]['options'], array_flip(array('class', 'multiple', 'em')))
                        );
                        break;
                }

                if (!isset($attrs['label'])) {
                    $filters[$field]['label'] = Util::humanize($field);
                }
            }
        }
    }

    private function setShowFieldsDefaults()
    {
        foreach ($this->modelNames as $model) {
            $fields = $this->config['models'][$model]['fields'];
            $options =& $this->config['models'][$model]['show'];
            $showFields =& $options['fields'];

            if (count($showFields) == 0) {
                $keys = array_keys($fields);
                $showFields = array_fill_keys($keys, null);
            }

            foreach ($showFields as $field => $attrs) {
                $showFields[$field]['name'] = $field;
                $type = $fields[$field]['type'];
                $showFields[$field]['type'] = $type;
                $showFields[$field]['get_method'] = $fields[$field]['get_method'];

                if (!isset($attrs['label'])) {
                    $showFields[$field]['label'] = $options['auto_labels'] ? Util::humanize($field) : $model.'.field.'.$field;
                }

                if (('date' == $type || 'datetime' == $type) && !isset($attrs['format'])) {
                    $showFields[$field]['format'] = 'j/M/Y';
                }
            }
        }
    }

    private function createServiceDefinitions(ContainerBuilder $container)
    {
        foreach ($this->config['models'] as $model => $options) {
            $this->createConfigurationDefinition($model, $container);
            $this->createActionsDefinition($model, $options, $container);
            $this->createModelManagerDefinition($model, $options, $container);
            $this->createListDefinition($model, $options['list'], $container);
            $this->createFormDefinition($model, $options['form'], $container);
            $this->createFilterDefinition($model, $options['filter'], $container);
            $this->createViewerDefinition($model, $options['show'], $container);
        }
    }

    private function createConfigurationDefinition($model, ContainerBuilder $container)
    {
        $container->setDefinition(sprintf('lyra_admin.%s.configuration', $model), new DefinitionDecorator('lyra_admin.configuration.abstract'))
            ->setArguments(array(new Parameter(sprintf('lyra_admin.%s.options', $model))));
    }

    private function createActionsDefinition($model, $options, ContainerBuilder $container)
    {
        $container->setDefinition(sprintf('lyra_admin.%s.actions', $model), new DefinitionDecorator('lyra_admin.action_collection'))
            ->setArguments(array($options['actions']));
    }

    private function createModelManagerDefinition($model, $options, ContainerBuilder $container)
    {
        $container->setDefinition(sprintf('lyra_admin.%s.model_manager', $model), new DefinitionDecorator($options['services']['model_manager']))
            ->setArguments(array(new Reference('doctrine.orm.entity_manager'), new Reference(sprintf('lyra_admin.%s.configuration', $model))));

    }

    private function createListDefinition($model, $options, ContainerBuilder $container)
    {
        $states = array(
            'column' => $options['default_sort']['column'],
            'order' => $options['default_sort']['order'],
            'page' => 1,
            'criteria' => array()
        );

        $listState = new DefinitionDecorator('lyra_admin.user_state');
        $listState
            ->replaceArgument(1, $states)
            ->replaceArgument(2, $model)
            ->setPublic(false);
        $container->setDefinition(sprintf('lyra_admin.%s.user_state', $model), $listState);

        $queryBuilder = new DefinitionDecorator('lyra_admin.query_builder');
        $queryBuilder
            ->setArguments(array(new Reference(sprintf('lyra_admin.%s.model_manager', $model))))
            ->setPublic(false);
        $container->setDefinition(sprintf('lyra_admin.%s.query_builder', $model), $queryBuilder);

        $pager = new DefinitionDecorator('lyra_admin.pager');
        $pager
            ->addMethodCall('setMaxRows', array($options['max_page_rows']))
            ->setPublic(false);
        $container->setDefinition(sprintf('lyra_admin.%s.pager', $model), $pager);

        $manager = new DefinitionDecorator('lyra_admin.security_manager');
        $manager
            ->addMethodCall('setModelName', array($model))
            ->setPublic(false);
        $container->setDefinition(sprintf('lyra_admin.%s.security_manager', $model), $manager);

        $columns = new DefinitionDecorator('lyra_admin.column_collection');
        $columns->setPublic(false);
        $container->setDefinition(sprintf('lyra_admin.%s.grid_columns', $model), $columns);

        $types = array('list_actions', 'object_actions', 'batch_actions', 'other_actions');
        foreach ($types as $type) {
            $this->createCollectionDefinition($model, $type, $options[$type], $container);
        }

        $container->setDefinition(sprintf('lyra_admin.%s.grid', $model), new DefinitionDecorator('lyra_admin.grid.abstract'))
            ->replaceArgument(0, new Reference(sprintf('lyra_admin.%s.pager', $model)))
            ->replaceArgument(1, new Reference(sprintf('lyra_admin.%s.query_builder', $model)))
            ->replaceArgument(2, new Reference(sprintf('lyra_admin.%s.security_manager', $model)))
            ->addMethodCall('setModelName', array($model))
            ->addMethodCall('setState', array(new Reference(sprintf('lyra_admin.%s.user_state', $model))))
            ->addMethodCall('setTitle', array($options['title']))
            ->addMethodCall('setTemplate', array($options['template']))
            ->addMethodCall('setTransDomain', array($options['trans_domain']))
            ->addMethodCall('setDefaultSort', array($options['default_sort']))
            ->addMethodCall('setColumns', array(new Reference(sprintf('lyra_admin.%s.grid_columns', $model))))
            ->addMethodCall('setActions', array(new Reference(sprintf('lyra_admin.%s.other_actions.collection', $model))))
            ->addMethodCall('setListActions', array(new Reference(sprintf('lyra_admin.%s.list_actions.collection', $model))))
            ->addMethodCall('setObjectActions', array(new Reference(sprintf('lyra_admin.%s.object_actions.collection', $model))))
            ->addMethodCall('setBatchActions', array(new Reference(sprintf('lyra_admin.%s.batch_actions.collection', $model))));
    }

    private function createFormDefinition($model, $options, ContainerBuilder $container)
    {
        $this->createCollectionDefinition($model, 'form_actions', $options['actions'], $container);
        $this->createCollectionDefinition($model, 'form_new_actions', $options['new']['actions'], $container);
        $this->createCollectionDefinition($model, 'form_edit_actions', $options['edit']['actions'], $container);

        $container->setDefinition(sprintf('lyra_admin.%s.form', $model), new DefinitionDecorator('lyra_admin.form.abstract'))
            ->addMethodCall('setModelName', array($model))
            ->addMethodCall('setTemplate', array($options['template']))
            ->addMethodCall('setTitle', array($options['new']['title'], $options['edit']['title']))
            ->addMethodCall('setTransDomain', array($options['trans_domain']))
            ->addMethodCall('setClass', array($options['class']))
            ->addMethodCall('setDataClass', array($options['data_class']))
            ->addMethodCall('setGroups', array($options['groups']))
            ->addMethodCall('setActions', array(new Reference(sprintf('lyra_admin.%s.form_actions.collection', $model))))
            ->addMethodCall('setNewActions', array(new Reference(sprintf('lyra_admin.%s.form_new_actions.collection', $model))))
            ->addMethodCall('setEditActions', array(new Reference(sprintf('lyra_admin.%s.form_edit_actions.collection', $model))))
        ;
    }

    private function createFilterDefinition($model, $options, ContainerBuilder $container)
    {
        $states = array(
            'criteria' => array()
        );

        $state = new DefinitionDecorator('lyra_admin.user_state');
        $state
            ->replaceArgument(1, $states)
            ->replaceArgument(2, $model)
            ->setPublic(false);
        $container->setDefinition(sprintf('lyra_admin.%s.filter_state', $model), $state);

        $this->createCollectionDefinition($model, 'filter_actions', $options['actions'], $container);

        $container->setDefinition(sprintf('lyra_admin.%s.filter', $model), new DefinitionDecorator('lyra_admin.filter.abstract'))
            ->replaceArgument(1, new Reference(sprintf('lyra_admin.%s.model_manager', $model)))
            ->addMethodCall('setModelName', array($model))
            ->addMethodCall('setTitle', array($options['title']))
            ->addMethodCall('setTransDomain', array($options['trans_domain']))
            ->addMethodCall('setState', array(new Reference(sprintf('lyra_admin.%s.filter_state', $model))))
            ->addMethodCall('setActions', array(new Reference(sprintf('lyra_admin.%s.filter_actions.collection', $model))));
    }

    private function createViewerDefinition($model, $options, ContainerBuilder $container)
    {
        $container->setDefinition(sprintf('lyra_admin.%s.viewer', $model), new DefinitionDecorator('lyra_admin.viewer.abstract'))
            ->addMethodCall('setModelName', array($model))
            ->addMethodCall('setTitle', array($options['title']));
    }

    private function createCollectionDefinition($model, $type, $options, ContainerBuilder $container)
    {
        $collection = new DefinitionDecorator('lyra_admin.action_collection');
        $collection
            ->setArguments(array($options))
            ->setPublic(false);

        $container->setDefinition(sprintf('lyra_admin.%s.%s.collection', $model, $type), $collection);
    }

    private function updateServiceDefinitions(ContainerBuilder $container)
    {
        foreach ($this->config['models'] as $model => $options) {

            if(isset($options['entity_manager'])) {
                $container->getDefinition(sprintf('lyra_admin.%s.model_manager', $model))
                    ->replaceArgument(0, new Reference($options['entity_manager']['id']));
            }

            $container->getDefinition(sprintf('lyra_admin.%s.grid_columns', $model))
                ->setArguments(array($options['list']['columns']));

            $container->getDefinition(sprintf('lyra_admin.%s.filter', $model))
                ->addMethodCall('setFields', array($options['filter']['fields']));

            $container->getDefinition(sprintf('lyra_admin.%s.form', $model))
                ->addMethodCall('setFields', array($options['fields']));

            $container->getDefinition(sprintf('lyra_admin.%s.query_builder', $model))
                ->addMethodCall('setFields', array($options['fields']));

            $container->getDefinition(sprintf('lyra_admin.%s.viewer', $model))
                ->addMethodCall('setFields', array($options['show']['fields']));
        }

        // Twig extension
        $options = array('theme_path' => $this->config['theme']);
        $container->getDefinition('twig.extension.lyra.jquery')
            ->replaceArgument(1, $options);
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
                if (isset($attrs['route_pattern']) && !isset($attrs['alias'])) {
                    $routes['models'][$model]['actions'][$action] = array(
                        'route_pattern' => $attrs['route_pattern'],
                        'route_defaults' => $attrs['route_defaults']
                    );
                }
            }
        }

        $container->setParameter('lyra_admin.routes', $routes);
    }

    private function setSecurityManagerOptions(ContainerBuilder $container)
    {
        $securityActions = array();

        foreach ($this->modelNames as $model) {
            $actions = $this->config['models'][$model]['actions'];
            foreach ($actions as $action => $attrs) {
                if (isset($attrs['roles']) && count($attrs['roles']) > 0) {
                    $securityActions[$model][$action] = $attrs['roles'];
                }
            }
        }

        $container->setParameter('lyra_admin.security_actions', $securityActions);
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
        if (!$container->has('doctrine')) {
            return;
        }

        $emNames = $container->get('doctrine')->getEntityManagerNames();

        foreach ($this->config['models'] as $model => $options) {
            if ($info = $this->getEntityManagerInfoForClass($container, $options['class'])) {
                // Removes entity manager from info array, we need only name, id in model config
                $em = array_shift($info);
                $this->metadata[$model] = $em->getClassMetadata($options['class']);
                $this->config['models'][$model]['entity_manager'] = $info;
            }
        }
    }

    private function getEntityManagerInfoForClass(ContainerBuilder $container, $class)
    {
        $emNames = $container->get('doctrine')->getEntityManagerNames();
        foreach ($container->get('doctrine')->getEntityManagerNames() as $name => $id) {
            $em = $container->get($id);
            if (!$em->getConfiguration()->getMetadataDriverImpl()->isTransient($class)) {
                return array('em' => $em, 'name' => $name, 'id' => $id);
            }
        }
    }
}
