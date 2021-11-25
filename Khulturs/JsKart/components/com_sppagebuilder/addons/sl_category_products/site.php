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

use Sellacious\Product;
use Sellacious\Media\Image\ImageHelper;
use Sellacious\Cache\Reader\ProductsCacheReader;


class SppagebuilderAddonSL_Category_Products extends SppagebuilderAddons
{

	public function render()
	{

		$class            = (isset($this->addon->settings->class) && $this->addon->settings->class) ? $this->addon->settings->class : '';
		$title            = (isset($this->addon->settings->title) && $this->addon->settings->title) ? $this->addon->settings->title : '';
		$title_alignment  = (isset($this->addon->settings->title_alignment) && $this->addon->settings->title_alignment) ? $this->addon->settings->title_alignment : '';
		$heading_selector = (isset($this->addon->settings->heading_selector) && $this->addon->settings->heading_selector) ? $this->addon->settings->heading_selector : 'h3';
		$limit            = (isset($this->addon->settings->total_products) && $this->addon->settings->total_products) ? $this->addon->settings->total_products : '8';
		$product_features = (isset($this->addon->settings->product_features) && $this->addon->settings->product_features) ? $this->addon->settings->product_features : 'hide';
		$layout           = (isset($this->addon->settings->layout) && $this->addon->settings->layout) ? $this->addon->settings->layout : 'grid';

		$db       = JFactory::getDBO();
		$app      = JFactory::getApplication();
		$jInput   = $app->input;
		$category = $jInput->getInt('category');
		$html     = '';
		$products = array();
		$styles   = array();

		$helper = SellaciousHelper::getInstance();

		if ($limit > 0)
		{
			jimport('joomla.application.component.model');
			JModelLegacy::addIncludePath(JPATH_SITE . '/components/com_sellacious/models');

			/** @var  SellaciousModelProducts $model */
			$model = JModelLegacy::getInstance('Products', 'SellaciousModel', array('ignore_request' => true));
			$model->setState('filter.category_id', $category);
			$model->setState('list.limit', $limit);

			$products = $model->getItems();
		}
		$args = array('id' => 'productsList', 'items' => $products, 'context' => array('products'), 'layout' => $layout);
		$html .= JLayoutHelper::render('sellacious.product.grid', $args);

		if ($products)
		{
			if ($helper->config->get('product_compare')):
				JHtml::_('script', 'com_sellacious/util.compare.js', false, true);
			endif;

			JHtml::_('script', 'com_sellacious/util.cart.aio.js', false, true);
			JHtml::_('script', 'com_sellacious/fe.view.products.js', false, true);
			JHtml::_('stylesheet', 'com_sellacious/fe.view.cart.aio.css', null, true);

			if ($layout == "grid")
			{
				$slCatProdsLayoutClass = 'sl-catprod-grid-layout';
				$slCatProdsWrapClass   = 'sl-catprod-grid-wrap';

			}
			elseif ($layout == "list")
			{
				$slCatProdsLayoutClass = 'sl-catprod-list-layout';
				$slCatProdsWrapClass   = 'sl-catprod-list-wrap';

			}

			$html .= '<div class="sl-catproducts-box ' . $slCatProdsLayoutClass . '">';

			$me    = JFactory::getUser();
			$c_cat = $helper->client->loadResult(array('list.select' => 'category_id', 'user_id' => $me->id));

			//Output
			if ($html)
			{
				$output = '<div class="sppb-addon sppb-addon-category-desc ' . $class . '">';
				$output .= ($title) ? '<' . $heading_selector . ' class="sppb-addon-title ' . $title_alignment . '">' . $title . '</' . $heading_selector . '>' : '';
				$output .= '<div class="sppb-addon-content">';
				$output .= $html;
				$output .= '</div>';
				$output .= '</div>';

				return $output;
			}

			return;
		}
	}

	public function stylesheets()
	{
		return array(
			JURI::base(true) . '/components/com_sppagebuilder/assets/css/sellacious/sl-categorystyle.css',
			JURI::base(true) . '/components/com_sppagebuilder/assets/css/sellacious/sl-categoryproducts.css'
		);
	}
}
