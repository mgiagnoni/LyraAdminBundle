<?php

/*
 * This file is part of the LyraAdminBundle package.
 *
 * Copyright 2011 Massimo Giagnoni <gimassimo@gmail.com>
 *
 * This source file is subject to the MIT license. Full copyright and license
 * information are in the LICENSE file distributed with this source code.
 */

namespace Lyra\AdminBundle\Tests\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\SessionStorage\ArraySessionStorage;
use Symfony\Component\HttpFoundation\Session;
use Lyra\AdminBundle\Controller\AdminController;

class AdminControllerTest extends \PHPUnit_Framework_TestCase
{
    protected $services;
    protected $listRenderer;
    protected $session;

    public function testIndexAction()
    {
        $this->services['request'] = Request::create('/', 'GET', array('lyra_admin_model' => 'test'));

        $this->listRenderer->expects($this->once())
            ->method('setPage')
            ->with(1);

        $this->listRenderer->expects($this->once())
            ->method('setSort')
            ->with(array('field' => null, 'order' => null));

        $this->listRenderer->expects($this->once())
            ->method('setFilterCriteria')
            ->with(array());

        $controller = new AdminController();
        $controller->setContainer($this->getMockContainer());
        $controller->indexAction();
    }

    public function testIndexActionPageFromRequest()
    {
        $this->services['request'] = Request::create('/', 'GET', array('page' => 2, 'lyra_admin_model' => 'test'));

        $this->listRenderer->expects($this->once())
            ->method('setPage')
            ->with(2);

        $controller = new AdminController();
        $controller->setContainer($this->getMockContainer());
        $controller->indexAction();

        $this->assertEquals(2, $this->session->get('test.page'));
    }

    public function testIndexActionPageFromSession()
    {
        $this->session->set('test.page', 3);
        $this->services['request'] = Request::create('/', 'GET', array('lyra_admin_model' => 'test'));

        $this->listRenderer->expects($this->once())
            ->method('setPage')
            ->with(3);

        $controller = new AdminController();
        $controller->setContainer($this->getMockContainer());
        $controller->indexAction();
    }

    public function testIndexActionSortFromRequest()
    {
        $this->services['request'] = Request::create('/', 'GET', array('field' => 'name', 'order' => 'desc', 'lyra_admin_model' => 'test'));
        $this->listRenderer->expects($this->once())
            ->method('setSort')
            ->with(array('field' => 'name', 'order' => 'desc'));

        $controller = new AdminController();
        $controller->setContainer($this->getMockContainer());
        $controller->indexAction();

        $this->assertEquals('name', $this->session->get('test.field'));
        $this->assertEquals('desc', $this->session->get('test.sort.order'));
    }

    protected function setUp()
    {
        $this->listRenderer = $this->getMockBuilder('Lyra\AdminBundle\Renderer\ListRenderer')
            ->disableOriginalConstructor()
            ->getMock();

        $rendererFactory = $this->getMockBuilder('Lyra\AdminBundle\Renderer\RendererFactory')
            ->disableOriginalConstructor()
            ->getMock();

        $rendererFactory->expects($this->once())
            ->method('getListRenderer')
            ->will($this->returnValue($this->listRenderer));

        $modelManager = $this->getMock('Lyra\AdminBundle\Model\ModelManagerInterface');
        $managerFactory = $this->getMock('Lyra\AdminBundle\Model\ModelManagerFactoryInterface');

        $managerFactory->expects($this->once())
            ->method('getModelManager')
            ->will($this->returnValue($modelManager));

        $this->session = new Session(new ArraySessionStorage());
        $templating = $this->getMock('Symfony\Bundle\FrameworkBundle\Templating\EngineInterface');
        $csrfProvider = $this->getMock('Symfony\Component\Form\Extension\Csrf\CsrfProvider\CsrfProviderInterface');

        $this->services = array(
            'session' => $this->session,
            'lyra_admin.renderer_factory' => $rendererFactory,
            'lyra_admin.model_manager_factory' => $managerFactory,
            'templating' => $templating,
            'form.csrf_provider' => $csrfProvider
        );
    }

    protected function getMockContainer()
    {
        $services = $this->services;
        $container = $this->getMock('Symfony\Component\DependencyInjection\ContainerInterface');

        $container->expects($this->any())
            ->method('get')
            ->will($this->returnCallBack(function($id) use ($services) {
                if (isset($services[$id])) {
                    return $services[$id];
                }
            }));

        return $container;
    }
}
