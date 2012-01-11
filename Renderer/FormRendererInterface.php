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

interface FormRendererInterface extends BaseRendererInterface
{
    /**
     * Sets form action.
     *
     * @param string $action (new/edit)
     */
    function setAction($action);

    /**
     * Gets form action.
     *
     * @return string
     */
    function getAction();

    /**
     * Gets form template.
     *
     * @return string
     */
    function getTemplate();

    /**
     * Gets the form object.
     *
     * @param array $data data passed to the form
     *
     * @return \Symfony\Component\Form\Form
     */
    function getForm($data);

    /**
     * Gets the form view object.
     *
     * @param string $field
     *
     * @return \Symfony\Component\Form\FormView
     */
    function getView($field);

    /**
     * Sets fields groups (fieldsets).
     *
     * @param array $groups
     */
    function setGroups(array $groups);

    /**
     * Gets fields groups (fieldsets).
     *
     * @return array
     */
    function getGroups();

    /**
     * Gets form title (header).
     *
     * @return string
     */
    function getTitle();
}
