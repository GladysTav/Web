<?php
/**
 * @version     1.7.4
 * @package     Sellacious HyperLocal Module
 *
 * @copyright   Copyright (C) 2012-2019 Bhartiy Web Technologies. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Aditya Chakraborty <info@bhartiy.com> - http://www.bhartiy.com
 */

// no direct access
use Joomla\Registry\Registry;
use Sellacious\Config\ConfigHelper;

defined('_JEXEC') or die('Restricted access');

jimport('sellacious.loader');

JLoader::register('ModSellaciousHyperlocalHelper', __DIR__ . '/helper.php');

$app                        = JFactory::getApplication();
$db                         = JFactory::getDbo();
$helper                     = SellaciousHelper::getInstance();
$hlConfig                   = ConfigHelper::getInstance('plg_system_sellacioushyperlocal');
$hlParams                   = $hlConfig->getParams();
$location                   = new Registry($app->getUserState('mod_sellacious_hyperlocal.user.location', array()));
$hyperlocal                 = $location->toString();
$adddetails_one_location    = $params->get('adddetails_one_location');
$adddetails_two_location    = $params->get('adddetails_two_location');
$adddetails_one_lat         = $params->get('adddetails_one_lat');
$adddetails_two_lat         = $params->get('adddetails_two_lat');
$display_location_via       = $params->get('display_location_via');
$show_location_by_style_one = $params->get('show_location_by_style_one');
$show_location_by_style_two = $params->get('show_location_by_style_two');

$meterUnit = $helper->unit->loadResult(array(
	'list.select' => 'a.id',
	'list.where'  => array(
		'a.title = ' . $db->quote('Meter'),
		'a.symbol = ' . $db->quote('m'),
		'a.unit_group = ' . $db->quote('Length'),
	),
));
$meterUnit = $meterUnit ?: null;
$layout    = $params->get('layout', 'default');

if (!empty($hlParams->get('hyperlocal_type')) && JPluginHelper::isEnabled('system', 'sellacioushyperlocal'))
{
	$productRadius         = $hlParams->get('product_radius');
	$googleApiKey          = $hlParams->get('google_api_key', '');
	$browser_detect        = $params->get('browser_detect', 1);
	$address_autocomplete  = $params->get('address_autocomplete', 1);
	$address_matching      = $params->get('address_matching', 1);
	$address_matching_area = $params->get('address_matching_area', 'city');
	$distance_filter       = $address_matching == 1 ? $params->get('show_filter_by_distance', 0) : 0;
	$distance_unit         = $distance_filter ? $params->get('distance_unit', 0) : '';
	$distance_unit         = $distance_unit ?: ($productRadius->u ?: $meterUnit);
	$distance_unit_value   = $helper->unit->loadResult(array('list.where' => array('a.id = ' . $distance_unit), 'list.select' => array('a.symbol')));
	$distance_min          = 0;
	$distance_max          = $params->get('distance_max', 200);
	$min_radius            = $location->get('min_radius', 0);
	$max_radius            = $location->get('max_radius', '');
	$autofill_components   = $params->get('autofill_components', array('zip', 'city', 'district', 'state', 'country'));

	if (!isset($productRadius->u))
	{
		return;
	}

	if (empty($googleApiKey))
	{
		$msg = JText::_('MOD_SELLACIOUS_HYPERLOCAL_GOOGLE_API_KEY_NOT_FOUND');
		require JModuleHelper::getLayoutPath('mod_sellacious_hyperlocal', '_empty');

		return;
	}

	$params->set('google_api_key', $googleApiKey);

	if ($distance_filter)
	{
		$max_radius = $max_radius ?: ($productRadius->m ?: 50);
	}
	else
	{
		$max_radius = $productRadius->m ?: 50;
	}

	$rate = $helper->unit->getRate($distance_unit, $meterUnit);

	$min_radius = (is_numeric($min_radius) || is_float($min_radius)) ? $min_radius : 0;
	$max_radius = (is_numeric($max_radius) || is_float($max_radius)) ? $max_radius : 50;

	$minDistance     = $helper->unit->convert($min_radius, $distance_unit, $meterUnit);
	$maxDistance     = $helper->unit->convert($max_radius, $distance_unit, $meterUnit);
	$productDistance = $helper->unit->convert($max_radius, $distance_unit, $meterUnit);

	$minDistance = is_numeric($minDistance) ? $minDistance : 0;
	$maxDistance = is_numeric($maxDistance) ? $maxDistance : 50000;

	$component_order = array('locality', 'city', 'district', 'state', 'country', 'zip');
	$components      = array_intersect($component_order, $autofill_components);

	//require JModuleHelper::getLayoutPath('mod_sellacious_hyperlocal', $layout);
    $layout              = $params->get('display_layout', '_:default');

    list($tpl, $layout) = strpos($layout, ':') ? explode(':', $layout): array('_', $layout);
    require JModuleHelper::getLayoutPath('mod_sellacious_hyperlocal', $layout);
}
else
{
	$msg = JText::_('MOD_SELLACIOUS_HYPERLOCAL_PLUGIN_NOT_ENABLED');
	require JModuleHelper::getLayoutPath('mod_sellacious_hyperlocal', '_empty');
}

