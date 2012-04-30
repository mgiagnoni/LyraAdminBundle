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

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session;
use Doctrine\ORM\Query;

/**
 * List renderer class.
 */
class ListRenderer extends BaseRenderer implements ListRendererInterface
{
    /**
     * @var mixed
     */
    protected $baseQueryBuilder;

    /**
     * @var mixed
     */
    protected $queryBuilder;

    /**
     * @var array
     */
    protected $columns;

    /**
     * @var array
     */
    protected $actions;

    /**
     * @var integer
     */
    protected $maxPageLinks = 7;

    /**
     * @var integer
     */
    protected $total;

    /**
     * @var array
     */
    protected $filterCriteria = array();

    /**
     * @var array
     */
    protected $sort;

    /**
     * @var integer
     */
    protected $page;

    public function getTemplate()
    {
        return $this->getOption('template');
    }

    public function getTitle()
    {
        return $this->getOption('title');
    }

    public function getColumns()
    {
        if (null === $this->columns) {
            $this->columns = $this->getOption('columns');
            $this->initColumns();
        }

        return $this->columns;
    }

    public function getBatchActions()
    {
        return $this->getAllowedActions('batch');
    }

    public function hasBatchActions()
    {
        return (boolean)count($this->getBatchActions());
    }

    public function getObjectActions()
    {
        return $this->getAllowedActions('object');
    }

    public function getListActions()
    {
        return $this->getAllowedActions('list');
    }

    public function getDefaultSort()
    {
        return $this->configuration->getListOption('default_sort');
    }

    public function getActions()
    {
        return $this->configuration->getOption('actions');
    }

    public function setSort(array $sort)
    {
        $this->sort = $sort;
    }

    public function getSort()
    {
        return $this->sort;
    }

    public function getColValue($colName, $object)
    {
        $field = $this->getColOption($colName, 'field');

        if (false !== strpos($field, '.')) {
            list($model, $field) = explode('.', $field);
            $method = $this->configuration->getFieldOption($model, 'get_method');
            $object = $object->$method();
            $method = $this->configuration->getAssocFieldOption($model, $field, 'get_method');
            $value = $object->$method();
        } else {
            $method = $this->configuration->getFieldOption($field, 'get_method');
            $value = $object->$method();
        }

        $function = $this->getColOption($colName, 'format_function');
        $format = $this->getColOption($colName, 'format');
        $type = $this->getColOption($colName, 'type');

        if ($function) {
            $value = call_user_func($function, $value, $format, $object);
        } else if(null !== $value && $format) {
            if ('date' == $type || 'datetime' == $type) {
                $value = $value->format($format);
            } else {
                $value = sprintf($format, $value);
            }
        }

        return $value;
    }

    public function hasBooleanActions($colName)
    {
        return 'boolean' == $this->getColOption($colName, 'type') && $this->getColOption($colName, 'boolean_actions');
    }

    public function getBooleanIcon($colName, $object)
    {
        return $this->getColValue($colName, $object) ? 'ui-icon-circle-check' : 'ui-icon-circle-close';
    }

    public function getBooleanText($colName, $object)
    {
        // TODO: make text configurable
        return $this->getColValue($colName, $object) ? 'on' : 'off';
    }

    public function getColFormat($colName)
    {
        return $this->getColOption($colName,'format');
    }

    public function getFilterCriteria()
    {
        return $this->filterCriteria;
    }

    public function setFilterCriteria($criteria)
    {
        $this->filterCriteria = $criteria;
    }

    public function getColOption($colName, $key)
    {
        $columns = $this->getColumns();
        if (!array_key_exists($key,$columns[$colName])) {
           throw new \InvalidArgumentException(sprintf('Column option %s does not exist', $key));
        }

        return $columns[$colName][$key];
    }

    public function getOption($key)
    {
        return $this->configuration->getListOption($key);
    }

    protected function initColumns()
    {
        $sort = $this->getSort();
        $sorted = $sort['column'];

        if ($sorted && isset($this->columns[$sorted])) {
            $this->columns[$sorted]['sorted'] = true;
            $this->columns[$sorted]['sort'] = $sort['order'];
            $this->columns[$sorted]['th_class'] = str_replace('sortable', 'sorted-'.$sort['order'], $this->columns[$sorted]['th_class']);
        }
    }

    protected function getAllowedActions($type)
    {
        $allowed = array();
        $actions = $this->getOption($type.'_actions');
        foreach ($actions as $action) {
            if ($this->isActionAllowed($action)) {
                $allowed[] = $action;
            }
        }

        return $allowed;
    }
}
