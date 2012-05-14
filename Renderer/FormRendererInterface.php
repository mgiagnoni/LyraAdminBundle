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

interface FormRendererInterface
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
     * Sets form template.
     *
     * @param string $template
     */
    function setTemplate($template);

    /**
     * Gets form template.
     *
     * @return string
     */
    function getTemplate();

    /**
     * Sets the form translation domain.
     *
     * Used in templates to translate form title, labels.
     *
     * @param string $transDomain
     */
    function setTransDomain($transDomain);

    /**
     * Gets the form translation domain.
     *
     * @return string
     */
    function getTransDomain();

    /**
     * Sets form type class.
     *
     * @param string $class FQN of type class
     */
    function setClass($class);

    /**
     * Gets form type class.
     *
     * @return string
     */
    function getClass();

    /**
     * Sets form data class.
     *
     * @param string $dataClass FQN of data class
     */
    function setDataClass($dataClass);

    /**
     * Gets form data class.
     *
     * @return string
     */
    function getDataClass();

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
     * Sets the form title (new,edit).
     *
     * @param string $newTitle
     * @param string $editTitle
     */
    function setTitle($newTitle, $editTitle);

    /**
     * Gets form title (header).
     *
     * @return string edit or new title based on form action
     */
    function getTitle();

    /**
     * Sets fields configuration options.
     *
     * @param array $fields
     */
    function setFields($fields);

    /**
     * Gets fields configuration options.
     *
     * @return array
     */
    function getFields();

    /**
     * Checks if form contains a given widget.
     *
     * @param string $widget widget name
     */
    function hasWidget($widget);
}
