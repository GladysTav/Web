<?php
/**
 * @version     1.7.3
 * @package     sellacious
 *
 * @copyright   Copyright (C) 2012-2019 Bhartiy Web Technologies. All rights reserved.
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

JHtml::_('stylesheet', 'com_sellacious/fe.view.cart.aio.css', null, true);


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

$itemisedShip = $helper->config->get('itemised_shipping', true);

$iErrors = $errors = array();
$cartValid = $cart->validate($errors, $iErrors, true);
?>
<div class="row mt-100">
	<div class="col-md-8 col-xs-12 cart-modal9">



<div class="cart-page-box">
    <div class="top-head">
	<h4 class="heading-cart"><i class="fa fa-shopping-cart"></i> <?php echo JText::_('COM_SELLACIOUS_YOUR_CART') ?></h4>
        <button type="button" class="btn btn-small btn-refresh hasTooltip pull-right" data-placement="left"   title="<?php echo JText::_('COM_SELLACIOUS_CART_BTN_REFRESH_CART_LABEL') ?>"><i class="fa fa-refresh"></i></button>
    </div>

	<div class="cart-product-list">
		<?php
		foreach ($items as $i => $item)
		{
		$link      = $item->getLinkUrl();
		$ship_tbd  = $item->getShipping('tbd');
		$ship_free = $item->getShipping('free');
		$ship_amt  = $item->getShipping('amount');

		// Fixme: Render package items
		// $package_items = $item->get('package_items');
		$package_items = null;
		$product_title = $item->getProperty('title') . ' - ' . $item->getProperty('variant_title');
		?>
		<div class="cart-main-details">
				<div class="cart-main-details-inner">
                        <div class="cart-img-thumb">
                            <span style="background-image: url(<?php echo $helper->product->getImage($item->getProperty('id'), $item->getProperty('variant_id'));?> )"></span>
                        </div>
<!--                        <div class="row">-->

                        <div class="cart-details">

					<div class="img_tit">
						<?php echo $package_items ? JText::_('COM_SELLACIOUS_CART_PACKAGE_ITEM_LABEL') : ''; ?>

						<a class="" href="<?php echo $link ?>" style="line-height: 1.6;"><?php echo trim($product_title, '- '); ?></a>
					</div>
					<div class="price-det">
						<ul class="cart-item-prices">
							<li class="main-price"><?php //echo JText::_('COM_SELLACIOUS_CART_ITEM_HEADING_PRICE'); ?><strong><?php echo $helper->currency->display($item->getPrice('sales_price'), $g_currency, $c_currency, true); ?></strong></li>
							<li><?php echo JText::_('COM_SELLACIOUS_CART_ITEM_HEADING_RATE'); ?>: <strong><?php echo $helper->currency->display($item->getPrice('basic_price'), $g_currency, $c_currency, true); ?></strong></li>
							<?php if ($item->getPrice('tax_amount') >= 0.01): ?>
								<li><?php echo JText::_('COM_SELLACIOUS_CART_ITEM_HEADING_TAX'); ?>: <strong><?php echo $helper->currency->display($item->getPrice('tax_amount'), $g_currency, $c_currency, true); ?></strong></li>
							<?php endif; ?>
							<?php if ($item->getPrice('discount_amount') >= 0.01): ?>
								<li><?php echo JText::_('COM_SELLACIOUS_CART_ITEM_HEADING_DISCOUNT'); ?>: <strong><?php echo $helper->currency->display($item->getPrice('discount_amount'), $g_currency, $c_currency, true); ?></strong></li>
							<?php endif; ?>
						</ul>

					</div>
					<div class="details-product">
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
							<?php echo JText::_('COM_SELLACIOUS_CART_ITEM_SOLD_BY'); ?> <span><?php echo $item->getProperty('seller_store') ? $item->getProperty('seller_store') : ($item->getProperty('seller_name') ? $item->getProperty('seller_name') : ($item->getProperty('seller_company') ? $item->getProperty('seller_company') : $item->getProperty('seller_username'))); ?></span>

							<div class="cart-item-ship-info text-left nowrap">
								<label>
									<?php
									if ($ship_free)
									{
										echo JText::_('COM_SELLACIOUS_CART_ITEM_SHIPRULE_SELECT_LABEL');
										echo JText::_('COM_SELLACIOUS_ORDER_SHIPMENT_FREE');
									}
									elseif ($ship_tbd)
									{
										echo JText::_('COM_SELLACIOUS_CART_ITEM_SHIPRULE_SELECT_LABEL');
										echo '<span class="tbd">' . JText::_('COM_SELLACIOUS_TBD') . '</span>';
									}
									elseif ($ship_amt >= 0.01)
									{
										echo JText::_('COM_SELLACIOUS_CART_ITEM_SHIPRULE_SELECT_LABEL');
										echo $helper->currency->display($ship_amt, $g_currency, $c_currency, true);
									}
									?>
								</label>
							</div>
							<?php
							if (!empty($iErrors) && isset($iErrors[$item->getUid()]))
							{
								?>
								<ul class="item-errors">
									<?php
									foreach ($iErrors[$item->getUid()] as $iError)
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

					</div>
                            <div class="quantity-product">
                                <div class="pull-left remove-area">
                                <a href="#" class="btn-remove-item hasTooltip" data-uid="<?php echo $item->getUid() ?>"   title="Remove"><i class="fa fa-trash-o fa-lg"></i><span><?php echo JText::_('COM_SELLACIOUS_CART_BTN_REMOVE_CART_LABEL') ?></span></a>
                                </div>
                                <div class="qtywrapper pull-right">
                                <div class="qtyarea">
                                    <span><strong><?php echo JText::_('COM_SELLACIOUS_CART_QTY_LABEL') ?></strong>&nbsp;:&nbsp;</span><input type="number" class="input item-quantity" data-uid="<?php echo $item->getUid() ?>"data-value="<?php echo $item->getQuantity() ?>" min="1" value="<?php echo $item->getQuantity() ?>" title=""/>
                                </div>
                                </div>
                            </div>

                        </div>
<!--                        </div>-->

				</div>
		</div>


			<?php
		}
		?>

	</div>

</div>
	</div>
	<div class="col-md-4 col-xs-12  cart-modal3">
		<div class="order-summery">
			<h4 class="title"><?php echo JText::_('COM_SELLACIOUS_CART_ORDER_SUMMARY') ?></h4>
		<div class="cart-items-footer">

			<div class="row">
				<div class="col-sm-12">
					<div class="bntareacart couponbtnarea">
						<?php if ($coupon_code): ?>
							<input type="text" class="coupon-code readonly" title="" value="<?php echo $coupon_code ?>"
								   placeholder="<?php echo JText::_('COM_SELLACIOUS_CART_COUPON_CODE_INPUT') ?>" readonly>
							<button type="button" class="btn-apply-coupon btn"><?php
								echo JText::_('COM_SELLACIOUS_CART_BTN_REMOVE_COUPON_LABEL') ?></button>
						<?php else: ?>
							<input type="text" class="coupon-code" title=""
								   placeholder="<?php echo JText::_('COM_SELLACIOUS_CART_COUPON_CODE_INPUT') ?>">
							<button type="button" class="btn-apply-coupon btn"><?php
								echo JText::_('COM_SELLACIOUS_CART_BTN_APPLY_COUPON_LABEL') ?></button>
						<?php endif; ?>
					</div>
				</div>
				<div class="col-sm-6">
					<div class="couponsarea">
						<?php if ($coupon_code): ?>
							<div class="mt-2">
								<span class="coupon-message"><?php echo JText::sprintf('COM_SELLACIOUS_CART_COUPON_DISCOUNT_MESSAGE', $coupon_code, $coupon_title); ?></span>
								<span>(–) <?php echo $helper->currency->display($totals->get('coupon_discount'), $g_currency, '', true); ?></span>
							</div>
						<?php elseif ($coupon_msg): ?>
							<div class="text-right">
								<span class="coupon-message"><?php echo $coupon_msg ?></span>
							</div>
						<?php endif; ?>
					</div>
				</div>
			</div>




		</div>
			<hr class="isolate">
			<ul class="pricing-total">
				<?php if ($totals->get('shipping') >= 0.01): ?>
					<li><div class="lbl-txt"><?php echo JText::_('COM_SELLACIOUS_CART_ITEM_SHIPRULE_SELECT_LABEL'); ?> </div><div class="amt-lbl"> <strong><?php echo $helper->currency->display($totals->get('shipping'), $g_currency, $c_currency, true); ?></strong></div></li>
				<?php endif; ?>
				<?php if ($totals->get('items.sub_total') >= 0.01): ?>
					<li><div class="lbl-txt"><?php echo JText::_('COM_SELLACIOUS_ORDERS_PRICE'); ?></div> <div class="amt-lbl"><strong><?php echo $helper->currency->display($totals->get('items.sub_total'), $g_currency, $c_currency, true); ?></strong></div></li>
				<?php endif; ?>
				<li><div class="lbl-txt"><?php echo JText::_('COM_SELLACIOUS_ORDERS_ESTIMATED_TOTAL'); ?></div><?php
					echo $totals->get('ship_tbd') ? '<span class="red">*</span>' : '' ?> :
					<div class="amt-lbl"> <span id="cart-total" data-amount="<?php echo $totals->get('cart_total') ?>">
							<strong><?php echo $helper->currency->display($totals->get('cart_total'), $g_currency, $c_currency, true); ?></strong></span></div></li>
				<?php $url = JRoute::_('index.php?option=com_sellacious&view=cart'); ?>
			</ul>
			<hr class="isolate">
			<ul class="pricing-total">
				<li><div class="lbl-txt"><?php echo JText::_('COM_SELLACIOUS_CART_GRAND_TOTAL_LABEL') ?></div>
					<?php echo $totals->get('ship_tbd') ? '<span class="red"> *</span>' : '' ?>:
					<div class="amt-lbl"><span class="grand-total strong nowrap" data-amount="<?php echo $totals->get('grand_total') ?>">
							<?php echo $helper->currency->display($totals->get('grand_total'), $g_currency, $c_currency, true); ?></span></div></li>
			</ul>
			<div class="buttons-more-cart">
			<?php if ($cartValid) : ?>
				<a href="<?php echo JRoute::_('index.php?option=com_sellacious&view=cart&layout=aio') ?>">
					<button type="button" class="checkout-btn btn  pull-left">
						<i class="fa fa-shopping-cart"></i>
						<?php echo JText::_('COM_SELLACIOUS_CART_CHECKOUT') ?></button>
				</a>
			<?php else: ?>
				<button type="button" class="checkout-btn btn btn-primary pull-left  disabled">
					<i class="fa fa-shopping-cart"></i>
					<?php echo JText::_('COM_SELLACIOUS_CART_CHECKOUT') ?></button>
			<?php endif; ?>

<!--			<button type="button" class="btn-clear-cart btn btn-warning btn-lg pull-left ">--><?php
//				echo JText::_('COM_SELLACIOUS_CART_BTN_CLEAR_CART_LABEL') ?><!--</button>-->
				<?php if ($helper->config->get('cart_shop_more_link')): ?>
					<a class="btn  pull-left btn-close continue-shop  " data-dismiss="modal"
					   href="<?php echo $helper->config->get('shop_more_redirect', JRoute::_('index.php')) ?>"
					   onclick="if (jQuery(this).closest('.modal').length) return false;"><?php
						echo JText::_('COM_SELLACIOUS_CART_BTN_CLOSE_CART_LABEL') ?> <i class="fa fa-chevron-right"></i> </a>
				<?php endif; ?>
			</div>



		</div>
	</div>

</div>
	<div class="button-fixed-bottom-row">
		<div class="row">
			<div class="w-50">
		<?php if ($cartValid) : ?>
			<a href="<?php echo JRoute::_('index.php?option=com_sellacious&view=cart&layout=aio') ?>">
				<button type="button" class="checkout-btn btn  pull-left margin-5">
					<i class="fa fa-shopping-cart"></i>
					<?php echo JText::_('COM_SELLACIOUS_CART_CHECKOUT') ?></button>
			</a>
		<?php else: ?>
			<button type="button" class="checkout-btn btn btn-primary pull-left margin-5 disabled">
				<i class="fa fa-shopping-cart"></i>
				<?php echo JText::_('COM_SELLACIOUS_CART_CHECKOUT') ?></button>
		<?php endif; ?>
		</div>
			<div class="w-50">

		<!--			<button type="button" class="btn-clear-cart btn btn-warning btn-lg pull-left margin-5">--><?php
		//				echo JText::_('COM_SELLACIOUS_CART_BTN_CLEAR_CART_LABEL') ?><!--</button>-->
		<?php if ($helper->config->get('cart_shop_more_link')): ?>
			<a class="btn  pull-left btn-close continue-shop  margin-5" data-dismiss="modal"
			   href="<?php echo $helper->config->get('shop_more_redirect', JRoute::_('index.php')) ?>"
			   onclick="if (jQuery(this).closest('.modal').length) return false;"><?php
				echo JText::_('COM_SELLACIOUS_CART_BTN_CLOSE_CART_LABEL') ?> <i class="fa fa-chevron-right"></i> </a>
		<?php endif; ?>
		</div>
		</div>
	</div>





</div>
