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
/** @var  string    $shippable */
/** @var  string    $shippable_text */
/** @var  int       $Itemid */
/** @var  string[]  $search_in */

JHtml::_('behavior.framework');

JHtml::_('stylesheet', 'mod_sellacious_filters/jquery.autocomplete.ui.css', array('relative' => true));
JHtml::_('script', 'mod_sellacious_filters/jquery.autocomplete.ui.js', array('relative' => true));
JHtml::_('script', 'mod_sellacious_filters/filters.store-location.js', array('relative' => true));
?>
<div class="filter-snap-in shippablesearch">
	<div class="filter-title filter-shippable"><?php
		echo JText::_('MOD_SELLACIOUS_FILTERS_SHOP_BY_SHIPPABLE_LOCATION'); ?></div>
	<div class="search-filter filter-location-autocomplete">

		<input type="hidden" name="filter[shippable]" class="location_custom"
			   id="filter_shippable" value="<?php echo $shippable; ?>">

		<input type="text" name="filter[shippable_text]"  class="location_custom_text"
			   id="filter_shippable_text" value="<?php echo $shippable_text; ?>"
			   placeholder="<?php echo JText::_('MOD_SELLACIOUS_FILTERS_SHIPPABLE_LOCATION_PLACEHOLDER'); ?>"
			   autocomplete="off" data-search-in="<?php echo htmlspecialchars(json_encode($search_in)) ?>"
			   data-item-id="<?php echo (int) $Itemid ?>" title="">

		<?php if ($this->autoSubmit): ?>
		<button class="btn-filter_shippable btn btn-default"
				onclick="this.form.submit();"><i class="fa fa-search"></i></button>
		<?php endif; ?>

	</div>
</div>
