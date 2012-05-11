<?php

/*
 * This file is part of the LyraContentBundle package.
 *
 * Copyright 2011 Massimo Giagnoni <gimassimo@gmail.com>
 *
 * This source file is subject to the MIT license. Full copyright and license
 * information are in the LICENSE file distributed with this source code.
 */

namespace Lyra\AdminBundle\Pager;
use Lyra\AdminBundle\QueryBuilder\QueryBuilderInterface;

interface PagerInterface
{
    /**
     * Sets the query builder to extract the records to paginate.
     *
     * @param \Lyra\AdminBundle\QueryBuilder\QueryBuilderInterface $queryBuilder
     */
    function setQueryBuilder(QueryBuilderInterface $queryBuilder);

    /**
     * Gets the pager query builder.
     *
     * @return \Lyra\AdminBundle\QueryBuilder\QueryBuilderInterface
     */
    function getQueryBuilder();

    /**
     * Sets the current page number.
     *
     * @param integer $page
     */
    function setPage($page);

    /**
     * Gets the current page number.
     *
     * @return integer
     */
    function getPage();

    /**
     * Sets the max number of rows to display on a page.
     *
     * @param integer $maxRows
     */
    function setMaxRows($maxRows);

    /**
     * Gets the max number of rows to display on a page.
     *
     * @return integer
     */
    function getMaxRows();

    /**
     * Gets the total number of records to paginate.
     *
     * @return integer
     */
    function getTotal();

    /**
     * Gets the records to display on the current page.
     *
     * Executes a query created with the pager query builder.
     *
     * @return array
     */
    function getResults();

    /**
     * Gets the total number of pages.
     *
     * Based on the total number of records and the number of records per page.
     *
     * @return integer
     */
    function getNBPages();

    /**
     * Gets the previous page number.
     *
     * @return integer
     */
    function getPrevPage();

    /**
     * Gets the next page number.
     *
     * @return integer
     */
    function getNextpage();

    /**
     * Gets an array of page numbers to display as pager links.
     *
     * @return array
     */
    function getPageLinks();
}
