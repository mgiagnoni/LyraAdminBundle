<?php

/*
 * This file is part of the LyraAdminBundle package.
 *
 * Copyright 2011-2012 Massimo Giagnoni <gimassimo@gmail.com>
 *
 * This source file is subject to the MIT license. Full copyright and license
 * information are in the LICENSE file distributed with this source code.
 */

namespace Lyra\AdminBundle\Twig\Extension;

class JQueryExtension extends \Twig_Extension
{
    private $locale;

    private $options;

    public function __construct($container, $options = array())
    {
        $this->locale = $container->isScopeActive('request') ? $container->get('request')->getLocale() : 'en_US';
        $this->options = $options;
    }

    public function getFunctions()
    {
        return array(
            'datepicker_lang_file' => new \Twig_Function_Method($this, 'getDatepickerLangFile'),
            'ui_theme_path' => new \Twig_Function_Method($this, 'getUiThemePath')
        );
    }

    public function getDatepickerLangFile()
    {
        $lang = false;
        $locale = str_replace('_', '-', $this->locale);

        if (in_array($locale, array('de-CH','en-GB','fr-CH','nl-BE','pt-BR','sr-SR','zh-CN','zh-TW','zh-HK'))) {
            $lang = $locale;
        } else {
            $locale = explode('-', $locale);
            if (in_array($locale[0], array('af','ar','az','bg','bs','ca','cs','da','de','el','eo','es','et','eu','fa','fi','fo','fr','he','hr','hu','hy','id','is','it','ja','ko','lt','lv','ms','nl','no','pl','ro','ru','sk','sl','sq','sr','sv','ta','th','tr','uk','vi'))) {
                $lang = $locale[0];
            }
        }

        if ($lang) {
            $lang = 'jquery.ui.datepicker-'.$lang.'.js';
        }

        return $lang;
    }

    public function getUiThemePath()
    {
        return $this->options['theme_path'];
    }

    public function getName()
    {
        return 'lyra_jquery';
    }
}

