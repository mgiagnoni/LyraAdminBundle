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
use Lyra\AdminBundle\Tests\Fixture\Entity\Dummy;

class ModelManagerTest extends \PHPUnit_Framework_TestCase
{
    protected $em;
    protected $repository;

    public function testFind()
    {
        $objectId = 'id';

        $this->repository->expects($this->once())
            ->method('find')
            ->with($objectId);

        $manager = new ModelManager($this->em);
        $manager->find($objectId);
    }

    public function testFindByIds()
    {
        $this->setExpectedQuery('SELECT WHERE a.id IN(1, 2, 3)');
        $manager = new ModelManager($this->em);
        $manager->findByIds(array(1,2,3));
    }

    public function testFieldValueByIds()
    {
        $this->setExpectedQuery("UPDATE SET a.field = 'value' WHERE a.id IN(1, 2, 3)");
        $manager = new ModelManager($this->em);
        $manager->setFieldvalueByIds('field', 'value', array(1,2,3));
    }

    public function testGetBaseQueryBuilder()
    {
        $manager = new ModelManager($this->em);
        $qb = $manager->getBaseListQueryBuilder();

        $this->assertEquals('SELECT a', $qb->getDQL());
    }

    public function testGetRepository()
    {
        $manager = new ModelManager($this->em);

        $this->assertEquals($this->repository, $manager->getRepository());
    }

    public function testSave()
    {
        $object = new Dummy;

        $this->em->expects($this->once())
            ->method('persist')
            ->with($object);

        $manager = new ModelManager($this->em);
        $manager->save($object);
    }

    public function testRemove()
    {
        $object = new Dummy;

        $this->em->expects($this->once())
            ->method('remove')
            ->with($object);

        $manager = new ModelManager($this->em);
        $manager->remove($object);
    }

    public function testRemoveByIds()
    {
        $object1 = new Dummy;
        $object1->setField1('test1');
        $object2 = new Dummy;
        $object2->setField1('test2');
        $ids = array(1,2);

        $manager = $this->getMockBuilder('Lyra\AdminBundle\Model\ORM\ModelManager')
            ->setConstructorArgs(array($this->em))
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
        $this->em = $this->getMockBuilder('Doctrine\ORM\EntityManager')
            ->disableOriginalConstructor()
            ->getMock();

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
