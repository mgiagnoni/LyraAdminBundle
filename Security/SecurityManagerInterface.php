<?php

/*
 * This file is part of the LyraAdminBundle package.
 *
 * Copyright 2011 Massimo Giagnoni <gimassimo@gmail.com>
 *
 * This source file is subject to the MIT license. Full copyright and license
 * information are in the LICENSE file distributed with this source code.
 */

namespace Lyra\AdminBundle\Security;

interface SecurityManagerInterface
{
    /**
     * Sets the model name.
     *
     * Needed to retrieve security configuration for that model
     *
     * @param string $model model name
     */
    function setModelName($model);

    /**
     * Checks if an action is allowed.
     *
     * @param string $actionName
     *
     * @return boolean
     */
    function isActionAllowed($actionName);

    /**
     * Throws access denied exception if an action is not allowed.
     *
     * @param string $actionName
     *
     * @throws Symfony\Component\Security\Core\Exception\AccessDeniedException
     */
    function allowOr403($actionName);
}
