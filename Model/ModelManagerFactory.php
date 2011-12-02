<?php

/*
 * This file is part of the LyraAdminBundle package.
 *
 * Copyright 2011 Massimo Giagnoni <gimassimo@gmail.com>
 *
 * This source file is subject to the MIT license. Full copyright and license
 * information are in the LICENSE file distributed with this source code.
 */

namespace Lyra\AdminBundle\Model;

use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Model manger factory class.
 *
 * Creates an instance of a model manager for the model whose
 * name is passsed as route parameter.
 */
class ModelManagerFactory implements ModelManagerFactoryInterface, ContainerAwareInterface
{
    protected $container;

    public function __construct(ContainerInterface $container)
    {
        $this->setContainer($container);
    }

    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }

    public function getModelManager($name = null)
    {
        if (null === $name) {
            $name = $this->getModelName();
        }

        $manager = $this->container->get(sprintf('lyra_admin.%s.model_manager', $name));
        $manager->setClass($this->container->getParameter(sprintf('lyra_admin.%s.class', $name)));

        return $manager;
    }

    protected function getModelName()
    {
        if (null === $name = $this->container->get('request')->get('lyra_admin_model')) {
            throw new \InvalidArgumentException('Unspecified model name, lyra_admin_model parameter not present in Request');
        }

        return $name;
    }
}
