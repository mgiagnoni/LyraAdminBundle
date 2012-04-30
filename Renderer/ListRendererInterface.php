<?php

/*
 * This file is part of the LyraAdminBundle package.
 *
 * Copyright 2011 Massimo Giagnoni <gimassimo@gmail.com>
 *
 * This source file is subject to the MIT license. Full copyright and license
 * information are in the LICENSE file distributed with this source code.
 */

namespace Lyra\AdminBundle\Renderer;

interface ListRendererInterface extends BaseRendererInterface
{
    /**
     * Gets list template.
     *
     * @return string
     */
    function getTemplate();

    /**
     * Gets list title (header).
     *
     * @return string
     */
    function getTitle();

    /**
     * Gets list columns configuration options.
     *
     * @return array
     */
    function getColumns();

    /**
     * Gets list batch actions names.
     *
     * @return array
     */
    function getBatchActions();

    /**
     * Checks if list has batch actions.
     *
     * @return boolean
     */
    function hasBatchActions();

    /**
     * Gets list object actions names.
     *
     * @return array
     */
    function getObjectActions();

    /**
     * Gets list actions names.
     *
     * @return array
     */
    function getListActions();

    /**
     * Gets configuration options of all list actions.
     *
     * @return array
     */
    function getActions();

    /**
     * Sets current sort field and sort direction (asc/desc).
     *
     * @param array $sort array('field' => $sortField, 'order' => $sortDir)
     */
    function setSort(array $sort);

    /**
     * Gets current sort field and sort direction (asc/desc).
     *
     * @return array array('field' => $sortField, 'order' => $sortDir)
     */
    function getSort();

    /**
     * Gets a column value.
     *
     * @param string $columName
     * @param array $object current record
     *
     * @return mixed
     */
    function getColValue($columnName, $object);

    /**
     * Check if boolean actions exist for a given list column.
     *
     * @param string $columnName
     *
     * @return boolean
     */
    function hasBooleanActions($columnName);

    /**
     * Returns the icon for a boolean action.
     *
     * @param string $columnName
     * @param array $object current record
     *
     * @return string
     */
    function getBooleanIcon($columnName, $object);

    /**
     * Returns the text for a boolean action.
     *
     * @param string $columnName
     * @param array $object current record
     *
     * @return string
     */
    function getBooleanText($columnName, $object);

    /**
     * Gets the column format.
     *
     * @param $columnName
     *
     * @return string
     */
    function getColFormat($columnName);

    /**
     * Gets the value of a column configuration option.
     *
     * @param string $columnName
     * @param string $key option key
     *
     * @return mixed
     */
    function getColOption($colName, $key);
}
