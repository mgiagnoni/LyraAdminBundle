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
        return $this->options['fields'];
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

    public function getFieldOptions($fieldName)
    {
        $fields = $this->getFields();

        if (!array_key_exists($fieldName, $fields)) {
            throw new \InvalidArgumentException(sprintf('Field %s does not exist', $fieldName));
        }

        return $fields[$fieldName];
    }

    public function getFieldOption($fieldName, $key)
    {
        $options = $this->getFieldOptions($fieldName);

        if (!array_key_exists($key, $options)) {
            throw new \InvalidArgumentException(sprintf('Field option %s does not exist', $key));
        }

        return  $options[$key];
    }

    public function getAssocFieldOption($assocModel, $fieldName, $key)
    {
        $options = $this->getFieldOptions($assocModel);

        if (!isset($options['assoc']['fields'][$fieldName])) {
            throw new \InvalidArgumentException(sprintf('Field %s.%s does not exist', $assocModel, $fieldName));
        }

        if (!array_key_exists($key, $options['assoc']['fields'][$fieldName])) {
            throw new \InvalidArgumentException(sprintf('Field option %s does not exist', $key));
        }

        return $options['assoc']['fields'][$fieldName][$key];
    }
}
