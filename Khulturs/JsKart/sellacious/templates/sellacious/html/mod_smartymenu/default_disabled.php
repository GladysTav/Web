<?php
/**
 * @version     2.0.0
 * @package     sellacious
 *
 * @copyright   Copyright (C) 2012-2020 Bhartiy Web Technologies. All rights reserved.
 * @license     SPL Sellacious Private License; see http://www.sellacious.com/spl.html
 * @author      Izhar Aazmi <info@bhartiy.com> - http://www.bhartiy.com
 */
// no direct access.
defined('_JEXEC') or die;

use Joomla\Registry\Registry;

foreach($parent_menus as $parent_menu)
{
	$menu_params = new Registry($parent_menu->params);
	$class       = $menu_params->get('menu-anchor_css', 'gear');

	$menu->addChild(new JSmartyMenuNode($parent_menu->title, '', 'class:' . $class));
}
