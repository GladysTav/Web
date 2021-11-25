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

use Joomla\Utilities\ArrayHelper;

/** @var  JLayoutFile      $this */
/** @var  stdClass         $displayData */
/** @var  Sellacious\Cart  $cart */
/** @var  array            $forms */

JHtml::_('stylesheet', 'com_sellacious/fe.view.custom-attributes.css', array('relative' => true, 'version' => S_VERSION_CORE));

$forms = $displayData->forms;
$cart  = $displayData->cart;

if (!$cart->hasShippable())
{
	return;
}

$items = $cart->getItems();
?>
<form id="shipment-form" action="index.php" method="post" onsubmit="return false;">
	<?php
		$helper     = SellaciousHelper::getInstance();
		$g_currency = $cart->getCurrency();
		$c_currency = $helper->currency->current('code_3');
	?>
	<div class="ctech-wrapper custom-attributes-container">
		<div class="table">
			<?php
			$row = 'even';

			foreach ($items as $item):

				if (!$item->isShippable())
				{
					continue;
				}

				$row = $row == 'even' ? 'odd' : 'even';

				$uid      = $item->getUid();
				$quantity = $item->getQuantity();
				$title    = trim($item->getProperty('title') . ' - ' . $item->getProperty('variant_title'), '- ');

				/** @var  JForm[]  $itemForms */
				$itemForms  = ArrayHelper::getValue($forms, $uid);
				$shipQuotes = $item->getShipQuotes() ?: array();
				$cQid       = $item->getShipQuoteId();
				$shipNote   = '';
				?>
				<div class="cart-item <?php echo $row ?>">
					<div class="item-image">
						<img class="product-thumb" src="<?php echo $item->getImageUrl(); ?>" alt="">
					</div>
					<div class="item-info">
						<a href="<?php echo $item->getLinkUrl(); ?>"><?php
							?> <?php echo $title ?></a><br>
						<span class="quantity-label"><?php echo JText::plural('COM_SELLACIOUS_ORDER_PREFIX_ITEM_QUANTITY_N',$item->getQuantity()); ?></span>
					</div>
					<div class="item-shippingrule">
						<?php if ($shipQuotes):
							$shipNote = '';

							foreach ($shipQuotes as $quote):
								$quote->selected = $quote->id == $cQid;

								$amount2   = isset($quote->amount2) ? $quote->amount2 : 0.00;
								$ship_amt  = $helper->currency->display($quote->amount, $g_currency, $c_currency, true);
								$ship_amt2 = $helper->currency->display($amount2, $g_currency, $c_currency, true);

								if ($quote->tbd)
								{
									$ship_label = ($quote->serviceName ?: $quote->ruleTitle) . ' (' .  JText::_('COM_SELLACIOUS_TBD') . ')';
								}
								elseif ($quote->total >= 0.01)
								{
									$ship_total = $helper->currency->display($quote->total, $g_currency, $c_currency, true);
									$ship_label = ($quote->serviceName ?: $quote->ruleTitle) . ' (' . $ship_total . ')';
								}
								else
								{
									$ship_total = JText::_('COM_SELLACIOUS_PRODUCT_SHIPPING_FEE_FREE');
									$ship_label = ($quote->serviceName ?: $quote->ruleTitle) . ' &mdash; ' . $ship_total;
								}

								$quote->label = $ship_label;

								if ($quote->selected):

									if (empty($quote->note) && $quantity > 1):
										$note_format = $amount2 ? '@ %s + %s x %d' : '@ %s';
										$quote->note = sprintf($note_format, $ship_amt, $ship_amt2, $quantity - 1);
									endif;

									$shipNote = isset($quote->note) ? $quote->note : '';

								endif;
							endforeach;

							if (count($shipQuotes) > 1): ?>
								<select name="shipment[<?php echo $uid ?>]" class="text-left select-shipment hasSelect2 nowrap w100p" data-uid="<?php echo $uid ?>" title="">
									<option value=""><?php echo JText::_('COM_SELLACIOUS_CART_ITEM_SHIPRULE_SELECT_OPTION') ?></option>
									<?php foreach ($shipQuotes as $quote):?>
										<option value="<?php echo $quote->id ?>" <?php echo $quote->selected ? 'selected' : '' ?>><?php echo $quote->label ?></option><?php
									endforeach;
									?>
								</select>
							<?php else:
								// If only one shipping rule is available for selection, then no need to show it as a dropdown
								foreach ($shipQuotes as $quote): ?>
									<span><?php echo $quote->label; ?></span>
									<input type="hidden" name="shipment[<?php echo $uid ?>]" value="<?php echo $quote->id ?>">
								<?php endforeach;
							endif; ?>

							<div class="center"><span class="label"><?php echo $shipNote; ?></span></div>
						<?php elseif (!$item->getShipping('tbd')):
							$serviceName = $item->getShipping('serviceName');
							$ruleTitle   = $item->getShipping('ruleTitle');
							$shipTotal   = $item->getShipping('total');

							if ($ruleTitle):
								echo $serviceName ? $ruleTitle . ' - ' . $serviceName . ':' : $ruleTitle . ':';
							endif;

							if ($shipTotal >= 0.01)
							{
								echo ' <span> ' . $helper->currency->display($shipTotal, $g_currency, $g_currency, true) . '</span>';
							}
							else
							{
								echo ' <span> ' . JText::_('COM_SELLACIOUS_PRODUCT_SHIPPING_FEE_FREE') . '</span>';
							}

						else:

							echo '<span class="tbd">' . JText::_('COM_SELLACIOUS_TBD') . '</span>';

						endif;
						?>

					</div>
				</div>
				<div class="shipping-form-block"></div>

				<?php
				if (!empty($itemForms)):
					foreach ($itemForms as $qId => $form):
						// If only one shipping rule is available for selection, then show the shipping form by default
						$active = ($qId == $cQid) || (count($itemForms) == 1 && count($shipQuotes) == 1) ? 'active' : '';

						$data = array(
							'form'        => $form,
							'class'       => 'shipment-method-form shipment_form_' . $uid . ' ' . $active . ' ' . $row,
							'id'          => 'shipment_form_' . $uid . '_' . $qId,
							'row'         => $row,
							'cartQuoteId' => $cQid,
						);

						echo JLayoutHelper::render('com_sellacious.cart.shippingform.shipping_form', (object) $data);
					endforeach;

				endif;

			endforeach;
			?>
		</div>
	</div>
</form>
<a class="ctech-btn ctech-btn-sm ctech-btn-primary ctech-rounded-circle btn-next ctech-text-white ctech-float-right"><i class="fa fa-arrow-right"></i></a>
<?php
