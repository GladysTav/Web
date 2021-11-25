<?php
/**
 * @version     2.0.0
 * @package     Sellacious Filters Module
 *
 * @copyright   Copyright (C) 2012-2020 Bhartiy Web Technologies. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Bhavika Matariya <info@bhartiy.com> - http://www.bhartiy.com
 */
// no direct access
defined('_JEXEC') or die;

use Sellacious\ProductsFilter\Category;

/** @var  Category  $this */

/** @var  int       $storeId */
/** @var  int       $catId */
/** @var stdClass[] $items */
foreach ($items as $item)
{
	if ($storeId)
	{
		$link = sprintf('index.php?option=com_sellacious&view=store&id=%d&filter[category_id]=%s', $storeId, $item->id);
	}
	else
	{
		$link = sprintf('index.php?option=com_sellacious&view=products&category_id=%s', $item->id);
	}

	$link  = JRoute::_($link);
	$class = $catId == $item->id ? ' active strong' : '';
	$title = $item->id > 1 ? htmlspecialchars($item->title) : 'Show All';

	echo '<li>';

	echo '<a href="' . $link . '" class="' . $class . '" title="' . $title . '">' . $title . '</a>';

	if (!empty($item->children))
	{
		echo '<ul>';

		$this->renderLevel($item->children, $storeId, $catId);

		echo '</ul>';
	}

	echo '</li>';
}
