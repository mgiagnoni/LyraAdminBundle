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

interface ShowRendererInterface
{
    /**
     * Sets the instance of the record to display.
     *
     * @param mixed $object
     */
    function setObject($object);

    /**
     * Gets the instance of the record to display.
     *
     * @return mixed
     */
    function getObject();

    /**
     * Gets the dialog title from configuration.
     *
     * @return string.
     */
    function getTitle();

    /**
     * Gets the fields to display from configuration.
     *
     * @return array
     */
    function getFields();

    /**
     * Gets a field value.
     *
     * @param string $field
     *
     * @return mixed
     */
    function getFieldValue($field);
}
