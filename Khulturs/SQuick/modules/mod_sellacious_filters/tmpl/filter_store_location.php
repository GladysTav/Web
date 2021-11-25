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
/** @var  string    $location */
/** @var  string    $location_custom */
/** @var  string    $location_text */
/** @var  int       $Itemid */
/** @var  string[]  $search_in */
/** @var  string    $ip_country */

JHtml::_('behavior.framework');

JHtml::_('stylesheet', 'mod_sellacious_filters/jquery.autocomplete.ui.css', array('relative' => true));
JHtml::_('script', 'mod_sellacious_filters/jquery.autocomplete.ui.js', array('relative' => true));
JHtml::_('script', 'mod_sellacious_filters/filters.store-location.js', array('relative' => true));

$locations = array();

$locations[] = JHtml::_('select.option', '0', JText::_('MOD_SELLACIOUS_FILTERS_FIELD_STORE_LOCATION_ANYWHERE'));

if ($ip_country)
{
	$locations[] = JHtml::_('select.option', '1', $ip_country);
}

$locations[] = JHtml::_('select.option', '2', JText::_('MOD_SELLACIOUS_FILTERS_FIELD_STORE_LOCATION_CUSTOM'));

$class = array('class' => 'store-location-options');
?>
<div class="filter-snap-in">

	<div class="filter-title filter-shop-location"><?php
		echo JText::_('MOD_SELLACIOUS_FILTERS_SHOP_BY_STORE_LOCATION'); ?></div>

	<div class="filter-store-location filter-location-autocomplete">

		<?php echo JHtml::_('select.radiolist', $locations, 'filter[store_location]', $class, 'value', 'text', $location); ?>

		<div class="<?php echo $location == 2 ? '' : 'hidden'; ?> s-l-custom-block">

			<input type="hidden" name="filter[store_location_custom]" class="location_custom"
				   id="filter_store_location_custom" value="<?php echo $location_custom ?>">

			<input type="text" name="filter[store_location_custom_text]" class="s-l-custom-text location_custom_text"
				   id="filter_store_location_custom_text" value="<?php echo $location_text ?>"
				   data-search-in="<?php echo htmlspecialchars(json_encode($search_in)) ?>"
				   data-item-id="<?php echo (int) $Itemid ?>" title="">

			<?php if ($this->autoSubmit): ?>
			<button class="btn-filter_shop_location btn btn-default"
					onclick="this.form.submit();"><i class="fa fa-search"></i></button>
			<?php endif; ?>

		</div>

	</div>

</div>
