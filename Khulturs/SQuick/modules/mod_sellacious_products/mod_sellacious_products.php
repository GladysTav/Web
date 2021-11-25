<?php
/**
 * @version     2.0.0
 * @package     Sellacious Products Module
 *
 * @copyright   Copyright (C) 2012-2020 Bhartiy Web Technologies. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Izhar Aazmi <info@bhartiy.com> - http://www.bhartiy.com
 */
// no direct access
defined('_JEXEC') or die('Restricted access');

if (!class_exists('SellaciousHelper'))
{
	return;
}

JLoader::register('ModSellaciousProductsHelper', __DIR__ . '/helper.php');

try
{
	$mod      = new ModSellaciousProductsHelper;
	$products = $mod->getList($params);

	if (count($products) == 0)
	{
		return;
	}
}
catch (Exception $e)
{
	JLog::add($e->getMessage(), JLog::WARNING);

	return;
}

/** @noinspection PhpIncludeInspection */
require JModuleHelper::getLayoutPath('mod_sellacious_products');
