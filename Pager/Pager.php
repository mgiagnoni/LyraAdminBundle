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

use Lyra\AdminBundle\QueryBuilder\QueryBuilderInterface;

/**
 * List pager class.
 */
class Pager implements PagerInterface
{
    /**
     * @var \Lyra\AdminBundle\QueryBuilder\QueryBuilderInterface
     */
    protected $baseQueryBuilder;

    /**
     * @var mixed
     */
    protected $queryBuilder;

    /**
     * @var mixed
     */
    protected $countQueryBuilder;

    /**
     * @var integer
     */
    protected $page;

    /**
     * @var integer
     */
    protected $maxRows;

    /**
     * @var integer
     */
    protected $maxPageLinks = 7;

    /**
     * @var integer
     */
    protected $total;

    public function setQueryBuilder(QueryBuilderInterface $qb)
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

            $this->queryBuilder = $this->baseQueryBuilder->buildQuery();
            $this->countQueryBuilder = clone $this->queryBuilder;
            $this->countQueryBuilder->resetDQLPart('orderBy');
        }
    }
}
