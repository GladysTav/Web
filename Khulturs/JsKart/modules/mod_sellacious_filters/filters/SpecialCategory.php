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
class SpecialCategory extends AbstractFilter
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
		$value = $this->module->getState()->get('filter.spl_category');

		if ($value)
		{
			$this->module->getLoader()->filterInJsonKey('spl_category', null, $value);
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
		$showAll = in_array('spl_category', $showAll) && $this->app->input->getString('showall') !== 'splcat';
		$state   = $this->module->getState();

		return array(
			'categories'  => $this->getCategories(),
			'storeId'     => $state->get('store.id'),
			'categoryId'  => $state->get('filter.spl_category'),
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
		if ($this->app->input->getCmd('view') === 'stores')
		{
			return;
		}

		parent::renderForm();
	}

	/**
	 * Get All Categories List
	 *
	 * @return  stdClass[]
	 *
	 * @since   2.0.0
	 */
	protected function getCategories()
	{
		$showAll = $this->app->input->getString('showall');
		$filter  = array('list.select' => 'a.id, a.title', 'list.where'  => array('a.state = 1', 'a.level > 0'));

		if ($showAll !== 'splcat')
		{
			$filter['list.limit'] = $this->module->getCfg('special_categories_limit', 1);
		}

		return $this->helper->splCategory->loadObjectList($filter);
	}
}
