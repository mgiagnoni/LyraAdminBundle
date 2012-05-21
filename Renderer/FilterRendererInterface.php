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
use Lyra\AdminBundle\Action\ActionCollectionInterface;

interface FilterRendererInterface
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
     * Sets the filter translation domain.
     *
     * Used in templates to translate list title, headers.
     *
     * @param string $transDomain
     */
    function setTransDomain($transDomain);

    /**
     * Gets the filter translation domain.
     *
     * @return string
     */
    function getTransDomain();

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
     * Sets filter actions.
     *
     * @param  \Lyra\AdminBundle\Action\ActionCollectionInterface
     */
    function setActions(ActionCollectionInterface $actions);

    /**
     * Gets filter actions.
     *
     * @return \Lyra\AdminBundle\Action\ActionCollectionInterface
     */
    function getActions();

    /**
     * Sets filter criteria
     *
     * @param array $criteria
     */
    function setCriteria($criteria);

    /**
     * Gets filter criteria
     *
     * @return array
     */
    function getCriteria();

    /**
     * Resets filter criteria.
     */
    function resetCriteria();

    /**
     * Sets search dialog title.
     *
     * @param string $title
     */
    function setTitle($title);

    /**
     * Gets search dialog title.
     *
     * @return string
     */
    function getTitle() ;

    /**
     * Gets search form.
     *
     * @return \Symfony\Component\Form\Form
     */
    function getForm();

    /**
     * Gets search form view.
     *
     * @return \Symfony\Component\Form\FormView
     */
    function getView();

    /**
     * Sets search form fields options.
     *
     * @param array $fields
     */
    function setFields($fields);

    /**
     * Gets search form fields options.
     *
     * @return array
     */
    function getFields();

    /**
     * Checks if filter fields are defined.
     *
     * @return boolean
     */
    function hasFields();

    /**
     * Check if search forms contains a given widget.
     *
     * @param $widget widget name
     *
     * @return boolean
     */
    function hasWidget($widget);
}
