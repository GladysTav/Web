<?php
/**
 * @version     2.0.0
 * @package     com_sellaciousreporting
 *
 * @copyright   Copyright (C) 2012-2020 Bhartiy Web Technologies. All rights reserved.
 * @license     SPL Sellacious Private License; see http://www.sellacious.com/spl.html
 * @author      Aditya Chakraborty <info@bhartiy.com> - http://www.bhartiy.com
 */

use Sellacious\Report\ReportHelper;
use Sellacious\User\User;

defined('_JEXEC') or die;

/** @var  SellaciousReportingViewReports  $this */

$user = User::getInstance();

JHtml::_('jquery.framework');

JHtml::_('stylesheet', 'com_sellaciousreporting/view.reports.css', array('version' => S_VERSION_CORE, 'relative' => true));
JHtml::_('script', 'com_sellaciousreporting/view.reports.js', array('version' => S_VERSION_CORE, 'relative' => true));

$canEdit = $user->authorise('core.edit', 'com_sellaciousreporting');

foreach ($this->items as $i => $obj)
{
	$reportId  = $obj->id;
	$canChange = $this->helper->access->check('report.edit.state');
	$canDelete = $this->helper->access->check('report.delete');
	$canEdit   = $this->helper->access->check('report.edit') || ($obj->created_by == $user->id && $this->helper->access->check('report.edit.own'));

	$link        = JRoute::_('index.php?option=com_sellaciousreporting&task=report.edit&id=' . $obj->id);
	$report_link = JRoute::_('index.php?option=com_sellaciousreporting&view=sreports&reportToBuild=' . $obj->handler . '&id=' . $reportId);

	$canEdit = ReportingHelper::canEditReport($obj->id, $canEdit);

	try
	{
		$handler = ReportHelper::getHandler($obj->handler);
	}
	catch (Exception $e)
	{
		$handler = null;
	}
	?>
	<tr role="row">
		<td class="nowrap text-center hidden-phone">
			<label>
				<input type="checkbox" name="cid[]" id="cb<?php echo $i ?>" class="checkbox style-0"
					   value="<?php echo $obj->id ?>" onclick="Joomla.isChecked(this.checked);"
					<?php echo ($canEdit || $canChange || $canDelete) ? '' : ' disabled="disabled"' ?>/>
				<span></span>
			</label>
		</td>
		<td class="nowrap text-center">
		<span class="btn-round"><?php
			echo JHtml::_('jgrid.published', $obj->state, $i, 'reports.', $canChange);?></span>
		</td>
		<td class="nowrap">
			<a href="<?php echo $report_link; ?>" title="<?php echo JText::_('COM_SELLACIOUSREPORTING_EDIT'); ?>">
				<?php echo $obj->title; ?>
			</a>
		</td>
		<td class="nowrap center">
			<?php echo JHtml::_('date', $obj->created, 'M d, Y H:i'); ?>
		</td>
		<td class="nowrap center">
			<?php if ($canEdit): ?>
				<a href="<?php echo $link; ?>"><?php echo JText::_('COM_SELLACIOUSREPORTING_EDIT_REPORT'); ?></a>
			<?php endif; ?>

		</td>
		<td class="nowrap center">
			<?php echo $handler ? $handler->getLabel() : JText::_('COM_SELLACIOUSREPORTING_HANDLER_UNAVAILABLE'); ?>
		</td>
		<td class="nowrap center">
			<?php echo $reportId; ?>
		</td>
	</tr>
	<?php
}

