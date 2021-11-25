<?php
/**
 * @version     2.0.0
 * @package     Sellacious Filters Module
 *
 * @copyright   Copyright (C) 2012-2020 Bhartiy Web Technologies. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Aditya Chakraborty <info@bhartiy.com> - http://www.bhartiy.com
 */
// no direct access
defined('_JEXEC') or die;

/** @var  array  $displayData */

extract($displayData);
/** @var  int  $open */
/** @var  int  $delivery */
/** @var  int  $pickup */
?>
<div class="filter-snap-in">

	<div class="filter-title filter-store-availability"><?php
		echo JText::_('MOD_SELLACIOUS_FILTERS_STORE_AVAILABILITY'); ?></div>

	<div class="filter-store">

		<label>
			<input type="checkbox" name="filter[show_open_stores]"
				   id="filter_show_open_stores" value="1"
				   <?php echo $open ? 'checked="checked"' : ''; ?>
				   <?php echo $this->autoSubmit ? 'onclick="this.form.submit();"' : ''; ?>>
			<?php echo JText::_('MOD_SELLACIOUS_FILTERS_SHOW_OPEN_STORES_LABEL'); ?>
		</label>

		<label>
			<input type="checkbox" name="filter[delivery_available]"
				   id="filter_delivery_available" value="1"
				   <?php echo $delivery ? 'checked="checked"' : ''; ?>
				   <?php echo $this->autoSubmit ? 'onclick="this.form.submit();"' : ''; ?>>
			<?php echo JText::_('MOD_SELLACIOUS_FILTERS_STORE_DELIVERY_AVAILABLE_LABEL'); ?>
		</label>

		<label>
			<input type="checkbox" name="filter[pickup_available]"
				   id="filter_pickup_available" value="1"
				   <?php echo $pickup ? 'checked="checked"' : ''; ?>
				   <?php echo $this->autoSubmit ? 'onclick="this.form.submit();"' : ''; ?>>
			<?php echo JText::_('MOD_SELLACIOUS_FILTERS_STORE_PICKUP_AVAILABLE_LABEL'); ?>
		</label>

	</div>

</div>
