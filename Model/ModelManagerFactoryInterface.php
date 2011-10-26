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

interface ModelManagerFactoryInterface
{
    /**
     * Gets a model manager instance.
     *
     * @param string $name model name
     *
     * @return ModelManagerInterface
     */
    function getModelManager($name);
}
