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
        return $this->options['list']['batch_actions'];
    }

    public function hasBatchActions()
    {
        return (boolean)count($this->options['list']['batch_actions']);
    }

    public function getObjectActions()
    {
        return $this->options['list']['object_actions'];
    }

    public function getListActions()
    {
        return $this->options['list']['list_actions'];
    }

    public function getActions()
    {
        return $this->options['actions'];
    }

    public function setSort(array $sort)
    {
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
        $page = min($this->getPage(), $this->getNbPages());

        return $qb
            ->select($alias)
            ->setFirstResult(($page - 1) * $maxRows)
            ->setMaxResults($maxRows)
            ->getQuery()->getResult(Query::HYDRATE_ARRAY);
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
        $columns = $this->getColumns();
        $value = $object[$columns[$colName]['property_name']];
        if ($format = $columns[$colName]['format']) {
            $value = sprintf($format, $value);
        }

        return $value;
    }

    public function getBooleanAction($colName, $object)
    {
        $columns = $this->getColumns();
        return $columns[$colName]['boolean_actions'][$this->getColValue($colName, $object) ? 1:0].'_'.$colName;
    }

    public function hasBooleanActions($colName)
    {
        $columns = $this->getColumns();
        return 'boolean' == $columns[$colName]['type'] && count($columns[$colName]['boolean_actions']) == 2;
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
        $columns = $this->getColumns();
        return $columns[$colName]['format'];
    }

    public function getFilterCriteria()
    {
        return $this->filterCriteria;
    }

    public function setFilterCriteria($criteria)
    {
        $this->filterCriteria = $criteria;
    }

    protected function initColumns()
    {
        $sort = $this->getSort();
        $fields = $this->getFields();
        foreach ($this->columns as $name => $attrs) {
            $type = $attrs['type'];
            if(null === $type && isset($fields[$name])) {
                $type = $fields[$name]['type'];
                $this->columns[$name]['type'] = $type;
            }

            $class = '';
            if ('boolean' == $type) {
                $class .= $type;
            }

            if ($class) {
                $class = 'class="'.trim($class).'"';
            }

            $this->columns[$name]['class'] = $class;

            $class = '';
            if ($this->columns[$name]['sortable']) {
                $class = 'sortable';
                if ($sort['field'] == $name) {
                    $this->columns[$name]['sorted'] = true;
                    $this->columns[$name]['sort'] = $sort['order'];
                    $class = 'sorted-'.$sort['order'];
                }
            }

            $class .= ' col-'.$name.' '.$type;

            if ($class) {
                $class = 'class="'.trim($class).'"';
            }

            $this->columns[$name]['th_class'] = $class;

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

        if (isset($sort['field'])) {
            $columns = $this->getColumns();
            $column = $columns[$sort['field']];

            $this->queryBuilder->orderBy($this->queryBuilder->getRootAlias().'.'.$column['property_name'], $sort['order']);
        }
    }

    protected function formatDate($date)
    {
        return $this->queryBuilder->expr()->literal($date->format('Y-m-d H:i:s'));
    }
}
