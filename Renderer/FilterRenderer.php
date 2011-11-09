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

    protected $filterFields;

    public function __construct(FormFactory $factory, array $options = array())
    {
        parent::__construct($options);

        $this->factory = $factory;
    }

    public function getTitle()
    {
        return $this->options['filter']['title'];
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
         if (null === $this->filterFields) {
            $this->filterFields = $this->options['filter']['fields'];
            $this->setFilterFieldsDefaults();
        }

        return $this->filterFields;
    }

    public function hasFields()
    {
        return (boolean)count($this->options['filter']['fields']);
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

    protected function setFilterFieldsDefaults()
    {
        $metadata = $this->getMetadata();

        foreach ($this->filterFields as $name => $attrs)
        {
            if (isset($metadata[$name])) {
                $this->filterFields[$name]['type'] = $metadata[$name]['type'];
            }
        }
    }
}
