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
/** @var  int         $storeId */
/** @var  int         $value */
/** @var  bool        $showShowAll */
/** @var  stdClass[]  $offers */
?>

<div class="filter-snap-in">
	<div class="filter-title filter-offers"><?php
		echo JText::_('MOD_SELLACIOUS_FILTERS_SHOP_BY_SPECIAL_OFFERS'); ?></div>

	<div class="filter-offers-list">

		<ul id="filter-list-group">

			<li>
				<a href="javascript:void(0);"
				   onclick="document.getElementById('filter_offer_id').value = '0'; document.getElementById('filter_offer_id').form.submit();"
				   class="<?php echo $value ? '' : 'active strong' ?>" title="All">All</a>
			</li>

			<?php foreach ($offers as $i => $offer): ?>

				<?php $class = $value == $offer->id ? 'active strong' : ''; ?>

				<li>
					<a href="javascript:void(0);"
					   onclick="document.getElementById('filter_offer_id').value = '<?php echo (int) $offer->id ?>'; document.getElementById('filter_offer_id').form.submit();"
					   class="<?php echo $class ?>" title="<?php echo $offer->title ?>"><?php echo $offer->title ?></a>
				</li>

			<?php endforeach; ?>

		</ul>

		<?php if ($showShowAll): ?>
			<div data-show-all="offer"><a href="javascript:void(0);">Show All</a></div>
		<?php endif; ?>

		<input type="hidden" name="filter[offer_id]" id="filter_offer_id" value="<?php echo $value ?>">

	</div>
</div>
