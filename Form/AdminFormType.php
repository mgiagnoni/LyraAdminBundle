<?php

/*
 * This file is part of the LyraAdminBundle package.
 *
 * Copyright 2011 Massimo Giagnoni <gimassimo@gmail.com>
 *
 * This source file is subject to the MIT license. Full copyright and license
 * information are in the LICENSE file distributed with this source code.
 */

namespace Lyra\AdminBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilder;

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
            switch($attrs['type']) {
                case 'text':
                    $type = 'textarea';
                    break;
                case 'boolean':
                    $type = 'checkbox';
                    $attrs['options']['required'] = false;
                    break;
                case 'datetime':
                    $type = 'datetime';
                    break;
                case 'entity':
                    $type = 'entity';
                    break;
                default:
                    $type = 'text';
            }

            $options = array();
            if(isset($attrs['options'])) {
                $options = $attrs['options'];
            }

            $builder->add($field, $type, $options);
        }
    }

    public function getName()
    {
        return 'lyra_admin_form_'.$this->name;
    }
}
