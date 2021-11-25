<?php
/**
 * @version     2.0.0
 * @package     Sellacious Filters Module
 *
 * @copyright   Copyright (C) 2012-2020 Bhartiy Web Technologies. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Izhar Aazmi <info@bhartiy.com> - http://www.bhartiy.com
 */
// no direct access
defined('_JEXEC') or die;

/** @var  array  $displayData */

extract($displayData);
/** @var  float  $min */
/** @var  float  $max */
?>
<div class="filter-snap-in">
	<div class="filter-title filter-price"><?php
		echo JText::_('MOD_SELLACIOUS_FILTERS_SHOP_BY_PRICE'); ?></div>
	<div class="filter-price-area">
		<input type="number" name="filter[price_from]" id="min_price_input"
			   value ="<?php echo $min; ?>" placeholder="Min" title=""/>
		<input type="number" name="filter[price_to]" id="max_price_input"
			   value ="<?php echo $max; ?>" placeholder="Max" title=""/>
	</div>
	<?php if ($this->autoSubmit): ?>
	<button class="btn-filter-price ctech-btn ctech-btn-dark ctech-btn-sm ctech-btn-block"
			onclick="this.form.submit();">Go</button>
	<?php endif; ?>
</div>
