<?php

/*
 * This file is part of the LyraAdminBundle package.
 *
 * Copyright 2011 Massimo Giagnoni <gimassimo@gmail.com>
 *
 * This source file is subject to the MIT license. Full copyright and license
 * information are in the LICENSE file distributed with this source code.
 */

namespace Lyra\AdminBundle\Tests\Fixture\Entity;

class Dummy
{
    private $field1;

    public function setField1($value)
    {
        $this->field1 = $value;
    }

    public function getField1()
    {
        return $this->field1;
    }
}
