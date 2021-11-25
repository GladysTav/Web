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

/**
 * Filter Class
 *
 * @package  Sellacious\ProductsFilter
 *
 * @since    2.0.0
 */
class Price extends AbstractFilter
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
		$min   = $this->module->getState()->get('filter.price_from');
		$max   = $this->module->getState()->get('filter.price_to');
		$query = $this->module->getLoader()->getQuery();

		if ($min)
		{
			$query->where('sales_price * forex_rate  >= ' . (float) $min);
		}

		if ($max)
		{
			$query->where('sales_price * forex_rate <= ' . (float) $max);
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
		$state = $this->module->getState();

		return array(
			'min' => $state->get('filter.price_from'),
			'max' => $state->get('filter.price_to'),
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

		if ($view === 'stores')
		{
			return;
		}

		parent::renderForm();
	}
}
