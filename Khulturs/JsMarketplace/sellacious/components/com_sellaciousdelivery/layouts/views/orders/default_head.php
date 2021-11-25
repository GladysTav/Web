<?php
/**
 * @version     1.7.4
 * @package     sellacious
 *
 * @copyright   Copyright (C) 2012-2019 Bhartiy Web Technologies. All rights reserved.
 * @license     SPL Sellacious Private License; see http://www.sellacious.com/spl.html
 * @author      Aditya Chakraborty <info@bhartiy.com> - http://www.bhartiy.com
 */
// no direct access
defined('_JEXEC') or die;

$prefix     = 'COM_SELLACIOUSDELIVERY_ORDERS_HEADING';
$listOrder  = $this->escape($this->state->get('list.ordering'));
$listDirn   = $this->escape($this->state->get('list.direction'));
?>
<tr role="row">
	<th style="width: 10px;">
		<label class="checkbox style-0">
			<input type="checkbox" name="checkall-toggle" value="" class="hasTooltip checkbox style-3"
				   title="<?php echo JHtml::tooltipText('JGLOBAL_CHECK_ALL') ?>" onclick="Joomla.checkAll(this);" />
			<span></span>
		</label>
	</th>
	<th class="nowrap hidden-phone" style="width:40px;">
		<?php echo JHtml::_('searchtools.sort', $prefix . '_ID', 'a.id', $listDirn, $listOrder); ?>
	</th>

	<th class="nowrap text-left" style="width:30px;">
		<?php echo JHtml::_('searchtools.sort', $prefix . '_ORDER_NUMBER', 'a.order_number', $listDirn, $listOrder); ?>
	</th>

	<th class="nowrap text-left">
		<?php echo JHtml::_('searchtools.sort', $prefix . '_ORDER_ITEM_TITLE', 'oi.product_title', $listDirn, $listOrder); ?>
	</th>

	<th class="nowrap text-left" style="width:40px;">
		<?php echo JHtml::_('searchtools.sort', $prefix . '_ORDER_ITEM_HEADING_QUANTITY', 'a.quantity', $listDirn, $listOrder); ?>
	</th>

	<th class="nowrap text-left">
		<?php echo JHtml::_('searchtools.sort', 'COM_SELLACIOUS_ORDER_HEADING_CUSTOMER', 'a.customer_name', $listDirn, $listOrder); ?>
	</th>

	<th class="nowrap hidden-phone" style="width:40px;">
		<?php echo JHtml::_('searchtools.sort', $prefix . '_DATE', 'a.created', $listDirn, $listOrder); ?>
	</th>

	<th class="nowrap hidden-phone" style="width:40px;">
		<?php echo JHtml::_('searchtools.sort', $prefix . '_DELIVERY_DATE', 'ods.slot_from_time', $listDirn, $listOrder); ?>
	</th>

	<th class="nowrap hidden-phone">
		<?php echo JText::_($prefix . '_DELIVERY_DUE'); ?>
	</th>

	<th class="nowrap text-center">
		<?php echo JHtml::_('searchtools.sort', $prefix . '_STATUS', 'ss.title', $listDirn, $listOrder); ?>
	</th>

	<th class="nowrap text-right" style="width:90px;">
		<?php echo JHtml::_('searchtools.sort', $prefix . '_TOTAL', 'a.sub_total', $listDirn, $listOrder); ?>
		</th>
</tr>
