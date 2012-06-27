<?php

/*
 * This file is part of the LyraAdminBundle package.
 *
 * Copyright 2011-2012 Massimo Giagnoni <gimassimo@gmail.com>
 *
 * This source file is subject to the MIT license. Full copyright and license
 * information are in the LICENSE file distributed with this source code.
 */

namespace Lyra\AdminBundle\FormFactory;

use Symfony\Component\Form\FormFactory;

/**
 * Form factory class.
 */
class AdminFormFactory
{
    /**
     * @var \Symfony\Component\Form\FormFactory $formFactory
     */
    protected $formFactory;

    /**
     * @param \Symfony\Component\Form\FormFactory $formFactory
     */
    public function __construct(FormFactory $formFactory)
    {
        $this->formFactory = $formFactory;
    }

    /**
     * Creates a form.
     *
     * @param string $type form type class name
     * @param string $name form name
     * @param array $data data passed to the form
     * @param array $options
     *
     * @return \Symfony\Component\Form\Form
     */
    public function createForm($type, $name, $data = null, array $options = array())
    {
        $builder = $this->formFactory->createNamedBuilder($name, $type, $data, $options);

        return $builder->getForm();
    }
}
