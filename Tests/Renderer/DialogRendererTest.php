<?php

/*
 * This file is part of the LyraAdminBundle package.
 *
 * Copyright 2011 Massimo Giagnoni <gimassimo@gmail.com>
 *
 * This source file is subject to the MIT license. Full copyright and license
 * information are in the LICENSE file distributed with this source code.
 */

namespace Lyra\AdminBundle\Tests\Renderer;

use Lyra\AdminBundle\Renderer\DialogRenderer;

class DialogRendererTest extends \PHPUnit_Framework_TestCase
{
    private $renderer;

    public function testGetTitle()
    {
        $this->renderer->setAction('delete');
        $this->assertEquals('test_title', $this->renderer->getTitle());
    }

    public function testGetMessage()
    {
        $this->renderer->setAction('delete');
        $this->assertEquals('test_message', $this->renderer->getMessage());
    }

    protected function setUp()
    {
        $this->renderer = new DialogRenderer($this->getOptions());
    }

    private function getOptions()
    {
        return array(
            'actions' => array(
                'delete' => array(
                    'dialog' => array('title' => 'test_title', 'message' => 'test_message')
                ),
            )
        );
    }
}
