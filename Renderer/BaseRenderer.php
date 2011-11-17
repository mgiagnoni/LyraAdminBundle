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

    /**
     * @var array
     */
    protected $fields;

    /**
     * @var array
     */
    protected $metadata = array();

    public function __construct(array $options = array())
    {
        $this->options = $options;
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
        if (null === $this->fields) {
            $this->fields = $this->options['fields'];
            $this->setFieldsDefaults();
        }

        return $this->fields;
    }

    public function setMetadata(array $metadata)
    {
        $this->metadata = $metadata;
    }

    public function getMetadata()
    {
        return $this->metadata;
    }

    protected function setFieldsDefaults()
    {
        foreach ($this->getMetadata() as $field => $attrs) {
            if (isset($attrs['id']) && $attrs['id'] === true) {
                continue;
            }
            $defaults = array('name' => $field, 'options' => array());
            if (!isset($this->fields[$field])) {
                $this->fields[$field] = $defaults;
            }

            if (isset($attrs['options'])) {
                $options = $attrs['options'];
            } else {
                $options = array();
            }

            $options = array_merge($options, $this->fields[$field]['options']);
            unset($this->fields[$field]['options']);
            $this->fields[$field] = array_merge($attrs, $defaults, $this->fields[$field]);
            $this->fields[$field]['options'] = $options;
        }

        foreach ($this->fields as $field => $attrs) {
            if (!isset($attrs['get_method'])) {
                $this->fields[$field]['get_method'] = 'get'.Util::camelize($field);
            }
            $this->fields[$field]['tag'] = Util::underscore($field);
        }
    }
}
