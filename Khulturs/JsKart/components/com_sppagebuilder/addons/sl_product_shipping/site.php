<?php
/**
 * @version     2.1.4
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

class SppagebuilderAddonSL_Product_Shipping extends SppagebuilderAddons
{

	public function render()
	{

		$class            = (isset($this->addon->settings->class) && $this->addon->settings->class) ? $this->addon->settings->class : '';
		$title            = (isset($this->addon->settings->title) && $this->addon->settings->title) ? $this->addon->settings->title : '';
		$heading_selector = (isset($this->addon->settings->heading_selector) && $this->addon->settings->heading_selector) ? $this->addon->settings->heading_selector : 'h3';
		$shipping_icon	= (isset($this->addon->settings->shipping_icon) && $this->addon->settings->shipping_icon) ? $this->addon->settings->shipping_icon : '';
		$shipping_icon_color  = (isset($this->addon->settings->shipping_icon_color) && $this->addon->settings->shipping_icon_color) ? $this->addon->settings->shipping_icon_color : '';

		$app     = JFactory::getApplication();
		$jInput  = $app->input;
		$product = $jInput->getInt('product');
		$seller  = $jInput->getInt('s');
		$html    = '';
		$helper  = SellaciousHelper::getInstance();

		if ($seller)
		{
			$prodHelper = new Sellacious\Product($product, 0, $seller);

			$seller_attr = $prodHelper->getSellerAttributes($seller);
			$c_currency  = $helper->currency->current('code_3');
			$s_currency  = $helper->currency->forSeller($seller, 'code_3');
			$result      = $helper->product->getItem($product);

			ob_start();

			if ($helper->config->get('show_shipping_info_on_detail') && $prodHelper->get('type') !== 'electronic'): ?>
			<div class="text-left product-ship-cost">
				<?php
				$iconColor = '';
				if ($shipping_icon_color)
				{
					$iconColor = 'style="color: ' . $shipping_icon_color . '"';
				}
				echo ($shipping_icon) ? '<i class="fa ' . $shipping_icon . '"' . $iconColor . '></i> ' : '';
				$flat_ship = $seller_attr->flat_shipping;
				$ship_fee  = $seller_attr->shipping_flat_fee;

				if ($flat_ship == 0)
				{
					echo JText::_('COM_SELLACIOUS_PRODUCT_SHIPPING_FEE_IN_CART');
				}
				elseif (round($ship_fee, 2) > 0)
				{
					$fee = $helper->currency->display($ship_fee, $s_currency, $c_currency, true);

					echo JText::sprintf('COM_SELLACIOUS_PRODUCT_SHIPPING_FEE_FLAT', $fee);
				}
				else
				{
					echo JText::_('COM_SELLACIOUS_PRODUCT_SHIPPING_FEE_FREE');
				}
				?>
			</div>

			<?php endif;

			$html = ob_get_clean();
		}

		//Output
		if ($html)
		{
			$output = '<div class="sppb-addon sppb-addon-product-shipping ' . $class . '">';
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
