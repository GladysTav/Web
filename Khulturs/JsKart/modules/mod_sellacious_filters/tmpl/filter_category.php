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

JHtml::_('script', 'mod_sellacious_filters/jquery.treeview.js', array('relative' => true));
JHtml::_('script', 'mod_sellacious_filters/filters.category.js', array('relative' => true));

/** @var  Category  $this */

/** @var  array  $displayData */
extract($displayData);

/** @var  int         $storeId */
/** @var  int         $catId */
/** @var  stdClass[]  $items */
/** @var  bool        $showShowAll */
?>
<div class="filter-snap-in">
	<div class="filter-title"><?php echo JText::_('MOD_SELLACIOUS_FILTERS_SHOP_BY_CATEGORY'); ?></div>
	<div class="filter-cat-list">
		<ul id="filter-list-group">
			<?php $this->renderLevel($items, $storeId, $catId); ?>
		</ul>
		<?php if ($showShowAll): ?>
			<div data-show-all="category" data-store-id="<?php echo $storeId ?>" data-category-id="<?php echo $catId ?>">
				<a href="javascript:void(0);"><?php echo JText::_('MOD_SELLACIOUS_FILTERS_LINK_SHOW_MORE'); ?></a></div>
		<?php endif; ?>
	</div>
</div>

