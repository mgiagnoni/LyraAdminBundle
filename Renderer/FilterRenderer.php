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

class FilterRenderer extends BaseRenderer implements FilterRendererInterface
{
    protected $form;

    protected $formView;

    public function __construct(FormFactory $factory, $configuration)
    {
        parent::__construct($configuration);

        $this->factory = $factory;
    }

    public function getTitle()
    {
        return $this->getOption('title');
    }

    public function getForm($data = null)
    {
        if (null === $this->form) {
            $this->form = $this->createForm($data);
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

    public function getFilterFields()
    {
        return $this->getOption('fields');
    }

    public function hasFields()
    {
        return (boolean)count($this->getFilterFields());
    }

    public function getOption($key)
    {
        return $this->configuration->getFilterOption($key);
    }

    public function hasWidget($widget)
    {
        foreach ($this->getFilterFields() as $field => $attrs) {
            if ($attrs['widget'] == $widget || ('daterange' == $attrs['widget'] && isset($attrs['options']['child_widget']) && $attrs['options']['child_widget'] == $widget)) {
                return true;
            }
        }

        return false;
    }


    protected function createForm($data = null)
    {
        $type = new AdminFilterFormType($this->getName(), $this->getFilterFields());

        return $this->factory->createForm($type, $this->getName(), $data);
    }

    protected function createFormView()
    {
        if (null === $this->formView) {
            $form = $this->getForm();
            $this->formView = $form->createView();
        }

        return $this->formView;
    }
}
