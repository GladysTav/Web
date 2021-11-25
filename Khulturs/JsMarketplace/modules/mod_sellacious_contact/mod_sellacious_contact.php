<?php
/**
 * @version     1.7.4
 * @package     Sellacious Contact Module
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

$contact_name       = $helper->config->get('contact_name');
$contact_email      = $helper->config->get('contact_email');
$contact_phone      = $helper->config->get('contact_phone');
$contact_address    = $helper->config->get('contact_address');
//$contact_location   = $helper->location->getGeoLocation()

if (!$allow_checkout)
{
	return;
}

$class_sfx = $params->get('class_sfx', '');
$text_color = $params->get('text_color', '#000000');
$icon_color = $params->get('icon_color', '#000000');

require JModuleHelper::getLayoutPath('mod_sellacious_contact');
