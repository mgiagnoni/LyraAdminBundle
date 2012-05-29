<?php

/*
 * This file is part of the LyraAdminBundle package.
 *
 * Copyright 2011 Massimo Giagnoni <gimassimo@gmail.com>
 *
 * This source file is subject to the MIT license. Full copyright and license
 * information are in the LICENSE file distributed with this source code.
 */

namespace Lyra\AdminBundle\Tests;

use Lyra\AdminBundle\Grid\Grid;
use Lyra\AdminBundle\Grid\ColumnCollection;
use Lyra\AdminBundle\Action\ActionCollection;

class GridTest extends \PHPUnit_Framework_TestCase
{
    private $grid;
    private $securityManager;

    public function testSetColumns()
    {
        $columns = new ColumnCollection(array('test' => array()));
        $this->grid->setColumns($columns);

        $this->assertSame($columns, $this->grid->getColumns());
    }

    public function testGetColumn()
    {
        $columns = new ColumnCollection(array('test' => array()));
        $this->grid->setColumns($columns);
        $column = $columns['test'];

        $this->assertSame($column, $this->grid->getColumn('test'));
    }

    public function testGetBatchActions()
    {
        $actions = new ActionCollection(array('test' => array(), 'test1' => array()));
        $this->grid->setBatchActions($actions);
        $this->setSecurityExpectsAllow();

        $this->assertEquals($actions, $this->grid->getBatchActions());
    }

    public function testGetBatchActionsFiltered()
    {
        $actions = new ActionCollection(array('test' => array(), 'test1' => array()));
        $this->grid->setBatchActions($actions);
        $this->setSecurityExpectsDisallow();
        $actions = $this->grid->getBatchActions();

        $this->assertTrue($actions->has('test'));
        $this->assertFalse($actions->has('test1'));
    }

    public function testHasBatchActions()
    {
        $actions = new ActionCollection(array('test' => array()));
        $this->grid->setBatchActions($actions);
        $this->setSecurityExpectsAllow();

        $this->assertTrue($this->grid->hasBatchActions());

        $actions = new ActionCollection();
        $this->grid->setBatchActions($actions);

        $this->assertFalse($this->grid->hasBatchActions());
    }

    public function testGetBatchAction()
    {
        $actions = new ActionCollection(array('test' => array()));
        $this->grid->setBatchActions($actions);
        $action = $actions['test'];

        $this->assertSame($action, $this->grid->getBatchAction('test'));
    }

    public function testGetObjectActions()
    {
        $actions = new ActionCollection(array('test' => array(), 'test1' => array()));
        $this->grid->setObjectActions($actions);
        $this->setSecurityExpectsAllow();

        $this->assertEquals($actions, $this->grid->getObjectActions());
    }

    public function testGetObjectActionsFiltered()
    {
        $actions = new ActionCollection(array('test' => array(), 'test1' => array()));
        $this->grid->setObjectActions($actions);
        $this->setSecurityExpectsDisallow();
        $actions = $this->grid->getObjectActions();

        $this->assertTrue($actions->has('test'));
        $this->assertFalse($actions->has('test1'));
    }

    public function testGetListActions()
    {
        $actions = new ActionCollection(array('test' => array(), 'test1' => array()));
        $this->grid->setListActions($actions);
        $this->setSecurityExpectsAllow();

        $this->assertEquals($actions, $this->grid->getListActions());
    }

    public function testGetListActionsFiltered()
    {
        $actions = new ActionCollection(array('test' => array(), 'test1' => array()));
        $this->grid->setListActions($actions);
        $this->setSecurityExpectsDisallow();
        $actions = $this->grid->getListActions();

        $this->assertTrue($actions->has('test'));
        $this->assertFalse($actions->has('test1'));
    }

    public function testGetSort()
    {
        $state = $this->getMockBuilder('Lyra\AdminBundle\UserState\UserStateInterface')
            ->disableOriginalConstructor()
            ->getMock();

        $state
            ->expects($this->at(0))
            ->method('get')
            ->with('column')
            ->will($this->returnValue('sort_col'))
        ;

        $state
            ->expects($this->at(1))
            ->method('get')
            ->with('order')
            ->will($this->returnValue('desc'))
        ;

        $this->grid->setState($state);

        $columns = new ColumnCollection(array(
            'sort_col' => array('field' => 'sort_field'),
            'other_col' => array('field' => 'other_field')
        ));

        $this->grid->setColumns($columns);

        $this->assertEquals(array(
            'column' => 'sort_col',
            'order' => 'desc',
            'field' => 'sort_field'
        ), $this->grid->getSort());

    }

    public function testGetSortWithDefault()
    {
        $state = $this->getMockBuilder('Lyra\AdminBundle\UserState\UserStateInterface')
            ->disableOriginalConstructor()
            ->getMock();

        $state
            ->expects($this->at(0))
            ->method('get')
            ->with('column')
            ->will($this->returnValue(null))
        ;

        $state
            ->expects($this->at(1))
            ->method('get')
            ->with('order')
            ->will($this->returnValue('desc'))
        ;

        $this->grid->setState($state);
        $this->grid->setDefaultSort(array('field' => 'sort_field', 'order' => 'asc'));

         $this->assertEquals(array(
            'column' => null,
            'order' => 'asc',
            'field' => 'sort_field'
        ), $this->grid->getSort());
    }

    protected function setUp()
    {
        $pager = $this->getMock('Lyra\AdminBundle\Pager\PagerInterface');

        $queryBuilder = $this->getMockBuilder('Lyra\AdminBundle\QueryBuilder\QueryBuilder')
            ->disableOriginalConstructor()
            ->getMock();

        $this->securityManager = $this->getMockBuilder('Lyra\AdminBundle\Security\SecurityManager')
            ->disableOriginalConstructor()
            ->getMock();

        $this->grid = new Grid($pager, $queryBuilder, $this->securityManager);
    }

    private function setSecurityExpectsAllow()
    {
        $this->securityManager
            ->expects($this->any())
            ->method('isActionAllowed')
            ->will($this->returnValue(true))
        ;
    }

    private function setSecurityExpectsDisallow()
    {
        $this->securityManager
            ->expects($this->at(0))
            ->method('isActionAllowed')
            ->will($this->returnValue(true));

        $this->securityManager
            ->expects($this->at(1))
            ->method('isActionAllowed')
            ->will($this->returnValue(false))
        ;
    }

}
