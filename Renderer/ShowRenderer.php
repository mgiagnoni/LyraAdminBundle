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

/**
 * Show renderer class.
 *
 * Displays a single record in a dialog window.
 */
class ShowRenderer extends BaseRenderer
{
    protected $object;

    public function setObject($object)
    {
        $this->object = $object;
    }

    public function getObject()
    {
        return $this->object;
    }

    public function getTitle()
    {
        return $this->configuration->getShowOption('title');
    }

    public function getFields()
    {
        return $this->configuration->getShowFieldsOptions();
    }

    public function getFieldValue($field)
    {
        $method = $this->configuration->getFieldOption($field, 'get_method');
        $type = $this->configuration->getFieldOption($field, 'type');
        $value = $this->object->$method();

        if ('date' == $type || 'datetime' == $type) {
            $value = $value->format($format = $this->configuration->getShowFieldOption($field, 'format'));
        }

        return $value;
    }
}
