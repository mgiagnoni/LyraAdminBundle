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

use Symfony\Component\Security\Core\SecurityContextInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class SecurityManager implements SecurityManagerInterface
{
    /**
     * @var \Symfony\Component\Security\Core\SecurityContextInterface
     */
    protected $securityContext;
    /**
     * @var string
     */
    protected $model;
    /**
     * @var array
     */
    protected $actions;

    public function __construct(SecurityContextInterface $securityContext, $actions)
    {
        $this->securityContext = $securityContext;
        $this->actions = $actions;
    }

    public function setModelName($model)
    {
        $this->model = $model;
    }

    public function isActionAllowed($action)
    {
        if (null === $this->model) {
            throw new \RunTimeException('Model name not set in SecurityManager');
        }

        if (!isset($this->actions[$this->model][$action])) {
            return true;
        }

        $roles = $this->actions[$this->model][$action];

        if (null === $this->securityContext || count($roles) == 0) {
            return true;
        }

        if ($this->securityContext->isGranted($roles)) {
            return true;
        }

        return false;
    }

    public function allowOr403($action)
    {
        if (!$this->isActionAllowed($action)) {
            throw new AccessDeniedException();
        }
    }
}
