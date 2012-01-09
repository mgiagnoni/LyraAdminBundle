<?php

/*
 * This file is part of the LyraAdminBundle package.
 *
 * Copyright 2011 Massimo Giagnoni <gimassimo@gmail.com>
 *
 * This source file is subject to the MIT license. Full copyright and license
 * information are in the LICENSE file distributed with this source code.
 */

namespace Lyra\AdminBundle\Tests\Model\ORM;

use Lyra\AdminBundle\Model\ORM\ModelManager;
use Lyra\AdminBundle\Configuration\AdminConfiguration;
use Lyra\AdminBundle\Tests\Fixture\Entity\Dummy;

class ModelManagerTest extends \PHPUnit_Framework_TestCase
{
    protected $em;
    protected $repository;
    protected $manager;

    public function testFind()
    {
        $objectId = 'id';

        $this->repository->expects($this->once())
            ->method('find')
            ->with($objectId);

        $this->manager->find($objectId);
    }

    public function testFindByIds()
    {
        $this->setExpectedQuery('SELECT WHERE a.id IN(1, 2, 3)');
        $this->manager->findByIds(array(1,2,3));
    }

    public function testFieldValueByIds()
    {
        $this->setExpectedQuery("UPDATE SET a.field = 'value' WHERE a.id IN(1, 2, 3)");
        $this->manager->setFieldvalueByIds('field', 'value', array(1,2,3));
    }

    public function testGetBaseQueryBuilder()
    {
        $qb = $this->manager->getBaseListQueryBuilder();
        $this->assertEquals('SELECT a', $qb->getDQL());
    }

    public function testGetRepository()
    {
        $this->assertEquals($this->repository, $this->manager->getRepository());
    }

    public function testSave()
    {
        $object = new Dummy;

        $this->em->expects($this->once())
            ->method('persist')
            ->with($object);

        $this->manager->save($object);
    }

    public function testRemove()
    {
        $object = new Dummy;

        $this->em->expects($this->once())
            ->method('remove')
            ->with($object);

        $this->manager->remove($object);
    }

    public function testRemoveByIds()
    {
        $object1 = new Dummy;
        $object1->setField1('test1');
        $object2 = new Dummy;
        $object2->setField1('test2');
        $ids = array(1,2);

        $manager = $this->getMockBuilder('Lyra\AdminBundle\Model\ORM\ModelManager')
            ->setConstructorArgs(array($this->em, $this->configuration))
            ->setMethods(array('findByIds'))
            ->getMock();

        $manager->expects($this->once())
            ->method('findByIds')
            ->with($ids)
            ->will($this->returnValue(array($object1, $object2)));

        $this->em->expects($this->at(0))
            ->method('remove')
            ->with($object1);

        $this->em->expects($this->at(1))
            ->method('remove')
            ->with($object2);

        $manager->removeByIds($ids);
    }

    protected function setUp()
    {
        $options = array(
            'class' => 'Lyra\AdminBundle\Tests\Fixture\Entity\Dummy'
        );

        $this->configuration = new AdminConfiguration($options);

        $this->em = $this->getMockBuilder('Doctrine\ORM\EntityManager')
            ->disableOriginalConstructor()
            ->getMock();

        $this->manager = new ModelManager($this->em, $this->configuration);

        $this->repository = $this->getMockBuilder('Doctrine\ORM\EntityRepository')
            ->disableOriginalConstructor()
            ->getMock();

        $queryBuilder = $this->getMockBuilder('Doctrine\ORM\QueryBuilder')
            ->setConstructorArgs(array($this->em))
            ->setMethods(array('expr'))
            ->getMock();

        $expr = new \Doctrine\ORM\Query\Expr;

        $queryBuilder->expects($this->any())
            ->method('expr')
            ->will($this->returnValue($expr));

        $this->repository->expects($this->any())
            ->method('createQueryBuilder')
            ->will($this->returnValue($queryBuilder));

        $this->em->expects($this->any())
            ->method('getRepository')
            ->will($this->returnValue($this->repository));
    }

    protected function setExpectedQuery($dql)
    {
        $query = $this->getMockBuilder('Lyra\AdminBundle\Tests\Fixture\ORM\DummyQuery')
            ->disableOriginalConstructor()
            ->setMethods(array('getResult', 'execute'))
            ->getMock();

        $this->em->expects($this->once())
            ->method('createQuery')
            ->with($dql)
            ->will($this->returnValue($query));
    }
}
