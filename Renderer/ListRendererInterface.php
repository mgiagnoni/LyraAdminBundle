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
     * Gets current sort field and sort direction (asc/desc).
     *
     * @return array array('field' => $sortField, 'order' => $sortDir)
     */
    function getSort();

    /**
     * Sets the base query builder used to retrieve list rows.
     *
     * @param mixed $queryBuilder
     */
    function setBaseQueryBuilder($queryBuilder);

    /**
     * Gets the total number of list rows.
     *
     * @return integer
     */
    function getTotal();

    /**
     * Gets list rows (for the current page)
     *
     * @return array query results are hydrated as array
     */
    function getResults();

    /**
     * Gets the total number of list pages.
     *
     * @return integer
     */
    function getNbPages();

    /**
     * Sets the current page number.
     *
     * @param integer $page
     */
    function setPage($page);

    /**
     * Gets the current page number.
     *
     * @return integer
     */
    function getPage();

    /**
     * Gets the previous page number.
     *
     * @return integer
     */
    function getPrevPage();

    /**
     * Gets the next page number.
     *
     * @return integer
     */
    function getNextPage();

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
     * Gets the name of boolean action (for a column of type 'boolean').
     *
     * @param string $columName
     * @param array $object current record
     *
     * @return string
     */
    function getBooleanAction($columnName, $object);

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
}
