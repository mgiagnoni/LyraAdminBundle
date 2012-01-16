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
        return $this->options['list']['template'];
    }

    public function getTitle()
    {
        return $this->options['list']['title'];
    }

    public function getColumns()
    {
        if (null === $this->columns) {
            $this->columns = $this->options['list']['columns'];
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
        return $this->options['list']['default_sort'];
    }

    public function getActions()
    {
        return $this->options['actions'];
    }

    public function setSort(array $sort)
    {
        if (!$sort['column']) {
            $default = $this->getDefaultSort();
            $sort['column'] = $default['column'];
            $sort['order'] = $default['order'];
        }

        $this->sort = $sort;
    }

    public function getSort()
    {
        return $this->sort;
    }

    public function setBaseQueryBuilder($qb)
    {
        $this->total = null;
        $this->baseQueryBuilder = $qb;
    }

    public function getQueryBuilder()
    {
        if (null === $this->queryBuilder) {
            $this->initQueryBuilder();
        }

        return $this->queryBuilder;
    }

    public function getTotal()
    {
        if (null === $this->total) {
            $qb = $this->getQueryBuilder();
            $alias = $qb->getRootAlias();

            $this->total = $qb
                ->select('COUNT('.$alias.')')
                ->setFirstResult(null)
                ->getQuery()->getSingleScalarResult();
            }

        return $this->total;
    }

    public function getResults()
    {
        $maxRows = $this->options['list']['max_page_rows'];
        $qb = $this->getQueryBuilder();
        $alias = $qb->getRootAlias();
        $page = min($this->getPage(), max(1, $this->getNbPages()));

        return $qb
            ->select($alias)
            ->setFirstResult(($page - 1) * $maxRows)
            ->setMaxResults($maxRows)
            ->getQuery()->getResult();
    }

    public function getNbPages()
    {
        return ceil($this->getTotal() / $this->options['list']['max_page_rows']);
    }

    public function setPage($page)
    {
        $this->page = $page;
    }

    public function getPage()
    {
        return $this->page;
    }

    public function getPrevPage()
    {
        return max(1, $this->getPage() - 1);
    }

    public function getNextPage()
    {
        return min($this->getNbPages(), $this->getPage() + 1);
    }

    public function getPageLinks()
    {
        $page = $this->getPage();
        $start = max(1, $page - floor($this->maxPageLinks / 2));
        $end = min($this->getNbPages(), $page + $this->maxPageLinks - ($page - $start + 1));
        $start = max(1, $start - ($this->maxPageLinks - ($end - $start + 1)));

        return range($start, $end);
    }

    public function getColValue($colName, $object)
    {
        $field = $this->getColOption($colName, 'field');

        if (false !== strpos($field, '.')) {
            list($model, $field) = explode('.', $field);
            $method = $this->getFieldOption($model, 'get_method');
            $object = $object->$method();
            $method = $this->getAssocFieldOption($model, $field, 'get_method');
            $value = $object->$method();
        } else {
            $method = $this->getFieldOption($field, 'get_method');
            $value = $object->$method();
        }

        $function = $this->getColOption($colName, 'format_function');
        $format = $this->getColOption($colName, 'format');
        $type = $this->getColOption($colName, 'type');

        if ($function) {
            $value = call_user_func($function, $value, $format, $object);
        } else if($format) {
            if ('date' == $type || 'datetime' == $type) {
                $value = $value->format($format);
            } else {
                $value = sprintf($format, $value);
            }
        }

        return $value;
    }

    public function getBooleanAction($colName, $object)
    {
        $actions = $this->getColOption($colName, 'boolean_actions');

        return $actions[$this->getColValue($colName, $object) ? 1:0].'_'.$colName;
    }

    public function hasBooleanActions($colName)
    {
        return 'boolean' == $this->getColOption($colName, 'type') && count($this->getColOption($colName, 'boolean_actions')) == 2;
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

    protected function initQueryBuilder()
    {
        $this->queryBuilder = $this->baseQueryBuilder;
        $this->addFilterCriteria();
        $this->addSort();
    }

    protected function addFilterCriteria()
    {
        $fields = $this->getFields();
        $criteria = $this->getFilterCriteria();
        $alias = $this->queryBuilder->getRootAlias();

        foreach ($criteria as $field => $value) {
            if(null === $value || '' == $value) {
                continue;
            }

            if (isset($fields[$field])) {
                switch ($fields[$field]['type']) {
                    case 'string':
                    case 'text':
                        $this->queryBuilder->andWhere(
                            $this->queryBuilder->expr()->like($alias.'.'.$field, $this->queryBuilder->expr()->literal($value.'%'))
                        );
                        break;
                    case 'date':
                    case 'datetime':
                        if (null !== $value['from']) {
                            $this->queryBuilder->andWhere(
                                $this->queryBuilder->expr()->gte($alias.'.'.$field, $this->formatDate($value['from']))
                            );
                        }
                        if (null !== $value['to']) {
                            $this->queryBuilder->andWhere(
                                $this->queryBuilder->expr()->lte($alias.'.'.$field, $this->formatDate($value['to']))
                            );
                        }
                        break;

                    case 'boolean':
                         $this->queryBuilder->andWhere(
                                $this->queryBuilder->expr()->eq($alias.'.'.$field, $value)
                            );

                        break;
                }
            }
        }
    }

    protected function addSort()
    {
        $sort = $this->getSort();

        if (isset($sort['column'])) {
            $field = $this->getColOption($sort['column'], 'field');
            if (false !== strpos($field, '.')) {
                list($model, $field) = explode('.', $field);
                $sortField = $this->getAssocFieldOption($model, $field, 'name');
                $options = $this->getFieldOption($model, 'assoc');
                $sortField = $options['model'].'.'.$sortField;
            } else {
                $sortField = $this->getFieldOption($field, 'name');
                $sortField = $this->queryBuilder->getRootAlias().'.'.$sortField;
            }
        } else {
            $default = $this->getDefaultSort();
            $sortField = $default['field'];
        }

        if (null !== $sortField) {
            $this->queryBuilder->orderBy($sortField, $sort['order']);
        }
    }

    protected function formatDate($date)
    {
        return $this->queryBuilder->expr()->literal($date->format('Y-m-d H:i:s'));
    }

    protected function getAllowedActions($type)
    {
        $actions = array();
        foreach ($this->options['list'][$type.'_actions'] as $action) {
            if ($this->isActionAllowed($action)) {
                $actions[] = $action;
            }
        }

        return $actions;
    }
}
