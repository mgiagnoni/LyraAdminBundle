<?php

/*
 * This file is part of the LyraAdminBundle package.
 *
 * Copyright 2011-2012 Massimo Giagnoni <gimassimo@gmail.com>
 *
 * This source file is subject to the MIT license. Full copyright and license
 * information are in the LICENSE file distributed with this source code.
 */

namespace Lyra\AdminBundle\Form;

use Lyra\AdminBundle\Action\ActionCollectionInterface;

interface FormInterface
{
    /**
     * Sets the model name.
     *
     * @param string $modelName
     */
    function setModelName($modelName);

    /**
     * Gets the model name.
     *
     * @return string
     */
    function getModelName();

    /**
     * Sets form actions.
     *
     * @param \Lyra\AdminBundle\Action\ActionCollectionInterface $actions
     */
    function setActions(ActionCollectionInterface $actions);

    /**
     * Gets form actions.
     *
     * @return \Lyra\AdminBundle\Action\ActionCollectionInterface
     */
    function getActions();

    /**
     * Sets form action.
     *
     * This is the action executed when the form is submitted.
     *
     * @param string $actionName (new/edit)
     * @throws InvalidArgumentException if action named $actionName does not exist in form config
     */
    function setAction($actionName);

    /**
     * Gets form action.
     *
     * Action is set by name and retrieved as object.
     *
     * @return \Lyra\AdminBundle\Action\ActionInterface
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
     * Sets the data/object associated to the form.
     *
     * @param mixed $data
     */
    function setData($data);

    /**
     * Gets the data/object associated to the form.
     *
     * @return mixed
     */
    function getData();

    /**
     * Gets the form object.
     *
     * @param array $data data passed to the form
     *
     * @return \Symfony\Component\Form\Form
     */
    function create();

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
