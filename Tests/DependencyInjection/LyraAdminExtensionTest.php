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
        $config = $this->getConfiguration();
        unset($config['models']['test']['class']);

        $loader = new LyraAdminExtension();
        $loader->load(array($config), new ContainerBuilder());
    }

    public function testClassParameter()
    {
        $config = $this->getConfiguration();
        $loader = new LyraAdminExtension();
        $this->configuration = new ContainerBuilder();
        $loader->load(array($config), $this->configuration);

        $this->assertParameter('Acme\MyBundle\Entity\MyEntity', 'lyra_admin.test.class');
    }

    public function testDefaultActions()
    {
        $config = $this->getConfiguration();
        $loader = new LyraAdminExtension();
        $this->configuration = new ContainerBuilder();
        $loader->load(array($config), $this->configuration);

        $options = $this->configuration->getParameter('lyra_admin.test.options');

        $this->assertEquals($options['actions'], $this->getActionDefaults());
    }

    public function testOverrideAction()
    {
       $yaml = <<<EOF
actions:
    edit:
        icon: dummy
EOF;
        $config = $this->parseConfiguration($yaml);
        $loader = new LyraAdminExtension();
        $this->configuration = new ContainerBuilder();
        $loader->load(array($config), $this->configuration);

        $defaults = $this->getActionDefaults();
        $defaults['edit']['icon'] = 'dummy';

        $options = $this->configuration->getParameter('lyra_admin.options');
        $this->assertEquals($options['actions'], $defaults);
    }

    public function testOverrideBuilderAction()
    {
        $yaml = <<<EOF
models:
    test:
        class: Acme\MyBundle\Entity\MyEntity
        controller: AcmeMyBundle:Test
        actions:
            new:
                icon: dummy
            delete:
                route_pattern: dummy
EOF;

        $config = $this->parseConfiguration($yaml);
        $loader = new LyraAdminExtension();
        $this->configuration = new ContainerBuilder();
        $loader->load(array($config), $this->configuration);

        $defaults = $this->getActionDefaults();
        $defaults['new']['icon'] = 'dummy';
        $defaults['delete']['route_pattern'] = 'dummy';

        $options = $this->configuration->getParameter('lyra_admin.test.options');
        $this->assertEquals($options['actions'], $defaults);
    }

    public function testNormalizeThemeOption()
    {
        $yaml = <<<EOF
theme: smoothness
EOF;

        $config = $this->parseConfiguration($yaml);
        $loader = new LyraAdminExtension();
        $this->configuration = new ContainerBuilder();
        $loader->load(array($config), $this->configuration);

        $options = $this->configuration->getParameter('lyra_admin.options');
        $this->assertEquals('bundles/lyraadmin/css/ui/smoothness', $options['theme']);

        //TODO: DRY
        $yaml = <<<EOF
theme: css/ui/redmond
EOF;

        $config = $this->parseConfiguration($yaml);
        $loader = new LyraAdminExtension();
        $this->configuration = new ContainerBuilder();
        $loader->load(array($config), $this->configuration);

        $options = $this->configuration->getParameter('lyra_admin.options');
        $this->assertEquals('css/ui/redmond', $options['theme']);
    }

    protected function getActionDefaults()
    {
        return array(
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
            )
        );
    }

    protected function getConfiguration()
    {
        $yaml = <<<EOF
models:
    test:
        class: Acme\MyBundle\Entity\MyEntity
        controller: AcmeMyBundle:Test
EOF;

        return $this->parseConfiguration($yaml);
    }

    protected function parseConfiguration($yaml)
    {
        $parser = new Parser();

        return $parser->parse($yaml);
    }

    private function assertParameter($value, $key)
    {
        $this->assertEquals($value, $this->configuration->getParameter($key));
    }
}
