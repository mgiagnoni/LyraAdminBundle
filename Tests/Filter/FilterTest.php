<?php

/*
 * This file is part of the LyraAdminBundle package.
 *
 * Copyright 2011-2012 Massimo Giagnoni <gimassimo@gmail.com>
 *
 * This source file is subject to the MIT license. Full copyright and license
 * information are in the LICENSE file distributed with this source code.
 */

namespace Lyra\AdminBundle\Tests\Filter;

use Lyra\AdminBundle\Filter\Filter;

class FilterTest extends \PHPUnit_Framework_TestCase
{
    private $filter;
    private $factory;
    private $state;

    public function testGetForm()
    {
        $this->factory->expects($this->once())
            ->method('createForm');

        $this->filter->getForm();
    }

    public function testGetView()
    {
        $form = $this->getMockBuilder('Symfony\Component\Form\Form')
            ->disableOriginalConstructor()
            ->getMock();

        $form->expects($this->once())
            ->method('createView');

        $this->factory->expects($this->once())
            ->method('createForm')
            ->will($this->returnValue($form));

        $this->filter->getView();
    }

    public function testSetCriteria()
    {
        $this->filter->setFields(array(
            'field1' => array('type' => 'text'),
            'field2' => array('type' => 'text'),
            'field3' => array('type' => 'date'),
            'field4' => array('type' => 'date'),
            'field5' => array('type' => 'date'),
            'field6' => array('type' => 'boolean'),
            'field7' => array('type' => 'boolean')
        ));

        $criteria = array(
            'field1' => 'val1',
            'field2' => null,
            'field3' => array('from' => '01/01/2012', 'to' => null),
            'field4' => array('from' => null, 'to' => '01/01/2012'),
            'field5' => array('from' => null, 'to' => null),
            'field6' => '',
            'field7' => 1
        );

         $expected = array(
            'field1' => 'val1',
            'field3' => array('from' => '01/01/2012', 'to' => null),
            'field4' => array('from' => null, 'to' => '01/01/2012'),
            'field7' => 1
        );

        $this->state->expects($this->once())
            ->method('set')
            ->with('criteria', $expected);

        $this->filter->setCriteria($criteria);
    }

    public function testHasFields()
    {
        $this->filter->setFields(array(
            'field1' => array()
        ));

        $this->assertTrue($this->filter->hasFields());

        $this->filter->setFields(array());

        $this->assertFalse($this->filter->hasFields());
    }

    public function testHasWidget()
    {
        $this->filter->setFields(array(
            'field1' => array('widget' => 'widget1'),
            'field2' => array('widget' => 'daterange', 'options' => array('child_widget' => 'widget2'))
        ));

        $this->assertTrue($this->filter->hasWidget('widget1'));
        $this->assertTrue($this->filter->hasWidget('widget2'));
        $this->assertFalse($this->filter->hasWidget('nope'));
    }

    protected function setUp()
    {
        $this->factory = $this->getMockBuilder('Lyra\AdminBundle\FormFactory\AdminFormFactory')
            ->disableOriginalConstructor()
            ->getMock();

        $this->state = $this->getMock('Lyra\AdminBundle\UserState\UserStateInterface');

        $manager = $this->getMock('Lyra\AdminBundle\Model\ModelManagerInterface');

        $this->filter = new Filter($this->factory, $manager);
        $this->filter->setState($this->state);
    }
}
