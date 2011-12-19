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
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;

/**
 * Bundle extension class.
 */
class LyraAdminExtension extends Extension
{
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

            $routes['models'][$name]['controller'] = $options['controller'];
            $routes['models'][$name]['route_pattern_prefix'] = $options['route_pattern_prefix'];
            $routes['models'][$name]['route_prefix'] = $options['route_prefix'];

            foreach ($options['actions'] as $action => $attrs) {
                if (!isset($attrs['route_pattern'])) {
                    continue;
                }
                $routes['models'][$name]['actions'][$action]['route_pattern'] = $attrs['route_pattern'];
                $routes['models'][$name]['actions'][$action]['route_defaults'] = $attrs['route_defaults'];
            }

            // Options for menu

            $menu[$name]['route'] = $options['route_prefix'].'_index';
            $menu[$name]['title'] = isset($options['title']) ? $options['title'] : ucfirst($name);
            $menu[$name]['trans_domain'] = $options['trans_domain'];

            // Default columns options

            foreach ($options['list']['columns'] as $col => $attrs) {
                $options['list']['columns'][$col]['sorted'] = false;
                $options['list']['columns'][$col]['name'] = $col;

                if (!isset($attrs['label'])) {
                    if ($options['list']['auto_labels']) {
                        // Humanize label
                        $options['list']['columns'][$col]['label'] = ucfirst(strtolower(str_replace('_', ' ', $col)));
                    } else {
                        $options['list']['columns'][$col]['label'] = $name.'.list.'.$col;
                    }
                }

                if (!isset($attrs['property_name'])) {
                    $options['list']['columns'][$col]['property_name'] = $col;
                }

                if (isset($attrs['template'])) {
                    $options['list']['columns'][$col]['type'] = 'template';
                }

            }

            $container->setAlias(sprintf('lyra_admin.%s.model_manager', $name), $options['services']['model_manager']);
            $options['theme'] = $config['theme'];
            $container->setParameter(sprintf('lyra_admin.%s.options', $name), $options);
            $container->setParameter(sprintf('lyra_admin.%s.actions.options', $name), array_diff_key($options, array('form' => null,'list'=>null)));
            $container->setParameter(sprintf('lyra_admin.%s.list.options', $name), array_diff_key($options, array('form' => null)));
            $container->setParameter(sprintf('lyra_admin.%s.form.options', $name), array_diff_key($options, array('list' => null)));
            $container->setParameter(sprintf('lyra_admin.%s.filter.options', $name), array_diff_key($options, array('form' => null,'list' => null)));
            $container->setParameter(sprintf('lyra_admin.%s.class', $name), $options['class']);
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
}
