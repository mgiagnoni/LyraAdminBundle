<?php

/*
 * This file is part of the LyraAdminBundle package.
 *
 * Copyright 2011 Massimo Giagnoni <gimassimo@gmail.com>
 *
 * This source file is subject to the MIT license. Full copyright and license
 * information are in the LICENSE file distributed with this source code.
 */

namespace Lyra\AdminBundle\Model\ORM;

use Lyra\AdminBundle\Model\ModelManager as BaseManager;
use Lyra\AdminBundle\Configuration\AdminConfigurationInterface;
use Doctrine\ORM\EntityManager;

/**
 * Generic model manager class (Doctrine ORM).
 */
class ModelManager extends BaseManager
{
    protected $em;

    protected $class;

    public function __construct(EntityManager $em, AdminConfigurationInterface $configuration)
    {
        $this->em = $em;
        $this->configuration = $configuration;
        $this->setClass($configuration->getOption('class'));
    }

    public function find($id)
    {
        return $this->getRepository()->find($id);
    }

    public function findByIds(array $ids)
    {
        $qb = $this->getRepository()->createQueryBuilder('a');
        $qb->where($qb->expr()->in('a.id', $ids));

        return $qb->getQuery()->getResult();
    }

    public function setFieldValueByIds($field, $value, array $ids)
    {
        $qb = $this->getRepository()->createQueryBuilder('a');
        $qb->update()
            ->set('a.'.$field, $qb->expr()->literal($value))
            ->where($qb->expr()->in('a.id', $ids));

        return $qb->getQuery()->execute();
    }

    public function getBaseListQueryBuilder()
    {
        $qb = $this->getRepository()
            ->createQueryBuilder('a')
            ->select('a');

        return $qb;
    }

    public function getRepository()
    {
        return $this->em->getRepository($this->class);
    }

    public function save($object)
    {
        $this->em->persist($object);
        $this->em->flush();

        return true;
    }

    public function remove($object)
    {
        $this->em->remove($object);
        $this->em->flush();
    }

    public function removeByIds(array $ids)
    {
        $objects = $this->findByIds($ids);

        foreach ($objects as $object) {
            $this->em->remove($object);
        }

        $this->em->flush();
    }

    public function buildQuery($criteria, $sort)
    {
        $qb = $this->getBaseListQueryBuilder();

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
