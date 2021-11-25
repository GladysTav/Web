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
use Sellacious\Price\PriceHelper;

defined('_JEXEC') or die ('restricted aceess');

class SppagebuilderAddonSL_Product_Price extends SppagebuilderAddons
{

	public function render()
	{

		$class            = (isset($this->addon->settings->class) && $this->addon->settings->class) ? $this->addon->settings->class : '';
		$title            = (isset($this->addon->settings->title) && $this->addon->settings->title) ? $this->addon->settings->title : '';
		$heading_selector = (isset($this->addon->settings->heading_selector) && $this->addon->settings->heading_selector) ? $this->addon->settings->heading_selector : 'h3';

		$app     = JFactory::getApplication();
		$input  = $app->input;
		$product = $input->getInt('product');
		$variant = $input->getInt('v');
		$seller  = $input->getInt('s');

		$html = '';

		$helper = SellaciousHelper::getInstance();

		//Options
		if ($product)
		{
			JHtml::_('script', 'com_sellacious/util.cart.aio.js', false, true);
			JHtml::_('script', 'com_sellacious/fe.view.products.js', false, true);

			if (empty($seller))
			{
				$seller = $helper->product->getSellers($product, false);
				$seller = $seller[0]->seller_uid;
			}

			$me    = JFactory::getUser();
			$c_cat = $helper->client->loadResult(array('list.select' => 'category_id', 'user_id' => $me->id));

			$prodHelper = new Sellacious\Product($product, $variant, $seller);

			$item  = $helper->product->getItem($product);
			$price = (object) $prodHelper->getPrice($seller, 1, $c_cat);
			$code  = $prodHelper->getCode($seller);
			$seller_attr = $prodHelper->getSellerAttributes($seller);

			$sellerHelper = new Sellacious\Seller($seller);
			$sellerInfo = $sellerHelper->getAttributes();

			$s_currency = $helper->currency->forSeller($price->seller_uid, 'code_3');
			$c_currency = $helper->currency->current('code_3');

			if (!is_object($seller_attr))
			{
				$seller_attr                 = new stdClass();
				$seller_attr->price_display  = 0;
				$seller_attr->stock_capacity = 0;

			}

			$item = array_merge((array) $item, (array) $price);
			$item = (object) $item;
			$item->price = $price;
			$item->code = $code;

			$item->price = $price;

			$item->seller_email  = $sellerInfo['email'];
			$item->seller_mobile = $sellerInfo['mobile'];

			ob_start();?>
			<div class="ctech-wrapper"><?php
			$priceHandler  = PriceHelper::getHandler($seller_attr->pricing_type);
			echo $priceHandler->renderLayout('price.default', new \Joomla\Registry\Registry($item));

			$html = ob_get_clean();
		}

		//Output
		if ($html)
		{
			$output = '<div class="sppb-addon sppb-addon-product-price ' . $class . '">';
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
