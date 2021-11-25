<?php
/**
 * @version     2.0.0
 * @package     sellacious
 *
 * @copyright   Copyright (C) 2012-2020 Bhartiy Web Technologies. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Saurabh Sabharwal <info@bhartiy.com> - http://www.bhartiy.com
 */
// no direct access
defined('_JEXEC') or die;
/** @var  array  $displayData */
/** @var  object $cart */
/** @var  object $totals */

extract($displayData);

$helper     = SellaciousHelper::getInstance();
$g_currency = $helper->currency->getGlobal('code_3');

// Call coupons only after getTotals()
$coupon_code = $cart->get('coupon.code');
$coupon_title = $cart->get('coupon.title');
$coupon_msg  = $cart->get('coupon.message');
?>

<div class="cart-price-info">
	<div class="cart-item-attr clearfix">
		<div class="cart-item-attr-label">
			<label><?php echo JText::_('COM_SELLACIOUS_CART_LABEL_SUB_TOTAL') ?></label>
		</div>
		<div class="cart-item-attr-value">
			<span id="cart-total" data-amount="<?php echo $totals->get('items.sub_total') ?>"><?php echo $helper->currency->display($totals->get('items.sub_total'), $g_currency, '', true); ?></span>
		</div>
	</div>

	<?php if (abs($totals->get('shipping')) >= 0.01): ?>
		<div class="cart-item-attr clearfix">
			<div class="cart-item-attr-label">
				<label>
					<?php echo JText::_('COM_SELLACIOUS_CART_LABEL_TOTAL_SHIPPING') ?>
				</label>
			</div>
			<div class="cart-item-attr-value">
				<span><?php echo $helper->currency->display($totals->get('shipping'), $g_currency, '', true); ?></span>
			</div>
		</div>

		<?php
		// Display Taxes on Shipping
		$shipping_shoprules = $totals->get('shipping_shoprules', array());

		// Do not just sort, rules are in hierarchical order
		foreach ($shipping_shoprules as $rule)
		{
			// $rule = {level, title, percent, amount, input, change, output};
			if (is_object($rule) && abs($rule->change) >= 0.01)
			{
				if (($rule->type == 'tax' || $rule->type == 'discount') && $rule->sum_method == 3)
				{
					?>
					<div class="cart-item-attr clearfix">
						<div class="cart-item-attr-label">
							<label>
								<?php echo $this->escape($rule->title) ?> @ <?php echo $rule->percent ? sprintf('%s%%', number_format($rule->amount, 2)) :
									$helper->currency->display($rule->amount, $g_currency, '', true); ?>
							</label>
						</div>
						<div class="cart-item-attr-value">
							<span><?php echo $helper->currency->display(abs($rule->change), $g_currency, '', true); ?></span>
						</div>
					</div>
					<?php
				}
			}
		}
		?>
	<?php endif; ?>
	<?php
	$cartRules = $cart->getShoprules();

	$taxDetails       = false;
	$discountDetails  = false;

	foreach ($cartRules as $rule)
	{
		if ($rule->type === 'tax')
		{
			$taxDetails = true;
		}
		if ($rule->type === 'discount')
		{
			$discountDetails = true;
		}
	}

	if (abs($totals->get('tax_amount')) >= 0.01): ?>
		<div class="cart-item-attr clearfix">
			<div class="cart-item-attr-label">
				<div class="ctech-d-inline-block ctech-position-relative">
					<?php echo JText::_('COM_SELLACIOUS_CART_LABEL_TOTAL_TAX') ?>
					<?php if ($taxDetails): ?>
						<div class="prices-dropdown-container">
							<a href="#" class="prices-dropdown-toggle"><i class="fa fa-info-circle"></i></a>
							<?php if (count($cartRules)): ?>
								<div class="dropdown-prices ctech-d-none">
									<?php
									// Do not just sort, rules are in hierarchical order
									foreach ($cartRules as $rule):
										// $rule = {level, title, percent, amount, input, change, output};
										if (is_object($rule) && abs($rule->change) >= 0.01):
											if ($rule->type == 'tax'): ?>
												<div class="cart-item-attr clearfix">
													<div class="cart-item-attr-label">
														<label>
															<span class="dropdown-tax-label"><?php echo $this->escape($rule->title) ?></span> @ <?php echo $rule->percent ? sprintf('%s%%', number_format($rule->amount, 2)) :
																$helper->currency->display($rule->amount, $g_currency, '', true); ?>
														</label>
													</div>
													<div class="cart-item-attr-value">
														<span><?php echo $helper->currency->display(abs($rule->change), $g_currency, '', true); ?></span>
													</div>
												</div>
											<?php endif;
										endif;
									endforeach; ?>
								</div>
							<?php endif; ?>
						</div>
					<?php endif; ?>
				</div>
			</div>
			<div class="cart-item-attr-value">
				<span class="ctech-text-danger"><?php echo $helper->currency->display($totals->get('tax_amount'), $g_currency, '', true); ?></span>
			</div>
		</div>
	<?php endif; ?>

	<?php if (abs($totals->get('discount_amount')) >= 0.01): ?>
		<div class="cart-item-attr clearfix">
			<div class="cart-item-attr-label">
				<div class="ctech-d-inline-block ctech-position-relative">
					<?php echo JText::_('COM_SELLACIOUS_CART_LABEL_TOTAL_DISCOUNT') ?>
					<?php if ($discountDetails): ?>
						<div class="prices-dropdown-container">
							<a href="#" class="prices-dropdown-toggle"><i class="fa fa-info-circle"></i></a>

							<?php if (count($cartRules)): ?>
								<div class="dropdown-prices ctech-d-none">
									<?php
									// Do not just sort, rules are in hierarchical order
									foreach ($cartRules as $rule):
										if (is_object($rule) && abs($rule->change) >= 0.01):
											if ($rule->type == 'discount'): ?>
												<div class="cart-item-attr clearfix">
													<div class="cart-item-attr-label">
														<label>
															<span class="dropdown-discount-label"><?php echo $this->escape($rule->title) ?></span> @ <?php echo $rule->percent ? sprintf('%s%%', number_format($rule->amount, 2)) :
																$helper->currency->display($rule->amount, $g_currency, '', true); ?>
														</label>
													</div>
													<div class="cart-item-attr-value">
														<span><?php echo $helper->currency->display(abs($rule->change), $g_currency, '', true); ?></span>
													</div>
												</div>
											<?php endif;
										endif;
									endforeach; ?>
								</div>
							<?php endif; ?>
						</div>
					<?php endif; ?>
				</div>
			</div>
			<div class="cart-item-attr-value">
				<span class="ctech-text-success">–<?php echo $helper->currency->display($totals->get('discount_amount'), $g_currency, '', true); ?></span>
			</div>
		</div>

	<?php endif; ?>

	<?php if ($coupon_code): ?>
		<div class="cart-item-attr clearfix">
			<div class="cart-item-attr-label">
				<label>
					<span class="coupon-message"><?php echo $coupon_code;?></span>
				</label>
			</div>
			<div class="cart-item-attr-value">
				<span>–<?php echo $helper->currency->display($totals->get('coupon_discount'), $g_currency, '', true); ?></span>
			</div>
		</div>
	<?php elseif ($coupon_msg): ?>
		<div class="cart-item-attr clearfix">
			<div class="">
				<span class="coupon-message"><?php echo $coupon_msg ?></span>
			</div>
		</div>
	<?php endif; ?>

	<div class="input-group coupon-group">
		<?php if ($coupon_code): ?>
			<input type="text" class="form-control coupon-code readonly ctech-rounded-0" value="<?php echo $coupon_code ?>"
			       placeholder="<?php echo JText::_('COM_SELLACIOUS_CART_COUPON_CODE_INPUT') ?>" readonly>
			<span class="input-group-btn">
						<button type="button" class="ctech-btn btn-apply-coupon ctech-btn-sm ctech-btn-success"><?php
							echo JText::_('COM_SELLACIOUS_CART_BTN_REMOVE_COUPON_LABEL') ?></button>
					</span>
		<?php else: ?>
			<input type="text" class="form-control coupon-code ctech-rounded-0"
			       placeholder="<?php echo JText::_('COM_SELLACIOUS_CART_COUPON_CODE_INPUT') ?>">
			<span class="input-group-btn">
						<button type="button" class="ctech-btn btn-apply-coupon ctech-btn-sm ctech-btn-success"><?php
							echo JText::_('COM_SELLACIOUS_CART_BTN_APPLY_COUPON_LABEL') ?></button>
					</span>
		<?php endif; ?>
	</div>
	<?php if ($coupon_code): ?>
		<div class="coupon-group clearfix">
			<span class="coupon-message">
				<span class="coupon-title"><?php echo JText::_('COM_SELLACIOUS_CART_COUPON_DISCOUNT') . ' ' . $coupon_code . ' '; ?></span>
				<span><?php echo JText::_('COM_SELLACIOUS_CART_COUPON_DISCOUNT_MESSAGE');?></span>
			</span>
		</div>
	<?php endif; ?>

	<div class="cart-item-attr clearfix total">
		<div class="cart-item-attr-label total">
			<label>
				<span><?php echo JText::_('COM_SELLACIOUS_CART_GRAND_TOTAL_LABEL'); ?></span>
			</label>
		</div>
		<div class="cart-item-attr-value total">
			<?php echo $totals->get('ship_tbd') ? '<span class="red"> *</span>' : '' ?>
			<span class="grand-total nowrap" data-amount="<?php echo $totals->get('grand_total') ?>">
					<?php echo $helper->currency->display($totals->get('grand_total'), $g_currency, '', true); ?>
				</span>
		</div>
	</div>

	<?php $errors = array(); ?>
	<div class="action-btn-area">
		<?php if ($cart->validate($errors)): ?>
			<button type="button" class="btn-next ctech-btn ctech-btn-primary ctech-btn-sm"><?php
				echo JText::_('COM_SELLACIOUS_CART_BTN_PROCEED_PAYMENT_LABEL') ?></button>
		<?php else: ?>
			<button type="button" class="ctech-btn ctech-btn-primary disabled ctech-btn-sm"><?php
				echo JText::_('COM_SELLACIOUS_CART_BTN_PROCEED_PAYMENT_LABEL') ?></button>
		<?php endif; ?>
	</div>
</div>
