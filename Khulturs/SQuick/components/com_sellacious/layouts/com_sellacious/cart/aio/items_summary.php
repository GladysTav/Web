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

use Sellacious\Form\CheckoutQuestionsFormHelper;

/** @var  stdClass         $displayData */
/** @var  Sellacious\Cart  $cart */
$cart       = $displayData->cart;
$helper     = SellaciousHelper::getInstance();
$g_currency = $helper->currency->getGlobal('code_3');

//IMPORTANT: Call all external method before rendering any html, so as to avoid any exception after html.
$itemisedShip = $helper->config->get('itemised_shipping', SellaciousHelperShipping::SHIPPING_SELECTION_PRODUCT);
$items        = $cart->getItems();
$totals       = $cart->getTotals();

$round_enabled = $helper->config->get('round_grand_total', 0);
?>
<a class="ctech-btn ctech-btn-sm ctech-float-right edit-cart-aio btn-cart-modal margin-5"><i class="fa fa-shopping-cart"></i> <?php
	echo JText::_('COM_SELLACIOUS_CART_BTN_SUMMARY_CART_LABEL') ?> </a>
<a class="ctech-btn ctech-btn-sm ctech-float-right btn-refresh ctech-btn-default margin-5"><?php
	echo JText::_('COM_SELLACIOUS_CART_BTN_REFRESH_CART_LABEL') ?> <i class="fa fa-refresh"></i> </a>
<div class="clearfix"></div>
<div class="cart-summary-section clearfix">
	<div class="cart-items-table clearfix w100p">
		<?php
		foreach ($items as $i => $item)
		{
			$link               = $item->getLinkUrl();
			$ship_tbd           = $item->getShipping('tbd');
			$ship_free          = $item->getShipping('free');
			$ship_total         = $item->getShipping('total');
			$shoprules          = $item->getShoprules();
			$shipping_shoprules = (array) $item->getShipping('shippingShopRules', array());
			$product_title      = trim($item->getProperty('title') . ' - ' . $item->getProperty('variant_title'), '- ');
			$image              = $item->getImageUrl();
			// Fixme: Render package items
			$package_items = null; // $item->get('package_items');
			?>
			<div class="cart-item clearfix">
				<div class="cart-item-image">
					<span class="cart-item-image-container" style="background-image: url('<?php echo $image ?>')"></span>
				</div>
				<div class="cart-item-information">
					<a href="<?php echo $link ?>" class="cart-item-title hasTooltip" data-placement="bottom" title="<?php echo $product_title; ?>"><span class="cart-item-quantity"><?php echo $item->getQuantity() . '&times;'; ?></span> <?php echo $product_title; ?></a>
					<?php if (count($shoprules)): ?>
						<a href="#" class="pull-right shoprule-info-toggle hasTooltip"
						   title="<?php echo JText::_('COM_SELLACIOUS_CART_ITEM_SHOPRULE_INFO_TIP') ?>"
						   data-uid="<?php echo $item->getUid() ?>"><i class="fa fa-plus-square-o"></i> </a>
					<?php endif; ?>
					<?php if ($package_items): ?>
						<hr class="simple">
						<ol class="package-items">
							<?php
							foreach ($package_items as $pkg_item):
								$pkg_item_title = trim($pkg_item->product_title . ' - ' . $pkg_item->variant_title, '- ');
								$pkg_item_sku   = trim($pkg_item->product_sku . '-' . $pkg_item->variant_sku, '- ');
								$url = JRoute::_('index.php?option=com_sellacious&view=product&p=' . $pkg_item->code);
								?><li><a class="normal" href="<?php echo $url ?>"><?php echo $pkg_item_title ?> (<?php echo $pkg_item_sku ?>)</a></li><?php
							endforeach;
							?>
						</ol>
					<?php endif; ?>

					<!-- Seller -->
					<div class="item-seller">
						<em><small><span class="sold-by-label"><?php echo JText::_('COM_SELLACIOUS_CART_ITEM_SOLD_BY'); ?></span></small></em>
						<?php echo $item->getProperty('seller_store') ? $item->getProperty('seller_store') : ($item->getProperty('seller_name') ? $item->getProperty('seller_name') : ($item->getProperty('seller_company') ? $item->getProperty('seller_company') : $item->getProperty('seller_username'))); ?>
					</div>

					<?php if ($er = $item->check()): ?>
						<div class="red small"><?php echo implode('<br>', $er); ?></div>
					<?php endif; ?>

					<div class="cart-item-price">
						<div class="dropdown-prices ctech-d-none">
							<?php if (round($item->getPrice('list_price'), 2) >= 0.01): ?>
								<div class="item-price price-row">
									<span class="cart-item-attr-label"><?php echo JText::_('COM_SELLACIOUS_CART_ITEM_HEADING_LIST_PRICE'); ?></span>
									<span class="cart-item-attr-value" class="ctech-font-weight-bold"><del><?php echo $helper->currency->display($item->getPrice('list_price'), $g_currency, '', true); ?></del></span>
								</div>
							<?php endif; ?>

							<div class="item-price price-row">
								<span class="cart-item-attr-label"><?php echo JText::_('COM_SELLACIOUS_CART_ITEM_HEADING_SELLING_PRICE'); ?></span>
								<span class="cart-item-attr-value" class="ctech-font-weight-bold"><?php echo $helper->currency->display($item->getPrice('basic_price'), $g_currency, '', true); ?></span>
							</div>

							<?php if ($itemisedShip == SellaciousHelperShipping::SHIPPING_SELECTION_PRODUCT): ?>
								<div class="price-row cart-item-itemised-shipping <?php echo $ship_tbd ? 'tbd' : '' ?>">
									<?php
									echo '<span class="cart-item-attr-label">';
										echo JText::_('COM_SELLACIOUS_CART_ITEM_HEADING_SHIPPING');
									echo '</span>';
									echo '<span class="cart-item-attr-value">';
										if ($ship_free)
										{
											echo JText::_('COM_SELLACIOUS_ORDER_SHIPMENT_FREE');
										}
										elseif ($ship_tbd)
										{
											echo JText::_('COM_SELLACIOUS_ORDER_SHIPMENT_TBD');
										}
										else
										{
											echo $helper->currency->display($ship_total, $g_currency, '', true);
										}
									echo '</span>';
									?>
									<?php if (count($shipping_shoprules)): ?>
										<a href="#" class="pull-right shipping-shoprule-info-toggle hasTooltip"
										   title="<?php echo JText::_('COM_SELLACIOUS_CART_ITEM_SHOPRULE_INFO_TIP') ?>"
										   data-uid="<?php echo $item->getUid() ?>"><i class="fa fa-plus-square-o"></i> </a>
									<?php endif; ?>
								</div>
							<?php endif; ?>

							<?php if (round($item->getPrice('tax_amount'), 2) >= 0.01): ?>
								<div class="item-price price-row">
									<span  class="cart-item-attr-label"><?php echo JText::_('COM_SELLACIOUS_CART_ITEM_HEADING_TAX'); ?></span>
									<span class="ctech-text-danger cart-item-attr-value"><?php echo $helper->currency->display($item->getPrice('tax_amount'), $g_currency, '', true); ?></span>
								</div>
							<?php endif; ?>

							<?php if (round($item->getPrice('discount_amount'), 2) >= 0.01): ?>
								<div class="price-row">
									<span class="cart-item-attr-label">
										<?php echo JText::_('COM_SELLACIOUS_CART_ITEM_HEADING_DISCOUNT'); ?>
									</span>
									<span class="ctech-text-success cart-item-attr-value">
										<?php echo $helper->currency->display($item->getPrice('discount_amount'), $g_currency, '', true); ?> Off
									</span>
								</div>
							<?php endif; ?>

							<?php
							if (count($shoprules))
							{
								echo $this->subLayout('shoprules', array('shoprules' => $shoprules, 'quantity' => $item->getQuantity()));
							}
							?>
						</div>
						<span class="ctech-text-primary total-price ctech-d-block">
							<?php echo $helper->currency->display($item->getPrice('sub_total') + $ship_total, $g_currency, '', true); ?> <a href="#" class="prices-dropdown-toggle"><i class="fa fa-info-circle"></i></a>
						</span>
					</div>

					<?php
					if ($coqData = CheckoutQuestionsFormHelper::getData('cart_summary', $cart, $item->getUid(), true)): ?>
						<div class="ctech-clearfix coq_wrapper">
							<a href="#checkout-questions-<?php echo $item->getUid();?>" role="button" data-toggle="ctech-modal"
							   class="btn-checkout-questions ctech-float-left ctech-text-primary"><i class="fa fa-pencil-alt"></i> <span
									class="checkout-questions-text"><?php echo JText::_('COM_SELLACIOUS_CHECKOUT_QUESTIONS_EDIT_IN_CART'); ?></span></a>
							<div class="ctech-clearfix"></div>
							<?php echo $this->subLayout('checkout_data', array('uid' => $item->getUid(), 'checkout_data' => $coqData)); ?>
						</div><?php
						echo JHtml::_('ctechBootstrap.modal', 'checkout-questions-' . $item->getUid(), JText::_('COM_SELLACIOUS_PRODUCT_CHECKOUT_QUESTIONS_FORM_TITLE'), '', '', array('url' => JRoute::_('index.php?option=com_sellacious&view=product&p=' . $item->getUid() . '&layout=coq&tmpl=component')));
					endif; ?>

					<?php
					if (count($shipping_shoprules))
					{
						echo $this->subLayout('shipping_shoprules', array('shipping_shoprules' => $shipping_shoprules, 'quantity' => $item->getQuantity()));
					}
					?>
				</div>
			</div>
			<?php
		}
		?>
	</div>

	<?php echo $this->subLayout('prices', array('cart' => $cart, 'totals' => $totals)) ?>
</div>
