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

use Symfony\Component\HttpFoundation\Request;
use Lyra\AdminBundle\FormFactory\AdminFormFactory as FormFactory;

/**
 * Form renderer class.
 */
class FormRenderer extends BaseRenderer implements FormRendererInterface
{
    /**
     * @var \Lyra\AdminBundle\FormFactory\AdminFormFactory
     */
    protected $factory;

    /**
     * @var string
     */
    protected $action;

    /**
     * @var \Symfony\Component\Form\Form
     */
    protected $form;

    /**
     * @var \Symfony\Component\Form\FormView
     */
    protected $formView;

    /**
     * @var array
     */
    protected $groups;

    public function __construct(FormFactory $factory, $configuration)
    {
        parent::__construct($configuration);

        $this->factory = $factory;
    }

    public function setAction($action)
    {
        $this->action = $action;
    }

    public function getAction()
    {
        return $this->action;
    }

    public function getTemplate()
    {
        return $this->getOption('template');
    }

    public function getForm($data = null)
    {
        if (null === $this->form) {
            $this->form = $this->createForm($data);
        }

        return $this->form;
    }

    public function getView($form = null)
    {
        if (null === $this->formView) {
            $this->formView = $this->createFormView();
        }

        return null === $form ? $this->formView : $this->formView[$form];
    }

    public function setGroups(array $groups)
    {
        $this->groups = $groups;
    }

    public function getGroups()
    {
        if (null === $this->groups) {
            $this->groups = $this->mergeGroups();
        }

        if (count($this->groups) == 0) {
            return array('main' => array(
                'caption' => null,
                'break_after' => false,
                'fields' => array_keys($this->getFields())
            ));

        }

        return $this->groups;
    }

    public function getTitle()
    {
        if (null === $this->action) {
            throw new \LogicException('Can\'t retrieve form title as renderer action is not set');
        }

        $options = $this->getOption($this->action);

        return $options['title'];
    }

    public function getOption($key)
    {
        return $this->configuration->getFormOption($key);
    }

    public function getFields()
    {
        return $this->configuration->getFieldsOptions();
    }

    protected function createForm($data = null)
    {
        $typeClass = $this->getOption('class');
        $fields = $this->getFields();

        $existing = array();

        foreach ($this->getGroups() as $group) {
            foreach ($group['fields'] as $field) {
                $existing[$field] = true;
                if (false !== strpos($field, '.')) {
                    list($form, $name) = explode('.', $field);
                    $fields[$field]['name'] = $name;
                    $fields[$field]['form'] = $form;
                }
            }
        }

        $fields = array_intersect_key($fields, $existing);
        $type = new $typeClass($this->getName(), $fields);

        return $this->factory->createForm($type, $this->getName(), $data, array('data_class' => $this->configuration->getOption('class')));
    }

    protected function mergeGroups()
    {
        if (null === $this->action) {
            throw new \LogicException('Can\'t merge form fields groups as form action is not set');
        }

        $options = $this->configuration->getFormOptions();
        $groups = array_merge(
            $options['groups'],
            $options[$this->action]['groups']
        );

        return $groups;
    }

    protected function createFormView()
    {
        if (null === $this->form) {
            throw new \LogicException('Can\'t create form view');
        }

        if (null === $this->formView) {
            $this->formView = $this->form->createView();
        }

        return $this->formView;
    }
}
