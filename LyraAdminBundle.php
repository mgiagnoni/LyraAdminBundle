<?php

/*
 * This file is part of the LyraAdminBundle package.
 *
 * Copyright 2011 Massimo Giagnoni <gimassimo@gmail.com>
 *
 * This source file is subject to the MIT license. Full copyright and license
 * information are in the LICENSE file distributed with this source code.
 */

namespace Lyra\AdminBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Lyra\AdminBundle\DependencyInjection\Compiler\ConfigureModelManagersPass;
use Lyra\AdminBundle\DependencyInjection\Compiler\SaveRouteResourcePass;
use Lyra\AdminBundle\DependencyInjection\Compiler\ConfigureFromMetadataPass;
use Symfony\Component\DependencyInjection\Compiler\PassConfig;

/**
 * LyraAdminBundle
 */
class LyraAdminBundle extends Bundle
{
    /**
     * {@inheritdoc}
     */
    public function build(ContainerBuilder $container)
    {
        parent::build($container);

        $container->addCompilerPass(new ConfigureModelManagersPass());
        if ('dev' == $container->getParameter('kernel.environment')) {
            $container->addCompilerPass(new SaveRouteResourcePass());
        }
        $container->addCompilerPass(new ConfigureFromMetadataPass(), PassConfig::TYPE_OPTIMIZE);
    }
}
