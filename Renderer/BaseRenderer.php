<?php

/*
 * This file is part of the LyraContentBundle package.
 *
 * Copyright 2011 Massimo Giagnoni <gimassimo@gmail.com>
 *
 * This source file is subject to the MIT license. Full copyright and license
 * information are in the LICENSE file distributed with this source code.
 */

namespace Lyra\AdminBundle\Renderer;

use Lyra\AdminBundle\Util\Util;
use Symfony\Component\Security\Core\SecurityContextInterface;

/**
 * Base class for all renderer services.
 */
abstract class BaseRenderer
{
    /**
     * @var string
     */
    protected $name;

    /**
     * @var array
     */
    protected $options;

    /**
     * @var array
     */
    protected $routeParams = array();

    protected $securityContext;

    public function __construct(array $options = array())
    {
        $this->options = $options;
    }

    public function setSecurityContext(SecurityContextInterface $securityContext)
    {
        $this->securityContext = $securityContext;
    }

    public function setName($name)
    {
        $this->name = $name;
    }

    public function getName()
    {
        return $this->name;
    }

    public function setOptions(array $options)
    {
        $this->options = $options;
    }

    public function getOptions()
    {
        return $this->options;
    }

    public function getTransDomain()
    {
        return $this->options['trans_domain'];
    }

    public function getRoutePrefix()
    {
        return $this->options['route_prefix'];
    }

    public function getTheme()
    {
        return $this->options['theme'];
    }

    public function setRouteParams(array $routeParams)
    {
        $this->routeParams = $routeParams;
    }

    public function getRouteParams()
    {
        return $this->routeParams;
    }

    public function getFields()
    {
        return $this->fields = $this->options['fields'];
    }

    public function isActionAllowed($action)
    {
        $roles = array();
        if (isset($this->options['actions'][$action]['roles'])) {
            $roles = $this->options['actions'][$action]['roles'];
        }
        if (null === $this->securityContext || count($roles) == 0) {
            return true;
        }
        if ($this->securityContext->isGranted($roles)) {
            return true;
        }

        return false;
    }
}
