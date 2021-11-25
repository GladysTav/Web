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

			/** @var  JForm[] $sellerItemForms */
			$sellerItemForms = ArrayHelper::getValue($forms, $sellerUid);
			$shipNote        = '';

			$shipQuotes = (array)$cart->getSellerShipQuotes($sellerUid) ?: array();
			?>
		<tr class="cart-item <?php echo $row ?>" data-seller-uid="<?php echo $sellerUid; ?>">
			<td style="width: 1%; ">&nbsp;</td>
			<td style="width: 65%;">
				<?php echo JText::_('COM_SELLACIOUS_CART_ITEM_SOLD_BY'); ?>
				<a href="<?php echo $seller->getStoreLink() ?>"><?php
					?><?php echo $seller->getSellerName() ?></a>
				<table>
					<tbody>
					<?php
					foreach ($items as $item):
						if (!$item->isShippable())
						{
							continue;
						}

						$uid      = $item->getUid();
						$quantity = $item->getQuantity();
						$title    = trim($item->getProperty('title') . ' - ' . $item->getProperty('variant_title'), '- ');
						$cQid     = $cart->getSellerShipQuoteId($sellerUid, $uid);
						?>
						<tr>
							<td>
								<img class="product-thumb" src="<?php echo $item->getImageUrl(); ?>" alt="">
							</td>
							<td style="width: 70%;">
								<a href="<?php echo $item->getLinkUrl(); ?>"><?php
									?><?php echo $title ?></a><br>
								<span><?php echo JText::sprintf('COM_SELLACIOUS_ORDER_PREFIX_ITEM_QUANTITY_N', $item->getQuantity()) ?></span>
							</td>
							<td style="">
								<?php if (isset($shipQuotes[$uid]) && !empty($shipQuotes[$uid])): ?>
									<?php if (count($shipQuotes[$uid]) > 1): ?>
										<select name="seller_shipment[<?php echo $sellerUid ?>][<?php echo $uid; ?>]"
										        class="text-left select-seller-shipment hasSelect2 nowrap w100p"
										        data-seller-uid="<?php echo $sellerUid ?>"
										        data-item-uid="<?php echo $uid ?>" title="">
											<option
												value=""><?php echo JText::_('COM_SELLACIOUS_CART_ITEM_SHIPRULE_SELECT_OPTION') ?></option>
											<?php
											foreach ($shipQuotes[$uid] as $quote):

												$ship_sel = $quote->id == $cQid ? 'selected' : '';

												if ($quote->total >= 0.01)
												{
													$ship_label = ($quote->serviceName ?: $quote->ruleTitle);
												}
												else
												{
													$ship_label = ($quote->serviceName ?: $quote->ruleTitle);
												}

												?>
												<option
												value="<?php echo $quote->id ?>" <?php echo $ship_sel ?>><?php echo $ship_label ?></option><?php

											endforeach;
											?>
										</select>
									<?php else:
										// If only one shipping rule is available for selection, then no need to show it as a dropdown
										foreach ($shipQuotes[$uid] as $quote):
											if ($quote->total >= 0.01)
											{
												$ship_label = ($quote->serviceName ?: $quote->ruleTitle);

												echo ' <span> ' . $ship_label . '</span>';
											}
											else
											{
												$ship_label = ($quote->serviceName ?: $quote->ruleTitle);

												echo ' <span> ' . $ship_label . '</span>';
											}
											?>
											<input type="hidden"
											       name="seller_shipment[<?php echo $sellerUid ?>][<?php echo $uid; ?>]"
											       value="<?php echo $quote->id ?>">
										<?php
										endforeach;
										?>
									<?php endif; ?>
								<?php
								else:

									echo '<span class="tbd">' . JText::_('COM_SELLACIOUS_TBD') . '</span>';
								endif; ?>
							</td>
						</tr>
						<?php
						if (isset($sellerItemForms[$uid]) && !empty($sellerItemForms[$uid])):
							$itemForms = $sellerItemForms[$uid];

							foreach ($itemForms as $qId => $form):
								// If only one shipping rule is available for selection, then show the shipping form by default
								$active = ($qId == $cQid) ? 'active' : '';
								$class  = 'seller-shipment-method-form seller_shipment_form_' . $sellerUid . '_' . $uid . ' ' . $active . ' ' . $row;
								$id     = 'seller_shipment_form_' . $sellerUid . '_' . $uid . '_' . $qId;
								$data   = array(
									'form'  => $form,
									'class' => $class,
									'id'    => $id,
									'row'   => $row,
								);

								echo JLayoutHelper::render('com_sellaciousopc.opc.shippingform.shipping_form', (object)$data);
							endforeach;
						endif;
						?>
					<?php endforeach; ?>
					</tbody>
				</table>
			</td>
			</tr><?php
		}
		?>
	</table>
</form>
