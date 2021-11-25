<?php
/**
 * @version     2.0.0
 * @package     Sellacious Filters Module
 *
 * @copyright   Copyright (C) 2012-2020 Bhartiy Web Technologies. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Izhar Aazmi <info@bhartiy.com> - http://www.bhartiy.com
 */
namespace Sellacious\ProductsFilter;

// no direct access
defined('_JEXEC') or die;

use stdClass;

/**
 * Filter Class
 *
 * @package  Sellacious\ProductsFilter
 *
 * @since    2.0.0
 */
class StoreName extends AbstractFilter
{
	/**
	 * Method to compute and assign the values to be cached for the filter to work
	 *
	 * @return  void
	 *
	 * @since   2.0.0
	 */
	public function buildCacheData()
	{

	}

	/**
	 * Method to populate the state with the filter values submitted
	 *
	 * @return  void
	 *
	 * @since   2.0.0
	 */
	public function populateState()
	{
		// Model will handle
	}

	/**
	 * Method to apply the filter to search query using the state with values submitted
	 *
	 * @return  void
	 *
	 * @since   2.0.0
	 */
	public function addFilter()
	{
		$value = $this->module->getState()->get('filter.shop_uid');

		if ($value)
		{
			$this->module->getLoader()->filterValue('seller_uid', $value);
		}
	}

	/**
	 * Get required data for the filter form, if any
	 *
	 * @return  array
	 *
	 * @since   2.0.0
	 */
	protected function getFilterFormData()
	{
		$showAll = (array) $this->module->getCfg('show_all_for');
		$showAll = in_array('shopname', $showAll) && $this->app->input->getString('showall') !== 'shopname';

		return array(
			'stores'      => $this->getSellers(),
			'value'       => $this->module->getState()->get('filter.shop_uid'),
			'showShowAll' => $showAll,
		);
	}

	/**
	 * Method to render the frontend filter form which can be submitted to apply a products filter
	 *
	 * @return  void
	 *
	 * @since   2.0.0
	 */
	public function renderForm()
	{
		$view = $this->app->input->getCmd('view');

		if ($view === 'store' || $view === 'stores')
		{
			return;
		}

		parent::renderForm();
	}

	/**
	 * Method to retrieve a list of all sellers with some product
	 *
	 * @return  stdClass[]
	 *
	 * @since   2.0.0
	 */
	public function getSellers()
	{
		$catId  = $this->module->getState()->get('filter.category_id', 1);

		$filter = array(
			'list.select' => 'a.user_id, a.title, a.store_name',
			'list.where'  => array('a.state = 1')
		);

		if ($catId)
		{
			$categories = $this->helper->category->getChildren($catId, 1);

			if (empty($categories))
			{
				$categories[] = 0;
			}

			$filter['list.join'] = array(
				array('INNER', '#__sellacious_product_sellers ps ON ps.seller_uid = a.user_id'),
				array('INNER', '#__sellacious_product_categories pc ON pc.product_id = ps.product_id'),
			);

			// @izharaazmi: Should we completely hide or just fade-out the unavailable sellers?
			$filter['list.where'][] = 'pc.category_id IN (' . implode(',', $categories) . ')';
			$filter['list.group'][] = 'a.user_id';
		}

		if ($this->app->input->getString('showall') != 'shopname')
		{
			$filter['list.limit'] = $this->module->getCfg('shop_name_limit', 10);
		}

		$filter['list.where'][] = 'u.block = 0';

		return $this->helper->seller->loadObjectList($filter);
	}
}
