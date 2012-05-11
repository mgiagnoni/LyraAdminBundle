<?php

/*
 * This file is part of the LyraAdminBundle package.
 *
 * Copyright 2012 Massimo Giagnoni <gimassimo@gmail.com>
 *
 * This source file is subject to the MIT license. Full copyright and license
 * information are in the LICENSE file distributed with this source code.
 */

namespace Lyra\AdminBundle\QueryBuilder;

use Lyra\AdminBundle\UserState\UserStateInterface;

interface QueryBuilderInterface
{
    /**
     * Sets user state service.
     *
     * @param \Lyra\AdminBundle\UserState\UserStateInterface $state
     */
    function setState(UserStateInterface $state);

    /**
     * Gets user state service.
     *
     * @return \Lyra\AdminBundle\UserState\UserStateInterface
     */
    function getState();

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
     * Returns a query builder to extract an ordered list of records
     * filtered by given search criteria.
     *
     */
    function buildQuery();
}
