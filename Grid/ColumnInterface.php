<?php

/*
 * This file is part of the LyraAdminBundle package.
 *
 * Copyright 2011 Massimo Giagnoni <gimassimo@gmail.com>
 *
 * This source file is subject to the MIT license. Full copyright and license
 * information are in the LICENSE file distributed with this source code.
 */

namespace Lyra\AdminBundle\Grid;

interface ColumnInterface
{
    /**
     * Gets the column name.
     *
     * @return boolean
     */
    function getName();

    /**
     * Sets the associated field name.
     *
     * @param string $fieldName
     */
    function setFieldName($fieldName);

    /**
     * Gets the associated field name.
     *
     * @return string
     */
    function getFieldName();

    /**
     * Sets the column data type.
     *
     * @param string $type
     */
    function setType($type);

    /**
     * Gets the column data type.
     *
     * @return string
     */
    function getType();

    /**
     * Sets the sortable flag.
     *
     * @param boolean $sortable
     */
    function setSortable($sortable);

    /**
     * Gets the sortable flag.
     *
     * @return boolean
     */
    function isSortable();

    /**
     * Sets the column label.
     *
     * @param string $label
     */
    function setLabel($label);

    /**
     * Gets the column label.
     *
     * @return string
     */
    function getLabel();

    /**
     * Sets the column format.
     *
     * @param string $format
     */
    function setFormat($format);

    /**
     * Gets the column format.
     *
     * @return string
     */
    function getFormat();

    /**
     * Sets the format function callback.
     *
     * @param string $formatFunction
     */
    function setFormatFunction($formatFunction);

    /**
     * Gets the format function callback.
     *
     * @return string
     */
    function getFormatFunction();

    /**
     * Sets the boolean action flag.
     *
     * @param boolean $booleanActions if true allows switching on/off boolean fields.
     */
    function setBooleanActions($booleanActions);

    /**
     * Checks the boolean action flag.
     *
     * @return boolean
     */
    function hasBooleanActions();

    /**
     * Sets the column template.
     *
     * @param string $template
     */
    function setTemplate($template);

    /**
     * Gets the column template.
     *
     * @return string
     */
    function getTemplate();

    /**
     * Sets the column get methods.
     *
     * Used to retrieve the column value from the object.
     *
     * @param array $methods array of method names
     */
    function setMethods($methods);

    /**
     * Gets the column get methods.
     *
     * @return array
     */
    function getMethods();

    /**
     * Gets the column value.
     *
     * @param mixed $object object bound to grid row
     *
     * @return mixed
     */
    function getValue($object);
}
