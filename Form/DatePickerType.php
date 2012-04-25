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
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\Form\Extension\Core\DataTransformer\DateTimeToLocalizedStringTransformer;
use Symfony\Component\Form\ReversedTransformer;
use Lyra\AdminBundle\Util\Util;

class DatePickerType extends AbstractType
{
    public function buildForm(FormBuilder $builder, array $options)
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
                $defaultOptions = $this->getDefaultOptions($options);

                $format = $defaultOptions['format'];
                $pattern = $options['format'];
            } else {
                throw new CreationException('The "format" option must be one of the IntlDateFormatter constants (FULL, LONG, MEDIUM, SHORT) or a string representing a custom pattern');
            }
        }

        $formatter = new \IntlDateFormatter(
            \Locale::getDefault(),
            $format,
            \IntlDateFormatter::NONE,
            \DateTimeZone::UTC,
            \IntlDateFormatter::GREGORIAN,
            $pattern
        );

        $builder->appendClientTransformer(new DateTimeToLocalizedStringTransformer($options['data_timezone'], $options['user_timezone'], $format, \IntlDateFormatter::NONE, \IntlDateFormatter::GREGORIAN, $pattern));

        if ($options['input'] === 'string') {
            $builder->appendNormTransformer(new ReversedTransformer(
                new DateTimeToStringTransformer($options['data_timezone'], $options['data_timezone'], 'Y-m-d')
            ));
        } elseif ($options['input'] === 'timestamp') {
            $builder->appendNormTransformer(new ReversedTransformer(
                new DateTimeToTimestampTransformer($options['data_timezone'], $options['data_timezone'])
            ));
        } elseif ($options['input'] === 'array') {
            $builder->appendNormTransformer(new ReversedTransformer(
                new DateTimeToArrayTransformer($options['data_timezone'], $options['data_timezone'], array('year', 'month', 'day'))
            ));
        }

        $builder
            ->setAttribute('date_pattern', $formatter->getPattern());
    }

    public function buildViewBottomUp(FormView $view, FormInterface $form)
    {
        $view->setAttribute('data-date', Util::ICUTojQueryDate($form->getAttribute('date_pattern')));
    }

    public function getDefaultOptions(array $options)
    {
        return array(
            'input'          => 'datetime',
            'format'         => \IntlDateFormatter::MEDIUM,
            'data_timezone'  => null,
            'user_timezone'  => null,
            // Don't modify \DateTime classes by reference, we treat
            // them like immutable value objects
            'by_reference'   => false,
            'error_bubbling' => false,
        );
    }

    public function getAllowedOptionValues(array $options)
    {
        return array(
            'input'       => array(
                'datetime',
                'string',
                'timestamp',
                'array',
            ),
        );
    }

    public function getName()
    {
        return 'date_picker';
    }

    public function getParent(array $options)
    {
        return 'field';
    }
}
