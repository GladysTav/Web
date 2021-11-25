<?php
/**
 * @version     2.0.0
 * @package     Sellacious Filters Module
 *
 * @copyright   Copyright (C) 2012-2020 Bhartiy Web Technologies. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Izhar Aazmi <info@bhartiy.com> - http://www.bhartiy.com
 */
// no direct access
defined('_JEXEC') or die;

jimport('sellacious.loader');

if (!class_exists('SellaciousHelper'))
{
	return;
}

require_once __DIR__ . '/module.php';

try
{
	/** @var  \ModSellaciousFilters  $mod */
	$mod = ModSellaciousFilters::getInstance();

	if (!$mod->isValid())
	{
		return;
	}

	ob_start();

	$mod->render();

	$app       = JFactory::getApplication();
	$class_sfx = $params->get('moduleclass_sfx');
	$submitBtn = $mod->getCfg('apply_filters_by') === 'submit';
	$html      = ob_get_clean();

	unset($mod);

	/** @noinspection  PhpIncludeInspection  */
	include JModuleHelper::getLayoutPath($module->module, 'filter');
}
catch (Exception $e)
{
	ob_end_clean();
}
