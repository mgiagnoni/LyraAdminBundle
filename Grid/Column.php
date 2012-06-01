<?php

/*
 * This file is part of the LyraAdminBundle package.
 *
 * Copyright 2011-2012 Massimo Giagnoni <gimassimo@gmail.com>
 *
 * This source file is subject to the MIT license. Full copyright and license
 * information are in the LICENSE file distributed with this source code.
 */

namespace Lyra\AdminBundle\Grid;

/**
 * Column.
 *
 * Represents a grid column.
 */
class Column implements ColumnInterface
{
    /**
     * @var string
     */
    protected $name;

    /**
     * @var string
     */
    protected $fieldName;

    /**
     * @var string
     */
    protected $type;

    /**
     * @var boolean
     */
    protected $sortable;

    /**
     * @var string
     */
    protected $label;

    /**
     * @var string
     */
    protected $format;

    /**
     * @var string
     */
    protected $formatFunction;

    /**
     * @var boolean
     */
    protected $booleanActions;

    /**
     * @var string
     */
    protected $template;

    /**
     * @var array
     */
    protected $methods;

    public function __construct($name)
    {
        $this->name = $name;
    }

    public function getName()
    {
        return $this->name;
    }

    public function setFieldName($fieldName)
    {
        $this->fieldName = $fieldName;
    }

    public function getFieldName()
    {
        return $this->fieldName;
    }

    public function setType($type)
    {
        $this->type = $type;
    }

    public function getType()
    {
        return $this->type;
    }

    public function setSortable($sortable)
    {
        $this->sortable = $sortable;
    }

    public function isSortable()
    {
        return $this->sortable;
    }

    public function setLabel($label)
    {
        $this->label = $label;
    }

    public function getLabel()
    {
        return $this->label;
    }

    public function setFormat($format)
    {
        $this->format = $format;
    }

    public function getFormat()
    {
        return $this->format;
    }

    public function setFormatFunction($formatFunction)
    {
        $this->formatFunction = $formatFunction;
    }

    public function getFormatFunction()
    {
        return $this->formatFunction;
    }

    public function setBooleanActions($booleanActions)
    {
       $this->booleanActions = $booleanActions;
    }

    public function hasBooleanActions()
    {
        return 'boolean' == $this->type && $this->booleanActions;
    }

    public function setTemplate($template)
    {
        $this->template = $template;
    }

    public function getTemplate()
    {
        return $this->template;
    }

    public function setMethods($methods)
    {
        $this->methods = $methods;
    }

    public function getMethods()
    {
        return $this->methods;
    }

    public function getValue($object)
    {
        foreach ($this->methods as $method) {
            $value = $object->$method();
            if (!is_object($value)) {
                break;
            }
            $object = $value;
        }

        if ($this->formatFunction) {
            $value = call_user_func($this->formatFunction, $value, $this->format, $object);
        } else if(null !== $value && $this->format) {
            if ('date' == $this->type || 'datetime' == $this->type) {
                $value = $value->format($this->format);
            } else {
                $value = sprintf($this->format, $value);
            }
        }

        return $value;
    }
}
