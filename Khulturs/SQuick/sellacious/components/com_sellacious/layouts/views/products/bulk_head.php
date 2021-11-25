<?php
/**
 * @version     2.0.0
 * @package     sellacious
 *
 * @copyright   Copyright (C) 2012-2020 Bhartiy Web Technologies. All rights reserved.
 * @license     SPL Sellacious Private License; see http://www.sellacious.com/spl.html
 * @author      Izhar Aazmi <info@bhartiy.com> - http://www.bhartiy.com
 */
// no direct access
defined('_JEXEC') or die;

$listOrder = $this->escape($this->state->get('list.ordering'));
$listDirn  = $this->escape($this->state->get('list.direction'));

$multi_seller = $this->helper->config->get('multi_seller', 0);
?>
<tr role="row">
	<th style="width: 10px;">
		<label class="checkbox style-0">
			<input type="checkbox" name="checkall-toggle" value="" class="hasTooltip checkbox style-3"
				   title="<?php echo JHtml::tooltipText('JGLOBAL_CHECK_ALL') ?>" onclick="Joomla.checkAll(this);"/>
			<span></span>
		</label>
	</th>
	<th class="nowrap" style="width: 50px;">
		<?php echo JText::_('COM_SELLACIOUS_PRODUCT_HEADING_IMAGE'); ?>
	</th>
	<th class="nowrap">
		<?php echo JHtml::_('searchtools.sort', 'JGLOBAL_TITLE', 'a.title', $listDirn, $listOrder); ?>
	</th>
	<?php if ($multi_seller): ?>
	<th class="text-center nowrap">
		<?php echo JHtml::_('searchtools.sort', 'COM_SELLACIOUS_PRODUCT_HEADING_SELLER_COMPANY', 'p.seller_company', $listDirn, $listOrder); ?>
	</th>
	<?php endif; ?>
	<?php $pTypes = (array) $this->helper->config->get('pricing_type'); ?>
	<th class="text-center nowrap" style="width:60px;">
		<?php echo JText::_('COM_SELLACIOUS_PRODUCT_HEADING_STOCK'); ?>
	</th>
	<th class="nowrap hidden-phone" style="width: 1%;">
		<?php echo JHtml::_('searchtools.sort', 'JGRID_HEADING_ID', 'a.id', $listDirn, $listOrder); ?>
	</th>
</tr>
