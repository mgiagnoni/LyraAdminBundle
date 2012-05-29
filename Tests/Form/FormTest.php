<?php

/*
 * This file is part of the LyraAdminBundle package.
 *
 * Copyright 2011 Massimo Giagnoni <gimassimo@gmail.com>
 *
 * This source file is subject to the MIT license. Full copyright and license
 * information are in the LICENSE file distributed with this source code.
 */

namespace Lyra\AdminBundle\Tests\Form;

use Lyra\AdminBundle\Form\Form;
use Lyra\AdminBundle\Action\ActionCollection;

class FormTest extends \PHPUnit_Framework_TestCase
{
    protected $form;

    public function testGetGroups()
    {
        $this->form->setFields(array('field1' => array()));
        $this->form->setAction('new');

        $this->assertEquals(array(
            'main' => array(
                'caption' => null,
                'break_after' => false,
                'fields' => array('field1')
            )), $this->form->getGroups());
    }

    public function testGetGroupsSameNewEdit()
    {
        $this->form->setFields(array('field1' => array()));

        $groups = array(
            'test_group' => array(
                'fields' => array('field1')
            )
        );

        $this->form->setGroups($groups);

        $this->form->setAction('new');
        $this->assertEquals($groups, $this->form->getGroups());

        $this->form->setAction('edit');
        $this->assertEquals($groups, $this->form->getGroups());
    }

    public function testGetGroupsDiffNewEdit()
    {
        $this->form->setFields(array('field1' => array()));

        $groups = array(
            '_new' => array(
                'group_new' => array(
                    'fields' => array('field1')
                )
            ),
            '_edit' => array(
                'group_edit' => array(
                    'fields' => array('field2')
                )
            )
        );

        $this->form->setGroups($groups);

        $this->form->setAction('new');
        $this->assertEquals(array(
            'group_new' => array(
                'fields' => array('field1')
            )
        ), $this->form->getGroups());

        $this->form->setAction('edit');
        $this->assertEquals(array(
            'group_edit' => array(
                'fields' => array('field2')
            )
        ), $this->form->getGroups());
    }

    protected function setUp()
    {
        $factory = $this->getMockBuilder('Lyra\AdminBundle\FormFactory\AdminFormFactory')
            ->disableOriginalConstructor()
            ->getMock();

        $this->form = new Form($factory);
        $actions = new ActionCollection(array('new' => array(), 'edit' => array()));
        $this->form->setActions($actions);
    }
}
