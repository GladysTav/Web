<?php
/**
 * @version     2.0.0
 * @package     Sellacious Products Module
 *
 * @copyright   Copyright (C) 2012-2020 Bhartiy Web Technologies. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Saurabh Sabharwal <info@bhartiy.com> - http://www.bhartiy.com
 */
// no direct access
defined('_JEXEC') or die('Restricted access');

$modPath = JPATH_SITE . '/modules/mod_sellacious_products/mod_sellacious_products.php';

if (file_exists($modPath))
{
	$params->set('module_type', 'recently_viewed');

	include $modPath;
}
