<?php
 /*
  * This file is part of the LyraAdminBundle package.
  *
  * Copyright 2011 Massimo Giagnoni <gimassimo@gmail.com>
  *
  * This source file is subject to the MIT license. Full copyright and license
  * information are in the LICENSE file distributed with this source code.
  */

namespace Lyra\AdminBundle\Action;

interface ActionCollectionInterface extends \ArrayAccess, \IteratorAggregate, \Countable
{
    /**
     * Gets an action of a given name.
     *
     * @param string $actionName
     *
     * @return \Lyra\AdminBundle\Action\ActionInterface
     */
    function get($actionName);

    /**
     * Adds an action to the collection.
     *
     * @param \Lyra\AdminBundle\Action\ActionInterface $action
     */
    function add(ActionInterface $action);

    /**
     * Removes an action from the collection.
     *
     * @param string $actionName action to remove
     */
    function remove($actionName);

    /**
     * Checks if the collection contains an action of a given name
     *
     * @param string $actionName
     */
    function has($actionName);
}

