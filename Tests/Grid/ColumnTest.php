<?php

/*
 * This file is part of the LyraAdminBundle package.
 *
 * Copyright 2011 Massimo Giagnoni <gimassimo@gmail.com>
 *
 * This source file is subject to the MIT license. Full copyright and license
 * information are in the LICENSE file distributed with this source code.
 */

namespace Lyra\AdminBundle\Tests\Grid;

use Lyra\AdminBundle\Grid\Column;

class ColumnTest extends \PHPUnit_Framework_TestCase
{
    public function testGetValue()
    {
        $column = new Column('test');
        $column->setMethods(array('getField1'));
        $object = new \Lyra\AdminBundle\Tests\Fixture\Entity\Dummy;
        $object->setField1('val1');

        $this->assertEquals('val1', $column->getValue($object));
    }
}
