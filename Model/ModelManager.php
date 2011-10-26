<?php

/*
 * This file is part of the LyraAdminBundle package.
 *
 * Copyright 2011 Massimo Giagnoni <gimassimo@gmail.com>
 *
 * This source file is subject to the MIT license. Full copyright and license
 * information are in the LICENSE file distributed with this source code.
 */

namespace Lyra\AdminBundle\Model;

/**
 * Generic model manager base class.
 */
abstract class ModelManager implements ModelManagerInterface
{
    public function setClass($class)
    {
        $this->class = $class;
    }

    public function getClass()
    {
        return $this->class;
    }

    public function create()
    {
        $class = $this->getClass();
        $object = new $class;

        return $object;
    }
}
