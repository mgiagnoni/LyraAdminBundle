<?php

/*
 * This file is part of the LyraAdminBundle package.
 *
 * Copyright 2011-2012 Massimo Giagnoni <gimassimo@gmail.com>
 *
 * This source file is subject to the MIT license. Full copyright and license
 * information are in the LICENSE file distributed with this source code.
 */

namespace Lyra\AdminBundle\Action;

/**
 * Represents a collection of actions.
 */
class ActionCollection implements ActionCollectionInterface
{
    /**
     * @var array
     */
    protected $actions = array();

    public function __construct($options = array())
    {
        $this->populateFromOptions($options);
    }

    public function get($actionName)
    {
        if (!$this->has($actionName)) {
            throw new \InvalidArgumentException(sprintf('Action "%s" does not exist.', $actionName));
        }

        return $this->actions[$actionName];
    }

    public function add(ActionInterface $action)
    {
        $this->actions[$action->getName()] = $action;
    }

    public function remove($actionName)
    {
        if ($this->has($actionName)) {
            unset($this->actions[$actionName]);
        }
    }

    public function has($actionName)
    {
        return isset($this->actions[$actionName]);
    }

    public function offsetExists($actionName)
    {
        return $this->has($actionName);
    }

    public function offsetGet($actionName)
    {
        return $this->get($actionName);
    }

    public function offsetSet($actionName, $action)
    {
        $this->add($actionName);
    }

    public function offsetUnset($actionName)
    {
        $this->remove($actionName);
    }

    public function getIterator()
    {
        return new \ArrayIterator($this->actions);
    }

    public function count()
    {
        return count($this->actions);
    }

    protected function populateFromOptions($options)
    {
        foreach ($options as $name => $attrs) {
                $action = new Action($name);
                if (isset($attrs['route_pattern'])) {
                    $action->setRoute($attrs['route_name'], $attrs['route_pattern'], array());
                }
                if (isset($attrs['text'])) {
                    $action->setText($attrs['text']);
                }
                if (isset($attrs['icon'])) {
                    $action->setButtonIcon($attrs['icon']);
                }
                if (isset($attrs['style'])) {
                    $action->setButtonStyle($attrs['style']);
                }
                if (isset($attrs['dialog'])) {
                    $action->setDialog($attrs['dialog']['title'], $attrs['dialog']['message']);
                }
                if (isset($attrs['trans_domain'])) {
                    $action->setTransDomain($attrs['trans_domain']);
                }
                if (isset($attrs['template'])) {
                    $action->setTemplate($attrs['template']);
                }
                if (isset($attrs['roles'])) {
                    $action->setRoles($attrs['roles']);
                }

                $this->add($action);
            }
    }
}
