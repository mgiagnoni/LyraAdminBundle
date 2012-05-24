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
        $grid = $this->getGrid();
        // Initializes grid persistent states (page, sort, criteria)
        $grid->getState()->initFromRequest($this->getRequest());

        return $this->container->get('templating')
            ->renderResponse($grid->getTemplate(), array(
                'grid' => $grid,
                'filter' => $this->getFilterRenderer(),
                'csrf' => $this->container->get('form.csrf_provider')->generateCsrfToken('list'),
            ));
    }

    /**
     * Creates a new object.
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function newAction()
    {
        $this->getSecurityManager()->allowOr403('new');
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
        $this->getSecurityManager()->allowOr403('edit');
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
     * Shows an object.
     *
     * @param mixed $id object primary key
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function showAction($id)
    {
        $renderer = $this->getShowRenderer();
        $object = $this->getModelManager()->find($id);
        $renderer->setObject($object);

        return $this->container->get('templating')
            ->renderResponse('LyraAdminBundle:Show:show.html.twig', array(
                'renderer' => $renderer,
            ));
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
        $this->getSecurityManager()->allowOr403('delete');
        $object = $this->getModelManager()->find($id);
        $request = $this->getRequest();

        if ('POST' === $request->getMethod()) {
            if ($this->container->get('form.csrf_provider')->isCsrfTokenValid('delete', $request->get('_token'))) {
                $this->getModelManager()->remove($object);
                $this->setFlash('lyra_admin success', 'flash.delete.success');
            }

            return $this->getRedirectToListResponse();
        }

        $actions = $this->getActions();

        return $this->container->get('templating')
            ->renderResponse('LyraAdminBundle:Dialog:delete.html.twig', array(
                'object' => $object,
                'csrf' => $this->container->get('form.csrf_provider')->generateCsrfToken('delete'),
                'action' => $actions->get('delete'),
                'cancel' => $actions->get('index')
            ));
    }

    /**
     * Action triggered by boolean switches and batch actions submit.
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function objectAction()
    {
        $action = $this->getRequest()->get('action');
        $response = null;

        switch(key($action)) {
            case '_boolean':
                $this->processBooleanAction($action);
                break;
            case 'batch':
                $response = $this->processBatchAction();
                break;
        }

        return null !== $response ? $response : $this->getRedirectToListResponse();
    }

    public function filterAction($action)
    {
        $response = null;
        switch ($action) {
            case 'save':
                if ('POST' == $this->getRequest()->getMethod()) {
                    $response = $this->saveFilterCriteria();
                }
                break;
            case 'reset':
                $this->getFilterRenderer()->resetCriteria();
                break;
            case 'criteria':
                $response = $this->showFilterCriteria();
                break;
        }

        return null !== $response ? $response : $this->getRedirectToListResponse();
    }

    public function navigationAction()
    {
        $menu = $this->container->getParameter('lyra_admin.menu');

        return $this->container->get('templating')
            ->renderResponse('LyraAdminBundle:Admin:navigation.html.twig', array(
                'menu' => $menu
            ));
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

    public function getConfiguration($name = null)
    {
        return $this->container->get(sprintf('lyra_admin.%s.configuration', $name ?: $this->getModelName()));
    }

    /**
     * Gets a grid instance configured for a given model.
     *
     * @param null|string $name model name, if null model name is taken from Request
     *
     * @return \Lyra\AdminBundle\Grid\Grid
     */
    public function getGrid($name = null)
    {
        return $this->container->get(sprintf('lyra_admin.%s.grid', $name ?: $this->getModelName()));
    }

    /**
     * Gets a show renderer service.
     *
     * @param string $name model name
     *
     * @return \Lyra\AdminBundle\Renderer\ShowRenderer
     */
    public function getShowRenderer($name = null)
    {
        return $this->container->get(sprintf('lyra_admin.%s.show_renderer', $name ?: $this->getModelName()));
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
        $renderer = $this->container->get(sprintf('lyra_admin.%s.form_renderer', $name ?: $this->getModelName()));
        $renderer->setAction($this->getRequest()->get('lyra_admin_action'));

        return $renderer;
    }

    /**
     * Gets action collection configured for the model.
     *
     * @param string $name model name
     *
     * @return \Lyra\AdminBundle\Action\ActionCollectionInterface
     */
    public function getActions($name = null)
    {
       return $this->container->get(sprintf('lyra_admin.%s.actions', $name ?: $this->getModelName()));
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
        return $this->container->get(sprintf('lyra_admin.%s.filter_renderer', $name ?: $this->getModelName()));
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
        return $this->container->get(sprintf('lyra_admin.%s.model_manager', $name ?: $this->getModelName()));
    }

    public function getSecurityManager($name = null)
    {
        $manager = $this->container->get(sprintf('lyra_admin.security_manager'));
        $manager->setModelName($name ?: $this->getModelName());

        return $manager;
    }

    /**
     * Returns the response to redirect to the list of objects.
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    protected function getRedirectToListResponse()
    {
        return new RedirectResponse(
            $this->container->get('router')->generate($this->getActions()->get('index')->getRouteName())
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
        $form = $this->getFormRenderer();

        return $this->container->get('templating')
            ->renderResponse($form->getTemplate(), array(
                'form' => $form,
                'object' => $object
            ));
    }

    protected function setFlash($action, $value)
    {
        $this->container->get('session')->setFlash($action, $value);
    }

    protected function executeBatchDelete($ids)
    {
        if ($this->getRequest()->get('batch_confirm')) {
            if ($this->container->get('form.csrf_provider')->isCsrfTokenValid('batch_delete', $this->getRequest()->get('_token'))) {
                $this->setFlash('lyra_admin success', 'flash.batch_delete.success');
                $this->getModelManager()->removeByIds($ids);
            }

            return $this->getRedirectToListResponse();
        }

        $actions = $this->getActions();

        return $this->container->get('templating')
            ->renderResponse('LyraAdminBundle:Dialog:batch_dialog.html.twig', array(
                'ids' => $ids,
                'action' => $actions->get('delete'),
                'form_action' => $actions->get('object'),
                'cancel' => $actions->get('index'),
                'csrf' => $this->container->get('form.csrf_provider')->generateCsrfToken('batch_delete'),
            ));
    }

    protected function processBatchAction()
    {
        if (null === $id = $this->getRequest()->get('ids')) {
            // TODO setflash
        } else if ($action = $this->getRequest()->get('batch_action')) {
            $this->getSecurityManager()->allowOr403($action);

            $method = 'executeBatch'.$action;

            return $this->$method($id);
        }
    }

    protected function processBooleanAction($action)
    {
        $data = array();
        $this->extractActionData($action, $data);
        if (count($data) == 4) {
            $this->executeBoolean($data[3], $data[2], 1 == $data[1]);
        }
    }

    protected function executeBoolean($id, $colName, $colValue)
    {
        if ($this->getGrid()->getColumn($colName)->hasBooleanActions()) {
            if ($this->container->get('form.csrf_provider')->isCsrfTokenValid('list', $this->getRequest()->get('_token'))) {
                $object = $this->getModelManager()->find($id);
                $method = 'set'.ucfirst($colName);
                $object->$method($colValue);
                $this->getModelManager()->save($object);
            }
        }
    }

    protected function getModelName()
    {
        if (null === $name = $this->getRequest()->get('lyra_admin_model')) {
           throw new \InvalidArgumentException('Unspecified model name, lyra_admin_model parameter not present in Request');
        }

        return $name;
    }

    protected function saveFilterCriteria()
    {
        $filter = $this->getFilterRenderer();
        $form = $filter->getForm();
        $form->bindRequest($this->getRequest());

        $filter->setCriteria($form->getData());
    }

    protected function showFilterCriteria()
    {
        return $this->container->get('templating')
            ->renderResponse('LyraAdminBundle:Filter:dialog.html.twig', array(
                'filter' => $this->getFilterRenderer(),
            ));
    }

    protected function extractActionData($action, &$data)
    {
        if (is_array($action)) {
            $key = key($action);
            $data[] = $key;
            $this->extractActionData($action[$key], $data);
        }
    }
}
