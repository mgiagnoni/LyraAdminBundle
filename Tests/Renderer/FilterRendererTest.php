<?php

/*
 * This file is part of the LyraAdminBundle package.
 *
 * Copyright 2011 Massimo Giagnoni <gimassimo@gmail.com>
 *
 * This source file is subject to the MIT license. Full copyright and license
 * information are in the LICENSE file distributed with this source code.
 */

namespace Lyra\AdminBundle\Tests\Renderer;

use Lyra\AdminBundle\Renderer\FilterRenderer;
use Lyra\AdminBundle\Configuration\AdminConfiguration;

class FilterRendererTest extends \PHPUnit_Framework_TestCase
{
    private $factory;
    private $options;
    private $configuration;

    public function testGetTitle()
    {
        $renderer = new FilterRenderer($this->factory, $this->configuration);
        $this->assertEquals('test', $renderer->getTitle());
    }

    public function testgetForm()
    {
        $this->factory->expects($this->once())
            ->method('createForm');

        $renderer = new FilterRenderer($this->factory, $this->configuration);
        $renderer->getForm();
    }

    public function testCreateFormView()
    {
        $form = $this->getMockBuilder('Symfony\Component\Form\Form')
            ->disableOriginalConstructor()
            ->getMock();

        $form->expects($this->once())
            ->method('createView');

        $this->factory->expects($this->once())
            ->method('createForm')
            ->will($this->returnValue($form));

        $renderer = new FilterRenderer($this->factory, $this->configuration);
        $renderer->getView();
    }

    public function testGetFilterFields()
    {
        $renderer = new FilterRenderer($this->factory, $this->configuration);

        $this->assertEquals(array(
            'field1' => array('type' => 'text'),
            'field2' => array('type' => 'boolean'),
        ), $renderer->getFilterFields());
    }

    public function testHasFields()
    {
        $renderer = new FilterRenderer($this->factory, $this->configuration);
        $this->assertTrue($renderer->hasFields());

        $this->options['filter']['fields'] = array();
        $configuration = new AdminConfiguration($this->options);
        $renderer = new FilterRenderer($this->factory, $configuration);
        $this->assertFalse($renderer->hasFields());
    }

    protected function setup()
    {
        $this->factory = $this->getMockBuilder('Lyra\AdminBundle\FormFactory\AdminFormFactory')
            ->disableOriginalConstructor()
            ->getMock();

        $this->options = array('filter' => array(
            'title' => 'test',
            'fields' => array(
                'field1' => array('type' => 'text'),
                'field2' => array('type' => 'boolean'),
            )
        ));

        $this->configuration = new AdminConfiguration($this->options);
    }
}
