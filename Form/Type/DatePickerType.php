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
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormViewInterface;
use Symfony\Component\Form\Extension\Core\DataTransformer\DateTimeToArrayTransformer;
use Symfony\Component\Form\Extension\Core\DataTransformer\DateTimeToStringTransformer;
use Symfony\Component\Form\Extension\Core\DataTransformer\DateTimeToTimestampTransformer;
use Symfony\Component\Form\Extension\Core\DataTransformer\DateTimeToLocalizedStringTransformer;
use Symfony\Component\Form\ReversedTransformer;
use Lyra\AdminBundle\Util\Util;

/**
 * Custom type to manage a date field with jQuery UI Datepicker.
 *
 * Code in part derived from DateType of Symfony Form component.
 */
class DatePickerType extends AbstractType
{
    const DEFAULT_FORMAT = \IntlDateFormatter::MEDIUM;

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $format = $options['format'];
        $pattern = null;

        $allowedFormatOptionValues = array(
            \IntlDateFormatter::FULL,
            \IntlDateFormatter::LONG,
            \IntlDateFormatter::MEDIUM,
            \IntlDateFormatter::SHORT,
        );

        // If $format is not in the allowed options, it's considered as the pattern of the formatter if it is a string
        if (!in_array($format, $allowedFormatOptionValues, true)) {
            if (is_string($format)) {
                $format = self::DEFAULT_FORMAT;
                $pattern = $options['format'];
            } else {
                throw new CreationException('The "format" option must be one of the IntlDateFormatter constants (FULL, LONG, MEDIUM, SHORT) or a string representing a custom pattern');
            }
        }

        $formatter = new \IntlDateFormatter(
            \Locale::getDefault(),
            $format,
            \IntlDateFormatter::NONE,
            'UTC',
            \IntlDateFormatter::GREGORIAN,
            $pattern
        );
        $formatter->setLenient(false);

        $builder->addViewTransformer(new DateTimeToLocalizedStringTransformer($options['data_timezone'], $options['user_timezone'], $format, \IntlDateFormatter::NONE, \IntlDateFormatter::GREGORIAN, $pattern));

        if ('string' === $options['input']) {
            $builder->addModelTransformer(new ReversedTransformer(
                new DateTimeToStringTransformer($options['data_timezone'], $options['data_timezone'], 'Y-m-d')
            ));
        } elseif ('timestamp' === $options['input']) {
            $builder->addModelTransformer(new ReversedTransformer(
                new DateTimeToTimestampTransformer($options['data_timezone'], $options['data_timezone'])
            ));
        } elseif ('array' === $options['input']) {
            $builder->addModelTransformer(new ReversedTransformer(
                new DateTimeToArrayTransformer($options['data_timezone'], $options['data_timezone'], array('year', 'month', 'day'))
            ));
        }

        $builder->setAttribute('date_pattern', $formatter->getPattern());
    }

    public function finishView(FormViewInterface $view, FormInterface $form, array $options)
    {
        $view->setAttribute('data-date', Util::ICUTojQueryDate($form->getAttribute('date_pattern')));
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'input'          => 'datetime',
            'format'         => self::DEFAULT_FORMAT,
            'data_timezone'  => null,
            'user_timezone'  => null,
            // Don't modify \DateTime classes by reference, we treat
            // them like immutable value objects
            'by_reference'   => false,
            'error_bubbling' => false,
        ));

        $resolver->setAllowedValues(array(
            'input' => array(
                'datetime',
                'string',
                'timestamp',
                'array',
            ),
        ));
    }

    public function getName()
    {
        return 'date_picker';
    }

    public function getParent()
    {
        return 'field';
    }
}
