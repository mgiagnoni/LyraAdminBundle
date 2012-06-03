<?php

/*
 * This file is part of the LyraAdminBundle package.
 *
 * Copyright 2011-2012 Massimo Giagnoni <gimassimo@gmail.com>
 *
 * This source file is subject to the MIT license. Full copyright and license
 * information are in the LICENSE file distributed with this source code.
 */

namespace Lyra\AdminBundle\Tests\QueryBuilder;

use Lyra\AdminBundle\QueryBuilder\QueryBuilder;

class QueryBuilderTest extends \PHPUnit_Framework_TestCase
{
    private $queryBuilder;

    public function testBuildQueryStringField()
    {
        $this->queryBuilder->setFields(array(
           'field1' => array('type' => 'string')
        ));

        $this->queryBuilder->setCriteria(array(
            'field1' => 'val1'
        ));

        $qb = $this->queryBuilder->buildQuery();

        $this->assertEquals("SELECT WHERE a.field1 LIKE 'val1%'", $qb->getDql());
    }

    public function testBuildQueryDateField()
    {
        $this->queryBuilder->setFields(array(
            'field1' => array('type' => 'date')
        ));

        $this->queryBuilder->setCriteria(array(
            'field1' => array(
                'from' => new \DateTime('1 June 2012'),
                'to' => new \DateTime('15 June 2012')
            )
        ));

        $qb = $this->queryBuilder->buildQuery();

        $this->assertEquals("SELECT WHERE a.field1 >= '2012-06-01 00:00:00' AND a.field1 <= '2012-06-15 00:00:00'", $qb->getDql());
    }

    protected function setUp()
    {
        $queryBuilder = $this->getMockBuilder('Doctrine\ORM\QueryBuilder')
            ->disableOriginalConstructor()
            ->setMethods(array('expr', 'getRootAlias'))
            ->getMock();

        $expr = new \Doctrine\ORM\Query\Expr;

        $queryBuilder->expects($this->any())
            ->method('expr')
            ->will($this->returnValue($expr));

        $queryBuilder->expects($this->any())
            ->method('getRootAlias')
            ->will($this->returnValue('a'));

        $manager = $this->getMock('Lyra\AdminBundle\Model\ModelManagerInterface');
        $manager->expects($this->once())
            ->method('getBaseListQueryBuilder')
            ->will($this->returnValue($queryBuilder));

        $this->queryBuilder = new QueryBuilder($manager);
    }
}
