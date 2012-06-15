<?php
 /*
  * This file is part of the LyraAdminBundle package.
  *
  * Copyright 2011-2012 Massimo Giagnoni <gimassimo@gmail.com>
  *
  * This source file is subject to the MIT license. Full copyright and license
  * information are in the LICENSE file distributed with this source code.
  */

namespace Lyra\AdminBundle\Action;

interface ActionInterface
{
    /**
     * Sets the action name.
     *
     * @param string $name
     */
    function setName($name);

    /**
     * Gets the action name.
     *
     * @return string
     */
    function getName();

    /**
     * Sets the action route name.
     *
     * @param string $routeName
     */
    function setRouteName($routeName);

    /**
     * Gets the action route name.
     *
     * @return string
     */
    function getRouteName();

    /**
     * Sets the action route pattern.
     *
     * @param string $routePattern
     */
    function setRoutePattern($routePattern);

    /**
     * Gets the action route pattern
     *
     * @return string
     */
    function getRoutePattern();

    /**
     * Sets the action route parameters.
     *
     * @param array $routeParams
     */
    function setRouteParams($routeParams);

    /**
     * Gets the action route parameters.
     *
     * @return array
     */
    function getRouteParams();

    /**
     * Sets the action text.
     *
     * For button caption. For batch actions is used as description in drop-down select.
     *
     * @param string $text
     */
    function setText($text);

    /**
     * Gets the action text.
     *
     * @return string
     */
    function getText();

    /**
     * Sets the action button icon.
     *
     * @param string $buttonIcon
     */
    function setButtonIcon($buttonIcon);

    /**
     * Gets the action button icon.
     *
     * @return string
     */
    function getButtonIcon();

    /**
     * Sets the action button style.
     *
     * @param string $buttonStyle icon-only|icon-text
     */
    function setButtonStyle($buttonStyle);

    /**
     * Gets the action button style.
     *
     * @return string
     */
    function getButtonStyle();

    /**
     * Sets the action dialog title.
     *
     * For confirmation dialog box (optional).
     *
     * @param string $dialogTitle
     */
    function setDialogTitle($dialogTitle);

    /**
     * Gets the action dialog title.
     *
     * For confirmation dialog box (optional).
     *
     * @return string
     */
    function getDialogTitle();

    /**
     * Sets the action dialog message.
     *
     * For confirmation dialog box (optional).
     *
     * @param string $dialogMessage
     */
    function setDialogMessage($dialogMessage);

    /**
     * Gets the action dialog message.
     *
     * For confirmation dialog box (optional).
     *
     * @return string
     */
    function getDialogMessage();

    /**
     * Checks if action requires a confirmation dialog.
     *
     * @return boolean
     */
    function hasDialog();

    /**
     * Sets action translation doamin.
     *
     * @param string $transDomain
     */
    function setTransDomain($transDomain);

    /**
     * Gets action translation doamin.
     *
     * @return string
     */
    function getTransDomain();

    /**
     * Sets action template.
     *
     * @param string $template
     */
    function setTemplate($template);

    /**
     * Gets action template.
     *
     * @return string
     */
    function getTemplate();

    /**
     * Sets user roles authorized to execute action.
     *
     * @param array $roles
     */
    function setRoles($roles);

    /**
     * Gets user roles authorized to execute action.
     *
     * @return array
     */
    function getRoles();

    /**
     * Checks if action route pattern has an object id param.
     *
     * @return boolean
     */
    function hasIdParam();
}

