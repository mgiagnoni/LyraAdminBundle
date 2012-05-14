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

/**
 * Confirmation dialog renderer class.
 */
class DialogRenderer extends BaseRenderer
{
    /**
     * @var string
     */
    protected $action;

    /**
     * @var array
     */
    protected $actions;

    /**
     * @var string
     */
    protected $transDomain;

    public function setActions($actions)
    {
        $this->actions = $actions;
    }

    public function getActions()
    {
        return $this->actions;
    }

    public function setAction($action)
    {
        $this->action = $action;
    }

    public function getAction()
    {
        return $this->action;
    }

    public function setTransDomain($transDomain)
    {
        $this->transDomain = $transDomain;
    }

    public function getTransDomain()
    {
        return $this->transDomain;
    }

    public function getTitle()
    {
        return $this->actions[$this->action]['title'];
    }

    public function getMessage()
    {
        return $this->actions[$this->action]['message'];
    }
}
