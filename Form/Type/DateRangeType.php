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
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class DateRangeType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $options['child_options'] = array_merge($options['child_options'], array('required' => false));
        $builder
            ->add('from', $options['child_widget'], $options['child_options'])
            ->add('to', $options['child_widget'], $options['child_options']);
    }

    public function getName()
    {
        return 'daterange';
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'child_widget' => 'datetime',
            'child_options' => array()
        ));
    }
}
