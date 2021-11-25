<?php
/**
 * @version     2.0.0
 * @package     Sellacious Login Module
 *
 * @copyright   Copyright (C) 2012-2020 Bhartiy Web Technologies. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Izhar Aazmi <info@bhartiy.com> - http://www.bhartiy.com
 */
defined('_JEXEC') or die;

JLoader::register('ModSellaciousLoginHelper', __DIR__ . '/helper.php');

$return    = ModSellaciousLoginHelper::getReturnUrl($params);
$loginOpts = ModSellaciousLoginHelper::getLoginOptions();
$user      = JFactory::getUser();
$helper    = SellaciousHelper::getInstance();

$defaultSeller = $helper->category->getDefault('seller');
$defaultClient = $helper->category->getDefault('client');

$usersConfig       = JComponentHelper::getParams('com_users');
$allowRegistration = $usersConfig->get('allowUserRegistration');

$allowClientRegistration = $allowRegistration && $defaultClient;
$allowSellerRegistration = $allowRegistration && $defaultSeller;

require JModuleHelper::getLayoutPath($module->module, $params->get('layout') . ($user->guest ? '' : '_logout'));
