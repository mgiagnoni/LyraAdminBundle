<?php

/*
 * This file is part of the LyraAdminBundle package.
 *
 * Copyright 2011 Massimo Giagnoni <gimassimo@gmail.com>
 *
 * This source file is subject to the MIT license. Full copyright and license
 * information are in the LICENSE file distributed with this source code.
 */

namespace Lyra\AdminBundle\UserState;

use Symfony\Component\HttpFoundation\Request;

interface UserStateInterface
{
    /**
     * Initializes persistent states values from request.
     *
     * @param \Symfony\Component\HttpFoundation\Request $request
     */
    function initFromRequest(Request $request);

    /**
     * Gets the value of a persistent state.
     *
     * @param string $state state id
     */
    function get($state);

    /**
     * Sets the value of a persistent state.
     *
     * @param string $state state id
     * @param mixed $value state value
     */
    function set($state, $value);
}

