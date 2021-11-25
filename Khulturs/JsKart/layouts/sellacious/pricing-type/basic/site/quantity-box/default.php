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

/** @var   JObject  $displayData */
$item   = $displayData;
$helper = SellaciousHelper::getInstance();
$me     = JFactory::getUser();

$showAddToCart                = $helper->config->inList('product', 'product_add_to_cart_display');
$showBuyNow                   = $helper->config->inList('product', 'product_buy_now_display');
$disable_checkout             = $helper->config->get('disable_checkout');
$display_stock_product_detail = $helper->config->get('display_stock_in_product_detail');
$disable_stock                = $item->get('disable_stock');
$reqLogin                     = $helper->config->get('login_to_see_price');

if ($disable_checkout == 0 && ($showAddToCart || $showBuyNow) && (!$reqLogin || !$me->guest))
{
	$stock  = $item->get('stock_capacity');
	$minQty = $item->get('quantity_min');

	$checkStock = $stock > 0 && (!$minQty || $stock >= $minQty);

	if ($checkStock || $item->get('disable_stock', 0) == 1)
	{
		?>
		<div class="qtyarea">
			<div class="quantitybox">
				<label><?php echo JText::_('COM_SELLACIOUS_PRODUCT_BUY_QUANTITY_INPUT_LABEL'); ?>
					<input type="number" name="quantity" id="product-quantity" min="1" data-uid="<?php echo $item->get('code') ?>" value="1"/>
				</label>
			</div>
		</div>
		<?php
		if ($display_stock_product_detail == 1): ?>
			<div class="fa fa-check-circle"></div>
			<?php
			echo $disable_stock == 0 ? $item->get('stock_capacity') . ' ' : '';
			echo JText::_('COM_SELLACIOUS_PRODUCT_STOCK_IN_CART');
		endif;
	}
	else
	{
		?>
		<div class="qtyarea">
			<button disabled="disabled" class="disabled ctech-btn lbl-no-stock ctech-btn-default hasTooltip outofstock" data-original-title="<?php echo JText::_('COM_SELLACIOUS_PRODUCT_OUT_OF_STOCK'); ?>">
				<i class="fa fa-times"></i> <?php echo JText::_('COM_SELLACIOUS_PRODUCT_OUT_OF_STOCK') ?>
			</button>
		</div>
		<?php
	}
}
