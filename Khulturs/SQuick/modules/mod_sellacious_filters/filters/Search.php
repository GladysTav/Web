<?php
/**
 * @version     2.0.0
 * @package     Sellacious Filters Module
 *
 * @copyright   Copyright (C) 2012-2020 Bhartiy Web Technologies. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Saurabh Sabhbarwal <info@bhartiy.com> - http://www.bhartiy.com
*/
namespace Sellacious\ProductsFilter;

// no direct access
defined('_JEXEC') or die;

/**
 * Filter Class
 *
 * @package  Sellacious\ProductsFilter
 *
 * @since    2.0.0
 */
class Search extends AbstractFilter
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
		$search = $this->module->getState()->get('filter.search');
		$query  = $this->module->getLoader()->getQuery();

		if ($search)
		{
			$words = array_filter(explode(' ', $search));

			foreach ($words as $keyword)
			{
				$kw   = $query->q('%' . $query->escape($keyword, true) . '%', false);
				$cond = array(
					'product_title LIKE ' . $kw,
					'variant_title LIKE ' . $kw,
					'product_sku LIKE ' . $kw,
					'variant_sku LIKE ' . $kw,
					'tags LIKE ' . $kw,
					'specifications LIKE ' . $kw,
				);

				$query->where('(' . implode(' OR ', $cond) . ')');
			}
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
		$value = $this->app->getUserState('com_sellacious.products.filter.search');

		return array('search' => $value);
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
}
