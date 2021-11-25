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

class SppagebuilderAddonSL_View_ProductRatings extends SppagebuilderAddons
{

	public function render()
	{

		$class            = (isset($this->addon->settings->class) && $this->addon->settings->class) ? $this->addon->settings->class : '';
		$title            = (isset($this->addon->settings->title) && $this->addon->settings->title) ? $this->addon->settings->title : '';
		$heading_selector = (isset($this->addon->settings->heading_selector) && $this->addon->settings->heading_selector) ? $this->addon->settings->heading_selector : 'h3';

		$app     = JFactory::getApplication();
		$input  = $app->input;
		$product = $input->getInt('product');

		$html = '';

		//Options
		if ($product)
		{
			$helper  = SellaciousHelper::getInstance();
			$ratings = $helper->rating->getProductRating($product);

			$rating_display = (array) $helper->config->get('product_rating_display');

			$html .= '<div class="product-ratings">';

			if ($helper->config->get('product_rating') && (in_array('product', $rating_display)))
			{
				$stars = round($ratings->rating * 2);
				$html .= '<div class="product-rating rating-stars star-' . $stars . '">' . number_format($ratings->rating, 1) . '</div>';
			}

			$html .= '</div>';
		}

		//Output
		if ($html)
		{
			$output = '<div class="sppb-addon sppb-addon-view-product-ratings ' . $class . '">';
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
		return array(
			JURI::base(true) . '/components/com_sppagebuilder/assets/css/sellacious/sl-productstyle.css',
			JURI::base(true) . '/components/com_sppagebuilder/assets/css/sellacious/sl-ratings.css'
		);
	}
}

