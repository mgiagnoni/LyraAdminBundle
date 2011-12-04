<?php

/*
 * This file is part of the LyraAdminBundle package.
 *
 * Copyright 2011 Massimo Giagnoni <gimassimo@gmail.com>
 *
 * This source file is subject to the MIT license. Full copyright and license
 * information are in the LICENSE file distributed with this source code.
 */

namespace Lyra\AdminBundle\Tests\Fixture\ORM;

use Doctrine\ORM\AbstractQuery;

/**
 * This class is needed because Doctrine\ORM\Query is declared
 * final and cannot be mocked.
 */
class DummyQuery extends AbstractQuery
{
    public function getSQL()
    {
    }

    /**
     * To not break fluent interface
     */
    public function setFirstResult()
    {
        return $this;
    }

    /**
     * To not break fluent interface
     */
    public function setMaxResults()
    {
        return $this;
    }

    protected function _doExecute()
    {
    }
}
