<?php

/*
 * This file is part of the LyraAdminBundle package.
 *
 * Copyright 2011 Massimo Giagnoni <gimassimo@gmail.com>
 *
 * This source file is subject to the MIT license. Full copyright and license
 * information are in the LICENSE file distributed with this source code.
 */

namespace Lyra\AdminBundle\Action;

/**
 * Represents a backend action.
 */
class Action implements ActionInterface
{
    /**
     * @var string
     */
    protected $name;

    /**
     * @var string
     */
    protected $routeName;

    /**
     * @var string
     */
    protected $routePattern;

    /**
     * @var array
     */
    protected $routeParams;

    /**
     * @var string
     */
    protected $text;

    /**
     * @var string
     */
    protected $buttonIcon;

    /**
     * @var string
     */
    protected $buttonStyle;

    /**
     * @var string
     */
    protected $dialogTitle;

    /**
     * @var string
     */
    protected $dialogMessage;

    /**
     * @var string
     */
    protected $transDomain;

    /**
     * @var string
     */
    protected $template;

    /**
     * @var array
     */
    protected $roles;

    public function __construct($name)
    {
        $this->name = $name;
    }

    public function setName($name)
    {
        $this->name = $name;
    }

    public function getName()
    {
        return $this->name;
    }

    public function setRouteName($routeName)
    {
        $this->routeName = $routeName;
    }

    public function getRouteName()
    {
        return $this->routeName;
    }

    public function setRoutePattern($routePattern)
    {
        $this->routePattern = $routePattern;
    }

    public function getRoutePattern()
    {
        return $this->routePattern;
    }

    public function setRouteParams($routeParams)
    {
        $this->routeParams = $routeParams;
    }

    public function getRouteParams()
    {
        return $this->routeParams;
    }

    public function setRoute($name, $pattern, $params)
    {
        $this->routeName = $name;
        $this->routePattern = $pattern;
        $this->routeParams = $params;
    }

    public function setText($text)
    {
        $this->text = $text;
    }

    public function getText()
    {
        return $this->text;
    }

    public function setButtonIcon($buttonIcon)
    {
        $this->buttonIcon = $buttonIcon;
    }

    public function getButtonIcon()
    {
        return $this->buttonIcon;
    }

    public function setButtonStyle($buttonStyle)
    {
        $this->buttonStyle = $buttonStyle;
    }

    public function getButtonStyle()
    {
        return $this->buttonStyle;
    }

    public function setButton($text, $icon, $style)
    {
        $this->buttonText = $text;
        $this->buttonIcon = $icon;
        $this->buttonStyle = $style;
    }

    public function setDialogTitle($dialogTitle)
    {
        $this->dialogTitle = $dialogTitle;
    }

    public function getDialogTitle()
    {
        return $this->dialogTitle;
    }

    public function setDialogMessage($dialogMessage)
    {
        $this->dialogMessage = $dialogMessage;
    }

    public function getDialogMessage()
    {
        return $this->dialogMessage;
    }

    public function setDialog($title, $message)
    {
        $this->dialogTitle = $title;
        $this->dialogMessage = $message;
    }

    public function hasDialog()
    {
        return null !== $this->dialogMessage;
    }

    public function setTransDomain($transDomain)
    {
        $this->transDomain = $transDomain;
    }

    public function getTransDomain()
    {
        return $this->transDomain;
    }

    public function setTemplate($template)
    {
        $this->template = $template;
    }

    public function getTemplate()
    {
        return $this->template;
    }

    public function setRoles($roles)
    {
        $this->roles = $roles;
    }

    public function getRoles()
    {
        return $this->roles;
    }

    public function __toString()
    {
        return $this->name;
    }
}
