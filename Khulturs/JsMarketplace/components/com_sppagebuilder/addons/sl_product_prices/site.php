<?php
/**
 * @version     2.2.0
 * @package     SP Page Builder Addons for Sellacious
 *
 * @copyright   Copyright (C) 2016. Bhartiy Web Technologies. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Bhavika Matariya <info@bhartiy.com> - http://www.bhartiy.com
 */

//no direct access
defined('_JEXEC') or die ('restricted aceess');

use Sellacious\Product;
use Sellacious\Seller;

class SppagebuilderAddonSL_Product_Prices extends SppagebuilderAddons
{

	public function render()
	{

		$class            = (isset($this->addon->settings->class) && $this->addon->settings->class) ? $this->addon->settings->class : '';
		$title            = (isset($this->addon->settings->title) && $this->addon->settings->title) ? $this->addon->settings->title : '';
		$heading_selector = (isset($this->addon->settings->heading_selector) && $this->addon->settings->heading_selector) ? $this->addon->settings->heading_selector : 'h3';
		$price_table_title  = (isset($this->addon->settings->price_table_title) && $this->addon->settings->price_table_title) ? $this->addon->settings->price_table_title : '';
		$price_table_bordercolor  = (isset($this->addon->settings->price_table_bordercolor) && $this->addon->settings->price_table_bordercolor) ? $this->addon->settings->price_table_bordercolor : '';
		$price_table_titlebg  = (isset($this->addon->settings->price_table_titlebg) && $this->addon->settings->price_table_titlebg) ? $this->addon->settings->price_table_titlebg : '';

		$app     = JFactory::getApplication();
		$input  = $app->input;
		$product = $input->getInt('product');
		$seller  = $input->getInt('s');
		$html    = '';
		$helper  = SellaciousHelper::getInstance();

		if ($seller)
		{
			$prodHelper = new Sellacious\Product($product, 0, $seller);

			$prices     = $prodHelper->getPrices($seller);
			$c_currency = $helper->currency->current('code_3');
			$s_currency = $helper->currency->forSeller($seller, 'code_3');
			ob_start();

			if (is_array($prices) && count($prices) > 1)
			{
				$borderStyle = '';
				$bgStyle = '';
				if ($price_table_bordercolor)
				{
					$borderStyle = 'style="border-color: ' . $price_table_bordercolor . '"';
				}

				if ($price_table_titlebg)
				{
					$bgStyle = 'style="background-color: ' . $price_table_titlebg . '"';
				}

			?>
				<table class="w100p price-list table-striped" <?php echo $borderStyle; ?> >
					<?php
					echo ($price_table_title) ? '<tr><th class="nowrap" colspan="2" ' . $bgStyle . '>' . $price_table_title . '</th></tr>' : '';
					foreach ($prices as $price)
					{
						if ($price->qty_min && $price->qty_max)
						{
							$label = "$price->qty_min to $price->qty_max";
						}
						elseif ($price->qty_min && !$price->qty_max)
						{
							$label = "Above $price->qty_min";
						}
						elseif (!$price->qty_min && $price->qty_max)
						{
							$label = "Below $price->qty_max";
						}
						elseif ($price->is_fallback == 0)
						{
							$label = 'Offer Price';
						}
						elseif ($price->is_fallback)
						{
							$label = 'Default Price';
						}
						else
						{
							$label = 'Standard Price';
						}

						if ($price->client_category)
						{
							$label .= '<br/><small style="color: #666;">' . $price->client_category . '<span class="red">*</span></small>';
						}
						else
						{
							// $label .= '<br/><small style="color: #666;">' . JText::_('COM_SELLACIOUS_PRODUCT_PRICE_ALL_CUSTOMER') . '</small>';
						}
						?>
						<tr>
							<td style="line-height: 1.1;"><?php echo $label; ?></td>
							<td class="nowrap"><?php echo $helper->currency->display($price->sales_price, $s_currency, $c_currency, true); ?></td>
						</tr>
						<?php
					}
					?>
				</table>
				<?php
			}
			?>
			<div class="clearfix"></div>

			<?php
			$html = ob_get_clean();
		}

		//Output
		if ($html)
		{
			$output = '<div class="sppb-addon sppb-addon-product-prices ' . $class . '">';
			$output .= ($title) ? '<' . $heading_selector . ' class="sppb-addon-title">' . $title . '</' . $heading_selector . '>' : '';
			$output .= '<div class="sppb-addon-content">';
			$output .= $html;
			$output .= '</div>';
			$output .= '</div>';

			return $output;
		}

		return;
	}

	public function stylesheets()
	{
		return array(JURI::base(true) . '/components/com_sppagebuilder/assets/css/sellacious/sl-productstyle.css');
	}

}
