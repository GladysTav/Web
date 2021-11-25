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
/** @var  int  $value */
?>
<div class="filter-snap-in">
	<div class="filter-title filter-product-stock"><?php
		echo JText::_('MOD_SELLACIOUS_FILTERS_SHOP_BY_STOCK'); ?></div>
	<div class="filter-stock">
		<label>
			<input type="checkbox" name="filter[hide_out_of_stock]" id="filter_hide_out_of_stock"
				   value="1" <?php echo $value ? 'checked="checked"' : ''; ?>
				   <?php echo $this->autoSubmit ? 'onclick="this.form.submit();"' : ''; ?>>
			<?php echo JText::_('MOD_SELLACIOUS_FILTERS_HIDE_OUT_STOCK_LABEL'); ?>
		</label>
	</div>
</div>
