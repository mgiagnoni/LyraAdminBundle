<?php

/*
 * This file is part of the LyraAdminBundle package.
 *
 * Copyright 2011 Massimo Giagnoni <gimassimo@gmail.com>
 *
 * This source file is subject to the MIT license. Full copyright and license
 * information are in the LICENSE file distributed with this source code.
 */

namespace Lyra\AdminBundle\Tests\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Lyra\AdminBundle\DependencyInjection\LyraAdminExtension;
use Symfony\Component\Yaml\Parser;

class LyraAdminExtensionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @expectedException \Symfony\Component\Config\Definition\Exception\InvalidConfigurationException
     */
    public function testThrowsExceptionUnlessClassSet()
    {
        $yaml = <<<EOF
models:
    test:
        controller: AcmeMyBundle:Test
EOF;
        $config = $this->getConfiguration($yaml);
    }

    public function testClassParameter()
    {
        $config = $this->getConfiguration();
        $this->assertEquals('Lyra\AdminBundle\Tests\Fixture\Entity\Dummy', $config->getParameter('lyra_admin.test.class'));
    }

    public function testDefaultActions()
    {
        $config = $this->getConfiguration();
        $options = $config->getParameter('lyra_admin.test.options');
        $this->assertEquals($options['actions'], $this->getActionDefaults());
    }

    public function testOverrideAction()
    {
       $yaml = <<<EOF
actions:
    edit:
        icon: dummy
EOF;

        $config = $this->getConfiguration($yaml);
        $defaults = $this->getActionDefaults();
        $defaults['edit']['icon'] = 'dummy';

        $options = $config->getParameter('lyra_admin.options');
        $this->assertEquals($options['actions'], $defaults);
    }

    public function testOverrideModelAction()
    {
        $yaml = <<<EOF
models:
    test:
        class: Lyra\AdminBundle\Tests\Fixture\Entity\Dummy
        controller: AcmeMyBundle:Test
        actions:
            new:
                icon: dummy
            delete:
                route_pattern: dummy
EOF;

        $config = $this->getConfiguration($yaml);
        $defaults = $this->getActionDefaults();
        $defaults['new']['icon'] = 'dummy';
        $defaults['delete']['route_pattern'] = 'dummy';

        $options = $config->getParameter('lyra_admin.test.options');
        $this->assertEquals($options['actions'], $defaults);
    }

    public function testNormalizeThemeOption()
    {
        $yaml = <<<EOF
theme: smoothness
EOF;

        $config = $this->getConfiguration($yaml);

        $options = $config->getParameter('lyra_admin.options');
        $this->assertEquals('bundles/lyraadmin/css/ui/smoothness', $options['theme']);

        $yaml = <<<EOF
theme: css/ui/redmond
EOF;

        $config = $this->getConfiguration($yaml);
        $options = $config->getParameter('lyra_admin.options');
        $this->assertEquals('css/ui/redmond', $options['theme']);
    }

    protected function getActionDefaults()
    {
        return array(
            'index' => array(
                'route_pattern' => 'list/{page}/{column}/{order}',
                'route_defaults' => array(
                    'page' => null,
                    'column' => null,
                    'order' => null
                ),
                'roles' => array()
            ),
            'new' => array(
                'route_pattern' => 'new',
                'route_defaults' => array(),
                'icon' => 'document',
                'text' => 'list.action.new',
                'trans_domain' => 'LyraAdminBundle',
                'roles' => array()
            ),
            'edit' => array(
                'route_pattern' => '{id}/edit',
                'route_defaults' => array(),
                'icon' => 'pencil',
                'text' => 'list.action.edit',
                'style' => 'icon-only',
                'trans_domain' => 'LyraAdminBundle',
                'roles' => array()
            ),
            'delete' => array(
                'route_pattern' => '{id}/delete',
                'route_defaults' => array(),
                'icon' => 'trash',
                'text' => 'list.action.delete',
                'style' => 'icon-only',
                'trans_domain' => 'LyraAdminBundle',
                'dialog' => array('title' => 'dialog.title.delete', 'message' => 'dialog.message.delete'),
                'roles' => array()
            ),
            'show' => array(
                'route_pattern' => '{id}/show',
                'route_defaults' => array(),
                'icon' => 'document',
                'text' => 'list.action.show',
                'style' => 'icon-only',
                'trans_domain' => 'LyraAdminBundle',
                'roles' => array()
            ),
            'object' => array(
                'route_pattern' => 'object',
                'route_defaults' => array(),
                'roles' => array()
            ),
            'filter' => array(
                'route_pattern' => 'filter/{reset}',
                'route_defaults' => array(
                    'reset' => null
                ),
                'roles' => array()
            )
        );
    }

    protected function getConfiguration($yaml = null)
    {
        if (null === $yaml) {

        $yaml = <<<EOF
models:
    test:
        class: Lyra\AdminBundle\Tests\Fixture\Entity\Dummy
        controller: AcmeMyBundle:Test
EOF;
        }

        $parsed = $this->parseConfiguration($yaml);
        $loader = new LyraAdminExtension();
        $configuration = new ContainerBuilder();
        $loader->load(array($parsed), $configuration);
        $loader->configureFromMetadata($configuration);

        return $configuration;
    }

    protected function parseConfiguration($yaml)
    {
        $parser = new Parser();

        return $parser->parse($yaml);
    }
}
