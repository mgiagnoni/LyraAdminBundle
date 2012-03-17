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

class AdminFilterFormType extends AbstractType
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
            $options = isset($attrs['options']) ? $attrs['options'] : array();
            $options = array_replace($options, array('required' => false));

            switch($attrs['type']) {
                case 'text':
                    $type = 'textarea';
                    break;
                case 'boolean':
                    $type = 'choice';
                    $options['choices'] = array(1 => 'Yes', 0 => 'No', null => 'Both');
                    $options['expanded'] = true;
                    break;
                case 'datetime':
                    $type = 'daterange';
                    break;
                case 'entity':
                    $type = 'entity';
                    break;
                default:
                    $type = 'text';
            }

            $builder->add($field, $type, $options);
        }
    }

    public function getName()
    {
        return 'lyra_admin_form_filter_'.$this->name;
    }
}
