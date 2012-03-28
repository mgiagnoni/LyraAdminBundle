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
use Lyra\AdminBundle\Configuration\AdminConfiguration;

class AdminControllerTest extends \PHPUnit_Framework_TestCase
{
    protected $services;
    protected $listRenderer;
    protected $filterRenderer;
    protected $session;

    public function testIndexAction()
    {
        $this->services['request'] = Request::create('/', 'GET', array('lyra_admin_model' => 'test'));

        $this->pager->expects($this->once())
            ->method('setPage')
            ->with(1);

        $this->listRenderer->expects($this->once())
            ->method('setSort')
            ->with(array('column' => null, 'field' => null, 'order' => null));

        $this->setIndexExpects();

        $controller = new AdminController();
        $controller->setContainer($this->getMockContainer());
        $controller->indexAction();
    }

    public function testIndexActionPageFromRequest()
    {
        $this->services['request'] = Request::create('/', 'GET', array('page' => 2, 'lyra_admin_model' => 'test'));

        $this->pager->expects($this->once())
            ->method('setPage')
            ->with(2);

        $this->setIndexExpects();

        $controller = new AdminController();
        $controller->setContainer($this->getMockContainer());
        $controller->indexAction();

        $this->assertEquals(2, $this->session->get('test.page'));
    }

    public function testIndexActionPageFromSession()
    {
        $this->session->set('test.page', 3);
        $this->services['request'] = Request::create('/', 'GET', array('lyra_admin_model' => 'test'));

        $this->pager->expects($this->once())
            ->method('setPage')
            ->with(3);

        $this->setIndexExpects();

        $controller = new AdminController();
        $controller->setContainer($this->getMockContainer());
        $controller->indexAction();
    }

    public function testIndexActionSortFromRequest()
    {
        $this->services['request'] = Request::create('/', 'GET', array('column' => 'name', 'order' => 'desc', 'lyra_admin_model' => 'test'));
        $this->listRenderer->expects($this->once())
            ->method('setSort')
            ->with(array('column' => 'name', 'field' => 'name', 'order' => 'desc'));

        $this->setIndexExpects();

        $controller = new AdminController();
        $controller->setContainer($this->getMockContainer());
        $controller->indexAction();

        $this->assertEquals('name', $this->session->get('test.sort.column'));
        $this->assertEquals('desc', $this->session->get('test.sort.order'));
    }

    public function testIndexActionSortFromSession()
    {
        $this->session->set('test.sort.column', 'name');
        $this->session->set('test.sort.order', 'desc');
        $this->services['request'] = Request::create('/', 'GET', array('lyra_admin_model' => 'test'));

        $this->listRenderer->expects($this->once())
            ->method('setSort')
            ->with(array('column' => 'name', 'field' => 'name','order' => 'desc'));

        $this->setIndexExpects();

        $controller = new AdminController();
        $controller->setContainer($this->getMockContainer());
        $controller->indexAction();
    }

    public function testFilterAction()
    {
        $this->services['request'] = Request::create('/', 'POST', array('lyra_admin_model' => 'test'));

        $form = $this->getMockBuilder('Symfony\Component\Form\Form')
            ->disableOriginalConstructor()
            ->getMock();

        $form->expects($this->once())
            ->method('bindRequest')
            ->with($this->services['request']);

        $form->expects($this->once())
            ->method('getData')
            ->will($this->returnValue(array('field' => 'value')));

        $this->filterRenderer->expects($this->once())
            ->method('getForm')
            ->will($this->returnValue($form));

        $this->filterRenderer->expects($this->once())
            ->method('getFilterFields')
            ->will($this->returnValue(array()));

        $this->services['router']->expects($this->exactly(2))
            ->method('generate')
            ->will($this->returnValue('/'));

        $controller = new AdminController();
        $controller->setContainer($this->getMockContainer());
        $controller->filterAction('save');

        $this->assertEquals(array('field' => 'value'), $this->session->get('test.criteria'));

        $this->services['request'] = Request::create('/', 'GET', array('lyra_admin_model' => 'test'));
        $controller->filterAction('reset');

        $this->assertEquals(array(), $this->session->get('test.criteria'));
    }

    protected function setUp()
    {
        $options = array(
            'list' => array(
                'default_sort' => array('column' => null, 'field' => null, 'order' => null),
                'max_page_rows' => 5,
                'columns' => array('name' => array('field' => 'name'))
            )
        );

        $configuration = New AdminConfiguration($options);
        $this->listRenderer = $this->getMock('Lyra\AdminBundle\Renderer\ListRendererInterface');
        $this->filterRenderer = $this->getMock('Lyra\AdminBundle\Renderer\FilterRendererInterface');

        $modelManager = $this->getMock('Lyra\AdminBundle\Model\ModelManagerInterface');
        $this->pager = $this->getMock('Lyra\AdminBundle\Pager\PagerInterface');

        $this->session = new Session(new ArraySessionStorage());
        $templating = $this->getMock('Symfony\Bundle\FrameworkBundle\Templating\EngineInterface');
        $csrfProvider = $this->getMock('Symfony\Component\Form\Extension\Csrf\CsrfProvider\CsrfProviderInterface');
        $router = $this->getMock('Symfony\Component\Routing\RouterInterface');

        $this->services = array(
            'session' => $this->session,
            'lyra_admin.test.configuration' => $configuration,
            'lyra_admin.test.list_renderer' => $this->listRenderer,
            'lyra_admin.test.filter_renderer' => $this->filterRenderer,
            'lyra_admin.test.model_manager' => $modelManager,
            'lyra_admin.pager' => $this->pager,
            'templating' => $templating,
            'form.csrf_provider' => $csrfProvider,
            'router' => $router
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

    protected function setIndexExpects()
    {
        $form = $this->getMockBuilder('Symfony\Component\Form\Form')
            ->disableOriginalConstructor()
            ->getMock();

        $form->expects($this->once())
            ->method('createView');

        $this->filterRenderer->expects($this->once())
            ->method('getForm')
            ->will($this->returnValue($form));
    }
}
