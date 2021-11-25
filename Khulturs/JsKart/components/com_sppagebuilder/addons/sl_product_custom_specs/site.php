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

class SppagebuilderAddonSL_Product_Custom_Specs extends SppagebuilderAddons
{

	public function render()
	{

		$class            = (isset($this->addon->settings->class) && $this->addon->settings->class) ? $this->addon->settings->class : '';
		$title            = (isset($this->addon->settings->title) && $this->addon->settings->title) ? $this->addon->settings->title : '';
		$heading_selector = (isset($this->addon->settings->heading_selector) && $this->addon->settings->heading_selector) ? $this->addon->settings->heading_selector : 'h3';
		$box_title        = (isset($this->addon->settings->custom_specs_title) && $this->addon->settings->custom_specs_title) ? $this->addon->settings->custom_specs_title : '';
		$rHtml            = (isset($this->addon->settings->html) && $this->addon->settings->html) ? $this->addon->settings->html : '';

		$app     = JFactory::getApplication();
		$jInput  = $app->input;
		$product = $jInput->getInt('product');
		$html    = '';

		if ($product)
		{
			$helper = new Sellacious\Product($product);
			$specs  = $helper->getSpecifications(false);

			if (!empty($specs) && !empty($rHtml))
			{
				$html .= '<div class="moreinfo-box">';
				$html .= ($box_title) ? '<h3>' . $box_title . '</h3>' : '';
				$html .= '<div class="innermoreinfo">';
				$html .= '<div class="specificationgroup">';

				$attrID  = \Joomla\Utilities\ArrayHelper::getColumn($specs, 'id');

				$attrVal = \Joomla\Utilities\ArrayHelper::getColumn($specs, 'value');
				$attrTitle = \Joomla\Utilities\ArrayHelper::getColumn($specs, 'title');

				$regex1 = '!\{field_id=(.*?)\}!i';
				preg_match_all($regex1, $rHtml, $matches1, PREG_SET_ORDER);
				$regex2 = '!\{label_id=(.*?)\}!i';
				preg_match_all($regex2, $rHtml, $matches2, PREG_SET_ORDER);

				if (is_array($matches1) && count($matches1))				{

					foreach ($matches1 as $match)
					{
						$key = array_search($match[1], $attrID);

						if ($key >= 0 )
						{
							$value = is_array($attrVal[$key]) ? implode(', ', $attrVal[$key]) : $attrVal[$key];

						}else{
							$value = "Not Available";

						}
						$rHtml = str_replace($match[0], $value, $rHtml);
					}
				}
				if (is_array($matches2) && count($matches2))
				{
					foreach ($matches2 as $match)
					{
						$key = array_search($match[1], $attrID);

						if ($key >= 0)
						{
							$rHtml = str_replace($match[0], $attrTitle[$key], $rHtml);
						}else{
							$rHtml = str_replace($match[0], "Not Available", $rHtml);
						}
					}
				}

				$html .= $rHtml;
				$html .= '</div>';
				$html .= '</div>';
				$html .= '</div>';
			}

		}

		//Output
		if ($html)
		{
			$output = '<div class="sppb-addon sppb-addon-category-desc ' . $class . '">';
			$output .= ($title) ? '<' . $heading_selector . ' class="sppb-addon-title">' . $title . '</' . $heading_selector . '>' : '';
			$output .= '<div class="sppb-addon-content">';
			$output .= $html;
			$output .= '</div>';
			$output .= '</div>';

			return $output;
		}

		return;
	}

}
