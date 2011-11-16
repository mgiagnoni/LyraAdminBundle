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

class BaseRendererTest extends \PHPUnit_Framework_TestCase
{
    public function testGetFields()
    {
        $renderer = $this->getClassMock();

        $this->assertEquals(array(
            'test-1' => array(
                'name' => 'test-1',
                'type' => 'string',
                'length' => 255,
                'label' => 'test',
                'form' => null,
                'options' => array(),
                'get_method' => 'getTest-1'
            ),
            'test-2' => array(
                'name' => 'test-2',
                'type' => 'string',
                'form' => null,
                'options' => array(),
                'get_method' => 'getTest-2'
            )
        ), $renderer->getFields());
    }

    public function testGetTransDomain()
    {
        $renderer = $this->getClassMock();

        $this->assertEquals('LyraAdminBundle', $renderer->getTransDomain());
    }

    public function testGetRoutePrefix()
    {
        $renderer = $this->getClassMock();

        $this->assertEquals('test_prefix', $renderer->getRoutePrefix());
    }

    public function testGetTheme()
    {
        $renderer = $this->getClassMock();

        $this->assertEquals('test_theme', $renderer->getTheme());
    }

    private function getClassMock()
    {
        $mock = $this->getMockForAbstractClass('Lyra\AdminBundle\Renderer\BaseRenderer');

        $metadata = array(
            'id' => array(
                'name' => 'id',
                'type' => 'integer',
                'id' => true
            ),
            'test-1' => array(
                'name' => 'test-1',
                'type' => 'string',
                'length' => 255
            ),
            'test-2' => array(
                'name' => 'test-2',
                'type' => 'datetime'
            )
        );
        $mock->setMetadata($metadata);

        $options = array(
            'trans_domain' => 'LyraAdminBundle',
            'route_prefix' => 'test_prefix',
            'theme' => 'test_theme',
            'fields' => array(
                'test-1' => array('label' => 'test', 'options' => array()),
                'test-2' => array('type' => 'string', 'options' => array())
            )
        );
        $mock->setOptions($options);

        return $mock;
    }

}
