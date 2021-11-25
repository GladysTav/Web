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

$items = $cart->getItems();
?>
<table class="cart-items-table w100p">
	<tbody>
	<?php
	foreach ($items as $uid => $cartItem)
	{
		/** @var  Sellacious\Cart\Item  $cartItem */
		$p_code = $helper->product->getCode($cartItem->getProperty('id'), $cartItem->getProperty('variant_id'), $cartItem->getProperty('seller_uid'));
		$link   = JRoute::_('index.php?option=com_sellacious&view=product&p=' . $p_code);

		$ship_tbd  = $cartItem->getShipping('tbd');
		$ship_free = $cartItem->getShipping('free');
		$ship_amt  = $cartItem->getShipping('amount');

		// Fixme: Package items to be rendered
		// $package_items = $item->get('package_items');
		$package_items = null;
		$product_title = trim($cartItem->getProperty('title') . ' - ' . $cartItem->getProperty('variant_title'), '- ');
		?>
		<tr>
			<td style="width: 42px">
				<img class="product-thumb" src="<?php
					echo $helper->product->getImage($cartItem->getProperty('id'), $cartItem->getProperty('variant_id')); ?>" alt="">
			</td>
			<td class="cart-item">
				<?php echo $package_items ? JText::_('COM_SELLACIOUS_CART_PACKAGE_ITEM_LABEL') : ''; ?>
				<a class="cart-item-title" href="<?php echo $link ?>" style="line-height: 1.6;"><?php echo $product_title; ?></a>
				<?php if ($package_items): ?>
					<div><small><?php echo JText::_('COM_SELLACIOUS_CART_PACKAGE_ITEM_INCLUDES'); ?></small></div>
					<ol class="package-items">
						<?php
						foreach ($package_items as $pkg_item):
							$url            = JRoute::_('index.php?option=com_sellacious&view=product&p=' . $pkg_item->code);
							$pkg_item_title = trim($pkg_item->product_title . ' - ' . $pkg_item->variant_title, '- ');
							$pkg_item_sku   = trim($pkg_item->product_sku . '-' . $pkg_item->variant_sku, '- ');
							?><li><a class="normal" href="<?php echo $url ?>"><?php
								echo $pkg_item_title ?> (<?php echo $pkg_item_sku ?>)</a></li><?php
						endforeach;
						?>
					</ol>
				<?php endif; ?>
				<br><div><?php echo JText::_('COM_SELLACIOUS_CART_ITEM_SOLD_BY'); ?> <span><?php echo $cartItem->getProperty('seller_store') ? $cartItem->getProperty('seller_store') : ($cartItem->getProperty('seller_name') ? $cartItem->getProperty('seller_name') : ($cartItem->getProperty('seller_company') ? $cartItem->getProperty('seller_company') : $cartItem->getProperty('seller_username'))); ?></span></div>
				<?php if ($er = $cartItem->check()): ?>
				<div class="red"><?php echo implode('<br>', $er); ?></div>
				<?php endif; ?>
			</td>
			<td style="width: 30px;">
				<input type="text" class="input item-quantity" data-uid="<?php echo $cartItem->getUid() ?>"
					   data-value="<?php echo $cartItem->getProperty('quantity') ?>"
					   value="<?php echo $cartItem->getQuantity() ?>" min="1" max="999" title=""/>
			</td>
			<td style="width: 80px;" class="text-right nowrap"><?php
				echo $helper->currency->display($cartItem->getPrice('sales_price'), $g_currency, $c_currency, true); ?>
				&nbsp;
			</td>
			<td style="width: 80px;" class="text-right nowrap"><?php
				echo $helper->currency->display($cartItem->getPrice('sub_total'), $g_currency, $c_currency, true); ?></td>
			<td class="text-center nowrap" style="width: 30px;">
				<a href="#" class="btn-remove-item hasTooltip" data-uid="<?php echo $cartItem->getUid() ?>"
				   title="Remove"><i class="fa fa-times-circle fa-lg"></i></a></td>
		</tr>
		<?php
	}
	?>
	</tbody>
	<tfoot>
		<tr>
			<td colspan="5">
				<a class="btn btn-small btn-default pull-right btn-next margin-5"><?php echo JText::_('COM_SELLACIOUS_PRODUCT_NEXT'); ?> <i class="fa fa-arrow-right"></i></a>
				<a class="btn btn-small pull-left btn-refresh btn-default margin-5"><?php
					echo JText::_('COM_SELLACIOUS_CART_BTN_REFRESH_CART_LABEL') ?> <i class="fa fa-sync"></i> </a>
				<a class="btn btn-small pull-left btn-clear-cart btn-warning margin-5"><?php
					echo JText::_('COM_SELLACIOUS_CART_BTN_CLEAR_CART_LABEL') ?> <i class="fa fa-trash-alt"></i> </a>
				<a class="btn btn-small pull-left btn-close btn-default margin-5"><?php
					echo JText::_('COM_SELLACIOUS_CART_BTN_CLOSE_CART_LABEL') ?> <i class="fa fa-times"></i> </a>
			</td>
		</tr>
	</tfoot>
</table>
