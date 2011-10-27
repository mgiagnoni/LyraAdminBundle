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

use Lyra\AdminBundle\Renderer\ListRenderer;

class ListRendererTest extends \PHPUnit_Framework_TestCase
{
    private $renderer;

    public function testGetTemplate()
    {
        $this->assertEquals('test_template', $this->renderer->getTemplate());
    }

    public function testGetTitle()
    {
        $this->assertEquals('test_title', $this->renderer->getTitle());
    }

    public function testGetSort()
    {
        $session = $this->getMockSession(true);
        $renderer = new ListRenderer($this->getMockRequest(), $session, $this->getOptions());
        $renderer->setName('test');
        $renderer->setMetadata($this->getMetadata());

        $this->assertEquals(array('field' => 'test-1', 'order' => 'desc'), $renderer->getSort());
    }

    public function testGetColumns()
    {
        $this->assertEquals(array(
            'test-1' => array(
                'name' => 'test-1',
                'label' => 'Test 1',
                'type' => 'string',
                'class' => null,
                'th_class' => 'class="col-test-1 string"',
                'sorted' => null,
                'property_name' => 'test-1'
            ),
            'test-2' => array(
                'name' => 'test-2',
                'label' => 'Test 2',
                'type' => 'datetime',
                'class' => null,
                'th_class' => 'class="col-test-2 datetime"',
                'sorted' => null,
                'property_name' => 'test-2'
            )
        ), $this->renderer->getColumns());
    }

    public function testGetColumnsWithSort()
    {
        $session = $this->getMockSession(true);
        $renderer = new ListRenderer($this->getMockRequest(), $session, $this->getOptions());
        $renderer->setName('test');
        $renderer->setMetadata($this->getMetadata());

        $cols = $renderer->getColumns();

        $this->assertTrue($cols['test-1']['sorted']);
        $this->assertNull($cols['test-2']['sorted']);
        $this->assertEquals('desc', $cols['test-1']['sort']);
        $this->assertRegExp('/sorted-desc/', $cols['test-1']['th_class']);
    }

    public function testGetBatchActions()
    {
        $this->assertEquals(array('delete'), $this->renderer->getBatchActions());
    }

    public function testHasBatchActions()
    {
        $this->assertTrue($this->renderer->hasBatchActions());
    }

    public function testGetObjectActions()
    {
        $this->assertEquals(array('edit', 'delete'), $this->renderer->getObjectActions());
    }

    public function testGetListActions()
    {
        $this->assertEquals(array('new'), $this->renderer->getListactions());
    }

    public function testGetColValue()
    {
        $this->assertEquals('val-1', $this->renderer->getColValue('test-1', array('test-1' => 'val-1', 'test-2' => 'val-2')));
    }
    protected function setUp()
    {
        $this->renderer = new ListRenderer($this->getMockRequest(), $this->getMockSession(), $this->getOptions());
        $this->renderer->setMetadata($this->getMetadata());
        $this->renderer->setName('test');
    }

    private function getOptions()
    {
        $options = array(
            'fields' => array(),
            'list' => array(
                'title' => 'test_title',
                'template' => 'test_template',
                'columns' => array(
                    'test-1' => array(
                        'name' => 'test-1',
                        'label' => 'Test 1',
                        'type' => null,
                        'sorted' => null,
                        'property_name' => 'test-1'
                    ),
                    'test-2' => array(
                        'name' => 'test-2',
                        'label' => 'Test 2',
                        'type' => null,
                        'sorted' => null,
                        'property_name' => 'test-2'
                    )
                ),
                'object_actions' => array('edit', 'delete'),
                'batch_actions' => array('delete'),
                'list_actions' => array('new'),
                'fields' => array()
            )
        );

        return $options;
    }

    private function getMetadata()
    {
        $metadata = array(
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

        return $metadata;
    }

    private function getMockRequest()
    {
        return
            $this->getMockBuilder('Symfony\Component\HttpFoundation\Request')
                ->disableOriginalConstructor()
                ->getMock();
    }

    private function getMockSession($withExpects = false)
    {
        $session = $this->getMockBuilder('Symfony\Component\HttpFoundation\Session')
            ->disableOriginalConstructor()
            ->getMock();

        if ($withExpects) {
            $session->expects($this->at(0))
                ->method('get')
                ->with('test.field')
                ->will($this->returnValue('test-1'));

            $session->expects($this->at(1))
                ->method('get')
                ->with('test.sort.order')
                ->will($this->returnValue('desc'));
        }

        return $session;
    }
}
