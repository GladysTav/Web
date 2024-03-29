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
?>
<tr role="row">
	<th style="width: 10px;">
		<label class="checkbox style-0">
			<input type="checkbox" name="checkall-toggle" value="" class="hasTooltip checkbox style-3"
			       title="<?php echo JHtml::tooltipText('JGLOBAL_CHECK_ALL') ?>" onclick="Joomla.checkAll(this);" />
			<span></span>
		</label>
	</th>
	<th class="nowrap" width="5%">
		<?php echo JHtml::_('searchtools.sort',  'JSTATUS', 'a.state', $listDirn, $listOrder); ?>
	</th>
	<th class="nowrap" width="1%">
	</th>
	<th class="nowrap">
		<?php echo JHtml::_('searchtools.sort', 'JGLOBAL_TITLE', 'a.title', $listDirn, $listOrder); ?>
	</th>
	<th class="nowrap center" style="width:80px">
		<?php echo JHtml::_('searchtools.sort', 'COM_SELLACIOUS_LOCATIONS_TITLE_ISO_CODE', 'a.iso_code', $listDirn, $listOrder); ?>
	</th>
	<th class="nowrap center">
		<?php echo JHtml::_('searchtools.sort', 'COM_SELLACIOUS_LOCATIONS_TITLE_COUNTRY', 'a.country_title', $listDirn, $listOrder); ?>
	</th>
	<th class="nowrap center">
		<?php echo JHtml::_('searchtools.sort', 'COM_SELLACIOUS_LOCATIONS_TITLE_STATE', 'a.state_title', $listDirn, $listOrder); ?>
	</th>
	<th class="nowrap center">
		<?php echo JHtml::_('searchtools.sort', 'COM_SELLACIOUS_LOCATIONS_TITLE_DISTRICT', 'a.district_title', $listDirn, $listOrder); ?>
	</th>
	<th class="nowrap center">
		<?php echo JHtml::_('searchtools.sort', 'COM_SELLACIOUS_LOCATIONS_TITLE_ZIP', 'a.zip_title', $listDirn, $listOrder); ?>
	</th>
	<th class="nowrap hidden-phone" style="width: 1%;">
		<?php echo JHtml::_('searchtools.sort',  'JGRID_HEADING_ID', 'a.id', $listDirn, $listOrder); ?>
	</th>
</tr>
