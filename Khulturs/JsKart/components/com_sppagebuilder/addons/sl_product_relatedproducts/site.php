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
use Sellacious\Cache\Reader\ProductsCacheReader;
class SppagebuilderAddonSL_Product_RelatedProducts extends SppagebuilderAddons
{

	public function render()
	{

		$class            = (isset($this->addon->settings->class) && $this->addon->settings->class) ? $this->addon->settings->class : '';
		$title            = (isset($this->addon->settings->title) && $this->addon->settings->title) ? $this->addon->settings->title : '';
		$title_alignment  = (isset($this->addon->settings->title_alignment) && $this->addon->settings->title_alignment) ? $this->addon->settings->title_alignment : '';
		$heading_selector = (isset($this->addon->settings->heading_selector) && $this->addon->settings->heading_selector) ? $this->addon->settings->heading_selector : 'h3';
		$box_title        = (isset($this->addon->settings->relatedproduct_title) && $this->addon->settings->relatedproduct_title) ? $this->addon->settings->relatedproduct_title : '';
		$limit            = (isset($this->addon->settings->total_products) && $this->addon->settings->total_products) ? $this->addon->settings->total_products : '50';
		$product_features = (isset($this->addon->settings->product_features) && $this->addon->settings->product_features) ? $this->addon->settings->product_features : 'hide';
		$layout           = (isset($this->addon->settings->layout) && $this->addon->settings->layout) ? $this->addon->settings->layout : 'grid';
		$autoplay         = (isset($this->addon->settings->autoplay) && $this->addon->settings->autoplay) ? $this->addon->settings->autoplay : '0';
		$autoplayspeed    = (isset($this->addon->settings->autoplayspeed) && $this->addon->settings->autoplayspeed) ? $this->addon->settings->autoplayspeed : '3000';

		$app     = JFactory::getApplication();
		$product = (int) $app->input->getInt('product');
		$seller  = (int) $app->input->getInt('s');
		$html    = '';

		$helper = SellaciousHelper::getInstance();

		$groups          = $helper->relatedProduct->loadColumn(array('list.select' => 'a.group_alias', 'product_id' => $product));
		$relatedProducts = $helper->relatedProduct->loadColumn(array(
			'list.select' => 'a.product_id',
			'group_alias' => $groups,
			'list.where'  => 'a.product_id !=' . $product,
			'list.start'  => 0,
			'list.limit'  => $limit,
		));
		shuffle($relatedProducts);

		if ($relatedProducts)
		{
			JHtml::_('script', 'com_sellacious/util.modal.js', false, true);
			JHtml::_('stylesheet', 'com_sellacious/util.modal.css', null, true);

			if ($helper->config->get('product_compare')):
				JHtml::_('script', 'com_sellacious/util.compare.js', false, true);
			endif;

			JHtml::_('script', 'com_sellacious/util.cart.aio.js', false, true);
			JHtml::_('script', 'com_sellacious/fe.view.products.js', false, true);


			$html .= '<div class="moreinfo-box">';
			$html .= ($box_title) ? '<h3>' . $box_title . '</h3>' : '';
			$html .= '<div class="innermoreinfo">';
			$html .= '<div class="sl-relatedproducts-box">';

			$me    = JFactory::getUser();
			$c_cat = $helper->client->loadResult(array('list.select' => 'category_id', 'user_id' => $me->id));
			$products = array();
			$loader = new ProductsCacheReader;

			$loader->filterValue('product_id', $relatedProducts, 'IN');

			$relatedItems = $loader->getItems();

			$args = array('id' => 'productsList', 'items' => $this->parseProducts($relatedItems), 'context' => array('products'), 'layout' => $layout);
			$html .= JLayoutHelper::render('sellacious.product.grid', $args);
			$html .= '<div class="clearfix"></div>';
			$html .= '</div>';
			$html .= '</div>';
			$html .= '</div>';


		}

		//Output
		if ($html)
		{
			$output = '<div class="sppb-addon sppb-addon-category-desc ' . $class . '">';
			$output .= ($title) ? '<' . $heading_selector . ' class="sppb-addon-title text-' . $title_alignment . '">' . $title . '</' . $heading_selector . '>' : '';
			$output .= '<div class="sppb-addon-content">';
			$output .= $html;
			$output .= '</div>';
			$output .= '</div>';

			return $output;
		}

		return;
	}

	public function parseProducts($products)
	{
		$helper = SellaciousHelper::getInstance();

		$g_currency = $helper->currency->getGlobal('code_3');
		$c_currency = $helper->currency->current('code_3');

		$items = array();

		foreach ($products as $item)
		{
			$item->category         = json_decode($item->category, true);
			$item->category_ext     = json_decode($item->category_ext, true);
			$item->spl_category     = json_decode($item->spl_category, true);
			$item->specifications   = json_decode($item->specifications, true);
			$item->product_rating   = json_decode($item->product_rating);
			$item->product_features = json_decode($item->product_features);
			$item->display_price    = $helper->currency->display($item->sales_price, $g_currency, $c_currency, true);

			$items[] = $item;
		}

		return $items;
	}

	public function stylesheets()
	{
		$layout = (isset($this->addon->settings->layout) && $this->addon->settings->layout) ? $this->addon->settings->layout : 'grid';
		if ($layout == 'carousel')
		{
			return array(
				JURI::base(true) . '/components/com_sppagebuilder/assets/css/sellacious/owl.carousel.min.css',
				JURI::base(true) . '/components/com_sppagebuilder/assets/css/sellacious/sl-relatedproducts.css',
			);
		}
		else
		{
			return array(
				JURI::base(true) . '/components/com_sppagebuilder/assets/css/sellacious/sl-relatedproducts.css',
			);
		}

	}

	public function scripts()
	{
		return array(JURI::base(true) . '/components/com_sppagebuilder/assets/js/sellacious/owl.carousel.js');
	}

}
