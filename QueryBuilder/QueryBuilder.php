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

use Lyra\AdminBundle\Configuration\AdminConfigurationInterface;
use Lyra\AdminBundle\Model\ModelManagerInterface;
use Lyra\AdminBundle\UserState\UserStateInterface;

/**
 * Query builder
 */
class QueryBuilder implements QueryBuilderInterface
{
    /**
     * @var \Lyra\AdminBundle\Configuration\AdminConfigurationInterface
     */
    protected $configuration;

    /**
     * @var \Lyra\AdminBundle\UserState\UserStateInterface
     */
    protected $state;

    /**
     * @var mixed
     */
    protected $baseQueryBuilder;

    public function __construct(ModelManagerInterface $manager, AdminConfigurationInterface $configuration)
    {
        $this->configuration = $configuration;
        $this->setBaseQueryBuilder($manager->getBaseListQueryBuilder());
    }

    public function setState(UserStateInterface $state)
    {
        $this->state = $state;
    }

    public function getState()
    {
        return $this->state;
    }

    public function setBaseQueryBuilder($queryBuilder)
    {
        $this->baseQueryBuilder = $queryBuilder;
    }

    public function getBaseQueryBuilder()
    {
        return $this->baseQueryBuilder;
    }

    public function buildQuery()
    {
        $sort = array(
            'column' => $this->state->get('column'),
            'order' => $this->state->get('order')
        );

        if (null !== $sort['column']) {
            $sort['field'] = $this->configuration->getListColumnOption($sort['column'], 'field');
        } else {
            $default = $this->configuration->getListOption('default_sort');
            $sort['field'] = $default['field'];
        }

        $criteria = $this->state->get('criteria');

        $qb = $this->getBaseQueryBuilder();

        $this->addFilterCriteria($qb, $criteria);
        $this->addSort($qb, $sort);

        return $qb;
    }

    protected function addFilterCriteria($qb, $criteria)
    {
        $fields = $this->configuration->getOption('fields');
        $alias = $qb->getRootAlias();

        foreach ($criteria as $field => $value) {
            if(null === $value || '' == $value) {
                continue;
            }

            if (isset($fields[$field])) {
                switch ($fields[$field]['type']) {
                    case 'string':
                    case 'text':
                        $qb->andWhere(
                            $qb->expr()->like($alias.'.'.$field, $qb->expr()->literal($value.'%'))
                        );
                        break;
                    case 'date':
                    case 'datetime':
                        if (null !== $value['from']) {
                            $qb->andWhere(
                                $qb->expr()->gte($alias.'.'.$field, $this->formatDate($qb, $value['from']))
                            );
                        }
                        if (null !== $value['to']) {
                            $qb->andWhere(
                                $qb->expr()->lte($alias.'.'.$field, $this->formatDate($qb, $value['to']))
                            );
                        }
                        break;
                    case 'boolean':
                        $qb->andWhere(
                            $qb->expr()->eq($alias.'.'.$field, $value)
                        );

                         break;
                    case 'entity':
                        $qb->andWhere(
                            $qb->expr()->eq($field.'.id', $value->getId())
                        );
                        break;
                }
            }
        }
    }

    protected function addSort($qb, $sort)
    {
        if (null !== $sort['field']) {
            $sortField = false !== strpos($sort['field'], '.') ? $sort['field'] : $qb->getRootAlias().'.'.$sort['field'];
            $qb->orderBy($sortField, $sort['order']);
        }
    }

    protected function formatDate($qb, $date)
    {
        return $qb->expr()->literal($date->format('Y-m-d H:i:s'));
    }
}

