<?php

/*
 * This file is part of the LyraAdminBundle package.
 *
 * Copyright 2011 Massimo Giagnoni <gimassimo@gmail.com>
 *
 * This source file is subject to the MIT license. Full copyright and license
 * information are in the LICENSE file distributed with this source code.
 */

namespace Lyra\AdminBundle\Tests\Functional;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class AdminControllerTest extends WebTestCase
{
    public function testIndex()
    {
        $client = static::createClient();
        $container = $client->getContainer();
        $manager = $container->get('lyra_admin.product.model_manager');

        // Empty list
        $crawler = $client->request('GET', '/admin/product/list');
        $this->assertTrue($crawler->filter('td.no-records')->count() == 1);

        // Add records
        $product = $manager->create();
        $product->setName('test');
        $product->setDescription('description');
        $product->setPrice('3.576');
        $manager->save($product);

        $product = $manager->create();
        $product->setName('test2');
        $product->setDescription('description2');
        $product->setPrice('1.23');
        $manager->save($product);

        $crawler = $client->reload();

        // List headers
        $attrs = $crawler->filter('table.ly-list th')->extract('class');
        // $attrs[0] is batch select box
        $this->assertEquals('sorted-asc col-name string', $attrs[1]);
        $this->assertEquals('col-description text', $attrs[2]);
        $this->assertEquals('sortable col-price float', $attrs[3]);

        // Sort links
        $this->assertTrue($crawler->filter('table.ly-list th.col-description a')->count() == 0);
        $href = $crawler->filter('table.ly-list th.col-name a')->attr('href');
        $this->assertRegexp('#name\/desc#', $href);
        $href = $crawler->filter('table.ly-list th.col-price a')->attr('href');
        $this->assertRegexp('#price\/asc#', $href);

        // Check order
        $values = $crawler->filter('table.ly-list tbody tr')->children()->extract('_text');
        $this->assertEquals('test', trim($values[1]));

        // Sort list by price
        $link = $crawler->selectLink('Price')->link();
        $crawler = $client->click($link);

        // List headers
        $attrs = $crawler->filter('table.ly-list th')->extract('class');
        // Column name
        $this->assertRegexp('#sortable#', $attrs[1]);
        // Column price
        $this->assertRegexp('#sorted-asc#', $attrs[3]);

        // Check order
        $values = $crawler->filter('table.ly-list tbody tr')->children()->extract('_text');
        $this->assertEquals('test2', trim($values[1]));
    }

    public function testNew()
    {
        $client = static::createClient();

        // New form
        $crawler = $client->request('GET', '/admin/product/new');
        $form = $crawler->filter('form.ly-form');
        $this->assertTrue($form->count() == 1);
        $this->assertStringEndsWith('admin/product/new', $form->attr('action'));

        // Product fields
        $this->assertEquals(1, $crawler->filter('input#product_name')->count());
        $this->assertEquals(1, $crawler->filter('input#product_price')->count());
        $this->assertEquals(1, $crawler->filter('textarea#product_description')->count());
    }

    protected function setup()
    {
        $client = static::createClient();
        $container = $client->getContainer();
        $em = $container->get('doctrine')->getEntityManager();
        $schemaTool = new \Doctrine\ORM\Tools\SchemaTool($em);
        $schemaTool->dropDatabase();
        $metadata = $em->getMetadataFactory()->getAllMetadata();
        if (!empty($metadata)) {
            $schemaTool->createSchema($metadata);
        }
    }
}
