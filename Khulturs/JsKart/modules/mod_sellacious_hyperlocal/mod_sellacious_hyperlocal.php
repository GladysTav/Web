<?php
/**
 * @version     2.0.0
 * @package     Sellacious HyperLocal Module
 *
 * @copyright   Copyright (C) 2012-2020 Bhartiy Web Technologies. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Izhar Aazmi <info@bhartiy.com> - http://www.bhartiy.com
 */
// no direct access
defined('_JEXEC') or die('Restricted access');

use Joomla\Registry\Registry;
use Sellacious\Hyperlocal\Distance;
use Sellacious\Hyperlocal\Settings;

jimport('sellacious.loader');

require_once __DIR__ . '/helper.php';

try
{
	$settings = Settings::getInstance();

	if (!$settings->isEnabled())
	{
		throw new Exception(JText::_('MOD_SELLACIOUS_HYPERLOCAL_SETTINGS_DISABLED'));
	}

	if (!$settings->getApiKey())
	{
		throw new Exception(JText::_('MOD_SELLACIOUS_HYPERLOCAL_GOOGLE_API_KEY_NOT_FOUND'));
	}

	$app       = JFactory::getApplication();
	$userState = $app->getUserState('mod_sellacious_hyperlocal.user.location');
	$userState = new Registry($userState);

	$components      = $settings->getAutofillComponents();
	$layout          = $settings->get('display_layout', 'default');
	$browserDetect   = $settings->get('browser_detect', 1);
	$autocomplete    = $settings->get('address_autocomplete', 'db');
	$hyperlocalType  = $settings->get('hyperlocal_type') == SellaciousHyperlocal::BY_RADIUS ? 'distance' : 'region';
	$distanceFilter  = $settings->get('distance_filter', 0) && $settings->get('hyperlocal_type') == SellaciousHyperlocal::BY_RADIUS;
	$distanceLimit   = $settings->get('distance_max', 5000);
	$distanceDefault = $settings->get('distance_default', 2000);
	$unitKey         = $settings->get('distance_unit', 'm');
	$unitSymbol      = Distance::getSymbol($unitKey);
	$unitRate        = Distance::toMeters(1, $unitKey);
	$distanceMin     = $userState->get('min_radius', 0);
	$distanceMax     = $userState->get('max_radius', $distanceDefault);
	$autoDetect      = ($browserDetect == 2) && !$app->getUserState('mod_sellacious_hyperlocal.user.no_detect') && !$app->getUserState('mod_sellacious_hyperlocal.user.location');
}
catch (Exception $e)
{
	$layout = 'default_error';
}

/** @noinspection PhpIncludeInspection */
require JModuleHelper::getLayoutPath($module->module, $layout);
