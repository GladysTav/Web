<?php
/**
 * @package     Joomla.Site
 * @subpackage  mod_sidemenu
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

// Include the menu functions only once
JLoader::register('ModMenuHelper', __DIR__ . '/helper.php');

$list       = ModMenuHelper::getList($params);
$base       = ModMenuHelper::getBase($params);
$active     = ModMenuHelper::getActive($params);
$default    = ModMenuHelper::getDefault();
$active_id  = $active->id;
$default_id = $default->id;
$path       = $base->tree;
$showAll    = $params->get('showAllChildren');
$class_sfx  = htmlspecialchars($params->get('class_sfx'), ENT_COMPAT, 'UTF-8');

if (count($list))
{
	require JModuleHelper::getLayoutPath('mod_topmenu', $params->get('layout', 'default'));
}
