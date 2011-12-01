<?php
 /*
  * This file is part of the LyraAdminBundle package.
  *
  * Copyright 2011 Massimo Giagnoni <gimassimo@gmail.com>
  *
  * This source file is subject to the MIT license. Full copyright and license
  * information are in the LICENSE file distributed with this source code.
  */

namespace Lyra\AdminBundle\Renderer;

use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Factory class for all renderers.
 */
class RendererFactory implements RendererFactoryInterface, ContainerAwareInterface
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

    public function getListRenderer($name = null)
    {
        $renderer = $this->container->get('lyra_admin.list_renderer');

        if (null === $name) {
            $name = $this->getModelName();
        }

        $renderer->setName($name);
        $renderer->setOptions($this->container->getParameter(sprintf('lyra_admin.%s.list.options', $name)));

        return $renderer;
    }

    public function getFormRenderer($name = null)
    {
        $renderer = $this->container->get('lyra_admin.form_renderer');

        if (null === $name) {
            $name = $this->getModelName();
        }

        $renderer->setName($name);
        $renderer->setOptions($this->container->getParameter(sprintf('lyra_admin.%s.form.options', $name)));
        $renderer->setAction($this->container->get('request')->get('lyra_admin_action'));

        return $renderer;
    }

    public function getDialogRenderer($name = null)
    {
        $renderer = $this->container->get('lyra_admin.dialog_renderer');

        if (null === $name) {
            $name = $this->getModelName();
        }

        $renderer->setAction($this->container->get('request')->get('lyra_admin_action'));
        $renderer->setOptions($this->container->getParameter(sprintf('lyra_admin.%s.actions.options', $name)));
        return $renderer;
    }

    public function getFilterRenderer($name = null)
    {
        $renderer = $this->container->get('lyra_admin.filter_renderer');

        if (null === $name) {
            $name = $this->getModelName();
        }

        $renderer->setName($name);
        $renderer->setOptions($this->container->getParameter(sprintf('lyra_admin.%s.filter.options', $name)));

        return $renderer;
    }

    protected function getModelName()
    {
        if (null === $name = $this->container->get('request')->get('lyra_admin_model')) {
           throw new \InvalidArgumentException('Unspecified model name, lyra_admin_model parameter not present in Request');
        }

        return $name;
    }
}
