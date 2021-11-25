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

/** @var  SellaciousdeliveryViewOrders  $this */
JHtml::_('behavior.framework');
JHtml::_('jquery.framework');

JHtml::_('script', 'media/com_sellacious/js/plugin/serialize-object/jquery.serialize-object.min.js', array('version' => S_VERSION_CORE));
JHtml::_('script', 'com_sellacious/util.modal.js', array('version' => S_VERSION_CORE, 'relative' => true));
JHtml::_('script', 'com_sellaciousdelivery/view.orders.js', array('version' => S_VERSION_CORE, 'relative' => true));

JHtml::_('stylesheet', 'com_sellacious/component.css', array('version' => S_VERSION_CORE, 'relative' => true));
JHtml::_('stylesheet', 'com_sellaciousdelivery/view.orders.css', array('version' => S_VERSION_CORE, 'relative' => true));
JHtml::_('stylesheet', 'com_sellacious/font-awesome.min.css', array('version' => S_VERSION_CORE, 'relative' => true));

$listOrder = $this->escape($this->state->get('list.ordering'));
$listDirn  = $this->escape($this->state->get('list.direction'));
$ordering  = ($listOrder == 'a.ordering');
$saveOrder = ($listOrder == 'a.ordering' && strtolower($listDirn) == 'asc');
$me        = JFactory::getUser();
$today     = JFactory::getDate();

$c_state    = $this->state->get('list.currency', 'current');
$c_currency = $c_state == 'current' ? '' : ($c_state == 'original' ? null : $this->helper->currency->getGlobal('code_3'));

foreach ($this->items as $i => $order)
{
	$canDelete  = $this->helper->access->check('order.delete', $order->id);

	$invoice_link    = JRoute::_('index.php?option=com_sellacious&view=order&layout=invoice&id=' . (int) $order->id);
	$receipt_link    = JRoute::_('index.php?option=com_sellacious&view=order&layout=receipt&id=' . (int) $order->id);
	$txn_link        = JRoute::_('index.php?option=com_sellacious&view=transactions&layout=order&tmpl=component&filter[order_id]=' . (int) $order->id);
	$title           = trim(sprintf('%s - %s', $order->product_title, $order->variant_title), ' -');
	$p_code          = $this->helper->product->getCode($order->product_id, $order->variant_id, $order->seller_uid);
	$p_url           = JRoute::_('../index.php?option=com_sellacious&view=product&p=' . $p_code);
	$order_url       = JRoute::_('index.php?option=com_sellacious&view=orders&filter[search]=id:' . $order->order_id);
	$order->status   = $this->helper->order->getStatus($order->order_id, $order->item_uid);
	$order->statuses = $this->helper->order->getStatuses('order.' . $order->product_type, $order->status->s_id);
	$fromTime        = JFactory::getDate($order->slot_from_time);
	$toTime          = JFactory::getDate($order->slot_to_time);

	$deliveryStatus = $this->getModel()->getDeliveryStatus($order);
	$ddClass        = $deliveryStatus['class'];
	$dStatus        = $deliveryStatus['status'];
	?>
	<tr id="oi-row-<?php echo $i ?>-<?php echo $order->oi_id ?>" class="order-row">
		<td class="nowrap center hidden-phone">
			<label>
				<input type="checkbox" name="cid[]" id="cb<?php echo $i ?>" class="checkbox style-0"
					   value="<?php echo $order->id ?>" onclick="Joomla.isChecked(this.checked);"
					<?php echo ($canDelete) ? '' : ' disabled="disabled"' ?> />
				<span></span>
			</label>
		</td>
		<td class="center hidden-phone" style="width:40px">
			<span><?php echo (int) $order->order_id; ?></span>
		</td>
		<td class="text-left" data-row="#order-items-<?php echo $i ?>">
			<a target="_blank" href="<?php echo $order_url ?>"><span class="monospace"><?php echo $order->order_number; ?></span></a>
		</td>
		<td class="nowrap" data-row="#oi-info-row-<?php echo $i ?>-<?php echo $order->oi_id ?>">
			<a target="_blank" class="product-title hasTooltip" href="<?php echo $p_url ?>" title="<?php echo $this->escape($title) ?>"><?php echo $this->escape($title) ?></a>
		</td>
		<td class="nowrap">
			<?php echo $order->quantity ?>
		</td>
		<td style="max-width: 120px; text-overflow: ellipsis; overflow: hidden; padding-right: 5px;"><?php
			echo $this->escape($order->customer_name) ?></td>
		<td style="width:100px" class="nowrap">
			<?php $time = JHtml::_('date', $order->created, 'h:i A'); ?>
			<span class="hasTooltip" title="<?php echo $time ?>"><?php
				echo JHtml::_('date', $order->created, 'M d, Y'); ?></span>
		</td>
		<td style="width:100px" class="nowrap">
			<?php $time = ($fromTime == $toTime) ? $fromTime->format('g:i A') : $fromTime->format('g:i A') . ' - ' . $toTime->format('g:i A');
			echo JHtml::_('date', $order->slot_from_time, 'M d, Y') . '<br><b>(' . $time . ')</b>'; ?>
		</td>
		<td class="<?php echo $ddClass?>">
			<?php echo $dStatus;?>
		</td>
		<td class="nowrap text-center">
			<span class="oi-status"><?php echo $order->order_status; ?></span>
			<?php if ($this->helper->access->checkAny(array('status', 'status.own'), 'order.item.edit.')): ?>
				<a href="#" class="txt-color-red btn-oi-status-edit" data-id="<?php echo $order->oi_id ?>"><i class="fa fa-edit"></i> </a>
			<?php endif; ?>
		</td>
		<td class="amount-cell strong">
			<?php echo $this->helper->currency->display($order->sub_total, $order->currency, $c_currency); ?>
		</td>
	</tr>
	<?php
	if ($this->helper->access->checkAny(array('status', 'status.own'), 'order.item.edit.'))
	{
		?>
		<tr id="oi-info-row-<?php echo $i ?>-<?php echo $order->oi_id ?>" class="hidden">
			<td colspan="9">
				<table class="w100p">
					<tbody>
					<tr>
						<td class="status-form-container bg-color-white v-top hidden">
							<?php echo $this->loadTemplate('item_statusform', $order); ?>
						</td>
						<td class="status-log-container bg-color-white v-top"></td>
					</tr>
					</tbody>
				</table>
			</td>
		</tr>
		<?php
	}
}

echo JHtml::_('form.token');
