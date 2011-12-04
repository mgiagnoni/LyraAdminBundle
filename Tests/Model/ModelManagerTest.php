<?php

/*
 * This file is part of the LyraAdminBundle package.
 *
 * Copyright 2011 Massimo Giagnoni <gimassimo@gmail.com>
 *
 * This source file is subject to the MIT license. Full copyright and license
 * information are in the LICENSE file distributed with this source code.
 */

namespace Lyra\AdminBundle\Tests\Model;

class ModelManagerTest extends \PHPUnit_Framework_TestCase
{
    public function testCreate()
    {
        $manager = $this->getMockForAbstractClass('Lyra\AdminBundle\Model\ModelManager');
        $manager->setClass('Lyra\AdminBundle\Tests\Fixture\Entity\Dummy');
        $object = $manager->create();

        $this->assertTrue($object instanceof \Lyra\AdminBundle\Tests\Fixture\Entity\Dummy);
    }
}
