<?php

/*
 * This file is part of the LyraAdminBundle package.
 *
 * Copyright 2011 Massimo Giagnoni <gimassimo@gmail.com>
 *
 * This source file is subject to the MIT license. Full copyright and license
 * information are in the LICENSE file distributed with this source code.
 */

namespace Lyra\AdminBundle\Util;

/**
 * Code for camelize(), underscore() comes from
 * Symfony\Component\DependencyInjection\Container class with minor changes.
 */
class Util
{
    static public function camelize($s)
    {
        return preg_replace_callback('/(^|_|\.)+(.)/', function ($match) { return ('.' === $match[1] ? '_' : '').strtoupper($match[2]); }, $s);

    }

    static public function underscore($s)
    {
        return strtolower(preg_replace(array('/([A-Z]+)([A-Z][a-z])/', '/([a-z\d])([A-Z])/'), array('\\1_\\2', '\\1_\\2'), $s));
    }

    static public function humanize($s)
    {
        return ucfirst(str_replace('_', ' ', static::underscore($s)));
    }

    static public function sortAssocArrayRecursive(array $a)
    {
        ksort($a);
        foreach ($a as $key => $value) {
          if (is_array($value)) {
            $a[$key] = static::sortAssocArrayRecursive($value);
          }
        }

        return $a;
    }
}

