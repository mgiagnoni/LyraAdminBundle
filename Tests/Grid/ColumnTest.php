<?php

/*
 * This file is part of the LyraAdminBundle package.
 *
 * Copyright 2011-2012 Massimo Giagnoni <gimassimo@gmail.com>
 *
 * This source file is subject to the MIT license. Full copyright and license
 * information are in the LICENSE file distributed with this source code.
 */

namespace Lyra\AdminBundle\Tests\Grid;

use Lyra\AdminBundle\Grid\Column;
use Lyra\AdminBundle\Tests\Fixture\Entity\Dummy;

class ColumnTest extends \PHPUnit_Framework_TestCase
{
    public function testGetValue()
    {
        $column = new Column('test');
        $column->setMethods(array('getField1'));
        $object = new Dummy;
        $object->setField1('val1');

        $this->assertEquals('val1', $column->getValue($object));
    }

    public function testGetValueWithFormat()
    {
        $column = new Column('test');
        $column->setMethods(array('getField1'));
        $column->setType('date');
        $column->setFormat('F d Y');
        $object = new Dummy;
        $object->setField1(new \DateTime('21-12-2012'));

        $this->assertEquals('December 21 2012', $column->getValue($object));
    }

}
