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

class FilterRendererTest extends \PHPUnit_Framework_TestCase
{
    protected $factory;
    protected $options;
    protected $metadata;

    public function testGetTitle()
    {
        $renderer = new FilterRenderer($this->factory, $this->options);
        $this->assertEquals('test', $renderer->getTitle());
    }

    public function testgetForm()
    {
        $this->factory->expects($this->once())
            ->method('createForm');

        $renderer = new FilterRenderer($this->factory, $this->options);
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

        $renderer = new FilterRenderer($this->factory, $this->options);
        $renderer->getView();
    }

    public function testGetFilterFields()
    {
        $renderer = new FilterRenderer($this->factory, $this->options);
        $renderer->setMetadata($this->metadata);

        $this->assertEquals(array(
            'field1' => array('type' => 'text'),
            'field2' => array('type' => 'boolean'),
        ), $renderer->getFilterFields());
    }

    public function testHasFields()
    {
        $renderer = new FilterRenderer($this->factory, $this->options);
        $this->assertTrue($renderer->hasFields());

        $this->options['filter']['fields'] = array();
        $renderer = new FilterRenderer($this->factory, $this->options);
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
                'field1' => array(),
                'field2' => array(),
            )
        ));

        $this->metadata = array(
            'field1' => array('type' => 'text'),
            'field2' => array('type' => 'boolean')
        );
    }

}
