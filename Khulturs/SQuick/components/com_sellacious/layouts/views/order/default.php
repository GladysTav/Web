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
use Joomla\Utilities\ArrayHelper;

/** @var  SellaciousViewOrder $this */
JHtml::_('jquery.framework');
JHtml::_('behavior.formvalidator');
JHtml::_('ctech.bootstrap');

JHtml::_('script', 'com_sellacious/fe.view.order.js', true, true);

JHtml::_('stylesheet', 'com_sellacious/font-awesome.min.css', null, true);
JHtml::_('stylesheet', 'com_sellacious/fe.component.css', null, true);
JHtml::_('stylesheet', 'com_sellacious/fe.view.order.css', null, true);
JHtml::_('stylesheet', 'com_sellacious/fe.modal.css', null, true);

$order = new Registry($this->item);
$items = $order->get('items');

$electronic = false;

foreach ($items as $item)
{
	if ($item->product_type !== 'physical')
	{
		$electronic = true;
		break;
	}
}

$c_currency         = $this->helper->currency->current('code_3');
$o_currency         = $order->get('currency');
$hasShippingAddress = $this->helper->order->hasShippingAddress($order->get('id'));

$show_dlink    = (int) $this->helper->config->get('show_order_download_link', 1);
$deliveryModes = ArrayHelper::getColumn($order->get('eproduct_delivery'), 'mode');

$dispatcher = $this->helper->core->loadPlugins();
$invoice_order_status = $this->helper->config->get('invoice_order_status', '');

$itemisedShipping = $order->get('shipping_rule_id') == 0;
$shipping_params  = (array) $order->get('shipping_params');
$checkout_forms   = $order->get('checkout_forms');
$orderParams      = new Registry($order->get('params'));

$sellerShippingRules         = $order->get('seller_shipping_rules');
$sellerwiseShippingInProduct = $orderParams->get('product_select_shipping', 0) == 1;
?>

<div class="ctech-wrapper">
	<script>
		Joomla.submitbutton = function (task, form) {
			form = form || document.getElementById('adminForm');

			if (document.formvalidator.isValid(form)) {
				Joomla.submitform(task, form);
			} else {
				form && Joomla.removeMessages();
				alert('<?php echo JText::_('COM_SELLACIOUS_ORDER_FORM_VALIDATION') ?>');
			}
		};
	</script>
	<div id="order_requests"><?php echo $this->loadTemplate('modals', $items); ?></div>

	<div class="order-details-wrapper">
		<form action="<?php echo JUri::getInstance()->toString() ?>" method="post" id="orderForm" name="orderForm">
			<div class="order-heading">
				<h4 class="ctech-float-left"><?php echo JText::_('COM_SELLACIOUS_ORDER_HEADING_DETAILS') ?></h4>
				<ul class="order-action-buttons ctech-float-right">
					<li>
						<?php $url = JRoute::_('index.php?option=com_sellacious&view=order&layout=print&tmpl=component&id=' . $order->get('id')); ?>
						<a target="_blank" href="<?php echo $url ?>">
							<div class="btn-action btn-print"><span
									class="ctech-text-primary"><?php echo strtoupper(JText::_('COM_SELLACIOUS_ORDER_PRINT_ORDER')); ?></span>
							</div>
						</a>
					</li>
					<?php if (!$invoice_order_status || count($order->get('invoice_ids', array())) > 0 || $order->get('status.status') == $invoice_order_status): ?>
					<li>
						<?php $url = JRoute::_('index.php?option=com_sellacious&view=order&layout=invoice&tmpl=component&id=' . $order->get('id')); ?>
						<a target="_blank" href="<?php echo $url ?>">
							<div class="btn-action btn-invoice"><span
									class="ctech-text-primary"><?php echo strtoupper(JText::_('COM_SELLACIOUS_ORDER_VIEW_INVOICE')); ?></span>
							</div>
						</a>
					</li>
					<?php endif; ?>
					<li>
						<?php $url = JRoute::_('index.php?option=com_sellacious&view=order&layout=receipt&id=' . $order->get('id')); ?>
						<a target="_blank" href="<?php echo $url ?>">
							<div class="btn-action btn-invoice"><span
									class="ctech-text-primary"><?php echo strtoupper(JText::_('COM_SELLACIOUS_ORDER_VIEW_RECEIPT')); ?></span>
							</div>
						</a>
					</li>
					<?php if ($this->helper->config->get('show_order_download_link') && $electronic): ?>
					<li>
						<?php $url = JRoute::_('index.php?option=com_sellacious&view=downloads'); ?>
						<a href="<?php echo $url ?>" target="_blank">
							<div class="btn-action btn-invoice">
								<span class="ctech-text-primary"><?php echo strtoupper(JText::_('COM_SELLACIOUS_ORDER_VIEW_DOWNLOADS')); ?></span>
							</div>
						</a>
					</li>
					<?php endif; ?>
				</ul>
				<div class="ctech-clearfix"></div>
			</div>

			<div class="order-basic-details ctech-float-right">
				<span><?php echo JText::_('COM_SELLACIOUS_ORDER_HEADING_DATE_LABEL'); ?> <?php echo JHtml::_('date', $order->get('created'), 'D, F d, Y h:i A'); ?>,</span>
				<span><?php echo JText::_('COM_SELLACIOUS_ORDER_HEADING_ID'); ?>:&nbsp;<?php echo $order->get('order_number') ?> <small>(<?php echo JText::plural('COM_SELLACIOUS_ORDER_PREFIX_TOTAL_ITEMS_N', count($items)); ?>)</small></span>
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
										<span class="address_mobile"><i class="fa fa-mobile-phone fa-lg"></i>
										<?php echo $order->get('st_mobile') ?></span> <br />
									<?php endif; ?>
									<?php if ($order->get('st_address')): ?>
										<span class="address_address"><?php echo $order->get('st_address') ?></span> <br />
									<?php endif; ?>
									<?php if ($order->get('st_landmark')): ?>
										<span class="address_landmark"><?php echo $order->get('st_landmark') ?>,</span>
									<?php endif; ?>
									<?php if ($order->get('st_district')): ?>
										<span class="address_district"><?php echo $order->get('st_district') ?>,</span> <br />
									<?php endif; ?>
									<?php if ($order->get('st_state')): ?>
										<span class="address_state_loc"><?php echo $order->get('st_state') ?></span>
									<?php endif; ?>
									<?php if ($order->get('st_zip')): ?>
										<span class="address_zip"> - <?php echo $order->get('st_zip') ?></span> <br />
									<?php endif; ?>
									<?php if ($order->get('st_country')): ?>
										<span class="address_country"><?php echo $order->get('st_country') ?></span>
									<?php endif; ?>
								</div>
							<?php endif; ?>
							<div id="address-billing-text">
								<div class="address_label"><?php echo JText::_('COM_SELLACIOUS_ORDER_BILLING_ADDRESS_LABEL'); ?></div>
								<span class="address_name ctech-text-primary"><?php echo $order->get('bt_name') ?></span>
								<?php if ($order->get('bt_mobile')): ?>
									<span class="address_mobile"><i class="fa fa-mobile-phone fa-lg"></i>
									<?php echo $order->get('bt_mobile') ?></span> <br />
								<?php endif; ?>
								<?php if ($order->get('bt_address')): ?>
									<span class="address_address"><?php echo $order->get('bt_address') ?></span> <br />
								<?php endif; ?>
								<?php if ($order->get('bt_landmark')): ?>
									<span class="address_landmark"><?php echo $order->get('bt_landmark') ?>,</span>
								<?php endif; ?>
								<?php if ($order->get('bt_district')): ?>
									<span class="address_district"><?php echo $order->get('bt_district') ?>,</span> <br />
								<?php endif; ?>
								<?php if ($order->get('bt_state')): ?>
									<span class="address_state_loc"><?php echo $order->get('bt_state') ?></span>
								<?php endif; ?>
								<?php if ($order->get('bt_zip')): ?>
									<span class="address_zip"> - <?php echo $order->get('bt_zip') ?></span> <br />
								<?php endif; ?>
								<?php if ($order->get('bt_country')): ?>
									<span class="address_country"><?php echo $order->get('bt_country') ?></span>
								<?php endif; ?>
							</div>
						</div>
					</div>
					<div class="ctech-col-sm-5">
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

							<?php if (!empty($sellerShippingRules)): ?>
								<table class="table table-noborder"><?php
								if ($sellerwiseShippingInProduct):
									foreach ($sellerShippingRules as $sellerUid => $shippingItems):
										foreach ($shippingItems as $itemUid => $sellerShippingRule):
											$productName = '';

											foreach ($items as $oi)
											{
												if ($oi->item_uid === $itemUid)
												{
													$productName = $oi->product_title;
													break;
												}
											}
											?>
											<tr>
											<td colspan="2" class="text-right"><?php echo $productName . '<br>(' . $sellerShippingRule->sellerName . ')'; ?></td>
											<td class="text-right"><?php echo $sellerShippingRule->ruleTitle; ?></td>
											<td class="text-right"><?php echo $this->helper->currency->display($sellerShippingRule->total, $o_currency, $c_currency, true); ?></td>
											</tr><?php endforeach;
									endforeach;
								else:
									foreach ($sellerShippingRules as $sellerShippingRule): ?>
										<tr>
											<td colspan="2" class="text-right"><?php echo $sellerShippingRule->sellerName; ?></td>
											<td class="text-right"><?php echo $sellerShippingRule->ruleTitle; ?></td>
											<td class="text-right"><?php echo $this->helper->currency->display($sellerShippingRule->total, $o_currency, $c_currency, true); ?></td>
										</tr>
									<?php endforeach;
								endif; ?>
								</table><?php
							endif; ?>

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
										<span class="coupon-label"><?php echo $this->escape($coupon->code) . ' : <em class="ctech-text-info">' . $this->escape($coupon->coupon_title) . '</em>' ?></span>
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
					</div>
				</div>
			</div>

			<div class="order-items">
				<div class="order-items-wrapper">
					<?php foreach ($items as $oi):
						$code = $this->helper->product->getCode($oi->product_id, $oi->variant_id, $oi->seller_uid);
						$p_url = JRoute::_('index.php?option=com_sellacious&view=product&p=' . $code);
						$title = trim(sprintf('%s - %s', $oi->product_title, $oi->variant_title), '- ');
						$images = $this->helper->product->getImages($oi->product_id, $oi->variant_id);
						$statuses = $this->helper->order->getStatusLog($oi->order_id, $oi->item_uid);

						if (empty($statuses))
						{
							$statuses = $this->helper->order->getStatusLog($oi->order_id, null, $oi->seller_uid);
						}
						?>
						<div class="order-item">
							<div class="order-item-basic-info">
								<?php foreach ($statuses as $si => $status): ?>
									<span class="<?php echo $si > 0 ? 'hidden toggle-element' : ''; ?> order-status-date">
									<?php echo JHtml::_('date', $status->created, 'M d, Y h:i A'); ?>
								</span>
									<span class="<?php echo $si > 0 ? 'hidden toggle-element' : ''; ?> hasTooltip order-status ctech-float-right"
										  data-placement="top" title="<?php echo $status->customer_notes ?>">
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

										<span class="order-quantity-label"><?php echo JText::_('COM_SELLACIOUS_ORDER_PREFIX_ITEM_QUANTITY_N'); ?></span><?php echo $oi->quantity; ?>
										<br />
										<?php if (abs($oi->shipping_amount) >= 0.01): ?>
											<span><?php echo JText::_('COM_SELLACIOUS_ORDER_ITEM_SHIPPING_AMOUNT_LABEL') ?>
												<?php echo $this->helper->currency->display($oi->shipping_amount, $order->get('currency'), $c_currency, true); ?></span>
											<br/>
										<?php endif; ?>

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
										<span class="item-seller"><?php echo JText::_('COM_SELLACIOUS_ORDER_PREFIX_ITEM_SELLER')?></span>
										<span><?php echo $oi->seller_company ?: $oi->seller_name; ?></span>

									</div>

									<div class="col-sm-4 buttons-order-multiple ctech-text-right">
										<button id="view-status-btn"
												class="ctech-btn ctech-btn-outline-info ctech-btn-sm ctech-rounded-0 nowrap btn-status-item"><?php
											echo JText::_('COM_SELLACIOUS_ORDER_REVIEW__STATUS_ITEM_BUTTON'); ?></button>
										<div class="text-left w100p v-top toggle-box mb-2 hidden" id="status-table">
											<table class="oi-status w100p">
												<?php foreach ($statuses as $si => $status): ?>
													<tr class="<?php echo $si > 2 ? 'hidden toggle-element' : ''; ?>">
														<td class="nowrap" style="width:90px;"><?php
															echo JHtml::_('date', $status->created, 'M d, Y h:i A'); ?></td>
														<td class="text-right">
															<abbr class="hasTooltip" data-placement="top" title="<?php
															echo $status->customer_notes ?>"><?php echo $status->s_title ?></abbr>
														</td>
													</tr>
												<?php endforeach; ?>
											</table>

											<?php if (count($statuses) > 3): ?>
												<div class="w100p text-center bg-color-dark thin-line btn-toggle">
													<a class="dark-link btn-micro toggle-element"><i class="fa fa-caret-down fa-lg"></i></a>
													<a class="dark-link btn-micro toggle-element hidden"><i class="fa fa-caret-up fa-lg"></i></a>
												</div>
											<?php endif; ?>
										</div>

										<td class="text-right nowrap v-top item-total">

											<?php
											$form = $this->helper->rating->getForm($oi->product_id, $oi->variant_id, $oi->seller_uid);
											if (($form instanceof JForm) && count($form->getFieldset()) > 0):?>
												<a
												class="ctech-btn ctech-btn-sm ctech-btn-outline-info ctech-btn-sm ctech-rounded-0 nowrap btn-review-item"
												href="<?php
												echo JRoute::_('index.php?option=com_sellacious&view=product&p=' . $oi->item_uid . '#reviewBox'); ?>"><?php
												echo JText::_('COM_SELLACIOUS_ORDER_REVIEW_ITEM_BUTTON'); ?></a><?php
											endif;

											$shippedStatus = reset($statuses);

											if (isset($shippedStatus) && $shippedStatus->shipment)
											{
												$shipmentStatus = $shippedStatus->shipment;

												$shipmentInfo = '';
												$shipmentInfo .= (isset($shipmentStatus->shipper) && trim($shipmentStatus->shipper) != '') ? '<span>' . JText::_('COM_SELLACIOUS_ORDERSTATUS_FIELD_SHIPPER_HINT') . ':' . $shipmentStatus->shipper . '</span>' : '';
												$shipmentInfo .= (isset($shipmentStatus->tracking_number) && trim($shipmentStatus->tracking_number) != '') ? '<span>' . JText::_('COM_SELLACIOUS_ORDERSTATUS_FIELD_TRACKING_NUMBER_HINT') . ':' . $shipmentStatus->tracking_number . '</span>' : '';
												$shipmentInfo .= (isset($shipmentStatus->tracking_url) && trim($shipmentStatus->tracking_url) != '') ? '<span>' . JText::_('COM_SELLACIOUS_ORDERSTATUS_FIELD_TRACKING_URL_HINT') . ':' . $shipmentStatus->tracking_url . '</span>' : '';
												$shipmentInfo .= (isset($shipmentStatus->source_district) && trim($shipmentStatus->source_district) != '') ? '<span>' . JText::_('COM_SELLACIOUS_ORDERSTATUS_FIELD_SOURCE_DISTRICT_HINT') . ':' . $shipmentStatus->source_district . '</span>' : '';
												$shipmentInfo .= (isset($shipmentStatus->source_zip) && trim($shipmentStatus->source_zip) != '') ? '<span>' . JText::_('COM_SELLACIOUS_ORDERSTATUS_FIELD_SOURCE_ZIP_HINT') . ':' . $shipmentStatus->source_zip . '</span>' : '';
												$shipmentInfo .= (isset($shipmentStatus->item_serial) && trim($shipmentStatus->item_serial) != '') ? '<span>' . JText::_('COM_SELLACIOUS_ORDERSTATUS_FIELD_ITEM_SERIAL_HINT') . ':' . $shipmentStatus->item_serial . '</span>' : '';
												if ($shipmentInfo !== ''):
													echo '<a class="ctech-btn ctech-btn-outline-primary ctech-btn-sm  btn-shipment-info" data-toggle="ctech-collapse" data-target="#shipment-info-box-' . $oi->id . '"><span> SHIPMENT INFO </span></a>'; ?>

													<div id="shipment-info-box-<?php echo $oi->id; ?>" class="ctech-collapse shipment-info-box">
														<?php echo $shipmentInfo; ?>
													</div>
												<?php endif;

												if (isset($shipmentStatus->tracking_url) && trim($shipmentStatus->tracking_url) != ''):
													$parsed = parse_url($shipmentStatus->tracking_url);
													if (empty($parsed['scheme'])):
														$shipmentStatus->tracking_url = 'http://' . ltrim($shipmentStatus->tracking_url, '/');
													endif;
												?>
													<a href="<?php echo $shipmentStatus->tracking_url; ?>" class="ctech-btn ctech-btn-outline-primary ctech-btn-sm  btn-track-shipment" target="_blank">
														<span><?php echo JText::_('COM_SELLACIOUS_ORDER_TRACK_SHIPMENT'); ?></span>
													</a>
												<?php endif;

											}

											if ($oi->return_available)
											{
												?><a href="#return-form-<?php echo $oi->id ?>" role="button" data-toggle="ctech-modal"
													 class="ctech-btn ctech-btn-outline-primary ctech-btn-sm btn-return-order">
												<span><?php echo JText::_('COM_SELLACIOUS_ORDER_PLACE_RETURN'); ?></span>
												</a><?php
											}

											if ($oi->exchange_available)
											{
												?><a href="#exchange-form-<?php echo $oi->id ?>" role="button" data-toggle="ctech-modal"
													 class="ctech-btn ctech-btn-outline-primary ctech-btn-sm btn-exchange-order">
												<span><?php echo JText::_('COM_SELLACIOUS_ORDER_PLACE_EXCHANGE'); ?></a><?php
											}
											?>
										</td>
									</div>

									<div class="col-sm-12">
										<?php
										if ($oi->shoprules && count($oi->shoprules))
										{
											?>
											<div>
												<div style="padding: 0">
													<table class="w100p shoprule-info">
														<?php
														foreach ($oi->shoprules as $ri => $rule)
														{
															settype($rule, 'object');

															if ($rule->change != 0)
															{
																?>
																<tr>
																	<td>
																		<?php echo str_repeat('|&mdash;', $rule->level - 1) ?>
																		<?php echo $this->escape($rule->title); ?>
																	</td>
																	<td class="text-right nowrap" style="width:90px;">
																		<?php
																		$value = $this->helper->currency->display(abs($rule->change), $o_currency, $c_currency, true);
																		echo ($rule->change >= 0.01) ? '(+) ' . $value : '(-) ' . $value;
																		?>
																	</td>
																</tr>
																<?php
															}
														}
														?>
													</table>
												</div>
											</div>
											<?php
										} ?>
									</div>
								</div>
							</div>
						</div>
					<?php endforeach; ?>
				</div>
			</div>

			<?php if (!empty($shipping_params) || !empty($checkout_forms)):?>
				<div class="order-additional-info">
					<?php echo $this->loadTemplate('shippingdata'); ?>
					<?php if (!empty($checkout_forms)): ?>
						<div class="order-additional-questions">
							<h5 class="additional-info-title"><?php echo JText::_('COM_SELLACIOUS_ORDER_ADDITIONAL_INFO_CHECKOUT_TITLE'); ?></h5>
							<?php foreach ($checkout_forms as $param): ?>
								<?php if ($param->value): ?>
									<div class="additional-param">
										<span class="additional-param-label"><?php echo $param->label; ?></span>
										<span class="additional-param-value"><?php echo $param->html ?: $param->value; ?></span>
									</div>
								<?php endif; ?>
							<?php endforeach; ?>
						</div>
					<?php endif; ?>
				</div>
			<?php endif; ?>
		</form>
	</div>
	<div class="ctech-clearfix"></div>
</div>
<?php
$info = array();
$dispatcher->trigger('onAfterRenderOrder', array('com_sellacious.order', $order, &$info));

foreach ($info as $item)
{
	echo $item;
}
?>
