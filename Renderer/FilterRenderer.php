<?php

/*
 * This file is part of the LyraAdminBundle package.
 *
 * Copyright 2011 Massimo Giagnoni <gimassimo@gmail.com>
 *
 * This source file is subject to the MIT license. Full copyright and license
 * information are in the LICENSE file distributed with this source code.
 */

namespace Lyra\AdminBundle\Renderer;

use Lyra\AdminBundle\FormFactory\AdminFormFactory as FormFactory;
use Lyra\AdminBundle\Form\AdminFilterFormType;
use Lyra\AdminBundle\UserState\UserStateInterface;
use Lyra\AdminBundle\Model\ModelManagerInterface;

/**
 * Filter renderer.
 */
class FilterRenderer extends BaseRenderer implements FilterRendererInterface
{
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
     * @var array
     */
    protected $fields;

    public function __construct(FormFactory $factory, ModelManagerInterface $modelManager, $configuration)
    {
        parent::__construct($configuration);

        $this->factory = $factory;
        $this->modelManager = $modelManager;
    }

    public function setState(UserStateInterface $state)
    {
        $this->state = $state;
    }

    public function getState()
    {
        return $this->state;
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
        $type = new AdminFilterFormType($this->getName(), $this->getFields());

        return $this->factory->createForm($type, $this->getName(), $this->getCriteria());
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
        foreach ($this->getFields() as $name => $attrs) {
            switch($attrs['type']) {
            case 'date':
            case 'datetime':
                if (null === $criteria[$name]['from'] && null === $criteria[$name]['to']) {
                    unset($criteria[$name]);
                }
                break;
            case 'boolean':
                if ('' == $criteria[$name]) {
                    unset($criteria[$name]);
                }
                break;
            default:
                if (empty($criteria[$name])) {
                    unset($criteria[$name]);
                }
            }
        }

        return $criteria;
    }
}
