<?php

/*
 * This file is part of the LyraAdminBundle package.
 *
 * Copyright 2011-2012 Massimo Giagnoni <gimassimo@gmail.com>
 *
 * This source file is subject to the MIT license. Full copyright and license
 * information are in the LICENSE file distributed with this source code.
 */

use Doctrine\Common\Annotations\AnnotationRegistry;

if (!$loader = @include __DIR__ . '/../vendor/autoload.php') {
    echo <<<EOF
You must set up the project dependencies, run the following commands:

    wget http://getcomposer.org/composer.phar
    php composer.phar install

EOF;

    exit(1);
}

AnnotationRegistry::registerLoader(array($loader, 'loadClass'));

spl_autoload_register(function($class) {
    if (0 === strpos($class, 'Lyra\\AdminBundle\\')) {
        $path = __DIR__.'/../'.implode('/', array_slice(explode('\\', $class), 2)).'.php';
        if (!stream_resolve_include_path($path)) {
            return false;
        }
        require_once $path;
        return true;
    }
});
