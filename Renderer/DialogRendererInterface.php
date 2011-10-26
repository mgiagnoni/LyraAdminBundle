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

interface DialogRendererInterface
{
    /**
     * Sets form dialog action.
     *
     * @param string action action name
     */
    function setAction($action);

    /**
     * Gets form dialog action
     *
     * @return string
     */
    function getAction();

    /**
     * Gets dialog title.
     *
     * @return string
     */
    function getTitle();

    /**
     * Gets dialog message.
     *
     * @return string
     */
    function getMessage();
}


