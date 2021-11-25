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

/** @var  array  $displayData */

extract($displayData);
/** @var  stdClass[]  $stores */
/** @var  int         $value */
/** @var  bool        $showShowAll */
?>
<div class="filter-snap-in">
	<div class="filter-title filter-shop-name"><?php
		echo JText::_('MOD_SELLACIOUS_FILTERS_SHOP_BY_SHOP_NAME'); ?></div>

	<div class="filter-shop-name-list">
		<?php
		$options = array();

		$options[] = JHtml::_('select.option', '0', JText::_('JALL'));

		foreach ($stores as $i => $store)
		{
			$options[] = JHtml::_('select.option', $store->user_id, $store->store_name ?: $store->title);
		}

		$actions = $this->autoSubmit ? array('onchange' => 'this.form.submit()') : array();

		echo JHtml::_('select.radiolist', $options, 'filter[shop_uid]', $actions, 'value', 'text', $value); ?>

		<?php if ($showShowAll): ?>
			<div data-show-all="shopname"><a href="javascript:void(0);">Show All</a></div>
		<?php endif; ?>
	</div>
</div>

