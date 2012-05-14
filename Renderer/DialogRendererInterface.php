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
     * Sets actions configuration for dialog.
     *
     * @param array $actions
     */
    function setActions($actions);

    /**
     * Gets actions configuration for dialog.
     *
     * @return array
     */
    function getActions();

    /**
     * Sets dialog action.
     *
     * @param string action action name
     */
    function setAction($action);

    /**
     * Gets dialog action
     *
     * @return string
     */
    function getAction();

    /**
     * Sets the dialog translation domain.
     *
     * Used in templates to translate dialog title, message.
     *
     * @param string $transDomain
     */
    function setTransDomain($transDomain);

    /**
     * Gets the dialog translation domain.
     *
     * @return string
     */
    function getTransDomain();

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


