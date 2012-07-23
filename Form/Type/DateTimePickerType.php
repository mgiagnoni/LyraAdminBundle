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
use Symfony\Component\Form\FormView;
use Symfony\Component\Form\Exception\CreationException;
use Symfony\Component\Form\Extension\Core\DataTransformer\DateTimeToArrayTransformer;
use Symfony\Component\Form\Extension\Core\DataTransformer\DateTimeToStringTransformer;
use Symfony\Component\Form\Extension\Core\DataTransformer\DateTimeToTimestampTransformer;
use Symfony\Component\Form\Extension\Core\DataTransformer\DateTimeToLocalizedStringTransformer;
use Symfony\Component\Form\ReversedTransformer;
use Lyra\AdminBundle\Util\Util;

/**
 * Custom type to manage a datetime field with jQuery UI Timepicker addon.
 *
 * Code in part derived from DateTimeType of Symfony Form component.
 *
 * @see https://github.com/trentrichardson/jQuery-Timepicker-Addon
 *
 */
class DateTimePickerType extends AbstractType
{
    const DEFAULT_DATE_FORMAT = \IntlDateFormatter::MEDIUM;
    const DEFAULT_TIME_FORMAT = \IntlDateFormatter::SHORT;

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $dateFormat = $options['date_format'];
        $timeFormat = $options['time_format'];
        $pattern = null;

        $allowedFormatOptionValues = array(
            \IntlDateFormatter::FULL,
            \IntlDateFormatter::LONG,
            \IntlDateFormatter::MEDIUM,
            \IntlDateFormatter::SHORT,
        );

        // If $format is not in the allowed options, it's considered as the pattern of the formatter if it is a string
        if (!in_array($dateFormat, $allowedFormatOptionValues, true)) {
            if (is_string($dateFormat)) {
                $dateFormat = self::DEFAULT_DATE_FORMAT;
                $pattern = $options['date_format'];
            } else {
                throw new CreationException('The "date_format" option must be one of the IntlDateFormatter constants (FULL, LONG, MEDIUM, SHORT) or a string representing a custom pattern');
            }
        }

        $formatter = new \IntlDateFormatter(
            \Locale::getDefault(),
            $dateFormat,
            \IntlDateFormatter::NONE,
            'UTC',
            \IntlDateFormatter::GREGORIAN,
            $pattern
        );

        $datePattern = $formatter->getPattern();

        if (!in_array($timeFormat, $allowedFormatOptionValues, true)) {
            if (is_string($timeFormat)) {
                $timeFormat = self::DEFAULT_TIME_FORMAT;
                $pattern = $options['time_format'];
            } else {
                throw new CreationException('The "time_format" option must be one of the IntlDateFormatter constants (FULL, LONG, MEDIUM, SHORT) or a string representing a custom pattern');
            }
        }

        $formatter = new \IntlDateFormatter(
            \Locale::getDefault(),
            \IntlDateFormatter::NONE,
            $timeFormat,
            'UTC',
            \IntlDateFormatter::GREGORIAN,
            $pattern
        );

        $timePattern = $formatter->getPattern();

        if (null !== $pattern) {
            $pattern = $datePattern . ' ' . $timePattern;
        }

        $builder->addViewTransformer(new DateTimeToLocalizedStringTransformer($options['data_timezone'], $options['user_timezone'], $dateFormat, $timeFormat, \IntlDateFormatter::GREGORIAN, $pattern));


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

        $builder
            ->setAttribute('date_pattern', $datePattern)
            ->setAttribute('time_pattern', $timePattern);
    }

    public function finishView(FormView $view, FormInterface $form, array $options)
    {
        $view->setAttribute('data-date', Util::ICUTojQueryDate($form->getAttribute('date_pattern')));
        $timePattern = $form->getAttribute('time_pattern');
        $view->setAttribute('data-time', Util::ICUTojQueryDate($timePattern));
        $view->setAttribute('data-ampm', false !== strpos($timePattern, 'h') ? '1' : '0');
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'input'         => 'datetime',
            'data_timezone' => null,
            'user_timezone' => null,
            'date_format'   => self::DEFAULT_DATE_FORMAT,
            'time_format'   => self::DEFAULT_TIME_FORMAT,
            // Don't modify \DateTime classes by reference, we treat
            // them like immutable value objects
            'by_reference'  => false,
            'data_class' => null,
            'compound' => false
        ));

        $resolver->setAllowedValues(array(
            'input'       => array(
                'datetime',
                'string',
                'timestamp',
                'array',
            ),
        ));
    }

    public function getName()
    {
        return 'datetime_picker';
    }

    public function getParent()
    {
        return 'field';
    }
}
