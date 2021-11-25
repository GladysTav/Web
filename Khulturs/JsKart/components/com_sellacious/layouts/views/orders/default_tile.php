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

/** @var  SellaciousViewOrders  $this */
/** @var  stdClass  $tplData */
$order = $tplData;

$c_currency = $this->helper->currency->current('code_3');
$paid       = $this->helper->order->isPaid($order->id);
$dispatcher = $this->helper->core->loadPlugins();
$o_url      = JRoute::_(sprintf('index.php?option=com_sellacious&view=order&id=%d', $order->id));
?>

<div class="order-basic-details">
	<div class="order-basic-head">
		<span><?php echo JHtml::_('date', $order->created, 'D, F d, Y h:i A') ?></span>
		<i class="fa order-<?php echo $paid ? 'paid fa-check ctech-text-success' : 'not-paid fa-times ctech-text-danger' ?>"> </i>
		<a href="<?php echo $o_url; ?>" class="order-detail-button ctech-float-right ctech-text-white ctech-rounded-circle ctech-bg-primary ctech-btn-sm ">
			<i class="fa fa-info"></i>
		</a>
		<span class="order-total ctech-float-right">
			<span class="order-total-label"><?php echo JText::_('COM_SELLACIOUS_ORDER_GRAND_TOTAL_LABEL'); ?>:</span>
			<strong><?php echo $this->helper->currency->display($order->grand_total, $order->currency, $c_currency, true); ?></strong>
	   	</span>
	</div>
	<div class="order-basic-body">
		<?php if (!empty($order->items)): ?>
			<?php foreach ($order->items as $oi):
				$code   = $this->helper->product->getCode($oi->product_id, $oi->variant_id, $oi->seller_uid);
				$p_url  = JRoute::_('index.php?option=com_sellacious&view=product&p=' . $code);
				$title  = trim(sprintf('%s - %s', $oi->product_title, $oi->variant_title), '- ');
				$images = $this->helper->product->getImages($oi->product_id, $oi->variant_id);
				$status = $this->helper->order->getStatus($oi->order_id, $oi->item_uid);

				if (!$status->id)
				{
					$status = $this->helper->order->getStatus($oi->order_id, null, $oi->seller_uid);
				}
				?>
				<div class="order-item">
					<div class="order-item-image">
						<a href="<?php echo $p_url; ?>">
							<span style="background-image: url('<?php echo reset($images); ?>')"></span>
						</a>
					</div>
					<div class="order-item-info">
						<a href="<?php echo $p_url; ?>" class="order-item-title ctech-text-dark"><?php echo $this->escape($title) ?></a>
						<span class="order-quantity-label"><?php echo JText::_('COM_SELLACIOUS_ORDER_PREFIX_ITEM_QUANTITY_N'); ?></span><?php echo $oi->quantity; ?><br/>
						<span class="seller-order"><?php echo JText::_('COM_SELLACIOUS_ORDER_PREFIX_ITEM_SELLER'); ?></span>
						<span><?php echo $oi->seller_company; ?></span>
					</div>
					<div class="order-item-price ctech-text-right">
						<strong class="item-total-price ctech-text-primary">
							<?php echo $this->helper->currency->display($oi->sub_total + $oi->shipping_amount, $order->currency, $c_currency, true); ?>
						</strong>
						<br/>
						<span class="item-status">
						<?php
						if ($status->s_title)
						{
							$status_dt = JHtml::_('date', $status->created, 'F d, Y (l)');
							echo JText::sprintf('COM_SELLACIOUS_ORDER_STATUS_AT_DATE_MESSAGE', $status->s_title, $status_dt);
						}
						?>
						</span>
					</div>
					<div class="ctech-clearfix"></div>
					<div class="order-item-status">
						<span class="status-label"><?php echo JText::_("COM_SELLACIOUS_ORDER_STATUS");?></span>
						<span class="status-item <?php echo $paid ? 'ctech-text-success' : 'ctech-text-danger' ?>"><?php echo $paid ? JText::_("COM_SELLACIOUS_ORDER_STATUS_PAID") : JText::_("COM_SELLACIOUS_ORDER_STATUS_UNPAID");?> </span>
					</div>
				</div>
			<?php endforeach; ?>
		<?php endif; ?>
	</div>
</div>


