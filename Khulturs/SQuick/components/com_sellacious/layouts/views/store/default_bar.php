<?php
/**
 * @version     2.0.0
 * @package     sellacious
 *
 * @copyright   Copyright (C) 2012-2020 Bhartiy Web Technologies. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Izhar Aazmi <info@bhartiy.com> - http://www.bhartiy.com
 */
// no direct access
defined('_JEXEC') or die;

$order       = $this->state->get('list.custom_ordering');
$sortOptions = array(
	'order_max'  => JText::_('COM_SELLACIOUS_PRODUCTS_ORDERING_ORDER_COUNT'),
	'rating_max' => JText::_('COM_SELLACIOUS_PRODUCTS_ORDERING_RATING'),
	'price_min'  => JText::_('COM_SELLACIOUS_PRODUCTS_ORDERING_PRICE_ASC'),
	'price_max'  => JText::_('COM_SELLACIOUS_PRODUCTS_ORDERING_PRICE_DESC'),
);
?>
<div class="sortingbar ctech-float-right">
	<label for="custom_ordering"><?php echo JText::_('COM_SELLACIOUS_SORT_BY') ?></label>
	<?php echo JHtml::_('select.genericlist', $sortOptions, 'custom_ordering', 'onchange="Joomla.submitform();"', 'value', 'text', $order); ?>
</div>
<div class="filter-icon ctech-float-left">
	<a class="ctech-btn-primary ctech-btn ctech-btn-sm" href="#" id="filters-toggle"><i
		 class="fa fa-filter"></i> <?php echo JText::_('MOD_SELLACIOUS_FILTERS_FILTER_MOBILE_LABEL'); ?></a>
</div>
