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

use Symfony\Component\Security\Core\SecurityContextInterface;

interface BaseRendererInterface
{
    /**
     * Sets the security context.
     *
     * @param SecurityContextInterface $securityContext
     */
    function setSecurityContext(SecurityContextInterface $securityContext);

    /**
     * Sets renderer name.
     *
     * @param string $name
     */
    function setName($name);

    /**
     * Gets renderer name.
     *
     * @return string
     */
    function getName();

    /**
     * Gets translation domain.
     *
     * @return string
     */
    function getTransDomain();

    /**
     * Gets route prefix.
     *
     * @return string
     */
    function getRoutePrefix();

    /**
     * Gets jQuery UI theme name.
     *
     * @return string
     */
    function getTheme();

    /**
     * Sets route parameters.
     *
     * @param array $routeParams
     */
    function setRouteParams(array $routeParams);

    /**
     * Gets route parameters.
     *
     * @return array
     */
    function getRouteParams();

    /**
     * Checks if an action is allowed.
     *
     * @param string $action action name
     *
     * @return Boolean
     */
    function isActionAllowed($action);
}
