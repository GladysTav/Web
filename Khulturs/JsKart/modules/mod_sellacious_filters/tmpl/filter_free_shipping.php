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

/** @var  array   $displayData */

extract($displayData);
/** @var  string  $value */
?>
<div class="filter-snap-in">
	<div class="filter-title filter-shipping">
		<?php echo JText::_('MOD_SELLACIOUS_FILTERS_SHOP_BY_SHIPPING'); ?></div>
	<div class="filter-shipping-options">
		<?php
		$shipping = array(
			JHtml::_('select.option', '0', JText::_('MOD_SELLACIOUS_FILTERS_FIELD_SHIPPING_ALL')),
			JHtml::_('select.option', '1', JText::_('MOD_SELLACIOUS_FILTERS_FIELD_SHIPPING_FREE'))
		);
		$actions = $this->autoSubmit ? array('onchange' => 'this.form.submit()') : null;

		echo JHtml::_('select.radiolist', $shipping, 'filter[free_shipping]', $actions, 'value', 'text', $value); ?>
	</div>
</div>

