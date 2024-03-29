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
$disable_checkout = $helper->config->get('disable_checkout');
$stock            = $item->get('stock_capacity');
$minQty           = $item->get('quantity_min');

$checkStock = $stock > 0 && (!$minQty || $stock >= $minQty);

$reqLogin = $helper->config->get('login_to_see_price');
$me       = JFactory::getUser();

if (($checkStock || $item->get('disable_stock', 0) == 1) && $disable_checkout == 0 && $showAddToCart && (!$reqLogin || !$me->guest))
{
	?>
	<button type="button" class="ctech-btn ctech-btn-warning btn-cart btn-add-cart"
		data-item="<?php echo $item->get('code') ?>"><?php
			echo JText::_('COM_SELLACIOUS_PRODUCT_ADD_TO_CART'); ?></button><?php
}
