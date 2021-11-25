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

/** @var  SellaciousViewCurrencies $this */

$listOrder = $this->escape($this->state->get('list.ordering'));
$listDirn  = $this->escape($this->state->get('list.direction'));

JHtml::_('jquery.framework');

JHtml::_('stylesheet', 'com_sellacious/view.mailqueue.css', array('version' => S_VERSION_CORE, 'relative' => true));
JHtml::_('script', 'com_sellacious/view.mailqueue.js', array('version' => S_VERSION_CORE, 'relative' => true));

JTable::getInstance('MailQueue', 'SellaciousTable');
$state_sent = SellaciousTableMailQueue::STATE_SENT;

foreach ($this->items as $i => $item)
{
	?>
	<tr role="row">
		<td class="nowrap">
			<?php echo $this->escape($item->context); ?>
		</td>
		<td class="nowrap" style="overflow-x: hidden">
			<i class="fa fa-search-plus btn-modal"></i>
			<div class="mail-content"><?php echo $item->body; ?></div>
			<?php echo $this->escape($item->subject); ?>
		</td>
		<td class="nowrap center" style="width: 220px;">
			<?php
			$r = array();

			foreach ($item->recipients as $rc)
			{
				if (is_string($rc))
				{
					$r[] = $rc;
				}
				elseif (is_array($rc))
				{
					$fmt = '';

					if (isset($rc[2]) && strlen($rc[2]))
					{
						$fmt .= ucfirst($rc[2]) . ': ';
					}

					if (isset($rc[1]) && strlen($rc[1]))
					{
						$fmt .= $rc[1] . ' &lt;' . $rc[0] . '&gt;';
					}
					else
					{
						$fmt .= $rc[0];
					}

					$r[] = $fmt;
				}
			}

			echo implode('<br/>', (array) $r);
			?>
		</td>
		<td class="nowrap center">
			<?php echo JText::plural('COM_SELLACIOUS_MAILQUEUE_FILTER_STATE_N', $item->state); ?>
		</td>
		<td class="nowrap center">
			<?php echo JHtml::_('date', $item->created, 'M d, Y h:i A'); ?>
		</td>
		<td class="nowrap center">
			<?php echo ($item->state == $state_sent) ? JHtml::_('date', $item->sent_date, 'M d, Y h:i A') : '-'; ?>
		</td>
		<td class="nowrap center">
			<?php if ($item->retries > 0): ?>
				<label class="label label-danger"><?php echo intval($item->retries); ?></label>
			<?php else: ?>
				<label class="label label-success"><?php echo intval($item->retries); ?></label>
			<?php endif; ?>
		</td>
		<td class="nowrap">
			<?php echo $this->escape($item->response); ?>
		</td>
		<td class="center hidden-phone">
			<?php echo (int) $item->id; ?>
		</td>
	</tr>
<?php
}
