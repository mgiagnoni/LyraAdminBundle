<?php

/*
 * This file is part of the LyraAdminBundle package.
 *
 * Copyright 2011-2012 Massimo Giagnoni <gimassimo@gmail.com>
 *
 * This source file is subject to the MIT license. Full copyright and license
 * information are in the LICENSE file distributed with this source code.
 */

namespace Lyra\AdminBundle\Configuration;

/**
 * Configuration class.
 *
 * Stores configuration options for each model.
 */
class AdminConfiguration implements AdminConfigurationInterface
{
    protected $options;

    public function __construct($options)
    {
        $this->options = $options;
    }

    public function getOption($key)
    {
        return $this->options[$key];
    }

    public function getFieldsOptions()
    {
        return $this->options['fields'];
    }

    public function getFieldOptions($fieldName)
    {
        $options = $this->getFieldsOptions();

        if (!array_key_exists($fieldName, $options)) {
            throw new \InvalidArgumentException(sprintf('Field %s does not exist', $fieldName));
        }

        return $options[$fieldName];
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

    public function getListOption($key)
    {
        $options = $this->options['list'];

        if (!array_key_exists($key, $options)) {
            throw new \InvalidArgumentException(sprintf('List option %s does not exist', $key));
        }

        return $this->options['list'][$key];
    }

    public function getListColumnOptions($column)
    {
        $columns = $this->getListOption('columns');

        if (!array_key_exists($column, $columns)) {
            throw new \InvalidArgumentException(sprintf('List column %s does not exist', $column));
        }

        return $columns[$column];
    }

    public function getListColumnOption($column, $key)
    {
        $options = $this->getListColumnOptions($column);

        if (!array_key_exists($key, $options)) {
            throw new \InvalidArgumentException(sprintf('Column option %s does not exist', $key));
        }

        return $options[$key];
    }

    public function getFormOptions()
    {
        return $this->options['form'];
    }

    public function getFormOption($key)
    {
        $options = $this->options['form'];

        if (!array_key_exists($key, $options)) {
            throw new \InvalidArgumentException(sprintf('Form option %s does not exist', $key));
        }

        return $options[$key];
    }

    public function getFilterOption($key)
    {
        $options = $this->options['filter'];

        if (!array_key_exists($key, $options)) {
            throw new \InvalidArgumentException(sprintf('Filter option %s does not exist', $key));
        }

        return $options[$key];
    }

    public function getActionOptions($action)
    {
        $options = $this->options['actions'];

        if (!array_key_exists($action, $options)) {
            throw new \InvalidArgumentException(sprintf('Action %s does not exist', $action));
        }

        return $options[$action];
    }

    public function getActionOption($action, $key)
    {
        $options = $this->getActionOptions($action);

        if (!array_key_exists($key, $options)) {
            throw new \InvalidArgumentException(sprintf('Action option %s does not exist', $key));
        }

        return $options[$key];
    }

    public function getShowOption($key)
    {
        $options = $this->options['show'];

        if (!array_key_exists($key, $options)) {
            throw new \InvalidArgumentException(sprintf('Show option %s does not exist', $key));
        }

        return $options[$key];
    }

    public function getShowFieldsOptions()
    {
        return $this->options['show']['fields'];
    }

    public function getShowFieldOptions($fieldName)
    {
        $options = $this->getShowFieldsOptions();

        if (!array_key_exists($fieldName, $options)) {
            throw new \InvalidArgumentException(sprintf('Field %s does not exist', $fieldName));
        }

        return $options[$fieldName];
    }

    public function getShowFieldOption($fieldName, $key)
    {
        $options = $this->getShowFieldOptions($fieldName);

        if (!array_key_exists($key, $options)) {
            throw new \InvalidArgumentException(sprintf('Field option %s does not exist', $key));
        }

        return  $options[$key];
    }
}
