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

/** @var  stdClass         $displayData */
/** @var  Sellacious\Cart  $cart */
$cart  = $displayData->cart;

$helper     = SellaciousHelper::getInstance();
$g_currency = $helper->currency->getGlobal('code_3');
$c_currency = $helper->currency->current('code_3');

JFactory::getDocument()->addScriptDeclaration("
	jQuery(document).ready(function ($) {
		$('.hasSelect2').select2();
	});
");

$items  = $cart->getItems();
$totals = $cart->getTotals();

// Call coupons only after getTotals()
$coupon_code = $cart->get('coupon.code');
$coupon_title = $cart->get('coupon.title');
$coupon_msg  = $cart->get('coupon.message');

$itemisedShip  = $helper->config->get('itemised_shipping', true);
$round_enabled = $helper->config->get('round_grand_total', 0);

$iErrors = $errors = array();
$cartValid = $cart->validate($errors, $iErrors, true);
?>
<div class="ctech-container-fluid">
	<div class="ctech-row">
		<div class="ctech-col-md-8 ctech-col-12 cart-items-list">
			<div class="cart-items-container">
				<?php foreach ($items as $i => $cartItem):
					$link      = $cartItem->getLinkUrl();
					$ship_tbd  = $cartItem->getShipping('tbd');
					$ship_free = $cartItem->getShipping('free');
					$ship_amt  = $cartItem->getShipping('amount');
					$package_items = null;
					$product_title = $cartItem->getProperty('title') .' '. $cartItem->getProperty('variant_title');
				?>
				<div class="cart-item">
					<div class="cart-item-thumb">
						<span style="background-image: url('<?php echo $helper->product->getImage($cartItem->getProperty('id'), $cartItem->getProperty('variant_id')); ?> ')"></span>
					</div>
					<div class="cart-item-details">
						<?php echo $package_items ? JText::_('COM_SELLACIOUS_CART_PACKAGE_ITEM_LABEL') : ''; ?>
						<a class="cart-item-title hasTooltip" data-placement="bottom" title="<?php echo $product_title; ?>" href="<?php echo $link ?>"><?php echo trim($product_title, '- '); ?></a>
						<?php if ($package_items): ?>
							<div><small><?php echo JText::_('COM_SELLACIOUS_CART_PACKAGE_ITEM_INCLUDES'); ?></small></div>
							<ol class="package-items">
								<?php
								foreach ($package_items as $pkg_item):
									$url = JRoute::_('index.php?option=com_sellacious&view=product&p=' . $pkg_item->code);
									?><li>
									<a class="normal" href="<?php echo $url ?>">
										<?php echo $pkg_item->product_title ?>
										<?php echo $pkg_item->variant_title ?>
										(<?php echo $pkg_item->product_sku ?>-<?php echo $pkg_item->variant_sku ?>)</a>
									</li><?php
								endforeach;
								?>
							</ol>
						<?php endif; ?>
						<div class="cart-item-prices">
							<div class="main-price"><?php echo $helper->currency->display($cartItem->getPrice('basic_price'), $g_currency, $c_currency, true); ?></div>

							<?php if ($cartItem->getPrice('tax_amount') >= 0.01): ?>
								<div><span class="tax-label"><?php echo JText::_('COM_SELLACIOUS_CART_ITEM_HEADING_TAX'); ?></span>: <strong><?php
										echo $helper->currency->display($cartItem->getPrice('tax_amount'), $g_currency, $c_currency, true); ?></strong></div>
							<?php endif; ?>

							<?php if ($cartItem->getPrice('discount_amount') >= 0.01): ?>
								<div><span class="discount-label"><?php echo JText::_('COM_SELLACIOUS_CART_ITEM_HEADING_DISCOUNT'); ?></span>: <strong><?php
										echo $helper->currency->display($cartItem->getPrice('discount_amount'), $g_currency, $c_currency, true); ?></strong></div>
							<?php endif; ?>

							<div><span class="sold-by-label"><?php echo JText::_('COM_SELLACIOUS_CART_ITEM_SOLD_BY'); ?></span> <span><?php echo $cartItem->getProperty('seller_store') ? $cartItem->getProperty('seller_store') : ($cartItem->getProperty('seller_name') ? $cartItem->getProperty('seller_name') : ($cartItem->getProperty('seller_company') ? $cartItem->getProperty('seller_company') : $cartItem->getProperty('seller_username'))); ?></span></div>
						</div>
						<?php if ($ship_free || $ship_tbd || $ship_amt >= 0.01): ?>
						<div class="cart-item-ship-info text-left nowrap">
							<label>
								<?php
								if ($ship_free)
								{
									echo '<span class="shipping-label">' . JText::_('COM_SELLACIOUS_CART_ITEM_SHIPRULE_SELECT_LABEL') . '</span>';
									echo JText::_('COM_SELLACIOUS_ORDER_SHIPMENT_FREE');
								}
								elseif ($ship_tbd)
								{
									echo '<span class="shipping-label">' . JText::_('COM_SELLACIOUS_CART_ITEM_SHIPRULE_SELECT_LABEL') . '</span>';
									echo '<span class="tbd">' . JText::_('COM_SELLACIOUS_TBD') . '</span>';
								}
								elseif ($ship_amt >= 0.01)
								{
									echo '<span class="shipping-label">' . JText::_('COM_SELLACIOUS_CART_ITEM_SHIPRULE_SELECT_LABEL') . '</span>';
									echo $helper->currency->display($ship_amt, $g_currency, $c_currency, true);
								}
								?>
							</label>
						</div>
						<?php endif ?>
						<?php
						if (!empty($iErrors) && isset($iErrors[$cartItem->getUid()]))
						{
							?>
							<ul class="item-errors">
								<?php
								foreach ($iErrors[$cartItem->getUid()] as $iError)
								{
									?>
									<li><div class="star-note star-1"><?php echo $iError ?></div></li>
									<?php
								}
								?>
							</ul>
							<?php
						}
						?>
						<div class="cart-item-actions">
							<div class="ctech-float-right cart-remove-item">
								<a href="#" class="btn-remove-item" data-uid="<?php echo $cartItem->getUid() ?>" title="Remove"><i class="fa fa-trash-alt"></i><span><?php echo JText::_('COM_SELLACIOUS_CART_BTN_REMOVE_CART_LABEL') ?></span></a>
							</div>
							<div class="cart-item-quantity ctech-float-left">
								<div class="item-quantity">
									<span><strong><?php echo JText::_('COM_SELLACIOUS_CART_QTY_LABEL') ?></strong>&nbsp;:&nbsp;</span><input type="number" class="input item-quantity" data-uid="<?php echo $cartItem->getUid() ?>" data-value="<?php echo $cartItem->getQuantity() ?>" min="1" value="<?php echo $cartItem->getQuantity() ?>" title=""/>
								</div>
							</div>
							<div class="ctech-clearfix"></div>
						</div>
					</div>
				</div>
				<?php endforeach; ?>
			</div>
		</div>
		<div id="cart-aio-summary" class="ctech-col-md-4 ctech-col-12 cart-items-summary">
			<div class="cart-head">
				<h4 class="title ctech-float-left"><?php echo JText::_('COM_SELLACIOUS_CART_ORDER_SUMMARY') ?></h4>
				<button type="button" class="ctech-btn ctech-btn-small ctech-bg-white btn-refresh hasTooltip ctech-float-right" data-placement="bottom"
						title="<?php echo JText::_('COM_SELLACIOUS_CART_BTN_REFRESH_CART_LABEL') ?>"><i class="fa fa-sync"></i></button>
				<button type="button" class="btn-clear-cart ctech-btn ctech-bg-white ctech-text-danger ctech-float-right hasTooltip" title="<?php
				echo JText::_('COM_SELLACIOUS_CART_BTN_CLEAR_CART_LABEL') ?>" data-placement="bottom"><i class="fa fa-trash-alt"></i></button>
				<div class="ctech-clearfix"></div>
			</div>
			<div class="cart-final-pricing">
				<?php if ($totals->get('items.basic') >= 0.01): ?>
					<div class="cart-final-price">
						<div class="lbl-txt">
							<?php echo JText::_('COM_SELLACIOUS_ORDERS_PRICE'); ?>
						</div>
						<div class="amt-lbl">
							<strong><?php echo $helper->currency->display($totals->get('items.basic'), $g_currency, $c_currency, true); ?></strong>
						</div>
					</div>
				<?php endif; ?>
				<?php if ($totals->get('shipping') >= 0.01): ?>
					<div class="cart-final-price">
						<div class="lbl-txt">
							<?php echo JText::_('COM_SELLACIOUS_CART_ITEM_SHIPRULE_SELECT_LABEL'); ?>
						</div>
						<div class="amt-lbl">
							<strong>+ <?php echo $helper->currency->display($totals->get('shipping'), $g_currency, $c_currency, true); ?></strong>
						</div>
					</div>
				<?php endif; ?>
				<?php if ($totals->get('tax_amount') >= 0.01): ?>
					<div class="cart-final-price">
						<div class="lbl-txt">
							<?php echo JText::_('COM_SELLACIOUS_ORDER_CART_TAXES'); ?>
						</div>
						<div class="amt-lbl">
							<strong>+ <?php echo $helper->currency->display($totals->get('tax_amount'), $g_currency, $c_currency, true); ?></strong>
						</div>
					</div>
				<?php endif; ?>
				<?php if ($totals->get('discount_amount') >= 0.01): ?>
					<div class="cart-final-price">
						<div class="lbl-txt">
							<?php echo JText::_('COM_SELLACIOUS_ORDER_CART_DISCOUNTS'); ?>
						</div>
						<div class="amt-lbl">
							<strong>- <?php echo $helper->currency->display($totals->get('discount_amount'), $g_currency, $c_currency, true); ?></strong>
						</div>
					</div>
				<?php endif; ?>
				<?php $url = JRoute::_('index.php?option=com_sellacious&view=cart'); ?>
				<?php if ($coupon_code): ?>
				<div class="cart-final-price">
					<div class="lbl-txt">
						<?php echo $coupon_code; ?>
					</div>
					<div class="amt-lbl">
						<strong>- <?php echo $helper->currency->display($totals->get('coupon_discount'), $g_currency, '', true); ?></strong>
					</div>
				</div>
				<?php endif; ?>
			</div>
			<div class="cart-items-promo">
				<?php if ($coupon_code): ?>
					<input type="text" class="coupon-code readonly ctech-rounded-0" title="" value="<?php echo $coupon_code ?>"
						   placeholder="<?php echo JText::_('COM_SELLACIOUS_CART_COUPON_CODE_INPUT') ?>" readonly>
					<button type="button" class="btn-apply-coupon ctech-btn ctech-btn-success ctech-rounded-0"><?php
						echo JText::_('COM_SELLACIOUS_CART_BTN_REMOVE_COUPON_LABEL') ?></button>
				<?php else: ?>
					<input type="text" class="coupon-code ctech-rounded-0" title=""
						   placeholder="<?php echo JText::_('COM_SELLACIOUS_CART_COUPON_CODE_INPUT') ?>">
					<button type="button" class="btn-apply-coupon ctech-btn ctech-btn-success ctech-rounded-0"><?php
						echo JText::_('COM_SELLACIOUS_CART_BTN_APPLY_COUPON_LABEL') ?></button>
				<?php endif; ?>
				<div class="ctech-clearfix"></div>
			</div>
			<hr class="isolate" />

			<div class="cart-buttons">
				<div class="cart-final-pricing">
					<div class="cart-final-price">
						<div class="lbl-txt">
							<?php echo JText::_('COM_SELLACIOUS_CART_GRAND_TOTAL_LABEL') ?>
						</div>
						<?php echo $totals->get('ship_tbd') ? '<span class="red"> *</span>' : '' ?>:
						<div class="amt-lbl">
							<span class="grand-total strong nowrap" data-amount="<?php echo $totals->get('grand_total') ?>">
								<?php echo $helper->currency->display($totals->get('grand_total'), $g_currency, $c_currency, true); ?>
							</span>
						</div>
					</div>
				</div>
				<?php if ($cartValid) : ?>
					<a href="<?php echo JRoute::_('index.php?option=com_sellacious&view=cart&layout=aio') ?>">
						<button type="button" class="checkout-btn ctech-btn ctech-btn-success ctech-rounder-0 ctech-float-left">
							<i class="fa fa-shopping-cart"></i>
							<?php echo JText::_('COM_SELLACIOUS_CART_CHECKOUT') ?>
						</button>
					</a>
				<?php else: ?>
					<button type="button" class="checkout-btn ctech-btn ctech-btn-primary ctech-rounder-0 ctech-float-left  disabled">
						<i class="fa fa-shopping-cart"></i>
						<?php echo JText::_('COM_SELLACIOUS_CART_CHECKOUT') ?>
					</button>
				<?php endif; ?>

				<?php if ($helper->config->get('cart_shop_more_link')): ?>
				<a class="ctech-btn ctech-btn-info ctech-rounder-0 ctech-text-white pull-left btn-close continue-shop  " data-dismiss="modal"
					   href="<?php echo $helper->config->get('shop_more_redirect', JRoute::_('index.php')) ?>"
					   onclick="if (jQuery(this).closest('.modal').length) return false;">
						<?php echo JText::_('COM_SELLACIOUS_CART_BTN_CLOSE_CART_LABEL') ?>
						<?php if (JFactory::getDocument()->direction === 'ltr'): ?>
							<i class="fa fa-chevron-right"></i>
						<?php elseif (JFactory::getDocument()->direction === 'rtl'): ?>
							<i class="fa fa-chevron-left"></i>
						<?php endif; ?>
					</a>
				<?php endif; ?>
				<div class="ctech-clearfix"></div>
			</div>
		</div>
	</div>
</div>


