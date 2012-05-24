<?php

/*
 * This file is part of the LyraAdminBundle package.
 *
 * Copyright 2011 Massimo Giagnoni <gimassimo@gmail.com>
 *
 * This source file is subject to the MIT license. Full copyright and license
 * information are in the LICENSE file distributed with this source code.
 */

namespace Lyra\AdminBundle\Grid;

/**
 * Grid columns.
 */
class ColumnCollection implements ColumnCollectionInterface
{
    /**
     * @var array
     */
    protected $columns;

    public function __construct($options = array())
    {
        $this->populateFromOptions($options);
    }

    public function get($columnName)
    {
        if (!$this->has($columnName)) {
            throw new \InvalidArgumentException(sprintf('Column "%s" does not exist.', $columnName));
        }

        return $this->columns[$columnName];
    }

    public function add(ColumnInterface $column)
    {
        $this->columns[$column->getName()] = $column;
    }

    public function remove($columnName)
    {
        if ($this->has($columnName)) {
            unset($this->columns[$columnName]);
        }
    }

    public function has($columnName)
    {
        return isset($this->columns[$columnName]);
    }

    public function offsetExists($columnName)
    {
        return $this->has($columnName);
    }

    public function offsetGet($columnName)
    {
        return $this->get($columnName);
    }

    public function offsetSet($columnName, $column)
    {
        $this->add($columnName);
    }

    public function offsetUnset($columnName)
    {
        $this->remove($columnName);
    }

    public function getIterator()
    {
        return new \ArrayIterator($this->columns);
    }

    public function count()
    {
        return count($this->columns);
    }

    protected function populateFromOptions($options)
    {
        foreach ($options as $name => $attrs) {
            $column = new Column($name);
            if (isset($attrs['field'])) {
                $column->setFieldName($attrs['field']);
            }
            if (isset($attrs['type'])) {
                $column->setType($attrs['type']);
            }
            if (isset($attrs['sortable'])) {
                $column->setSortable($attrs['sortable']);
            }
            if (isset($attrs['label'])) {
                $column->setLabel($attrs['label']);
            }
            if (isset($attrs['format'])) {
                $column->setFormat($attrs['format']);
            }
            if (isset($attrs['format_function'])) {
                $column->setFormatFunction($attrs['format_function']);
            }
            if (isset($attrs['template'])) {
                $column->setTemplate($attrs['template']);
            }
            if (isset($attrs['boolean_actions'])) {
                $column->setBooleanActions($attrs['boolean_actions']);
            }
            if (isset($attrs['get_methods'])) {
                $column->setMethods($attrs['get_methods']);
            }

            $this->add($column);
        }
    }
}
