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

use Lyra\AdminBundle\Configuration\AdminConfigurationInterface;
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
    protected $routeParams = array();

    /**
     * @var \Symfony\Component\Security\Core\SecurityContextInterface
     */
    protected $securityContext;

    /**
     * @var \Lyra\AdminBundle\Configuration\AdminConfigurationInterface
     */
    protected $configuration;

    public function __construct(AdminConfigurationInterface $configuration)
    {
        $this->configuration = $configuration;
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

    public function getTransDomain()
    {
        return $this->configuration->getOption('trans_domain');
    }

    public function getRoutePrefix()
    {
        return $this->configuration->getOption('route_prefix');
    }

    public function setRouteParams(array $routeParams)
    {
        $this->routeParams = $routeParams;
    }

    public function getRouteParams()
    {
        return $this->routeParams;
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
