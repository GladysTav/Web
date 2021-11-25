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

/** @var  \Sellacious\ProductsFilter\SpecialCategory  $this */

/** @var  array  $displayData */

extract($displayData);

/** @var  stdClass[]  $categories */
/** @var  int         $storeId */
/** @var  int         $categoryId */
/** @var  bool        $showShowAll */
?>
<div class="filter-snap-in">
	<div class="filter-title filter-spl-categories"><?php
		echo JText::_('MOD_SELLACIOUS_FILTERS_SHOP_BY_SPECIAL_CATEGORIES'); ?></div>

	<div class="filter-spl-categories-list">

			<?php
			$options = array();

			$options[] = JHtml::_('select.option', '0', JText::_('JALL'));

			foreach ($categories as $i => $category)
			{
				$options[] = JHtml::_('select.option', $category->id, $category->title ?: $store);
			}

			$actions = $this->autoSubmit ? array('onchange' => 'this.form.submit()') : array();

			echo JHtml::_('select.radiolist', $options, 'filter[spl_category]', $actions, 'value', 'text', $categoryId);
			?>

			<?php if ($showShowAll): ?>
				<div data-show-all="splcat"><a href="javascript:void(0);">Show All</a></div>
			<?php endif; ?>

	</div>
</div>

