<?php

/*
 * This file is part of the LyraAdminBundle package.
 *
 * Copyright 2011-2012 Massimo Giagnoni <gimassimo@gmail.com>
 *
 * This source file is subject to the MIT license. Full copyright and license
 * information are in the LICENSE file distributed with this source code.
 */

namespace Lyra\AdminBundle\Filter;

use Lyra\AdminBundle\FormFactory\AdminFormFactory as FormFactory;
use Lyra\AdminBundle\Form\Type\AdminFilterFormType;
use Lyra\AdminBundle\UserState\UserStateInterface;
use Lyra\AdminBundle\Model\ModelManagerInterface;
use Lyra\AdminBundle\Action\ActionCollectionInterface;

/**
 * Filter renderer.
 */
class Filter implements FilterInterface
{
    /**
     * @var \Lyra\AdminBundle\FormFactory\AdminFormFactory
     */
    protected $factory;

    /**
     * @var string
     */
    protected $modelName;

    /**
     * @var string
     */
    protected $transDomain;

    /**
     * @var \Symfony\Component\Form\Form
     */
    protected $form;

    /**
     * @var \Symfony\Component\Form\FormView
     */
    protected $formView;

    /**
     * @var \Lyra\AdminBundle\UserState\UserStateInterface
     */
    protected $state;

    /**
     * @var \Lyra\AdminBundle\Model\ModelManagerInterface
     */
    protected $modelManager;

    /**
     * @var string
     */
    protected $title;

    /**
     * @var \Lyra\AdminBundle\Action\ActionCollectionInterface
     */
    protected $actions;

    /**
     * @var array
     */
    protected $fields;

    public function __construct(FormFactory $factory, ModelManagerInterface $modelManager)
    {
        $this->factory = $factory;
        $this->modelManager = $modelManager;
    }

    public function setModelName($modelName)
    {
        $this->modelName = $modelName;
    }

    public function getModelName()
    {
        return $this->modelName;
    }

    public function setTransDomain($transDomain)
    {
        $this->transDomain = $transDomain;
    }

    public function getTransDomain()
    {
        return $this->transDomain;
    }

    public function setState(UserStateInterface $state)
    {
        $this->state = $state;
    }

    public function getState()
    {
        return $this->state;
    }

    public function setActions(ActionCollectionInterface $actions)
    {
        $this->actions = $actions;
    }

    public function getActions()
    {
        return $this->actions;
    }

    public function setCriteria($criteria)
    {
        $criteria = $this->removeEmptyCriteria($criteria);
        $this->state->set('criteria', $criteria);
    }

    public function getCriteria()
    {
        // criteria objects coming back from session need to be managed
        $criteria = $this->modelManager->mergeFilterCriteriaObjects($this->state->get('criteria'));

        return $criteria;
    }

    public function resetCriteria()
    {
        $this->state->set('criteria', array());
    }

    public function setTitle($title)
    {
        $this->title = $title;
    }

    public function getTitle()
    {
        return $this->title;
    }

    public function getForm()
    {
        if (null === $this->form) {
            $this->form = $this->createForm();
        }

        return $this->form;
    }

    public function getView()
    {
        if (null === $this->formView) {
            $this->formView = $this->createFormView();
        }

        return $this->formView;
    }

    public function setFields($fields)
    {
        $this->fields = $fields;
    }

    public function getFields()
    {
        return $this->fields;
    }

    public function hasFields()
    {
        return (boolean)count($this->getFields());
    }

    public function hasWidget($widget)
    {
        foreach ($this->getFields() as $field => $attrs) {
            if ($attrs['widget'] == $widget || ('daterange' == $attrs['widget'] && isset($attrs['options']['child_widget']) && $attrs['options']['child_widget'] == $widget)) {
                return true;
            }
        }

        return false;
    }

    protected function createForm()
    {
        $type = new AdminFilterFormType($this->getModelName(), $this->getFields());

        return $this->factory->createForm($type, $this->getModelName(), $this->getCriteria());
    }

    protected function createFormView()
    {
        if (null === $this->formView) {
            $form = $this->getForm();
            $this->formView = $form->createView();
        }

        return $this->formView;
    }

    protected function removeEmptyCriteria($criteria)
    {
        $fields = $this->getFields();
        $filtered = array();

        foreach ($criteria as $name => $value) {
            if (!isset($fields[$name])) {
                continue;
            }
            switch($fields[$name]['type']) {
                case 'date':
                case 'datetime':
                    if (null === $value['from'] && null === $value['to']) {
                        $value = null;
                    }
                    break;
                case 'boolean':
                    if ('' == $value) {
                        $value = null;
                    }
                    break;
            }

            if (null !== $value) {
                $filtered[$name] = $value;
            }
        }

        return $filtered;
    }
}
