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
     * @var string
     */
    protected $newTitle;

    /**
     * @var string
     */
    protected $editTitle;

    /**
     * @var string
     */
    protected $template;

    /**
     * @var string
     */
    protected $transDomain;

    /**
     * @var string
     */
    protected $class;

    /**
     * @var string
     */
    protected $dataClass;

    /**
     * @var array
     */
    protected $fields;

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

    public function setTemplate($template)
    {
        $this->template = $template;
    }

    public function getTemplate()
    {
        return $this->template;
    }

    public function setTransDomain($transDomain)
    {
        $this->transDomain = $transDomain;
    }

    public function getTransDomain()
    {
        return $this->transDomain;
    }

    public function setClass($class)
    {
        $this->class = $class;
    }

    public function getClass()
    {
        return $this->class;
    }

    public function setDataClass($class)
    {
        $this->dataClass = $class;
    }

    public function getDataClass()
    {
        return $this->dataClass;
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
        if (null === $this->action) {
            throw new \LogicException('Can\'t retrieve form groups as form action is not set');
        }

        $key = '_'.$this->action;

        if (isset($this->groups[$key])) {
            $groups = $this->groups[$key];
        } else {
            $groups = $this->groups;
        }

        if (count($groups) == 0) {
            return array('main' => array(
                'caption' => null,
                'break_after' => false,
                'fields' => array_keys($this->getFields())
            ));

        }

        return $groups;
    }

    public function setTitle($newTitle, $editTitle)
    {
        $this->newTitle = $newTitle;
        $this->editTitle = $editTitle;
    }

    public function getTitle()
    {
        if (null === $this->action) {
            throw new \LogicException('Can\'t retrieve form title as form action is not set');
        }

        return $this->action == 'new' ? $this->newTitle : $this->editTitle;
    }

    public function setFields($fields)
    {
        $this->fields = $fields;
    }

    public function getFields()
    {
        return $this->fields;
    }

    public function hasWidget($widget)
    {
        foreach ($this->getFields() as $field => $attrs) {
            if ($attrs['widget'] == $widget) {
                return true;
            }
        }

        return false;
    }

    protected function createForm($data = null)
    {
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
        $typeClass = $this->class;
        $type = new $typeClass($this->getName(), $fields);

        return $this->factory->createForm($type, $this->getName(), $data, array('data_class' => $this->dataClass));
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
