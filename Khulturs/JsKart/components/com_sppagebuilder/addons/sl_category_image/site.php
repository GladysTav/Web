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

class SppagebuilderAddonSL_Category_Image extends SppagebuilderAddons
{

	public function render()
	{

		$class            = (isset($this->addon->settings->class) && $this->addon->settings->class) ? $this->addon->settings->class : '';
		$title            = (isset($this->addon->settings->title) && $this->addon->settings->title) ? $this->addon->settings->title : '';
		$heading_selector = (isset($this->addon->settings->heading_selector) && $this->addon->settings->heading_selector) ? $this->addon->settings->heading_selector : 'h3';
		$col_size         = (isset($this->addon->settings->col_size) && $this->addon->settings->col_size) ? $this->addon->settings->col_size : '6';

		$app      = JFactory::getApplication();
		$jInput   = $app->input;
		$category = $jInput->getInt('category');
		$html     = '';

		$helper = SellaciousHelper::getInstance();

		//Options
		if ($category)
		{
			$result    = $helper->category->getItem($category);
			$image     = $helper->media->getImage('categories.images', $category);
			$cat_title = $result->title ?: '';

			if ($image)
			{
				$html .= '<div class="col-sm-' . $col_size . '">';
				$html .= '<div class="category-image">';
				$html .= '<span class="catimage" style="background-image:url(' . $image . ')" title="' . $cat_title . '"></span>';
				$html .= '</div>';
				$html .= '</div>';
			}
		}

		//Output
		if ($html)
		{
			$output = '<div class="sppb-addon sppb-addon-category-image ' . $class . '">';
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
