<?php

/*
 * This file is part of the LyraAdminBundle package.
 *
 * Copyright 2011 Massimo Giagnoni <gimassimo@gmail.com>
 *
 * This source file is subject to the MIT license. Full copyright and license
 * information are in the LICENSE file distributed with this source code.
 */

namespace Lyra\AdminBundle\Configuration;

interface AdminConfigurationInterface
{
    /**
     * Gets a configuration option.
     *
     * @param string $key option key
     *
     * @return mixed
     */
    function getOption($key);

    /**
     * Gets all fields configuration options.
     *
     * @return array
     */
    function getFieldsOptions();

    /**
     * Gets all the configuration options of a given field.
     *
     * @param string $field
     *
     * @return array
     */
    function getFieldOptions($field);

    /**
     * Gets a configuration option of a given field.
     *
     * @param string $field
     * @param string $key option key
     *
     * @return mixed
     */
    function getFieldOption($field, $key);

    /**
     * Gets a configuration option of a field of an associated model.
     *
     * @param string $assocModel
     * @param string $field
     * @param string $key option key
     *
     * @return mixed
     */
    function getAssocFieldOption($assocModel, $field, $key);

    /**
     * Gets a list configuration option.
     *
     * @param string $key option key
     *
     * @return mixed
     */
    function getListOption($key);

    /**
     * Gets all configuration options of a list column.
     *
     * @param string $column column name
     *
     * @return array
     */
    function getListColumnOptions($column);

    /**
     * Gets a list column configuration option.
     *
     * @param string $column column name
     * @param string $key option key
     *
     * @return mixed
     */
    function getListColumnOption($column, $key);

    /**
     * Gets all form configuration options.
     *
     * @return array
     */
    function getFormOptions();

    /**
     * Gets a form configuration option.
     *
     * @param string $key option key
     *
     * @return mixed
     */
    function getFormOption($key);

    /**
     * Gets a filter configuration option.
     *
     * @param string $key option key
     *
     * @return mixed
     */
    function getFilterOption($key);

    /**
     * Gets all the configuration options of an action.
     *
     * @param string $action action name
     *
     * @return array
     */
    function getActionOptions($action);

    /**
     * Gets an action configuration option.
     *
     * @param string $action action name
     * @param string $key option key
     *
     * @return mixed
     */
    function getActionOption($action, $key);

    /**
     * Gets a show configuration option.
     *
     * @param string $key option key
     *
     * @return mixed
     */
    function getShowOption($key);

    /**
     * Gets all the configuration options of show fields.
     *
     * @return array
     */
    function getShowFieldsOptions();

    /**
     * Gets all the configurations options of a given show field.
     *
     * @param string $field
     *
     * @return array
     */
    function getShowFieldOptions($field);

    /**
     * Gets a configuration option of a show field.
     *
     * @param string $field
     * @param string $key option key
     *
     * @return mixed
     */
    function getShowFieldOption($field, $key);
}
