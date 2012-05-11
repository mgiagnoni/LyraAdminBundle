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

interface FilterRendererInterface
{
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
     * Gets search form fields options.
     *
     * @return array
     */
    function getFilterFields();

    /**
     * Checks if filter fields are defined.
     *
     * @return boolean
     */
    function hasFields();
}
