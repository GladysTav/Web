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

class SppagebuilderAddonSL_Product_Specs extends SppagebuilderAddons
{

	public function render()
	{

		$class            = (isset($this->addon->settings->class) && $this->addon->settings->class) ? $this->addon->settings->class : '';
		$title            = (isset($this->addon->settings->title) && $this->addon->settings->title) ? $this->addon->settings->title : '';
		$heading_selector = (isset($this->addon->settings->heading_selector) && $this->addon->settings->heading_selector) ? $this->addon->settings->heading_selector : 'h3';
		$box_title        = (isset($this->addon->settings->specs_title) && $this->addon->settings->specs_title) ? $this->addon->settings->specs_title : '';

		$app     = JFactory::getApplication();
		$jInput  = $app->input;
		$product = $jInput->getInt('product');
		$variant = $jInput->getInt('v');
		$seller  = $jInput->getInt('s');
		$html    = '';

		if ($product)
		{
			$helper = new Sellacious\Product($product, $variant, $seller);

			$specGroups = $helper->getSpecifications();

			if (!empty($specGroups))
			{
				$html = '<div class="moreinfo-box">';
				$html .= ($box_title) ? '<h3>' . $box_title . '</h3>' : '';
				$html .= '<div class="innermoreinfo">';
				$html .= '<div class="specificationgroup">';

				foreach ($specGroups as $specGroup)
				{
					if (!empty($specGroup['fields']))
					{
						$html .= '<h4>' . $specGroup['group_title'] . '</h4>';
						$html .= '<dl class="dl-horizontal dl-leftside">';

						foreach ($specGroup['fields'] as $field)
						{
							$value = is_array($field->value) ? implode(', ', $field->value) : $field->value;

							$html .= '<dt>' . $field->title . '</dt>';
							$html .= '<dd>' . $value . '</dd>';
						}
						$html .= '</dl>';
					}
				}
				$html .= '</div>';
				$html .= '</div>';
				$html .= '</div>';
			}
		}

		//Output
		if ($html)
		{
			$output = '<div class="sppb-addon sppb-addon-product-specs ' . $class . '">';
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
