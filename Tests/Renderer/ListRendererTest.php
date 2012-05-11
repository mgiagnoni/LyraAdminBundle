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
use Lyra\AdminBundle\Configuration\AdminConfiguration;

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

    public function testGetColumns()
    {
        $this->assertEquals(array(
            'test-1' => array(
                'field' => 'test-1',
                'type' => 'string',
                'name' => 'test-1',
                'label' => 'Test 1',
                'th_class' => 'sortable',
                'sorted' => null,
                'sortable' => true,
                'property_name' => 'test-1',
                'format' => null,
                'format_function' => null
            ),
            'test-2' => array(
                'field' => 'test-2',
                'type' => 'datetime',
                'name' => 'test-2',
                'label' => 'Test 2',
                'th_class' => '',
                'sorted' => null,
                'sortable' => false,
                'property_name' => 'test-2',
                'format' => 'j/M/Y',
                'format_function' => null
            )
        ), $this->renderer->getColumns());
    }

    public function testGetColumnsWithSort()
    {
        $this->renderer->setSort(array('column' => 'test-1', 'order' => 'desc'));
        $cols = $this->renderer->getColumns();

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
        $options = $this->getOptions();
        $options['fields']['test-1']['get_method'] = 'getField1';
        $options['fields']['test-1']['options'] = array();
        $pager = $this->getMock('Lyra\AdminBundle\Pager\PagerInterface');
        $configuration = new AdminConfiguration($options);
        $renderer = new ListRenderer($pager, $configuration);
        $renderer->setSort(array('column' => null, 'order' => null));
        $object = new \Lyra\AdminBundle\Tests\Fixture\Entity\Dummy;
        $object->setField1('val-1');
        $this->assertEquals('val-1', $renderer->getColValue('test-1', $object));
    }

    protected function setUp()
    {
        $pager = $this->getMock('Lyra\AdminBundle\Pager\PagerInterface');

        $this->renderer = new ListRenderer($pager, $this->getConfiguration());
        $this->renderer->setName('test');
        $this->renderer->setSort(array('column' => null, 'order' => null));
    }

    private function getOptions()
    {
        $options = array(
            'fields' => array(),
            'list' => array(
                'title' => 'test_title',
                'template' => 'test_template',
                'default_sort' => array('column' => null, 'field' => null, 'order' => 'asc'),
                'columns' => array(
                    'test-1' => array(
                        'field' => 'test-1',
                        'type' => 'string',
                        'name' => 'test-1',
                        'label' => 'Test 1',
                        'sorted' => null,
                        'property_name' => 'test-1',
                        'format' => null,
                        'format_function' => null,
                        'sortable' => true,
                        'th_class' => 'sortable'
                    ),
                    'test-2' => array(
                        'field' => 'test-2',
                        'type' => 'datetime',
                        'name' => 'test-2',
                        'label' => 'Test 2',
                        'sorted' => null,
                        'property_name' => 'test-2',
                        'format' => 'j/M/Y',
                        'format_function' => null,
                        'sortable' => false,
                        'th_class' => ''
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

    private function getConfiguration()
    {
        return new AdminConfiguration($this->getOptions());
    }
}
