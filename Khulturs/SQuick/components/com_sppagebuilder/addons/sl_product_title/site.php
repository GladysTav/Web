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

class SppagebuilderAddonSL_Product_Title extends SppagebuilderAddons
{

	public function render()
	{

		$class            = (isset($this->addon->settings->class) && $this->addon->settings->class) ? $this->addon->settings->class : '';
		$title            = (isset($this->addon->settings->title) && $this->addon->settings->title) ? $this->addon->settings->title : '';
		$heading_selector = (isset($this->addon->settings->heading_selector) && $this->addon->settings->heading_selector) ? $this->addon->settings->heading_selector : 'h3';
		$show_ratings     = (isset($this->addon->settings->show_ratings) && $this->addon->settings->show_ratings) ? $this->addon->settings->show_ratings : '0';
		$show_seperator     = (isset($this->addon->settings->show_seperator) && $this->addon->settings->show_seperator) ? $this->addon->settings->show_seperator : '0';

		$app     = JFactory::getApplication();
		$jInput  = $app->input;
		$product = $jInput->getInt('product');
		$variant = $jInput->getInt('v');
		$seller  = $jInput->getInt('s');

		$html = '';

		//Options
		if ($product)
		{
			$prodHelper = new \Sellacious\Product($product, $variant, $seller);

			$helper  = SellaciousHelper::getInstance();
			$ratings = $helper->rating->getProductRating($product);

			$variant_title  = $prodHelper->get('variant_title');
			$variant_title  = $variant_title ? '&nbsp;<small>(' . $variant_title . ')</small>' : '';
			$rating_display = (array) $helper->config->get('product_rating_display');

			$html .= '<div class="maintitlearea">';
			$html .= '<div class="product-title">';
			$html .= '<h2>' . $prodHelper->get('title') . $variant_title . '</h2>';

			if ($helper->config->get('product_rating') && (in_array('product', $rating_display)) && $show_ratings)
			{
				$stars = round($ratings->rating * 2);
				$html .= '<div class="product-rating rating-stars star-' . $stars . '">' . number_format($ratings->rating, 1) . '</div>';
			}

			$html .= '</div>';
			$html .= '</div>';

			if ($show_seperator)
			{
				$html .= '<hr class="isolate">';
			}
		}

		//Output
		if ($html)
		{
			$output = '<div class="sppb-addon sppb-addon-product-title ' . $class . '">';
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

