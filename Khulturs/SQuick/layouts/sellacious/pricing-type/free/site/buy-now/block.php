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

/** @var  JObject  $displayData */
$item   = $displayData;
$helper = SellaciousHelper::getInstance();

$showBuyNow       = $helper->config->inList('product_modal', 'product_buy_now_display');
$disable_checkout = $helper->config->get('disable_checkout');
$stock            = $item->get('stock_capacity');
$minQty           = $item->get('quantity_min');

$checkStock = $stock > 0 && (!$minQty || $stock >= $minQty);

$reqLogin = $helper->config->get('login_to_see_price');
$me       = JFactory::getUser();

if (($checkStock || $item->get('disable_stock', 0) == 1) && $disable_checkout == 0 && $showBuyNow && (!$reqLogin || !$me->guest))
{
	?>
	<button type="button" class="ctech-btn ctech-btn-primary ctech-btn-block btn-cart btn-add-cart"
		data-item="<?php echo $item->get('code') ?>" data-checkout="true"><?php
			echo JText::_('COM_SELLACIOUS_PRODUCT_BUY_NOW'); ?> </button><?php
}
