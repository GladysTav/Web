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

class SppagebuilderAddonSL_Product_Introtext extends SppagebuilderAddons
{

	public function render()
	{

		$class            = (isset($this->addon->settings->class) && $this->addon->settings->class) ? $this->addon->settings->class : '';
		$title            = (isset($this->addon->settings->title) && $this->addon->settings->title) ? $this->addon->settings->title : '';
		$heading_selector = (isset($this->addon->settings->heading_selector) && $this->addon->settings->heading_selector) ? $this->addon->settings->heading_selector : 'h3';
		$introtext_bordercolor  = (isset($this->addon->settings->introtext_bordercolor) && $this->addon->settings->introtext_bordercolor) ? $this->addon->settings->introtext_bordercolor : '';
		$introtext_bgcolor  = (isset($this->addon->settings->introtext_bgcolor) && $this->addon->settings->introtext_bgcolor) ? $this->addon->settings->introtext_bgcolor : '';

		$app     = JFactory::getApplication();
		$input  = $app->input;
		$product = $input->getInt('product');
		$html    = '';

		$helper = SellaciousHelper::getInstance();

		//Options
		if ($product)
		{
			$result = $helper->product->getItem($product);

			if (!empty($result->introtext))
			{
				$introStyle = '';
				if ($introtext_bordercolor && $introtext_bgcolor)
				{
					$introStyle = 'style="border-color: ' . $introtext_bordercolor . '; background-color: ' . $introtext_bgcolor . '"';
				}
				elseif ($introtext_bordercolor && !$introtext_bgcolor)
				{
					$introStyle = 'style="border-color: ' . $introtext_bordercolor . '"';
				}
				elseif (!$introtext_bordercolor && $introtext_bgcolor)
				{
					$introStyle = 'style="background-color: ' . $introtext_bgcolor . '"';
				}

				$html = '<blockquote class="introtext" ' . $introStyle . '>' . $result->introtext . '</blockquote>';

			}
		}

		//Output
		if ($html)
		{
			$output = '<div class="sppb-addon sppb-addon-product-desc ' . $class . '">';
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
