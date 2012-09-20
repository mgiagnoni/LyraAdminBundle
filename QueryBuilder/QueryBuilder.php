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

use Lyra\AdminBundle\Model\ModelManagerInterface;

/**
 * Query builder
 */
class QueryBuilder implements QueryBuilderInterface
{
    /**
     * @var mixed
     */
    protected $baseQueryBuilder;

    /**
     * @var array
     */
    protected $sort;

    /**
     * @var array
     */
    protected $criteria;

    /**
     * @var array
     */
    protected $fields;

    /**
     * @param \Lyra\AdminBundle\Model\ModelManagerInterface $manager
     */
    public function __construct(ModelManagerInterface $manager)
    {
        $this->setBaseQueryBuilder($manager->getBaseListQueryBuilder());
    }

    public function setBaseQueryBuilder($queryBuilder)
    {
        $this->baseQueryBuilder = $queryBuilder;
    }

    public function getBaseQueryBuilder()
    {
        return $this->baseQueryBuilder;
    }

    public function setSort(array $sort)
    {
        $this->sort = $sort;
    }

    public function getSort()
    {
        return $this->sort;
    }

    public function setCriteria(array $criteria)
    {
        $this->criteria = $criteria;
    }

    public function getCriteria()
    {
        return $this->criteria;
    }

    public function setFields(array $fields)
    {
        $this->fields = $fields;
    }

    public function getFields()
    {
        return $this->fields;
    }

    public function buildQuery()
    {
        $qb = $this->getBaseQueryBuilder();

        $this->addFilterCriteria($qb, $this->criteria);
        $this->addSort($qb, $this->sort);

        return $qb;
    }

    protected function addFilterCriteria($qb, $criteria)
    {
        $alias = $qb->getRootAlias();

        foreach ($criteria as $field => $value) {
            if(null === $value || '' == $value) {
                continue;
            }

            if (isset($this->fields[$field])) {
                switch ($this->fields[$field]['type']) {
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
                    case 'integer':
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

