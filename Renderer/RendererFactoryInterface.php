<?php

/*
 * This file is part of the LyraAdminBundle package.
 *
 * Copyright 2011 Massimo Giagnoni <gimassimo@gmail.com>
 *
 * This source file is subject to the MIT license. Full copyright and license
 * information are in the LICENSE file distributed with this source code.
 */

namespace Lyra\Adminbundle\Renderer;

interface RendererFactoryInterface
{
    /**
     * Gets an instance of a list renderer.
     *
     * @param string $name model name
     *
     * @return ListRendererInterface
     */
    function getListRenderer($name);

    /**
     * Gets an instance of a form renderer.
     *
     * @param string $name model name
     *
     * @return FormRendererInterface
     */
    function getFormRenderer($name);

    /**
     * Gets an instance of a dialog renderer
     *
     * @param string $name model name
     *
     * @return DialogRendererInterface
     */
    function getDialogRenderer($name);

    /**
     * Gets an instance of a filter renderer
     *
     * @param string $name model name
     *
     * @return FilterRendererInterface
     */
    function getFilterRenderer($name);
}

