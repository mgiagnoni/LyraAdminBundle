<?php

/*
 * This file is part of the LyraAdminBundle package.
 *
 * Copyright 2011-2012 Massimo Giagnoni <gimassimo@gmail.com>
 *
 * This source file is subject to the MIT license. Full copyright and license
 * information are in the LICENSE file distributed with this source code.
 */

namespace Lyra\AdminBundle\UserState;

use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Request;

/**
 * Manages 'states' persisted in user session.
 *
 * Some persistent states are list current page, sorted column, filter criteria.
 * All these values need to be persisted between different requests throughout
 * the user session. This prevents, for example, that list sort or selected filter
 * criteria are lost when users leave a list and then navigate back.
 */
class UserState implements UserStateInterface
{
    /**
     * @var \Symfony\Component\HttpFoundation\Session
     */
    protected $session;

    /**
     * @var array
     */
    protected $states;

    /**
     * @var string
     */
    protected $prefix;

    /**
     * @param \Symfony\Component\HttpFoundation\Session\Session $session
     * @param array $states key state id, value state default
     * @param string $prefix
     */
    public function __construct(Session $session, array $states = array(), $prefix = '')
    {
        $this->session = $session;
        $this->states = $states;
        $this->prefix = $prefix;
    }

    public function initFromRequest(Request $request)
    {
        foreach ($this->states as $state => $default) {
            if (null !== $value = $request->get($state)) {
                $this->session->set($this->prefix.'.'.$state, $value);
            }
        }
    }

    public function get($state)
    {
        if (!in_array($state, array_keys($this->states))) {
            throw new \RunTimeException("Requested state $state does not exist");
        }

        return $this->session->get($this->prefix.'.'.$state, $this->states[$state]);
    }

    public function set($state, $value)
    {
        if (!in_array($state, array_keys($this->states))) {
            throw new \RunTimeException("Requested state $state does not exist");
        }

        $this->session->set($this->prefix.'.'.$state, $value);
    }
}
