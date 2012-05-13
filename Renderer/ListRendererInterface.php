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

use Lyra\AdminBundle\UserState\UserStateInterface;

interface ListRendererInterface
{
    /**
     * Sets user state service.
     *
     * @param \Lyra\AdminBundle\UserState\UserStateInterface $state
     */
    function setState(UserStateInterface $state);

    /**
     * Gets user state service.
     *
     * @return \Lyra\AdminBundle\UserState\UserStateInterface
     */
    function getState();

    /**
     * Sets list template.
     *
     * @param string $template
     */
    function setTemplate($template);

    /**
     * Gets list template.
     *
     * @return string
     */
    function getTemplate();

    /**
     * Sets list title (header).
     *
     * @param string $title
     */
    function setTitle($title);

    /**
     * Gets list title (header).
     *
     * @return string
     */
    function getTitle();

    /**
     * Sets the list translation domain.
     *
     * Used in templates to translate list title, headers.
     *
     * @param string $transDomain
     */
    function setTransDomain($transDomain);

    /**
     * Gets the list translation domain.
     *
     * @return string
     */
    function getTransDomain();

    /**
     * Sets list columns configuration options.
     *
     * @param array $columns
     */
    function setColumns($columns);

    /**
     * Gets list columns configuration options.
     *
     * @return array
     */
    function getColumns();

    /**
     * Sets list batch actions names.
     *
     * @param array $actions
     */
    function setBatchActions($actions);

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
     * Sets list object action names.
     *
     * @param array $actions
     */
    function setObjectActions($actions);

    /**
     * Gets list object actions names.
     *
     * @return array
     */
    function getObjectActions();

    /**
     * Sets list actions names.
     *
     * @param array $actions
     */
    function setListActions($actions);

    /**
     * Gets list actions names.
     *
     * @return array
     */
    function getListActions();

    /**
     * Sets full configuration options of all list actions
     *
     * @param array $actions assoc array with action name as key, options as value
     */
    function setActions($actions);

    /**
     * Gets configuration options of all list actions.
     *
     * @return array
     */
    function getActions();

    /**
     * Sets current sort column and sort direction (asc/desc).
     *
     * @param array $sort array('column' => $sortCol, 'order' => $sortDir)
     */
    function setSort(array $sort);

    /**
     * Gets current sort column and sort direction (asc/desc).
     *
     * @return array array('column' => $sortCol, 'order' => $sortDir)
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

    /**
     * Checks if an action is allowed.
     *
     * @param string $action action name
     *
     * @return Boolean
     */
    function isActionAllowed($action);
}
