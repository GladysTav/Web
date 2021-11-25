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

/** @var  Registry $displayData */
$registry  = $displayData;
$helper    = SellaciousHelper::getInstance();
$me        = JFactory::getUser();
$reqLogin  = $helper->config->get('login_to_see_price');
$showPrice = $helper->config->inList('product', 'product_price_display_pages');

if (!($reqLogin && $me->guest) && $showPrice)
{
	$prices = $registry->get('prices');

	if (is_array($prices) && count($prices) > 1)
	{
		$c_currency = $helper->currency->current('code_3');
		$s_currency = $helper->currency->forSeller($registry->get('seller_uid'), 'code_3');
		?>
		<div class="ctech-clearfix"></div>
		<table class="w100p price-list ctech-table-striped">
			<tr>
				<th class="nowrap" colspan="2"><?php echo JText::_('COM_SELLACIOUS_PRODUCT_PRICE_OFFER'); ?></th>
			</tr>
			<?php
			foreach ($prices as $price)
			{
				if ($price->qty_min && $price->qty_max)
				{
					$label = JText::sprintf('COM_SELLACIOUS_PRODUCT_PRICE_QUANTITY_MIN_TO_MAX', $price->qty_min, $price->qty_max);
				}
				elseif ($price->qty_min && !$price->qty_max)
				{
					$label = JText::sprintf('COM_SELLACIOUS_PRODUCT_PRICE_ABOVE_QUANTITY_MIN', $price->qty_min);
				}
				elseif (!$price->qty_min && $price->qty_max)
				{
					$label = JText::sprintf('COM_SELLACIOUS_PRODUCT_PRICE_BELOW_QUANTITY_MAX', $price->qty_max);
				}
				elseif ($price->is_fallback == 0)
				{
					$label = JText::_('COM_SELLACIOUS_PRODUCT_PRICE_OFFER_PRICE');
				}
				elseif ($price->is_fallback)
				{
					$label = JText::_('COM_SELLACIOUS_PRODUCT_PRICE_DEFAULT_PRICE');
				}
				else
				{
					$label = JText::_('COM_SELLACIOUS_PRODUCT_PRICE_STANDARD_PRICE');
				}

				if ($price->client_category)
				{
					$label .= sprintf('<br/><small style="color: #666;">%s<span class="red">*</span></small>', $price->client_category);
				}
				?>
				<tr>
					<td style="line-height: 1.1;" class="productQuantity"><strong><?php echo $label; ?></strong></td>
					<td class="nowrap priceForQuantity">
						<?php
						if (round($price->sales_price, 2) >= 0.01)
						{
							try
							{
								echo $helper->currency->display($price->sales_price, $s_currency, $c_currency, true);
							}
							catch (Exception $e)
							{
							}
						}
						else
						{
							echo JText::_('COM_SELLACIOUS_PRODUCT_PRICE_FREE');
						}
						?>
					</td>
				</tr>
				<?php
			}
			?>
		</table>
		<?php
	}
}
