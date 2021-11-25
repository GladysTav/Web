<?php
/**
 * @version     2.0.0
 * @package     sellacious
 *
 * @copyright   Copyright (C) 2012-2020 Bhartiy Web Technologies. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Aditya Chakraborty <info@bhartiy.com> - http://www.bhartiy.com
 */

// no direct access
use Joomla\Utilities\ArrayHelper;

defined('_JEXEC') or die;

/** @var  stdClass $displayData */
/** @var  Sellacious\Cart $cart */
/** @var  array $forms */
$forms = $displayData->forms;
$cart  = $displayData->cart;

if (!$cart->hasShippable())
{
	return;
}

$sellers = $cart->getSellers();
?>
<form id="shipment-form" action="index.php" method="post" onsubmit="return false;">
	<?php
	$helper     = SellaciousHelper::getInstance();
	$g_currency = $cart->getCurrency();
	$c_currency = $helper->currency->current('code_3');
	?>
	<table class="table">
		<?php
		$row = 'even';

		/** @var \Sellacious\Cart\Seller $seller */
		foreach ($sellers as $sellerUid => $seller)
		{
			$row   = $row == 'even' ? 'odd' : 'even';
			$items = $seller->getItems();

			/** @var  JForm[] $itemItemForms */
			$itemForms = ArrayHelper::getValue($forms, $sellerUid);
			$cQid      = $cart->getSellerShipQuoteId($sellerUid);
			$shipNote  = '';

			$shipQuotes = (array) $cart->getSellerShipQuotes($sellerUid) ?: array();
			?>
			<tr class="cart-item <?php echo $row ?>">
				<td style="width: 1%; ">&nbsp;</td>
				<td style="width: 65%;">
					<?php echo JText::_('COM_SELLACIOUS_CART_ITEM_SOLD_BY'); ?>
					<a href="<?php echo $seller->getStoreLink() ?>"><?php
						?><?php echo $seller->getSellerName() ?></a>
					<table>
						<?php
						foreach ($items as $item):
							if (!$item->isShippable())
							{
								continue;
							}

							$uid      = $item->getUid();
							$quantity = $item->getQuantity();
							$title    = trim($item->getProperty('title') . ' - ' . $item->getProperty('variant_title'), '- ');
							?>
							<tr>
								<td>
									<img class="product-thumb" src="<?php echo $item->getImageUrl(); ?>" alt="">
								</td>
								<td style="width: 90%;">
									<a href="<?php echo $item->getLinkUrl(); ?>"><?php
										?><?php echo $title ?></a><br>
									<span><?php echo JText::sprintf('COM_SELLACIOUS_ORDER_PREFIX_ITEM_QUANTITY_N', $item->getQuantity()) ?></span>
								</td>
							</tr>
						<?php
						endforeach;
						?>
					</table>
				</td>
				<td class="center">
					<?php if (!empty($shipQuotes)): ?>
						<?php if (count($shipQuotes) > 1): ?>
							<select name="seller_shipment[<?php echo $sellerUid ?>]"
							        class="text-left select-seller-shipment hasSelect2 nowrap w100p"
							        data-seller-uid="<?php echo $sellerUid ?>" title="">
								<option
									value=""><?php echo JText::_('COM_SELLACIOUS_CART_ITEM_SHIPRULE_SELECT_OPTION') ?></option>
								<?php
								foreach ($shipQuotes as $quote):

									$ship_sel = $quote->id == $cQid ? 'selected' : '';
									$ship_amt = $helper->currency->display($quote->amount, $g_currency, $c_currency, true);
									$ship_amt2 = $helper->currency->display((isset($quote->amount2) ? $quote->amount2 : 0.00), $g_currency, $c_currency, true);

									if ($quote->total >= 0.01)
									{
										$ship_total = $helper->currency->display($quote->total, $g_currency, $c_currency, true);
										$ship_label = ($quote->serviceName ?: $quote->ruleTitle) . ' (' . $ship_total . ')';
									}
									else
									{
										$ship_total = JText::_('COM_SELLACIOUS_PRODUCT_SHIPPING_FEE_FREE');
										$ship_label = ($quote->serviceName ?: $quote->ruleTitle) . ' &mdash; ' . $ship_total;
									}

									if ($ship_sel):
										if (empty($quote->note) && $quantity > 1):
											$note_format = $quote->amount2 ? '@ %s + %s x %d' : '@ %s';
											$quote->note = sprintf($note_format, $ship_amt, $ship_amt2, $quantity - 1);
										endif;

										$shipNote = isset($quote->note) ? $quote->note : '';

									endif;

									?>
									<option
									value="<?php echo $quote->id ?>" <?php echo $ship_sel ?>><?php echo $ship_label ?></option><?php

								endforeach;
								?>
							</select>
						<?php else:
							// If only one shipping rule is available for selection, then no need to show it as a dropdown
							foreach ($shipQuotes as $quote):
								if ($quote->total >= 0.01)
								{
									$ship_total = $helper->currency->display($quote->total, $g_currency, $c_currency, true);
									$ship_label = ($quote->serviceName ?: $quote->ruleTitle) . ' (' . $ship_total . ')';

									echo ' <span> ' . $ship_label . '</span>';
								}
								else
								{
									$ship_total = JText::_('COM_SELLACIOUS_PRODUCT_SHIPPING_FEE_FREE');
									$ship_label = ($quote->serviceName ?: $quote->ruleTitle) . ' &mdash; ' . $ship_total;

									echo ' <span> ' . $ship_label . '</span>';
								}

								$ship_amt  = $helper->currency->display($quote->amount, $g_currency, $c_currency, true);
								$ship_amt2 = $helper->currency->display((isset($quote->amount2) ? $quote->amount2 : 0.00), $g_currency, $c_currency, true);

								if (empty($quote->note) && $quantity > 1):
									$note_format = (isset($quote->amount2) ? $quote->amount2 : 0.00) ? '@ %s + %s x %d' : '@ %s';
									$quote->note = sprintf($note_format, $ship_amt, $ship_amt2, $quantity - 1);
								endif;

								$shipNote = isset($quote->note) ? $quote->note : '';
								?>
								<input type="hidden" name="seller_shipment[<?php echo $sellerUid ?>]"
								       value="<?php echo $quote->id ?>">
							<?php
							endforeach;
							?>
						<?php endif;
					else:
						echo '<div class="center padding-10">' . JText::_('COM_SELLACIOUS_CART_NO_SHIPPING_METHOD_AVAILABLE_FOR_SELLER') . '</div>';
					endif; ?>
				</td>
			</tr>
			<?php
			if (!empty($itemForms)):

				foreach ($itemForms as $qId => $form):

					// If only one shipping rule is available for selection, then show the shipping form by default
					$active = ($qId == $cQid) || (count($shipQuotes) == 1) ? 'active' : '';
					$class  = 'seller-shipment-method-form seller_shipment_form_' . $sellerUid . ' ' . $active . ' ' . $row;
					$id     = 'seller_shipment_form_' . $sellerUid . '_' . $qId;
					$data   = array(
						'form'  => $form,
						'class' => $class,
						'id'    => $id,
						'row'   => $row,
					);

					echo JLayoutHelper::render('com_sellaciousopc.opc.shippingform.shipping_form', (object)$data);

				endforeach;

			endif;
		}
		?>
	</table>
</form>
