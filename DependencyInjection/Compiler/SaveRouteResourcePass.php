<?php

/*
 * This file is part of the LyraAdminBundle package.
 *
 * Copyright 2011 Massimo Giagnoni <gimassimo@gmail.com>
 *
 * This source file is subject to the MIT license. Full copyright and license
 * information are in the LICENSE file distributed with this source code.
 */

namespace Lyra\AdminBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;
use Lyra\AdminBundle\Util\Util;

class SaveRouteResourcePass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        $routes = Util::sortAssocArrayRecursive($container->getParameter('lyra_admin.routes'));
        $cache = $container->getParameter('kernel.cache_dir').'/lyra_admin.routes.meta';
        $cached = array();
        if (file_exists($cache)) {
            $cached = Util::sortAssocArrayRecursive(unserialize(file_get_contents($cache)));
        }
        if ($routes != $cached) {
            file_put_contents($cache, serialize($routes));
        }
    }
}
