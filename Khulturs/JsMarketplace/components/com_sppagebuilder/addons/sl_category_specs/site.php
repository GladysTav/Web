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

class SppagebuilderAddonSL_Category_Specs extends SppagebuilderAddons
{

	public function render()
	{

		$class            = (isset($this->addon->settings->class) && $this->addon->settings->class) ? $this->addon->settings->class : '';
		$title            = (isset($this->addon->settings->title) && $this->addon->settings->title) ? $this->addon->settings->title : '';
		$heading_selector = (isset($this->addon->settings->heading_selector) && $this->addon->settings->heading_selector) ? $this->addon->settings->heading_selector : 'h3';
		$box_title        = (isset($this->addon->settings->cat_specs_title) && $this->addon->settings->cat_specs_title) ? $this->addon->settings->cat_specs_title : '';

		$app      = JFactory::getApplication();
		$input   = $app->input;
		$category = $input->getInt('category');
		$html     = '';

		$helper = SellaciousHelper::getInstance();

		if ($category)
		{
			$result = $helper->category->getItem($category);
			$specs  = json_decode($result->params, true);

			if (!empty($specs['specs'][0]['label']))
			{
				$html = '<div class="moreinfo-box">';
				$html .= ($box_title) ? '<h3>' . $box_title . '</h3>' : '';
				$html .= '<div class="innermoreinfo">';
				$html .= '<div class="specificationgroup">';
				$html .= '<dl class="dl-horizontal dl-leftside">';

				foreach ($specs['specs'] as $spec)
				{
					$html .= '<dt>' . $spec['label'] . '</dt>';
					$html .= '<dd>' . $spec['value'] . '</dd>';
				}

				$html .= '</dl>';
				$html .= '</div>';
				$html .= '</div>';
				$html .= '</div>';
			}
		}

		//Output
		if ($html)
		{
			$output = '<div class="sppb-addon sppb-addon-category-specs ' . $class . '">';
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
		return array(JURI::base(true) . '/components/com_sppagebuilder/assets/css/sellacious/sl-categorystyle.css');

	}

}
