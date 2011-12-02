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

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;

/**
 * Bundle configuration
 */
class Configuration implements ConfigurationInterface
{
    /**
     * Generates the configuration tree.
     *
     * @return TreeBuilder
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();

        $rootNode = $treeBuilder->root('lyra_admin', 'array');

        $rootNode
            ->children()
                ->scalarNode('db_driver')->defaultValue('orm')->cannotBeEmpty()->end()
                ->scalarNode('route_pattern_prefix')->defaultValue('admin')->end()
                ->scalarNode('theme')
                    ->defaultValue('bundles/lyraadmin/css/ui/ui-lightness')
                    ->cannotBeEmpty()
                    ->beforeNormalization()
                        ->ifTrue(function($v) {return !empty($v) && false === strpos($v, '/');})
                        ->then(function($v) {
                            return 'bundles/lyraadmin/css/ui/'.$v;
                        })
                    ->end()
                ->end()
            ->end();

        $this->addActionsSection($rootNode);

        $models = $this->addModelsSection($rootNode);
        $this->addModelFieldsSection($models);
        $this->addModelActionsSection($models);

        $list = $this->addModelListSection($models);
        $this->addModelListColumnsSection($list);
        $this->addModelListActionsSection($list);

        $form = $this->addModelFormSection($models);
        $this->addModelFormGroupsSection($form);
        $this->addModelFormNewSection($form);
        $this->addModelFormEditSection($form);

        $filter = $this->addModelFilterSection($models);
        $this->addModelServicesSection($models);

        return $treeBuilder;
    }

    private function addActionsSection(ArrayNodeDefinition $node)
    {
        $actionDefaults = array(
            'index' => array(
                'route_pattern' => 'list/{page}/{field}/{order}',
                'route_defaults' => array(
                    'page' => null,
                    'field' => null,
                    'order' => null
                )
            ),
            'new' => array(
                'route_pattern' => 'new',
                'route_defaults' => array(),
                'icon' => 'document',
                'text' => 'list.action.new',
                'trans_domain' => 'LyraAdminBundle'
            ),
            'edit' => array(
                'route_pattern' => '{id}/edit',
                'route_defaults' => array(),
                'icon' => 'pencil',
                'text' => 'list.action.edit',
                'trans_domain' => 'LyraAdminBundle'
            ),
            'delete' => array(
                'route_pattern' => '{id}/delete',
                'route_defaults' => array(),
                'icon' => 'trash',
                'text' => 'list.action.delete',
                'trans_domain' => 'LyraAdminBundle',
                'dialog' => array('title' => 'dialog.title.delete', 'message' => 'dialog.message.delete')
            ),
            'object' => array(
                'route_pattern' => 'object',
                'route_defaults' => array()
            ),
            'filter' => array(
                'route_pattern' => 'filter/{reset}',
                'route_defaults' => array(
                    'reset' => null
                )
            )
        );

        $node
            ->children()
                ->arrayNode('actions')
                    ->addDefaultsIfNotSet()
                    ->useAttributeAskey('name')
                    ->beforeNormalization()
                        ->always()
                        ->then(function($v) use ($actionDefaults)
                        {
                            $actions = array();

                            foreach ($actionDefaults as $key => $options) {
                                if (isset($v[$key])) {
                                    $actions[$key] = array_merge($options, $v[$key]);
                                } else {
                                    $actions[$key] = $options;
                                }
                            }

                            return $actions;
                        }
                    )
                    ->end()
                    ->prototype('array')
                        ->children()
                            ->scalarNode('route_pattern')->end()
                            ->arrayNode('route_defaults')
                                ->useAttributeAsKey('name')
                                ->prototype('variable')->end()
                            ->end()
                            ->scalarNode('text')->end()
                            ->scalarNode('icon')->end()
                            ->scalarNode('trans_domain')->end()
                            ->arrayNode('dialog')
                                ->children()
                                    ->scalarNode('title')->end()
                                    ->scalarNode('message')->end()
                                ->end()
                            ->end()
                        ->end()
                    ->end()
                    ->defaultValue($actionDefaults)
                ->end()
            ->end();
    }

    private function addModelsSection(ArrayNodeDefinition $node)
    {
       return $node
            ->children()
                ->arrayNode('models')
                ->useAttributeAskey('name')
                ->prototype('array')
                    ->children()
                        ->scalarNode('class')->isRequired()->end()
                        ->scalarNode('controller')->cannotBeEmpty()->defaultValue('LyraAdminBundle:Admin')->end()
                        ->scalarNode('route_prefix')->end()
                        ->scalarNode('route_pattern_prefix')->end()
                        ->scalarNode('trans_domain')->defaultValue('LyraAdminBundle')->end()
                    ->end();
    }

    private function addModelFieldsSection(ArrayNodeDefinition $node)
    {
        $node
            ->children()
                 ->arrayNode('fields')
                    ->addDefaultsIfNotSet()
                    ->useAttributeAsKey('name')
                    ->prototype('array')
                        ->children()
                            ->scalarNode('label')->end()
                            ->scalarNode('type')->end()
                            ->scalarNode('get_method')->end()
                            ->arrayNode('options')
                                ->useAttributeAsKey('name')
                                ->prototype('variable')->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
            ->end();
    }

    private function addModelActionsSection(ArrayNodeDefinition $node)
    {
        $node
            ->children()
                ->arrayNode('actions')
                    ->addDefaultsIfNotSet()
                    ->useAttributeAskey('name')
                    ->prototype('array')
                        ->children()
                            ->scalarNode('route_pattern')->end()
                            ->arrayNode('route_defaults')
                                ->useAttributeAsKey('name')
                                ->prototype('variable')->end()
                            ->end()
                            ->scalarNode('text')->end()
                            ->scalarNode('icon')->end()
                            ->scalarNode('trans_domain')->end()
                            ->arrayNode('dialog')
                                ->children()
                                    ->scalarNode('title')->end()
                                    ->scalarNode('message')->end()
                                ->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
            ->end();
    }

    private function addModelListSection(ArrayNodeDefinition $node)
    {
        return $node
            ->children()
                ->arrayNode('list')
                ->addDefaultsIfNotSet()
                ->children()
                    ->scalarNode('template')->cannotBeEmpty()->defaultValue('LyraAdminBundle:Admin:index.html.twig')->end()
                    ->scalarNode('max_page_rows')->defaultValue(20)->end()
                    ->scalarNode('title')->defaultNull()->end()
                    ->scalarNode('auto_labels')->defaultTrue()->end()
                    ->arrayNode('default_sort')
                        ->addDefaultsIfNotSet()
                        ->children()
                            ->scalarNode('field')->defaultNull()->end()
                            ->scalarNode('order')->defaultValue('asc')->end()
                        ->end()
                    ->end()
                ->end();
    }

    private function addModelListColumnsSection(ArrayNodeDefinition $node)
    {
        $node
            ->children()
                ->arrayNode('columns')
                    ->useAttributeAskey('name')
                    ->prototype('array')
                        ->children()
                            ->scalarNode('type')->defaultNull()->end()
                            ->scalarNode('sortable')->defaultTrue()->end()
                            ->scalarNode('property_name')->end()
                            ->scalarNode('label')->end()
                            ->scalarNode('format')->defaultNull()->end()
                            ->scalarNode('format_function')->defaultNull()->end()
                            ->arrayNode('boolean_actions')
                                ->defaultValue(array('_boolean_on','_boolean_off'))
                                ->prototype('scalar')->end()
                            ->end()
                            ->scalarNode('template')->end()
                        ->end()
                    ->end()
                ->end()
            ->end();
    }

    private function addModelListActionsSection(ArrayNodeDefinition $node)
    {
        $node
            ->children()
                ->arrayNode('object_actions')
                    ->defaultValue(array('edit','delete'))
                    ->prototype('scalar')->end()
                ->end()
                ->arrayNode('batch_actions')
                    ->defaultValue(array('delete'))
                    ->prototype('scalar')->end()
                ->end()
                ->arrayNode('list_actions')
                    ->defaultValue(array('new'))
                    ->prototype('scalar')->end()
                ->end()
            ->end();
    }

    private function addModelFormSection(ArrayNodeDefinition $node)
    {
        return $node
            ->children()
                ->arrayNode('form')
                ->addDefaultsIfNotSet()
                 ->children()
                    ->scalarNode('template')->cannotBeEmpty()->defaultValue('LyraAdminBundle:Admin:form.html.twig')->end()
                    ->scalarNode('class')->cannotBeEmpty()->defaultValue('Lyra\AdminBundle\Form\AdminFormType')->end()
                ->end();
    }

    private function addModelFormGroupsSection(ArrayNodeDefinition $node)
    {
        $node
            ->children()
                ->arrayNode('groups')
                    ->useAttributeAsKey('name')
                    ->prototype('array')
                        ->children()
                            ->scalarNode('caption')->defaultNull()->end()
                            ->scalarNode('break_after')->defaultFalse()->end()
                            ->scalarNode('embed')->end()
                            ->arrayNode('fields')
                                ->prototype('scalar')->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
            ->end();
    }

    private function addModelFormNewSection(ArrayNodeDefinition $node)
    {
        $new = $node
            ->children()
                ->arrayNode('new')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->scalarNode('title')->defaultValue('New')->end()
                    ->end();
        $this->addModelFormGroupsSection($new);
    }

    private function addModelFormEditSection(ArrayNodeDefinition $node)
    {
        $edit = $node
            ->children()
                ->arrayNode('edit')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->scalarNode('title')->defaultValue('Edit')->end()
                    ->end();
        $this->addModelFormGroupsSection($edit);
    }

    private function addModelFilterSection(ArrayNodeDefinition $node)
    {
        $node
            ->children()
                ->arrayNode('filter')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->scalarNode('title')->defaultValue('Filter')->end()
                        ->arrayNode('fields')
                            ->useAttributeAsKey('name')
                            ->prototype('array')
                                ->children()
                                    ->scalarNode('widget')->end()
                                ->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
            ->end();
    }

    private function addModelServicesSection(ArrayNodeDefinition $node)
    {
        $node
            ->children()
                ->arrayNode('services')
                ->addDefaultsIfNotSet()
                ->children()
                    ->scalarNode('model_manager')->defaultValue('lyra_admin.default.model_manager')->end()
                ->end()
            ->end();
    }
}
