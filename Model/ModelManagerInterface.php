<?php

/*
 * This file is part of the LyraAdminBundle package.
 *
 * Copyright 2011 Massimo Giagnoni <gimassimo@gmail.com>
 *
 * This source file is subject to the MIT license. Full copyright and license
 * information are in the LICENSE file distributed with this source code.
 */

namespace Lyra\AdminBundle\Model;

interface ModelManagerInterface
{
    /**
     * Sets model class.
     *
     * @param string $class class name
     */
    function setClass($class);

    /**
     * Gets model class.
     *
     * @return string
     */
    function getClass();

    /**
     * Creates an instance of a model object.
     *
     * @return mixed
     */
    function create();

    /**
     * Saves an object.
     *
     * @param mixed $object object to save
     */
    function save($object);

    /**
     * Removes an object.
     *
     * @param mixed $object object to remove
     */
    function remove($object);

    /**
     * Removes multiple objects selected by primary key.
     *
     * @param array $ids array of primary keys
     */
    function removeByIds(array $ids);

    /**
     * Removes all objects from the repository.
     */
    function removeAll();

    /**
     * Finds a model object by primary key.
     *
     * @param mixed $id;
     *
     * @return mixed
     */
    function find($id);

    /**
     * Finds a collection of object selected by primary key.
     *
     * @param array $ids array of primary keys
     */
    function findByIds(array $ids);

    /**
     * Sets a field value on multiple objects selected by primary key.
     *
     * @param string $field field name
     * @param mixed $value value to set
     * @param array $ids array of primary keys
     */
    function setFieldValueByIds($field, $value, array $ids);

    /**
     * Gets the query builder to retrieve list results.
     *
     * @return mixed
     */
    function getBaseListQueryBuilder();

    /**
     * Gets model class repository
     *
     * @return mixed
     */
    function getRepository();

    /**
     * Assures that filter criteria stored as objects are managed by the
     * entity manager.
     *
     * @param array $criteria
     *
     * @return array
     */
    function mergeFilterCriteriaObjects($criteria);
}
