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

use Joomla\Registry\Registry;

/** @var  SellaciousViewOrders  $this */
JHtml::_('behavior.framework');
JHtml::_('jquery.framework');

JHtml::_('script', 'media/com_sellacious/js/plugin/serialize-object/jquery.serialize-object.min.js', array('version' => S_VERSION_CORE));
JHtml::_('script', 'com_sellacious/util.modal.js', array('version' => S_VERSION_CORE, 'relative' => true));
JHtml::_('script', 'com_sellacious/view.orders.js', array('version' => S_VERSION_CORE, 'relative' => true));

JHtml::_('stylesheet', 'com_sellacious/component.css', array('version' => S_VERSION_CORE, 'relative' => true));
JHtml::_('stylesheet', 'com_sellacious/view.orders.css', array('version' => S_VERSION_CORE, 'relative' => true));
JHtml::_('stylesheet', 'com_sellacious/font-awesome.min.css', array('version' => S_VERSION_CORE, 'relative' => true));

$listOrder = $this->escape($this->state->get('list.ordering'));
$listDirn  = $this->escape($this->state->get('list.direction'));
$ordering  = ($listOrder == 'a.ordering');
$saveOrder = ($listOrder == 'a.ordering' && strtolower($listDirn) == 'asc');
$me        = JFactory::getUser();

$c_state    = $this->state->get('list.currency', 'current');
$c_currency = $c_state == 'current' ? '' : ($c_state == 'original' ? null : $this->helper->currency->getGlobal('code_3'));

$invoice_order_status = $this->helper->config->get('invoice_order_status', '');
$shipHandlers         = $this->helper->shipping->getHandlers();
$listSelect           = $this->state->get('list.columns');

foreach ($this->items as $i => $order)
{
	$canDelete  = $this->helper->access->check('order.delete', $order->id);

	$invoice_link = JRoute::_('index.php?option=com_sellacious&view=order&layout=invoice&id=' . (int) $order->id);
	$receipt_link = JRoute::_('index.php?option=com_sellacious&view=order&layout=receipt&id=' . (int) $order->id);
	$txn_link     = JRoute::_('index.php?option=com_sellacious&view=transactions&layout=order&tmpl=component&filter[order_id]=' . (int) $order->id);

	$osh            = $order->shipping_handler;
	$labelSupported = $osh && isset($shipHandlers[$osh]) && $shipHandlers[$osh]->printLabelSupported;

	if (!$labelSupported)
	{
		foreach ($order->items as $oi)
		{
			$ish            = $oi->shipping_handler;
			$labelSupported = $ish && isset($shipHandlers[$ish]) && $shipHandlers[$ish]->printLabelSupported;

			if ($labelSupported)
			{
				break;
			}
		}
	}

	$selectShip = $order->product_select_shipping;
	?>
	<tr id="order-<?php echo $i ?>" class="order-row">
		<td class="nowrap center hidden-phone">
			<label>
				<input type="checkbox" name="cid[]" id="cb<?php echo $i ?>" class="checkbox style-0"
					   value="<?php echo $order->id ?>" onclick="Joomla.isChecked(this.checked);"
					<?php echo ($canDelete) ? '' : ' disabled="disabled"' ?> />
				<span></span>
			</label>
		</td>
		<?php if (!$listSelect || in_array('a.id', $listSelect)): ?>
			<td class="center hidden-phone" style="width:40px">
				<span><?php echo (int) $order->id; ?></span>
			</td>
		<?php endif; ?>

		<?php if (!$listSelect || in_array('a.created', $listSelect)): ?>
			<td style="width:100px" class="nowrap">
				<?php $time = JHtml::_('date', $order->created, 'h:i A'); ?>
				<span class="hasTooltip" title="<?php echo $time ?>"><?php
					echo JHtml::_('date', $order->created, 'M d, Y'); ?></span>
			</td>
		<?php endif; ?>

		<?php if (!$listSelect || in_array('a.customer_name', $listSelect)): ?>
			<td style="max-width: 120px; text-overflow: ellipsis; overflow: hidden; padding-right: 5px;"><?php
				echo $this->escape($order->customer_name) ?></td>
		<?php endif; ?>

		<?php if (!$listSelect || in_array('a.order_number', $listSelect)): ?>
			<td class="text-left" data-row="#order-items-<?php echo $i ?>">
				<span class="monospace"><?php echo $order->order_number; ?></span><?php

				if (!empty($order->items))
				{
					?>
				<button type="button" class="btn-toggle button-mini pull-right"><i class="fa fa-plus"></i></button>
				<button type="button" class="btn-toggle button-mini pull-right hidden"><i class="fa fa-minus"></i></button>
					<?php
				}
				?>
			</td>
		<?php else: ?>
			<td class="text-center" data-row="#order-items-<?php echo $i ?>">
				<?php
				if (!empty($order->items))
				{
					?>
					<button type="button" class="btn-toggle button-mini"><?php echo JText::_('COM_SELLACIOUS_ORDER_EXPAND'); ?></button>
					<button type="button" class="btn-toggle button-mini hidden"><?php echo JText::_('COM_SELLACIOUS_ORDER_COLLAPSE'); ?></button>
					<?php
				}
				else
				{
					echo '&mdash;';
				}
				?>
			</td>
		<?php endif; ?>

		<?php if(!$listSelect || in_array('ss.title', $listSelect)): ?>
            <td class="nowrap text-center order-status-col">
                <span class="order-status"><?php echo $order->order_status; ?></span>

				<?php if ($this->helper->access->checkAny(array('status', 'status.own'), 'order.item.edit.') && $order->status_edit): ?>
                    <a href="#" title="<?php echo JText::_('COM_SELLACIOUS_ORDERS_CHANGE_STATUS'); ?>" class=" hasTooltip txt-color-red btn-order-status-edit hidden" data-id="<?php $order->id ?>"><i class="fa fa-edit"></i></a>
				<?php endif; ?>
			</td>
		<?php endif; ?>

		<?php if(!$listSelect || in_array('payment_method', $listSelect)): ?>
			<td class="">
				<?php echo $order->payment_method; ?>
			</td>
		<?php endif; ?>

		<?php if(!$listSelect || in_array('a.shipping_rule', $listSelect)): ?>
			<td class="">
				<?php if ($order->seller_shipping_rule_ids || !empty($order->seller_shipping_rules)): ?>
					<?php if ($selectShip && !empty($order->seller_shipping_rules)):
						$sellerShippingRules = new Registry($order->seller_shipping_rules);

						if ($seller_uid = $this->state->get('filter.seller_uid'))
						{
							$sellerShippingRule = $sellerShippingRules->get($seller_uid);

							if ($sellerShippingRule)
							{
								?>
								<table class="table table-bordered">
									<?php foreach ($sellerShippingRule as $itemUid => $rule): ?>
										<tr role="row">
											<tbody>
											<tr>
												<td><?php echo $itemUid; ?></td>
												<td><?php echo $rule->ruleTitle; ?></td>
											</tr>
											</tbody>
										</tr>
									<?php endforeach; ?>
								</table>
								<?php
							}
						}
						elseif (!$this->helper->access->check('order.list') && $this->helper->access->check('order.list.own'))
						{
							$sellerShippingRule = $sellerShippingRules->get($me->id);

							if ($sellerShippingRule)
							{
								?>
								<table class="table table-bordered">
									<?php foreach ($sellerShippingRule as $itemUid => $rule): ?>
										<tr role="row">
											<tbody>
											<tr>
												<td><?php echo $itemUid; ?></td>
												<td><?php echo $rule->ruleTitle; ?></td>
											</tr>
											</tbody>
										</tr>
									<?php endforeach; ?>
								</table>
								<?php
							}
						}
						else
						{
							foreach ($sellerShippingRules as $sellerUid => $items)
							{
								$seller = $this->helper->seller->getItem(array('user_id' => $sellerUid));

								echo $seller->title;
								?>
								<table class="table table-bordered">
									<?php foreach ($items as $itemUid => $rule): ?>
										<tr role="row">
											<tbody>
											<tr>
												<td><?php echo $itemUid; ?></td>
												<td><?php echo $rule->ruleTitle; ?></td>
											</tr>
											</tbody>
										</tr>
									<?php endforeach; ?>
								</table>
								<?php
							}
							?>
							<?php
						}
					elseif (!empty($order->seller_shipping_rule_ids) || !empty($order->seller_shipping_rules)):
						if ($seller_uid = $this->state->get('filter.seller_uid'))
						{
							echo isset($order->seller_shipping_rule_ids[$seller_uid]) ? $order->seller_shipping_rule_ids[$seller_uid]->shipping_title : '';
						}
						elseif (!$this->helper->access->check('order.list') && $this->helper->access->check('order.list.own'))
						{
							if (isset($order->seller_shipping_rule_ids[$me->id]))
							{
								echo $order->seller_shipping_rule_ids[$me->id]->shipping_title;
							}
							elseif (isset($order->seller_shipping_rules[$me->id]))
							{
								echo $order->seller_shipping_rules[$me->id]->ruleTitle;
							}
						}
						else
						{
							?>
							<table class="table table-bordered">
								<?php foreach ($order->seller_shipping_rules as $sellerShippingRule): ?>
									<tr role="row">
										<tbody>
										<tr>
											<td><?php echo $sellerShippingRule->sellerName; ?></td>
											<td><?php echo $sellerShippingRule->ruleTitle; ?></td>
										</tr>
										</tbody>
									</tr>
								<?php endforeach; ?>
							</table>
						<?php }
					endif; ?>
				<?php else: ?>
					<?php echo $order->shipping_rule; ?><br>
					<?php echo $order->shipping_service; ?>
				<?php endif; ?>
			</td>
		<?php endif; ?>

		<?php if(!$listSelect || in_array('a.product_shipping', $listSelect)): ?>
			<td class="nowrap amount-cell">
				<?php if ($order->seller_shipping_rule_ids):
					if ($selectShip):
						$sellerShippingRuleIds = new Registry($order->seller_shipping_rule_ids);

						if ($seller_uid = $this->state->get('filter.seller_uid'))
						{
							$sellerShippingRuleTotal = $sellerShippingRuleIds->get($seller_uid . '.total', 0);
							echo $this->helper->currency->display($sellerShippingRuleTotal, $order->currency, $c_currency);
						}
						elseif (!$this->helper->access->check('order.list') && $this->helper->access->check('order.list.own'))
						{
							$sellerShippingRuleTotal = $sellerShippingRuleIds->get($me->id . '.total', 0);
							echo $this->helper->currency->display($sellerShippingRuleTotal, $order->currency, $c_currency);
						}
						else
						{
							?>
							<table class="table table-bordered">
								<?php
								if ($order->seller_shipping_rule_ids):
									foreach ($order->seller_shipping_rule_ids as $sellerUid => $rule)
									{
										$seller = $this->helper->seller->getItem(array('user_id' => $sellerUid));
										?>
										<tr role="row">
											<tbody>
											<tr>
												<td><?php echo $seller->title;?></td>
												<td><?php echo $this->helper->currency->display($rule->total, $order->currency, $c_currency)?></td>
											</tr>
											</tbody>
										</tr>
										<?php
									}
								endif; ?>
							</table>
							<?php
						}
					else:
						if (!empty($order->seller_shipping_rules)):
							if ($seller_uid = $this->state->get('filter.seller_uid'))
							{
								echo isset($order->seller_shipping_rules[$seller_uid]) ? $this->helper->currency->display($order->seller_shipping_rules[$seller_uid]->total, $order->currency, $c_currency) : '';
							}
							elseif (!$this->helper->access->check('order.list') && $this->helper->access->check('order.list.own') && isset($order->seller_shipping_rules[$me->id]))
							{
								echo $this->helper->currency->display($order->seller_shipping_rules[$me->id]->total, $order->currency, $c_currency);
							}
							else
							{
								?>
								<table class="table table-bordered">
									<?php foreach ($order->seller_shipping_rules as $sellerShippingRule): ?>
										<tr role="row">
											<tbody>
											<tr>
												<td><?php echo $sellerShippingRule->sellerName;?></td>
												<td><?php echo $this->helper->currency->display($sellerShippingRule->total, $order->currency, $c_currency)?></td>
											</tr>
											</tbody>
										</tr>
									<?php endforeach; ?>
								</table>
								<strong><?php echo $this->helper->currency->display($order->product_shipping, $order->currency, $c_currency); ?></strong>
							<?php }
						endif;
					endif;
				else:
					echo $this->helper->currency->display($order->product_shipping, $order->currency, $c_currency);
				endif; ?>
			</td>
		<?php endif; ?>

		<?php if(!$listSelect || in_array('cu.amount', $listSelect)): ?>
			<td class="amount-cell">
				<?php
				if ($order->coupon_code)
				{
					echo $this->helper->currency->display($order->coupon_amount, $order->currency, $c_currency);

					echo '<br/><small class="red">(' . $this->escape($order->coupon_code) . ')</small>';
				}
				else
				{
					echo 'NA';
				}
				?>
			</td>
		<?php endif; ?>

		<?php if(!$listSelect || in_array('a.cart_taxes', $listSelect)): ?>
			<td class="amount-cell">
				<?php echo $this->helper->currency->display($order->product_taxes + $order->cart_taxes, $order->currency, $c_currency); ?>
			</td>
		<?php endif; ?>

		<?php if(!$listSelect || in_array('a.cart_discounts', $listSelect)): ?>
			<td class="amount-cell">
				<?php echo $this->helper->currency->display($order->product_discounts + $order->cart_discounts, $order->currency, $c_currency); ?>
			</td>
		<?php endif; ?>

		<?php if(!$listSelect || in_array('a.grand_total', $listSelect)): ?>
			<td class="amount-cell strong">
				<?php echo $this->helper->currency->display($order->grand_total, $order->currency, $c_currency); ?>
			</td>
		<?php endif; ?>

		<td class="nowrap center">
			<div class="btn-group" id="actionbtngroup">
				<a class="btn btn-primary dropdown-toggle" data-toggle="dropdown" href="#">
					<?php echo JText::_('COM_SELLACIOUS_SELECT_ACTIONS'); ?> <span class="caret"></span></a>
				<ul class="dropdown-menu">
					<li>
						<?php $link = JRoute::_('index.php?option=com_sellacious&view=order&layout=history&tmpl=component&id=' . $order->id); ?>
						<a class="btn-modal" href="<?php echo $link ?>"><span data-modal='{"h": "500", "w": "1100"}' >
						<i class="fa fa-clock-o"></i> <?php echo JText::_('COM_SELLACIOUS_ORDER_HISTORY'); ?></span></a>
					</li>
						<?php if (!$invoice_order_status || count($order->invoice_ids) > 0 || $order->order_status_id == $invoice_order_status): ?>
					<li><a target="_blank" href="<?php echo $invoice_link ?>"><i class="fa fa-file-text"></i> <?php echo JText::_('COM_SELLACIOUS_ORDERS_INVOICE'); ?></a></li>
						<?php endif; ?>
					<li><a target="_blank" href="<?php echo $txn_link ?>"><i class="fa fa-table"></i> <?php echo JText::_('COM_SELLACIOUS_ORDERS_TRANSACTIONS'); ?></a></li>
					<li><a target="_blank" href="<?php echo $receipt_link ?>"><i class="fa fa-file-o"></i> <?php echo JText::_('COM_SELLACIOUS_ORDERS_RECEIPT'); ?></a></li>
				</ul>
			</div>
		</td>
	</tr>

	<tr id="order-items-<?php echo $i ?>" class="hidden order-items-row">
		<td colspan="13">
			<?php if (!empty($order->items)): ?>
				<table class="w100p order-items table table-bordered">
					<thead>
					<tr>
						<th><?php echo JText::_('COM_SELLACIOUS_ORDER_ITEM_TITLE'); ?></th>
						<th class="nowrap text-center" style="width:155px"><?php echo JText::_('COM_SELLACIOUS_ORDER_ITEM_HEADING_SELLER'); ?></th>
						<th class="nowrap text-center" style="width: 55px"><?php echo JText::_('COM_SELLACIOUS_ORDER_ITEM_HEADING_QUANTITY'); ?></th>
						<th class="nowrap text-center" style="width:200px"><?php echo JText::_('COM_SELLACIOUS_ORDER_ITEM_HEADING_STATUS'); ?></th>
						<th class="nowrap text-center" style="width: 90px"><?php echo JText::_('COM_SELLACIOUS_ORDER_ITEM_HEADING_LIST_PRICE'); ?></th>
						<th class="nowrap text-center" style="width: 90px"><?php echo JText::_('COM_SELLACIOUS_ORDER_ITEM_HEADING_SALES_PRICE'); ?></th>
						<th class="nowrap text-center" style="width: 90px"><?php echo JText::_('COM_SELLACIOUS_ORDER_ITEM_HEADING_SHIPPING'); ?></th>
						<th class="nowrap text-center" style="width: 90px"><?php echo JText::_('COM_SELLACIOUS_ORDER_ITEM_HEADING_SHIPPING_COST'); ?></th>
						<th class="nowrap text-center" style="width: 90px"><?php echo JText::_('COM_SELLACIOUS_ORDER_ITEM_HEADING_TAX'); ?></th>
						<th class="nowrap text-center" style="width: 90px"><?php echo JText::_('COM_SELLACIOUS_ORDER_ITEM_HEADING_DISCOUNTS'); ?></th>
						<th class="nowrap text-center" style="width: 88px"><?php echo JText::_('COM_SELLACIOUS_ORDER_ITEM_HEADING_TOTAL'); ?></th>
					</tr>
					</thead>
					<tbody>
					<?php
					foreach ($order->items as $oii => $oi)
					{
						$p_code = $this->helper->product->getCode($oi->product_id, $oi->variant_id, $oi->seller_uid);
						$p_url  = JRoute::_('../index.php?option=com_sellacious&view=product&p=' . $p_code);

						$oi->title    = trim(sprintf('%s - %s', $oi->product_title, $oi->variant_title), ' -');
						$oi->statuses = $this->helper->order->getStatuses('order.' . $oi->product_type, $oi->status->s_id, false, true);
						?>
                        <tr id="oi-row-<?php echo $i ?>-<?php echo $oii ?>" data-uid="<?php echo $oi->item_uid; ?>">
							<td data-row="#oi-info-row-<?php echo $i ?>-<?php echo $oii ?>">
								<?php echo $oi->package_items ? JText::_('COM_SELLACIOUS_CART_PACKAGE_ITEM_LABEL') : ''; ?>
								<a target="_blank" href="<?php echo $p_url ?>">
									<?php echo $this->escape($oi->title) . ($oi->local_sku ? '(' . JText::_('COM_SELLACIOUS_ORDER_ITEM_SKU') . $oi->local_sku . ')' : ''); ?>
								</a>

								<?php if ($oi->package_items): ?>
									<hr class="thin-line">
									<ol class="package-items">
										<?php
										foreach ($oi->package_items as $pkg_item):
											$url = JRoute::_('../index.php?option=com_sellacious&view=product&p=' . $pkg_item->code);
											?><li><a class="dark-link-off" href="<?php echo $url ?>"><?php
												echo $pkg_item->product_title ?> <?php echo $pkg_item->variant_title ?>
												(<?php echo $pkg_item->product_sku ?>-<?php echo $pkg_item->variant_sku ?>)</a></li><?php
										endforeach;
										?>
									</ol>
								<?php endif; ?>
							</td>
							<td class="text-center">
								<?php echo $oi->seller_company ? $oi->seller_company : "[$oi->seller_name]" ?>
							</td>
							<td class="text-center">
								<?php echo $oi->quantity ?>
							</td>
							<td class="text-center" style="padding: 1px;">
								<span class="oi-status"><?php echo $oi->status->s_title ? $oi->status->s_title : 'NA'; ?> </span>
								<?php if (!$order->status_edit && $this->helper->access->checkAny(array('status', 'status.own'), 'order.item.edit.')): ?>
									<a href="#" class="txt-color-red btn-oi-status-edit" data-id="<?php $oi->id ?>"><i class="fa fa-edit"></i> </a>
								<?php endif; ?>
							</td>
							<td class="amount-cell text-italic">
								<?php echo $this->helper->currency->display($oi->list_price + $oi->variant_price, $order->currency, $c_currency); ?>
							</td>
							<td class="amount-cell text-italic">
								<?php echo $this->helper->currency->display($oi->basic_price, $order->currency, $c_currency); ?>
							</td>
							<td>
								<?php echo $oi->shipping_rule; ?><br>
								<?php echo $oi->shipping_service; ?>
							</td>
							<td class="amount-cell">
								<?php
								if ($oi->shipping_free):
									echo JText::_('COM_SELLACIOUS_ORDER_SHIPPING_COST_FREE');
								else:
									echo $this->helper->currency->display($oi->shipping_amount, $order->currency, $c_currency);
								endif;
								?>
							</td>
							<td class="amount-cell text-italic">
								<?php echo $this->helper->currency->display($oi->tax_amount, $order->currency, $c_currency); ?>
							</td>
							<td class="amount-cell text-italic">
								<?php echo $this->helper->currency->display($oi->discount_amount, $order->currency, $c_currency); ?>
							</td>
							<td class="amount-cell">
								<?php echo $this->helper->currency->display($oi->sub_total, $order->currency, $c_currency); ?>
							</td>
						</tr>
						<?php
						if ($this->helper->access->checkAny(array('status', 'status.own'), 'order.item.edit.') && !$order->status_edit)
						{
							?>
							<tr id="oi-info-row-<?php echo $i ?>-<?php echo $oii ?>" class="hidden">
								<td colspan="11">
									<table class="w100p">
										<tbody>
										<tr>
											<td class="status-form-container bg-color-white v-top hidden">
												<?php echo $this->loadTemplate('item_statusform', $oi); ?>
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
					?>
					</tbody>
					<tfoot>
					<tr>
						<td class="text-right" colspan="7"><?php echo JText::_('COM_SELLACIOUS_ORDER_CART_TAXES'); ?></td>
						<td class="text-center">-</td>
						<td class="amount-cell"><?php echo $this->helper->currency->display($order->cart_taxes, $order->currency, $c_currency); ?></td>
						<td class="text-center">-</td>
						<td class="text-center">-</td>
					</tr>
					<tr>
						<td class="text-right" colspan="7"><?php echo JText::_('COM_SELLACIOUS_ORDER_CART_DISCOUNTS'); ?></td>
						<td class="text-center">-</td>
						<td class="text-center">-</td>
						<td class="amount-cell"><?php echo $this->helper->currency->display($order->cart_discounts, $order->currency, $c_currency); ?></td>
						<td class="text-center">-</td>
					</tr>
					</tfoot>
				</table>
			<?php endif; ?>
		</td>
	</tr>
	<?php if ($this->helper->access->checkAny(array('status', 'status.own'), 'order.item.edit.') && $order->status_edit): ?>
    <tr id="order-info-row-<?php echo $i ?>" class="hidden order-info-row">
        <td colspan="11">
            <table class="w100p">
                <tbody>
                <tr>
                    <td class="status-form-container bg-color-white v-top hidden">
						<?php echo $this->loadTemplate('order_statusform', $order); ?>
                    </td>
                    <td class="status-log-container bg-color-white v-top"></td>
                </tr>
                </tbody>
            </table>
        </td>
    </tr>
<?php
endif;
}
?>
<div class="item-status-detail"><?php
	// Fixme: Add note here for what this line does!
	echo JLayoutHelper::render('com_sellacious.orders.status.form', isset($oi) ? $oi : null); ?></div>

<?php echo JHtml::_('form.token'); ?>
