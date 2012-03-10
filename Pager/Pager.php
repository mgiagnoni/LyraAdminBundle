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
    protected $queryBuilder;

    protected $countQueryBuilder;

    protected $page;

    protected $maxRows;

    protected $maxPageLinks = 7;

    protected $total;

    public function setQueryBuilder($qb)
    {
        $this->queryBuilder = $qb;
        $this->countQueryBuilder = clone $qb;
        $this->countQueryBuilder->resetDQLPart('orderBy');
    }

    public function getQueryBuilder()
    {
        return $this->queryBuilder;
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
        $maxRows = $this->getMaxRows();
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
}
