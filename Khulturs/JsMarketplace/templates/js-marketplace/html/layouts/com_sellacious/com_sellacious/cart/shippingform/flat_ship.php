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

if ($cart->count() == 0)
{
	return;
}

$helper     = SellaciousHelper::getInstance();
$g_currency = $cart->getCurrency();
$c_currency = $helper->currency->current('code_3');

$shipName = (string) $cart->getShipping('ruleTitle');
$shipping = (float) $cart->getShipping('total');
$ship_tbd = (bool) $cart->getShipping('tbd');
?>
<h3><?php echo $shipName ?>: <?php echo $helper->currency->display($shipping, $g_currency, $c_currency); ?></h3>

<a class="next-btn-shipping btn-next pull-right"><i class="fa fa-arrow-right"></i></a><?php
