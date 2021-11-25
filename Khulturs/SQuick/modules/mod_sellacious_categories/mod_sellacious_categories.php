<?php
/**
 * @version     2.0.0
 * @package     Sellacious Categories Module
 *
 * @copyright   Copyright (C) 2012-2020 Bhartiy Web Technologies. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Chandni Thakur <info@bhartiy.com> - http://www.bhartiy.com
 */

// no direct access
defined('_JEXEC') or die('Restricted access');

jimport('sellacious.loader');

$helper    = SellaciousHelper::getInstance();

/** @var $params */
$classSfx          = $params->get('class_sfx', '');
$categories        = $params->get('categories', '');
$showSubCategories = $params->get('show_sub_categories', '1');
$orderBy           = $params->get('order_by', '');
$displayLayout     = $params->get('display_layout', 'grid');
JLoader::register('ModSellaciousCategoriesHelper', __DIR__ . '/helper.php');

try
{
    $categoryList = ModSellaciousCategoriesHelper::getCategories($categories, $showSubCategories, $orderBy);

    if ($displayLayout === 'style2')
    {
	    $mainCategory = array($params->get('main_category', ''));
	    $mainCategory = ModSellaciousCategoriesHelper::getCategories($mainCategory);

	    $mainCategoryAlignment = $params->get('main_category_alignment', 'left');
    }
    elseif ($displayLayout === 'collage')
    {
    	$categories = $params->get('collage_categories', '');
    }
}
catch (Exception $e)
{
    return;
}

if (empty($categoryList) && empty($categories))
{
    return true;
}

require JModuleHelper::getLayoutPath('mod_sellacious_categories', $displayLayout);
