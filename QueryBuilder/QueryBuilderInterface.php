<?php

/*
 * This file is part of the LyraAdminBundle package.
 *
 * Copyright 2011-2012 Massimo Giagnoni <gimassimo@gmail.com>
 *
 * This source file is subject to the MIT license. Full copyright and license
 * information are in the LICENSE file distributed with this source code.
 */

namespace Lyra\AdminBundle\QueryBuilder;

use Lyra\AdminBundle\UserState\UserStateInterface;

interface QueryBuilderInterface
{
    /**
     * Sets the base query builder.
     *
     * @param mixed $queryBuilder
     */
    function setBaseQueryBuilder($queryBuilder);

    /**
     * Gets the base query builder.
     *
     * @return mixed
     */
    function getBaseQueryBuilder();

    /**
     * Sets sort field / sort order.
     *
     * @param array $sort array('field' => $fieldName, 'order' => $sortOrder)
     */
    function setSort(array $sort);

    /**
     * Gets sort field / order.
     *
     * @return array
     */
    function getSort();

    /**
     * Sets search criteria.
     *
     * @param array $criteria
     */
    function setCriteria(array $criteria);

    /**
     * Gets search criteria.
     *
     * @return array
     */
    function getCriteria();

    /**
     * Sets fields options.
     *
     * @param array $fields
     */
    function setFields(array $fields);

    /**
     * Gets fields options.
     *
     * @return array
     */
    function getFields();

    /**
     * Returns a query builder to extract an ordered list of records
     * filtered by given search criteria.
     *
     * @return mixed
     */
    function buildQuery();
}
