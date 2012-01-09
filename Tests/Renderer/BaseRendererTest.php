<?php

/*
 * This file is part of the LyraContentBundle package.
 *
 * Copyright 2011 Massimo Giagnoni <gimassimo@gmail.com>
 *
 * This source file is subject to the MIT license. Full copyright and license
 * information are in the LICENSE file distributed with this source code.
 */

namespace Lyra\AdminBundle\Tests\Renderer;

use Lyra\AdminBundle\Configuration\AdminConfiguration;
use Lyra\AdminBundle\Renderer\BaseRenderer;

class BaseRendererTest extends \PHPUnit_Framework_TestCase
{
    private $renderer;

    public function testGetTransDomain()
    {
        $this->assertEquals('LyraAdminBundle', $this->renderer->getTransDomain());
    }

    public function testGetRoutePrefix()
    {
        $this->assertEquals('test_prefix', $this->renderer->getRoutePrefix());
    }

    public function testGetTheme()
    {
        $this->assertEquals('test_theme', $this->renderer->getTheme());
    }

    protected function setup()
    {
        $options = array(
            'trans_domain' => 'LyraAdminBundle',
            'route_prefix' => 'test_prefix',
            'theme' => 'test_theme',
        );

        $configuration = new AdminConfiguration($options);
        $this->renderer = new TestRenderer($configuration);
    }

}

class TestRenderer extends BaseRenderer {}
