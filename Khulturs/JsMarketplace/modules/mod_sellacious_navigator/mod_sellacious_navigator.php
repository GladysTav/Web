<?php
/**
 * @version     __DEPLOY_VERSION__
 * @package     Sellacious Cart Module
 *
 * @copyright   Copyright (C) 2012-2019 Bhartiy Web Technologies. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Saurabh Sabharwal <info@bhartiy.com> - http://www.bhartiy.com
 */

// no direct access
defined('_JEXEC') or die('Restricted access');

jimport('sellacious.loader');

/** @var  Joomla\Registry\Registry  $params */
$helper    = SellaciousHelper::getInstance();
$allow_checkout = $helper->config->get('allow_checkout');

if (!$allow_checkout)
{
	return;
}

$cart      = $helper->cart->getCart();
$class_sfx = $params->get('class_sfx', '');
$style = $params->get('navigator_style', '0');
$menu_items = $params->get('menu_items');


require JModuleHelper::getLayoutPath('mod_sellacious_navigator');
