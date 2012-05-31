<?php

/*
 * This file is part of the LyraAdminBundle package.
 *
 * Copyright 2011-2012 Massimo Giagnoni <gimassimo@gmail.com>
 *
 * This source file is subject to the MIT license. Full copyright and license
 * information are in the LICENSE file distributed with this source code.
 */

namespace Lyra\AdminBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilder;
use Lyra\AdminBundle\Util\Util;

/**
 * Generic form type for all admin forms.
 */
class AdminFormType extends AbstractType
{
    protected $name;

    protected $fields;

    public function __construct($name, $fields)
    {
        $this->name = $name;
        $this->fields = $fields;
    }

    public function buildForm(FormBuilder $builder, array $options)
    {
        foreach ($this->fields as $field => $attrs) {
            $builder->add($attrs['tag'], $attrs['widget'], $attrs['options']);
        }
    }

    public function getName()
    {
        return 'lyra_admin_form_'.$this->name;
    }
}
