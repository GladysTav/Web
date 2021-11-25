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

class SppagebuilderAddonSL_Category_Subcategories extends SppagebuilderAddons
{

	public function render()
	{

		$class            = (isset($this->addon->settings->class) && $this->addon->settings->class) ? $this->addon->settings->class : '';
		$title            = (isset($this->addon->settings->title) && $this->addon->settings->title) ? $this->addon->settings->title : '';
		$heading_selector = (isset($this->addon->settings->heading_selector) && $this->addon->settings->heading_selector) ? $this->addon->settings->heading_selector : 'h3';
		$showpcount       = (isset($this->addon->settings->showpcount) && $this->addon->settings->showpcount) ? $this->addon->settings->showpcount : '0';
		$showccount       = (isset($this->addon->settings->showccount) && $this->addon->settings->showccount) ? $this->addon->settings->showccount : '0';

		$app      = JFactory::getApplication();
		$jInput   = $app->input;
		$category = $jInput->getInt('category');
		$html     = '';
		$children = array();
		$helper = SellaciousHelper::getInstance();

		if ($category)
		{
			$catItem = $helper->category->getItem($category);
			if ($catItem->level > 0)
			{
				$childLevel = $catItem->level +1;
				$children = $helper->category->getChildren($catItem->id, false, array('a.state = 1', 'a.level = '.$childLevel));
 				sort($children);
			}

			if ($children)
			{
				$html     .='<div class="catogry-childblocks">';
				$items = array();
				foreach ($children as $child)
				{
					$item = $helper->category->getItem($child);
					$items[] = $item;
				}
				$opts = array(
					'items'   => $helper->category->parseCategoriesForLayout($items),
					'id'      => 'categoriesList',
					'context' => array('categories'),
					'layout'  => 'grid'
				);
				$html .= JLayoutHelper::render('sellacious.categories.default', $opts);
				
				$html  .=	'</div>';
				$html  .=	'<div class="clearfix"></div>';
			}

		}

		//Output
		if ($html)
		{
			$output = '<div class="sppb-addon sppb-addon-category-subcategories ' . $class . '">';
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
