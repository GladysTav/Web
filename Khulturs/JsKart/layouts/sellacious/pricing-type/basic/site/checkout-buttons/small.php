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

/** @var  Registry  $displayData */
$item   = $displayData;
$helper = SellaciousHelper::getInstance();

$showAddToCart    = $helper->config->inList('product', 'product_add_to_cart_display');
$showBuyNow       = $helper->config->inList('product', 'product_buy_now_display');
$disable_checkout = $helper->config->get('disable_checkout');
$stock            = $item->get('stock_capacity');
$minQty           = $item->get('quantity_min');

$checkStock = $stock > 0 && (!$minQty || $stock >= $minQty);

$reqLogin = $helper->config->get('login_to_see_price');
$me       = JFactory::getUser();

if ($disable_checkout == 0 && ($showAddToCart || $showBuyNow) && (!$reqLogin || !$me->guest))
{
	if ($checkStock || $item->get('disable_stock', 0) == 1)
	{
		if ($showAddToCart): ?>
		<button type="button" class="ctech-btn ctech-btn-warning btn-cart-sm btn-add-cart"
				data-item="<?php echo $item->get('code') ?>"><i class="fa fa-shopping-cart"></i></button><?php
		endif;

		if ($showBuyNow): ?>
		<button type="button" class="ctech-btn ctech-btn-primary btn-cart-sm btn-add-cart"
				data-item="<?php echo $item->get('code') ?>" data-checkout="true">&nbsp;<i class="fa fa-bolt"></i> </button><?php
		endif;
	}
	else
	{
		?>
		<button class="ctech-btn lbl-no-stock ctech-btn-default hasTooltip" disabled title="<?php echo JText::_('COM_SELLACIOUS_PRODUCTS_OUT_OF_STOCK'); ?>">
			<i class="fa fa-times"></i><span><?php echo JText::_('COM_SELLACIOUS_PRODUCTS_OUT_OF_STOCK'); ?><span>
		</button>
		<?php
	}

}
