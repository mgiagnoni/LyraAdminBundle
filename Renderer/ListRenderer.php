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
     * @var \Symfony\Component\HttpFoundation\Request
     */
    protected $request;

    /**
     * @var \Symfony\Component\HttpFoundation\Session
     */
    protected $session;

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
    protected $maxPageLinks = 3;

    /**
     * @var integer
     */
    protected $total;

    public function __construct(Request $request, Session $session, array $options = array())
    {
        parent::__construct($options);

        $this->request = $request;
        $this->session = $session;
    }

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
        return count($this->options['list']['batch_actions']);
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

    public function getSort()
    {
        if ($this->request->get('field')) {
            $this->session->set($this->name.'.field', $this->request->get('field'));
            $this->session->set($this->name.'.sort.order', $this->request->get('order'));
        }

        $sort = array('field' => $this->session->get($this->name.'.field', null), 'order' => $this->session->get($this->name.'.sort.order', 'asc'));

        return $sort;
    }

    public function setQueryBuilder($qb)
    {
        $this->total = null;
        $sort = $this->getSort();

        if (isset($sort['field'])) {
            $columns = $this->getColumns();
            $column = $columns[$sort['field']];

            $qb->orderBy($qb->getRootAlias().'.'.$column['property_name'], $sort['order']);
        }

        $this->queryBuilder = $qb;
    }

    public function getTotal()
    {
        if (null === $this->total) {
            $alias = $this->queryBuilder->getRootAlias();

            $this->total = $this->queryBuilder
                ->select('COUNT('.$alias.')')
                ->setFirstResult(null)
                ->getQuery()->getSingleScalarResult();
            }

        return $this->total;
    }

    public function getResults()
    {
        $alias = $this->queryBuilder->getRootAlias();
        $maxRows = $this->options['list']['max_page_rows'];

        return $this->queryBuilder
            ->select($alias)
            ->setFirstResult(($this->getCurrentPage() - 1) * $maxRows)
            ->setMaxResults($maxRows)
            ->getQuery()->getResult(Query::HYDRATE_ARRAY);
    }

    public function getNbPages()
    {
        return ceil($this->getTotal() / $this->options['list']['max_page_rows']);
    }

    public function getCurrentPage()
    {
        if ($page = $this->request->get('page')) {
            $this->session->set($this->name.'.page', $page);
        }

        return $this->session->get($this->name.'.page', 1);
    }

    public function getPrevPage()
    {
        return max(1, $this->getCurrentPage() - 1);
    }

    public function getNextPage()
    {
        return min($this->getNbPages(), $this->getCurrentPage() + 1);
    }

    public function getPageLinks()
    {
        $page = $this->getCurrentPage();
        $start = max(1, $page - floor($this->maxPageLinks / 2));
        $end = min($this->getNbPages(), $page + $this->maxPageLinks - ($page - $start + 1));
        $start = max(1, $start - ($this->maxPageLinks - ($end - $start + 1)));

        return range($start, $end);
    }

    public function getColValue($colName, $object)
    {
        return $object[$this->columns[$colName]['property_name']];
    }

    public function getBooleanAction($colName, $object)
    {
        return $this->columns[$colName]['boolean_actions'][$this->getColValue($colName, $object) ? 1:0].'_'.$colName;
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
            if ($sort['field'] == $name) {
                $this->columns[$name]['sorted'] = true;
                $this->columns[$name]['sort'] = $sort['order'];
                $class .= 'sorted-'.$sort['order'];
            }

            $class .= ' col-'.$name.' '.$type;

            if ($class) {
                $class = 'class="'.trim($class).'"';
            }

            $this->columns[$name]['th_class'] = $class;

        }
    }
}
