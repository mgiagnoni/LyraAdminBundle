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

use Lyra\AdminBundle\Configuration\AdminConfigurationInterface;
use Lyra\AdminBundle\Pager\PagerInterface;
use Lyra\AdminBundle\UserState\UserStateInterface;
use Lyra\AdminBundle\Action\ActionCollectionInterface;
use Lyra\AdminBundle\Security\SecurityManagerInterface;

/**
 * List renderer class.
 */
class ListRenderer extends BaseRenderer implements ListRendererInterface
{
    /**
     * @var \Lyra\AdminBundle\Pager\PagerInterface
     */
    protected $pager;

    /**
     * @var \Lyra\AdminBundle\UserState\UserStateInterface
     */
    protected $state;

    /**
     * @var array
     */
    protected $columns;

    /**
     * @var array
     */
    protected $sort;

    /**
     * @var string
     */
    protected $title;

    /**
     * @var string
     */
    protected $template;

    /**
     * @var string
     */
    protected $transDomain;

    /**
     * @var \Lyra\AdminBundle\Action\ActionCollectionInterface
     */
    protected $actions;

    /**
     * @var \Lyra\AdminBundle\Action\ActionCollectionInterface
     */
    protected $listActions;

    /**
     * @var \Lyra\AdminBundle\Action\ActionCollectionInterface
     */
    protected $objectActions;

    /**
     * @var \Lyra\AdminBundle\Action\ActionCollectionInterface
     */
    protected $batchActions;

    /**
     * @var \Lyra\AdminBundle\Security\SecurityManagerInterface
     */
    protected $securityManager;

    public function __construct(PagerInterface $pager, SecurityManagerInterface $securityManager, AdminConfigurationInterface $configuration)
    {
        parent::__construct($configuration);

        $this->pager = $pager;
        $this->securityManager = $securityManager;
    }

    public function setState(UserStateInterface $state)
    {
        $this->state = $state;
    }

    public function getState()
    {
        return $this->state;
    }

    public function setTemplate($template)
    {
        $this->template = $template;
    }

    public function getTemplate()
    {
        return $this->template;
    }

    public function setTitle($title)
    {
        $this->title = $title;
    }

    public function getTitle()
    {
        return $this->title;
    }

    public function setTransDomain($transDomain)
    {
        $this->transDomain = $transDomain;
    }

    public function getTransDomain()
    {
        return $this->transDomain;
    }

    public function getPager()
    {
        $this->pager->setPage($this->state->get('page'));

        return $this->pager;
    }

    public function setColumns($columns)
    {
        $this->columns = $columns;
    }

    public function getColumns()
    {
        $this->initColumns();

        return $this->columns;
    }

    public function setBatchActions(ActionCollectionInterface $actions)
    {
        $this->batchActions = $actions;
    }

    public function getBatchActions()
    {
        return $this->filterAllowedActions($this->batchActions);
    }

    public function hasBatchActions()
    {
        return (boolean)count($this->getBatchActions());
    }

    public function getBatchAction($actionName)
    {
        if ($this->batchActions->has($actionName)) {
            return $this->batchActions->get($actionName);
        }
    }

    public function setObjectActions(ActionCollectionInterface $actions)
    {
        $this->objectActions = $actions;
    }

    public function getObjectActions()
    {
        return $this->filterAllowedActions($this->objectActions);
    }

    public function getObjectAction($actionName)
    {
        if ($this->objectActions->has($actionName)) {
            return $this->objectActions->get($actionName);
        }
    }

    public function setListActions(ActionCollectionInterface $actions)
    {
        $this->listActions = $actions;
    }

    public function getListActions()
    {
        return $this->filterAllowedActions($this->listActions);
    }

    public function getListAction($actionName)
    {
        if ($this->listActions->has($actionName)) {
            return $this->listActions->get($actionName);
        }

    }

    public function setActions(ActionCollectionInterface $actions)
    {
        $this->actions = $actions;
    }

    public function getActions()
    {
        return $this->actions;
    }

    public function setSort(array $sort)
    {
        $this->sort = $sort;
    }

    public function getSort()
    {
        if (null === $this->sort) {
            $sort = array(
                'column' => $this->state->get('column'),
                'order' => $this->state->get('order')
            );

            $this->sort = $sort;
        }

        return $this->sort;
    }

    public function getColValue($colName, $object)
    {
        $methods = $this->getColOption($colName, 'get_methods');

        foreach ($methods as $method) {
            $value = $object->$method();
            if (is_object($value)) {
                $object = $value;
            }
        }

        $function = $this->getColOption($colName, 'format_function');
        $format = $this->getColOption($colName, 'format');
        $type = $this->getColOption($colName, 'type');

        if ($function) {
            $value = call_user_func($function, $value, $format, $object);
        } else if(null !== $value && $format) {
            if ('date' == $type || 'datetime' == $type) {
                $value = $value->format($format);
            } else {
                $value = sprintf($format, $value);
            }
        }

        return $value;
    }

    public function hasBooleanActions($colName)
    {
        return 'boolean' == $this->getColOption($colName, 'type') && $this->getColOption($colName, 'boolean_actions');
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

    public function getColFormat($colName)
    {
        return $this->getColOption($colName,'format');
    }

    public function getColOption($colName, $key)
    {
        if (!array_key_exists($key, $this->columns[$colName])) {
           throw new \InvalidArgumentException(sprintf('Column option %s does not exist', $key));
        }

        return $this->columns[$colName][$key];
    }

    protected function initColumns()
    {
        $sort = $this->getSort();
        $sorted = $sort['column'];

        if ($sorted && isset($this->columns[$sorted])) {
            $this->columns[$sorted]['sorted'] = true;
            $this->columns[$sorted]['sort'] = $sort['order'];
            $this->columns[$sorted]['th_class'] = str_replace('sortable', 'sorted-'.$sort['order'], $this->columns[$sorted]['th_class']);
        }
    }

    protected function filterAllowedActions($actions)
    {
        $allowed = array();
        foreach ($actions as $action) {
            if ($this->securityManager->isActionAllowed($action->getName())) {
                $allowed[] = $action;
            }
        }

        return $allowed;
    }
}
