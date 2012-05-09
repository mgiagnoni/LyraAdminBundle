<?php

/*
 * This file is part of the LyraAdminBundle package.
 *
 * Copyright 2011 Massimo Giagnoni <gimassimo@gmail.com>
 *
 * This source file is subject to the MIT license. Full copyright and license
 * information are in the LICENSE file distributed with this source code.
 */

namespace Lyra\AdminBundle\Pager;

/**
 * List pager class.
 */
class Pager implements PagerInterface
{
    protected $baseQueryBuilder;

    protected $queryBuilder;

    protected $countQueryBuilder;

    protected $page;

    protected $maxRows;

    protected $sort;

    protected $criteria;

    protected $maxPageLinks = 7;

    protected $total;

    public function setQueryBuilder($qb)
    {
        $this->baseQueryBuilder = $qb;
    }

    public function getQueryBuilder()
    {
        return $this->baseQueryBuilder;
    }

    public function setPage($page)
    {
        $this->page = $page;
    }

    public function getPage()
    {
        return $this->page;
    }

    public function setMaxRows($max)
    {
        $this->maxRows = $max;
    }

    public function getMaxRows()
    {
        return $this->maxRows;
    }

    public function setCriteria($criteria)
    {
        $this->criteria = $criteria;
    }

    public function getCriteria()
    {
        return $this->criteria;
    }

    public function setSort($sort)
    {
        $this->sort = $sort;
    }

    public function getSort()
    {
        return $this->sort;
    }

    public function getTotal()
    {
        if (null === $this->total) {
            $this->initQueryBuilders();
            $alias = $this->countQueryBuilder->getRootAlias();

            $this->total = $this->countQueryBuilder
                ->select('COUNT('.$alias.')')
                ->setFirstResult(null)
                ->getQuery()->getSingleScalarResult();
            }

        return $this->total;
    }

    public function getResults()
    {
        $this->initQueryBuilders();
        $maxRows = $this->getMaxRows();
        $alias = $this->queryBuilder->getRootAlias();
        $page = min($this->getPage(), max(1, $this->getNbPages()));

        return $this->queryBuilder
            ->select($alias)
            ->setFirstResult(($page - 1) * $maxRows)
            ->setMaxResults($maxRows)
            ->getQuery()->getResult();
    }

    public function getNbPages()
    {
        return ceil($this->getTotal() / $this->getMaxRows());
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

    protected function initQueryBuilders()
    {
        if (null === $this->queryBuilder) {
            $criteria = $this->getCriteria();
            $sort = $this->getSort();

            $this->queryBuilder = $this->baseQueryBuilder->buildQuery($criteria, $sort);
            $this->countQueryBuilder = clone $this->queryBuilder;
            $this->countQueryBuilder->resetDQLPart('orderBy');
        }
    }
}
