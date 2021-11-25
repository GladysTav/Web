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

use Joomla\Registry\Registry;

/** @var SellaciousViewOrder $this */
JHtml::_('jquery.framework');
JHtml::_('ctech.bootstrap');

JHtml::_('script', 'com_sellacious/plugin/serialize-object/jquery.serialize-object.min.js', false, true);
JHtml::_('script', 'com_sellacious/fe.view.order.payment.js', false, true);

JHtml::_('stylesheet', 'com_sellacious/font-awesome.min.css', null, true);
JHtml::_('stylesheet', 'com_sellacious/fe.component.css', null, true);
JHtml::_('stylesheet', 'com_sellacious/fe.view.order.payment.css', null, true);

$order = new Registry($this->item);
$items = $order->get('items');

$hasShippable       = $this->helper->order->hasShippable($order->get('id'));
$hasShippingAddress = $this->helper->order->hasShippingAddress($order->get('id'));
$hasBillingAddress  = $this->helper->order->hasBillingAddress($order->get('id'));
$addressPending     = $this->helper->order->checkAddressPending($order->get('id'));
$allowAddressEdit   = $this->helper->config->get('allow_order_address_change');
$c_currency         = $this->helper->currency->current('code_3');
$o_currency         = $order->get('currency');

$shipping_params = (array) $order->get('shipping_params');
$checkout_forms  = $order->get('checkout_forms');
?>
<div class="ctech-wrapper">
	<div class="complete-order-box">
		<h4 class="orders-heading"><?php echo JText::sprintf('COM_SELLACIOUS_ORDER_HEADING_PAYMENT', $order->get('order_number')) ?></h4>

		<div class="complete-order-wrapper">
			<div class="order-basic-info">
				<span><?php echo JText::_('COM_SELLACIOUS_ORDER_HEADING_DATE_LABEL'); ?> <?php echo JHtml::_('date', $order->get('created'), 'D, F d, Y h:i A'); ?></span>
				<span class="pull-right"><?php echo JText::_('COM_SELLACIOUS_ORDER_HEADING_ID'); ?>:&nbsp;<?php
					echo $order->get('order_number') ?> <small>(<?php echo JText::plural('COM_SELLACIOUS_ORDER_PREFIX_TOTAL_ITEMS_N', count($items)); ?>)</small></span>
			</div>
			<div class="ctech-clearfix"></div>

			<div class="order-advanced-info">
				<div class="ctech-row">
					<div class="ctech-col-sm-7">
						<div id="address-viewer">
							<?php if ($hasShippingAddress) : ?>
								<div id="address-shipping-text">
									<div class="address_label"><?php echo JText::_('COM_SELLACIOUS_ORDER_SHIPPING_ADDRESS_LABEL'); ?></div>
									<span class="address_name ctech-text-primary"><?php echo $order->get('st_name') ?></span>

									<?php if ($order->get('st_mobile')): ?>
										<span class="address_mobile">
											<i class="fa fa-mobile-phone fa-lg"></i><?php echo $order->get('st_mobile') ?></span>
									<?php endif; ?>
									<?php if ($order->get('st_address')): ?>
										<span class="address_address"><?php echo $order->get('st_address') ?></span>
									<?php endif; ?>
									<?php if ($order->get('st_landmark')): ?>
										<span class="address_landmark"><?php echo $order->get('st_landmark') ?>,</span>
									<?php endif; ?>
									<?php if ($order->get('st_district')): ?>
										<span class="address_district"><?php echo $order->get('st_district') ?>,</span>
									<?php endif; ?>
									<?php if ($order->get('st_state')): ?>
										<span class="address_state_loc"><?php echo $order->get('st_state') ?></span>
									<?php endif; ?>
									<?php if ($order->get('st_zip')): ?>
										<span class="address_zip"> - <?php echo $order->get('st_zip') ?></span>
									<?php endif; ?>
									<?php if ($order->get('st_country')): ?>
										<span class="address_country"><?php echo $order->get('st_country') ?></span>
									<?php endif; ?>
								</div>
							<?php endif; ?>
							<?php if ($hasBillingAddress) : ?>
								<div id="address-billing-text">
									<div class="address_label"><?php echo JText::_('COM_SELLACIOUS_ORDER_BILLING_ADDRESS_LABEL'); ?></div>
									<span class="address_name ctech-text-primary"><?php echo $order->get('bt_name') ?></span>
									<?php if ($order->get('bt_mobile')): ?>
										<span class="address_mobile">
											<i class="fa fa-mobile-phone fa-lg"></i><?php echo $order->get('bt_mobile') ?></span>
									<?php endif; ?>
									<?php if ($order->get('bt_address')): ?>
										<span class="address_address"><?php echo $order->get('bt_address') ?></span>
									<?php endif; ?>
									<?php if ($order->get('bt_landmark')): ?>
										<span class="address_landmark"><?php echo $order->get('bt_landmark') ?>,</span>
									<?php endif; ?>
									<?php if ($order->get('bt_district')): ?>
										<span class="address_district"><?php echo $order->get('bt_district') ?>,</span>
									<?php endif; ?>
									<?php if ($order->get('bt_state')): ?>
										<span class="address_state_loc"><?php echo $order->get('bt_state') ?></span>
									<?php endif; ?>
									<?php if ($order->get('bt_zip')): ?>
										<span class="address_zip"> - <?php echo $order->get('bt_zip') ?></span>
									<?php endif; ?>
									<?php if ($order->get('bt_country')): ?>
										<span class="address_country"><?php echo $order->get('bt_country') ?></span>
									<?php endif; ?>
								</div>
							<?php endif; ?>
						</div>
					</div>
					<div class="ctech-col-sm-5">
						<?php $oShoprules = $order->get('shoprules'); ?>

						<?php if (!empty($oShoprules)): ?>
							<div class="w100p order-summary">
								<?php
								$cart_shoprules = (array) $order->get('shoprules');

								if (count($cart_shoprules)):
									?>
									<div class="order-summary-head">
										<div class="order-summary-title">
											<?php echo JText::_('COM_SELLACIOUS_ORDER_HEADING_SHOPRULE_SUMMARY') ?>
										</div>
									</div>
									<?php
									foreach ($cart_shoprules as $rule):
										if ($rule->change != 0): ?>
											<div class="order-summary-row">
												<div class="order-summary-row-label">
													<?php echo str_repeat('|&mdash;', $rule->level - 1) ?>
													<?php echo $this->escape($rule->title); ?>:
												</div>
												<div class="order-summary-row-value">
													<?php
													$value = $this->helper->currency->display(abs($rule->change), $o_currency, $c_currency, true);
													echo $rule->change >= 0 ? '(+) ' . $value : '(-) ' . $value;
													?>
												</div>
											</div>
										<?php
										endif;
									endforeach;
								endif;
								?>

								<?php if (abs($order->get('product_shipping')) >= 0.01): ?>
									<div class="order-summary-row">
										<div class="order-summary-single">
											<?php echo $order->get('shipping_rule') ? JText::sprintf('COM_SELLACIOUS_ORDER_PREFIX_ITEM_SHIPPING_RULE', $order->get('shipping_rule')) : ''; ?>
										</div>
									</div>
									<div class="order-summary-row">
										<div class="order-summary-row-label">
											<?php echo JText::_('COM_SELLACIOUS_ORDER_TOTAL_SHIPPING'); ?>
										</div>
										<div class="order-summary-row-value">
											<?php echo $this->helper->currency->display($order->get('product_shipping'), $o_currency, $c_currency, true) ?>
										</div>
									</div>
								<?php endif; ?>

								<?php if ($coupon = $order->get('coupon')): ?>
									<div class="order-summary-row">
										<div class="order-summary-row-label">
											<span
												class="coupon-label"><?php echo $this->escape($coupon->code) . ' : <em class="ctech-text-info">' . $this->escape($coupon->coupon_title) . '</em>' ?></span>
										</div>
										<div class="order-summary-row-value">
											(-) <?php echo $this->helper->currency->display($coupon->amount, $o_currency, $c_currency, true) ?>
										</div>
									</div>
								<?php endif; ?>

								<div class="order-summary-row order-grand-total">
									<div class="order-summary-row-label">
										<?php echo JText::_('COM_SELLACIOUS_ORDER_HEADING_GRAND_TOTAL'); ?>
									</div>
									<div class="order-summary-row-value ctech-text-primary grand-total-price "><?php
										echo $this->helper->currency->display($order->get('grand_total'), $o_currency, $c_currency, true) ?>
									</div>
								</div>

								<?php if ($order->get('payment.fee_amount')): ?>
									<div class="order-summary-row">
										<div class="order-summary-row-label"><?php
											echo JText::sprintf('COM_SELLACIOUS_ORDER_HEADING_PAYMENT_FEE_METHOD', $order->get('payment.method_name')); ?></div>
										<div class="order-summary-row-value">
											<?php echo $this->helper->currency->display($order->get('payment.fee_amount'), $o_currency, $c_currency, true) ?>
										</div>
									</div>
								<?php endif; ?>
							</div>
						<?php endif; ?>
					</div>
				</div>

				<div class="ctech-row">
					<div class="ctech-col-sm-12">
						<?php if ($allowAddressEdit || !$hasBillingAddress || ($hasShippable && !$hasShippingAddress)): ?>
							<?php echo $this->loadTemplate('address', 'st') ?>
						<?php endif; ?>
					</div>
				</div>
			</div>

			<div class="order-items">
				<div class="order-items-wrapper">
					<?php foreach ($items as $oi):

						$code     = $this->helper->product->getCode($oi->product_id, $oi->variant_id, $oi->seller_uid);
						$p_url    = JRoute::_('index.php?option=com_sellacious&view=product&p=' . $code);
						$title    = trim(sprintf('%s - %s', $oi->product_title, $oi->variant_title), '- ');
						$images   = $this->helper->product->getImages($oi->product_id, $oi->variant_id);
						$statuses = $this->helper->order->getStatusLog($oi->order_id, $oi->item_uid);

						if (empty($statuses))
						{
							$statuses = $this->helper->order->getStatusLog($oi->order_id, null, $oi->seller_uid);
						}
						?>
						<div class="order-item">
							<div class="order-item-basic-info">
								<?php foreach ($statuses as $si => $status): ?>
									<span class="<?php echo $si > 2 ? 'hidden toggle-element' : ''; ?> order-status-date">
							<?php echo JHtml::_('date', $status->created, 'M d, Y h:i A'); ?>
						</span>
									<span class="hasTooltip order-status ctech-float-right" data-placement="top"
										  title="<?php echo $status->customer_notes ?>">
							<?php echo $status->s_title ?>
						</span>
								<?php endforeach; ?>
							</div>
							<div class="ctech-row">
								<div class="ctech-col-sm-12">
									<div class="item-image">
										<a href="<?php echo $p_url; ?>">
											<span style="background-image:url('<?php echo reset($images); ?>')"></span>
										</a>
									</div>
									<div class="item-info">
										<?php echo $oi->package_items ? JText::_('COM_SELLACIOUS_CART_PACKAGE_ITEM_LABEL') : ''; ?>
										<a class="item-title" href="<?php echo $p_url ?>"><?php echo $this->escape($title) ?></a>
										<p class="item-price ctech-text-primary"><?php echo $this->helper->currency->display($oi->sub_total, $order->get('currency'), $c_currency, true); ?></p>
										<?php if (abs($oi->shipping_amount) >= 0.01): ?>
											<small><?php echo JText::_('COM_SELLACIOUS_ORDER_ITEM_SHIPPING_AMOUNT_LABEL') ?>
												<?php echo $this->helper->currency->display($oi->shipping_amount, $order->get('currency'), $c_currency, true); ?></small>
											<br/>
										<?php endif; ?><br>
										<?php echo JText::plural('COM_SELLACIOUS_ORDER_PREFIX_ITEM_QUANTITY_N', $oi->quantity) ?>
										<br/>
										<?php if ($oi->package_items): ?>
											<hr class="simple">
											<ol class="package-items">
												<?php
												foreach ($oi->package_items as $pkg_item):
													$url = JRoute::_('index.php?option=com_sellacious&view=product&p=' . $pkg_item->code);
													$pk_title = trim(sprintf('%s - %s', $pkg_item->product_title, $pkg_item->variant_title), '- ');
													$pk_sku = trim(sprintf('%s-%s', $pkg_item->product_sku, $pkg_item->variant_sku), '- ')
													?>
													<li><a class="dark-link-off" href="<?php echo $url ?>"><?php echo $pk_title ?>
														(<?php echo $pk_sku ?>)</a></li><?php
												endforeach;
												?>
											</ol>
										<?php endif; ?>
										<span class="item-seller"><?php echo JText::sprintf('COM_SELLACIOUS_ORDER_PREFIX_ITEM_SELLER', $oi->seller_company) ?></span>
									</div>
								</div>
							</div>
						</div>
					<?php endforeach; ?>
				</div>
			</div>

			<?php if (!empty($shipping_params) || !empty($checkout_forms)): ?>
				<div class="order-additional-info">
					<?php echo $this->loadTemplate('shippingdata'); ?>
					<?php if (!empty($checkout_forms)): ?>
						<div class="order-additional-questions">
							<h5 class="additional-info-title"><?php echo JText::_('COM_SELLACIOUS_ORDER_ADDITIONAL_INFO_CHECKOUT_TITLE'); ?></h5>
							<?php foreach ($checkout_forms as $param): ?>
								<div class="additional-param">
									<span class="additional-param-label"><?php echo $param->label; ?></span>
									<span class="additional-param-value"><?php echo $param->value; ?></span>
								</div>
							<?php endforeach; ?>
						</div>
					<?php endif; ?>
				</div>
			<?php endif; ?>

			<?php if (!$addressPending): ?>
			<div id="payment-forms">
				<?php
				$options       = array('debug' => 0);
				$args          = new stdClass;
				$args->methods = $this->helper->paymentMethod->getMethods('cart', true, $order->get('customer_uid') ?: false, $order->get('id'));
				$html          = JLayoutHelper::render('com_sellacious.payment.forms', $args, '', $options);

				echo $html;
				?>
			</div>
			<?php else: ?>
				<p class="text-center red"><?php echo JText::_('COM_SELLACIOUS_ORDER_ADDRESSES_PENDING_MESSAGE'); ?></p>
			<?php endif; ?>
		</div>
	</div>
	<input type="hidden" id="order_id" name="order_id" value="<?php echo $order->get('id') ?>"/>
	<?php echo JHtml::_('form.token'); ?>
</div>
