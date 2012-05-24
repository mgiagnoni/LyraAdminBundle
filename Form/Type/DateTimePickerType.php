<?php

 /*
  * This file is part of the LyraAdminBundle package.
  *
  * Copyright 2011 Massimo Giagnoni <gimassimo@gmail.com>
  *
  * This source file is subject to the MIT license. Full copyright and license
  * information are in the LICENSE file distributed with this source code.
  */

namespace Lyra\AdminBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilder;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\Form\Exception\CreationException;
use Symfony\Component\Form\Extension\Core\DataTransformer\DateTimeToLocalizedStringTransformer;
use Symfony\Component\Form\ReversedTransformer;
use Lyra\AdminBundle\Util\Util;

class DateTimePickerType extends AbstractType
{
    public function buildForm(FormBuilder $builder, array $options)
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
        $defaultOptions = $this->getDefaultOptions($options);

        if (!in_array($dateFormat, $allowedFormatOptionValues, true)) {
            if (is_string($dateFormat)) {
                $dateFormat = $defaultOptions['date_format'];
                $pattern = $options['date_format'];
            } else {
                throw new CreationException('The "date_format" option must be one of the IntlDateFormatter constants (FULL, LONG, MEDIUM, SHORT) or a string representing a custom pattern');
            }
        }

        $formatter = new \IntlDateFormatter(
            \Locale::getDefault(),
            $dateFormat,
            \IntlDateFormatter::NONE,
            \DateTimeZone::UTC,
            \IntlDateFormatter::GREGORIAN,
            $pattern
        );

        $datePattern = $formatter->getPattern();

        if (!in_array($timeFormat, $allowedFormatOptionValues, true)) {
            if (is_string($timeFormat)) {
                $timeFormat = $defaultOptions['time_format'];
                $pattern = $options['time_format'];
            } else {
                throw new CreationException('The "time_format" option must be one of the IntlDateFormatter constants (FULL, LONG, MEDIUM, SHORT) or a string representing a custom pattern');
            }
        }

        $formatter = new \IntlDateFormatter(
            \Locale::getDefault(),
            \IntlDateFormatter::NONE,
            $timeFormat,
            \DateTimeZone::UTC,
            \IntlDateFormatter::GREGORIAN,
            $pattern
        );

        $timePattern = $formatter->getPattern();

        if (null !== $pattern) {
            $pattern = $datePattern . ' ' . $timePattern;
        }

        $builder->appendClientTransformer(new DateTimeToLocalizedStringTransformer($options['data_timezone'], $options['user_timezone'], $dateFormat, $timeFormat, \IntlDateFormatter::GREGORIAN, $pattern));


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
            ->setAttribute('date_pattern', $datePattern)
            ->setAttribute('time_pattern', $timePattern);
    }

    public function buildViewBottomUp(FormView $view, FormInterface $form)
    {
        $view->setAttribute('data-date', Util::ICUTojQueryDate($form->getAttribute('date_pattern')));
        $timePattern = $form->getAttribute('time_pattern');
        $view->setAttribute('data-time', Util::ICUTojQueryDate($timePattern));
        $view->setAttribute('data-ampm', false !== strpos($timePattern, 'h') ? '1' : '0');

    }

    public function getDefaultOptions(array $options)
    {
        return array(
            'input'         => 'datetime',
            'data_timezone' => null,
            'user_timezone' => null,
            'date_format'   => \IntlDateFormatter::MEDIUM,
            'time_format'   => \IntlDateFormatter::SHORT,
            // Don't modify \DateTime classes by reference, we treat
            // them like immutable value objects
            'by_reference'  => false,
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
        return 'datetime_picker';
    }

    public function getParent(array $options)
    {
        return 'field';
    }
}
