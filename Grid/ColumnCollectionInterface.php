<?php

/*
 * This file is part of the LyraAdminBundle package.
 *
 * Copyright 2011-2012 Massimo Giagnoni <gimassimo@gmail.com>
 *
 * This source file is subject to the MIT license. Full copyright and license
 * information are in the LICENSE file distributed with this source code.
 */

namespace Lyra\AdminBundle\Grid;

interface ColumnCollectionInterface extends \ArrayAccess, \IteratorAggregate, \Countable
{
    /**
     * Gets a column of a given name.
     *
     * @param string $columnName
     *
     * @return \Lyra\AdminBundle\Grid\ColumnInterface
     */
    function get($columnName);

    /**
     * Adds a column to the collection.
     *
     * @param \Lyra\AdminBundle\Grid\ColumnInterface $column
     */
    function add(ColumnInterface $column);

    /**
     * Removes a column from the collection.
     *
     * @param string $columnName
     */
    function remove($columnName);

    /**
     * Checks if the collection contains a column of a given name.
     *
     * @param string $columnName
     *
     * @return boolean
     */
    function has($columnName);
}
