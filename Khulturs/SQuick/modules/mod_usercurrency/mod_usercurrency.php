<?php
/**
 * @version     2.0.0
 * @package     sellacious
 *
 * @copyright   Copyright (C) 2012-2020 Bhartiy Web Technologies. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Izhar Aazmi <info@bhartiy.com> - http://www.bhartiy.com
 */
defined('_JEXEC') or die;

use Joomla\Registry\Registry;

// Include the sellacious helpers
JLoader::import('sellacious.loader');

if (class_exists('SellaciousHelper'))
{
	/** @var  Registry  $params */
	require JModuleHelper::getLayoutPath('mod_usercurrency', $params->get('layout', 'default'));
}
