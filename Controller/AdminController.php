<?php

/*
 * This file is part of the LyraAdminBundle package.
 *
 * Copyright 2011 Massimo Giagnoni <gimassimo@gmail.com>
 *
 * This source file is subject to the MIT license. Full copyright and license
 * information are in the LICENSE file distributed with this source code.
 */

namespace Lyra\AdminBundle\Controller;

use Symfony\Component\DependencyInjection\ContainerAware;
use Symfony\Component\HttpFoundation\RedirectResponse;

/**
 * Base controller to manage CRUD actions.
 */
class AdminController extends ContainerAware
{
    /**
     * Displays a list of objects.
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function indexAction()
    {
        $filterRenderer = $this->getFilterRenderer();
        $listRenderer = $this->getListRenderer();
        $listRenderer->setFilterCriteria($this->getFilterCriteria());
        $listRenderer->setPage($this->getCurrentPage());
        $listRenderer->setSort($this->getSort());
        $listRenderer->setBaseQueryBuilder($this->getModelManager()->getBaseListQueryBuilder());

        return $this->container->get('templating')
            ->renderResponse($listRenderer->getTemplate(), array(
                'renderer' => $listRenderer,
                'filter' => $filterRenderer
            ));
    }

    /**
     * Creates a new object.
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function newAction()
    {
        $object = $this->getModelManager()->create();
        $form = $this->getFormRenderer()->getForm($object);

        $request = $this->getRequest();
        if ('POST' == $request->getMethod()) {
            $form->bindRequest($request);
            if ($form->isValid() && $this->getModelManager()->save($object)) {
                return $this->getRedirectToListResponse();
            }
        }

        return $this->getRenderFormResponse($form);
    }

    /**
     * Edits an object.
     *
     * @param mixed $id object primary key
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function editAction($id)
    {
        $object = $this->getModelManager()->find($id);
        $form = $this->getFormRenderer()->getForm($object);

        $request = $this->getRequest();
        if ('POST' == $request->getMethod()) {
            $form->bindRequest($request);
            if ($form->isValid() && $this->getModelManager()->save($object)) {
                return $this->getRedirectToListResponse();
            }
        }

        return $this->getRenderFormResponse($form);
    }

    /**
     * Deletes an object.
     *
     * @param mixed $id object primary key
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function deleteAction($id)
    {
        $object = $this->getModelManager()->find($id);

        $form = $this->container->get('form.factory')
            ->createBuilder('form')
            ->getForm();

        if ('POST' === $this->getRequest()->getMethod()) {
            $this->getModelManager()->remove($object);
            $this->setFlash('lyra_admin success', 'flash.delete.success');

            return $this->getRedirectToListResponse();
        }

        $renderer = $this->getDialogRenderer();
        $renderer->setRouteParams(array('id' => $object->getId()));

        return $this->container->get('templating')
            ->renderResponse('LyraAdminBundle:Admin:delete.html.twig', array(
                'object' => $object,
                'form' => $form->createView(),
                'renderer' => $renderer
            ));
    }

    /**
     * Action triggered by boolean switches and other list buttons.
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function objectAction()
    {
        $reqAction = $this->getRequest()->get('action');
        $action = key($reqAction);
        if(is_array($reqAction[$action])){
            $id = key($reqAction[$action]);
        }
        $response = $colName = null;

        if ('batch' == $action) {
            $action = null;
            if (null === $id = $this->getRequest()->get('ids')) {
                // TODO setflash
            } else if ($action =  $this->getRequest()->get('batch_action')) {
                $action =  'Batch'.$action;
            }
        } else if (false !== strpos($action, '_boolean')) {
            $parts = explode('_', $action);
            if (count($parts) > 2 && in_array($parts[2], array('on','off'))) {
                $action = 'Boolean';
                $colName = $parts[3];
                $colValue = 'on' === $parts[2];
            }
        }

        if ($action) {
            $method = 'execute'.$action;
            $response = $colName ? $this->$method($id, $colName, $colValue) : $this->$method($id);
        }

        if (null !== $response) {
            return $response;
        }

        return $this->getRedirectToListResponse();
    }

    public function filterAction($reset)
    {
        $request = $this->getRequest();
        if ($reset)  {
            $this->container->get('session')->set($this->getModelName().'.criteria', array());
        } else if ('POST' == $request->getMethod()) {
            $form = $this->getFilterRenderer()->getForm();
            $form->bindRequest($request);
            $this->container->get('session')->set($this->getModelName().'.criteria', $form->getData());
        }

        return $this->getRedirectToListResponse();
    }

    /**
     * Gets the Request service.
     *
     * @return \Symfony\Component\HttpFoundation\Request
     */
    public function getRequest()
    {
        return $this->container->get('request');
    }

    public function getSession()
    {
        return $this->container->get('session');
    }

    /**
     * Gets a list renderer service.
     *
     * @param string $name model name
     *
     * @return \Lyra\AdminBundle\Renderer\ListRenderer
     */
    public function getListRenderer($name = null)
    {
        return $this->container->get('lyra_admin.renderer_factory')->getListRenderer($name);
    }

    /**
     * Gets a form renderer service.
     *
     * @param string $name model name
     *
     * @return \Lyra\AdminBundle\Renderer\FormRenderer
     */
    public function getFormRenderer($name = null)
    {
        return $this->container->get('lyra_admin.renderer_factory')->getFormRenderer($name);
    }

    /**
     * Gets a dialog renderer service.
     *
     * @param string $name model name
     *
     * @return \Lyra\AdminBundle\Renderer\DialogRenderer
     */
    public function getDialogRenderer($name = null)
    {
        return $this->container->get('lyra_admin.renderer_factory')->getDialogRenderer($name);
    }

    /**
     * Gets a filter renderer service.
     *
     * @param string $name model name
     *
     * @return \Lyra\AdminBundle\Renderer\FilterRenderer
     */
    public function getFilterRenderer($name = null)
    {
        return $this->container->get('lyra_admin.renderer_factory')->getFilterRenderer($name);
    }

    /**
     * Gets a model manager service.
     *
     * @param string $name model name
     *
     * @return \Lyra\AdminBundle\Model\ModelManager
     */
    public function getModelManager($name = null)
    {
        return $this->container->get('lyra_admin.model_manager_factory')->getModelManager($name);
    }

    /**
     * Returns the response to redirect to the list of objects.
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    protected function getRedirectToListResponse()
    {
        $renderer = $this->getListrenderer();

        return new RedirectResponse(
            $this->container->get('router')->generate($renderer->getRoutePrefix().'_index')
        );
    }

    /**
     * Returns the response to render the form.
     *
     * @param \Symfony\Component\Form\Form $form
     * @return \Symfony\Component\HttpFoundation\Response
     */
    protected function getRenderFormResponse($form)
    {
        $object = $form->getData();
        $renderer = $this->getFormRenderer();

        if ('edit' == $renderer->getAction()) {
            $renderer->setRouteParams(array('id' => $object->getId()));
        }

        return $this->container->get('templating')
            ->renderResponse($renderer->getTemplate(), array(
                'renderer' => $renderer,
            ));
    }

    protected function setFlash($action, $value)
    {
        $this->container->get('session')->setFlash($action, $value);
    }

    protected function executeBatchDelete($ids)
    {
        $form = $this->container->get('form.factory')
            ->createBuilder('form')
            ->getForm();

        if ($this->getRequest()->get('batch_confirm')) {
            $this->setFlash('lyra_admin success', 'flash.batch_delete.success');
            $this->getModelManager()->removeByIds($ids);

            return $this->getRedirectToListResponse();
        }

        $renderer = $this->getDialogRenderer();
        $renderer->setAction('delete');

        return $this->container->get('templating')
            ->renderResponse('LyraAdminBundle:Admin:batch_dialog.html.twig', array(
                'ids' => $ids,
                'form' => $form->createView(),
                'renderer' => $renderer
            ));
    }

    protected function executeBoolean($id, $colName, $colValue)
    {
        if ($this->getListRenderer()->hasBooleanActions($colName)) {
            $object = $this->getModelManager()->find($id);
            $method = 'set'.ucfirst($colName);
            $object->$method($colValue);
            $this->getModelManager()->save($object);
        }
    }

    protected function getFilterCriteria()
    {
        return $this->container->get('session')->get($this->getModelName().'.criteria', array());
    }

    protected function getSort()
    {
        if ($field = $this->getRequest()->get('field')) {
            $this->getSession()->set($this->getModelName().'.field', $field);
            $this->getSession()->set($this->getModelName().'.sort.order', $this->getRequest()->get('order'));
        }

        $default = $this->getListRenderer()->getDefaultSort();
        $sort = array('field' => $this->getSession()->get($this->getModelName().'.field', $default['field']), 'order' => $this->getSession()->get($this->getModelName().'.sort.order', $default['order']));

        return $sort;
    }

    protected function getCurrentPage()
    {
        if ($page = $this->getRequest()->get('page')) {
            $this->getSession()->set($this->getModelName().'.page', $page);
        }

        return $this->getSession()->get($this->getModelName().'.page', 1);
    }

    protected function getModelName()
    {
        if (null === $name = $this->getRequest()->get('lyra_admin_model')) {
           throw new \InvalidArgumentException('Unspecified model name, lyra_admin_model parameter not present in Request');
        }

        return $name;
    }
}
