<?php

/*
 * This file is part of the LyraAdminBundle package.
 *
 * Copyright 2011-2012 Massimo Giagnoni <gimassimo@gmail.com>
 *
 * This source file is subject to the MIT license. Full copyright and license
 * information are in the LICENSE file distributed with this source code.
 */

namespace Lyra\AdminBundle\Grid;

use Lyra\AdminBundle\Pager\PagerInterface;
use Lyra\AdminBundle\UserState\UserStateInterface;
use Lyra\AdminBundle\Action\ActionCollectionInterface;
use Lyra\AdminBundle\Action\ActionCollection;
use Lyra\AdminBundle\QueryBuilder\QueryBuilderInterface;
use Lyra\AdminBundle\Security\SecurityManagerInterface;

/**
 * Grid.
 *
 * Displays a list of records in a grid with sortable columns
 * and pagination links.
 */
class Grid implements GridInterface
{
    /**
     * @var \Lyra\AdminBundle\Pager\PagerInterface
     */
    protected $pager;

    /**
     * @var string
     */
    protected $modelName;

    /**
     * @var \Lyra\AdminBundle\UserState\UserStateInterface
     */
    protected $state;

    /**
     * @var \Lyra\AdminBundle\Grid\ColumnCollectionInterface
     */
    protected $columns;

    /**
     * @var array
     */
    protected $sort;

    /**
     * @var array
     */
    protected $defaultSort;

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

    /**
     * @var \Lyra\AdminBundle\QueryBuilder\QueryBuilderInterface
     */
    protected $queryBuilder;

    public function __construct(PagerInterface $pager, QueryBuilderInterface $queryBuilder, SecurityManagerInterface $securityManager)
    {
        $this->pager = $pager;
        $this->queryBuilder = $queryBuilder;
        $this->securityManager = $securityManager;
    }

    public function setModelName($modelName)
    {
        $this->modelName = $modelName;
    }

    public function getModelName()
    {
        return $this->modelName;
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
        $sort = $this->getSort();
        unset($sort['column']);

        $this->queryBuilder->setSort($sort);
        $this->queryBuilder->setCriteria($this->state->get('criteria'));
        $this->pager->setPage($this->state->get('page'));
        $this->pager->setQueryBuilder($this->queryBuilder);

        return $this->pager;
    }

    public function getQueryBuilder()
    {
        return $this->queryBuilder;
    }

    public function setColumns(ColumnCollectionInterface $columns)
    {
        $this->columns = $columns;
    }

    public function getColumns()
    {
        return $this->columns;
    }

    public function getColumn($columnName)
    {
        return $this->columns[$columnName];
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

    public function setDefaultSort(array $sort)
    {
        $this->defaultSort = $sort;
    }

    public function getDefaultSort()
    {
        return $this->defaultSort;
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

            if (null !== $sort['column']) {
                $sort['field'] = $this->getColumn($sort['column'])->getFieldName();
            } else {
                $sort['field'] = $this->defaultSort['field'];
                $sort['order'] = $this->defaultSort['order'];
            }

            $this->sort = $sort;
        }

        return $this->sort;
    }

    protected function filterAllowedActions($actions)
    {
        $allowed = new ActionCollection();
        foreach ($actions as $action) {
            if ($this->securityManager->isActionAllowed($action->getName())) {
                $allowed->add($action);
            }
        }

        return $allowed;
    }
}
