<?php
/**
 * @version     2.0.0
 * @package     sellacious
 *
 * @copyright   Copyright (C) 2012-2020 Bhartiy Web Technologies. All rights reserved.
 * @license     SPL Sellacious Private License; see http://www.sellacious.com/spl.html
 * @author      Aditya Chakraborty <info@bhartiy.com> - http://www.bhartiy.com
 */
// no direct access
defined('_JEXEC') or die;

JHtml::_('jquery.framework');
JHtml::_('stylesheet', 'com_sellacioustemplates/view.templates.css', false, true);
JHtml::_('script', 'com_sellacioustemplates/view.templates.js', false, true);

$listOrder = $this->escape($this->state->get('list.ordering'));
$listDirn  = $this->escape($this->state->get('list.direction'));

$saveOrder = ($listOrder == 'a.ordering' && strtolower($listDirn) == 'asc');

?>
<tr role="row">
	<th class="center" style="width:10px;">
		<label class="checkbox style-0">
			<input type="checkbox" name="checkall-toggle" value="" class="hasTooltip checkbox style-3"
				   title="<?php echo JHtml::tooltipText('JGLOBAL_CHECK_ALL') ?>" onclick="Joomla.checkAll(this);"/>
			<span></span>
		</label>
	</th>
	<th class="nowrap center" width="5%">
		<?php echo JText::_('JSTATUS'); ?>
	</th>
	<th class="nowrap">
		<?php echo JText::_('COM_SELLACIOUSTEMPLATES_TEMPLATES_HEADING_CONTEXT'); ?>
	</th>
	<th class="nowrap" width="40%">
		<?php echo JText::_('COM_SELLACIOUSTEMPLATES_TEMPLATES_HEADING_TITLE'); ?>
	</th>
	<th class="nowrap" width="8%">

	</th>
	<th class="nowrap hidden-phone" style="width:1%;">
		<?php echo JText::_('JGRID_HEADING_ID'); ?>
	</th>
</tr>
