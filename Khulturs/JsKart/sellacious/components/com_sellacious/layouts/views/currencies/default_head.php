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

$saveOrder = ($listOrder == 'a.ordering' && strtolower($listDirn) == 'asc');
?>
<tr role="row">
	<th class="center" style="width: 10px;">
		<label class="checkbox style-0">
			<input type="checkbox" name="checkall-toggle" value="" class="hasTooltip checkbox style-3"
				   title="<?php echo JHtml::tooltipText('JGLOBAL_CHECK_ALL') ?>" onclick="Joomla.checkAll(this);"/>
			<span></span>
		</label>
	</th>
	<th class="nowrap center" width="5%">
		<?php echo JHtml::_('searchtools.sort', 'JSTATUS', 'a.state', $listDirn, $listOrder); ?>
	</th>
	<th class="nowrap">
		<?php echo JHtml::_('searchtools.sort', 'JGLOBAL_TITLE', 'a.title', $listDirn, $listOrder); ?>
	</th>
	<th class="nowrap center" width="100px">
		<?php echo JHtml::_('searchtools.sort', 'COM_SELLACIOUS_CURRENCY_HEADING_CODE_3', 'a.code_3', $listDirn, $listOrder); ?>
	</th>
	<th class="nowrap center" width="100px">
		<?php echo JText::sprintf('COM_SELLACIOUS_CURRENCY_HEADING_FOREX_XXX_PER_UNIT', $this->state->get('filter.forex', 'USD')); ?>
	</th>
	<th class="nowrap center" width="100px">
		<?php echo JText::sprintf('COM_SELLACIOUS_CURRENCY_HEADING_FOREX_UNIT_PER_XXX', $this->state->get('filter.forex', 'USD')); ?>
	</th>
	<th class="nowrap center" width="100px">
		<?php echo JHtml::_('searchtools.sort', 'COM_SELLACIOUS_CURRENCY_HEADING_SYMBOL', 'a.symbol', $listDirn, $listOrder); ?>
	</th>
	<th class="nowrap center" width="25px">
		<?php echo JHtml::_('searchtools.sort', 'COM_SELLACIOUS_CURRENCY_HEADING_DECIMAL_PLACES', 'a.decimal_places', $listDirn, $listOrder); ?>
	</th>
	<th width="15px">
		<?php echo JHtml::_('searchtools.sort', 'COM_SELLACIOUS_CURRENCY_HEADING_DECIMAL_SEP', 'a.decimal_sep', $listDirn, $listOrder); ?>
	</th>
	<th width="15px">
		<?php echo JHtml::_('searchtools.sort', 'COM_SELLACIOUS_CURRENCY_HEADING_THOUSAND_SEP', 'a.thousand_sep', $listDirn, $listOrder); ?>
	</th>
	<th class="nowrap center" width="100px">
		<?php echo JText::_('COM_SELLACIOUS_CURRENCY_HEADING_MODIFIED'); ?>
	</th>
	<th class="nowrap hidden-phone" style="width: 1%;">
		<?php echo JHtml::_('searchtools.sort', 'JGRID_HEADING_ID', 'a.id', $listDirn, $listOrder); ?>
	</th>
</tr>
