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

    public function setAction($action)
    {
        $this->action = $action;
    }

    public function getAction()
    {
        return $this->action;
    }

    public function getTitle()
    {
        return $this->options['actions'][$this->action]['dialog']['title'];
    }

    public function getMessage()
    {
        return $this->options['actions'][$this->action]['dialog']['message'];
    }
}
