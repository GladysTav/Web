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
class StoreLocation extends AbstractFilter
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
		$state = $this->module->getState();

		if ($value = $this->app->getUserState('com_sellacious.products.filter.store_location_custom'))
		{
			$state->set('filter.store_location_custom', $value);
		}

		if ($value = $this->app->getUserState('com_sellacious.products.filter.store_location_custom_text'))
		{
			$state->set('filter.store_location_custom_text', $value);
		}
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
		$value = $this->module->getState()->get('filter.store_location');

		// Todo: implement
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
			'location'        => $state->get('filter.store_location'),
			'location_custom' => $state->get('filter.store_location_custom'),
			'location_text'   => $state->get('filter.store_location_custom_text'),
			'Itemid'          => $this->app->input->getInt('Itemid'),
			'search_in'       => $this->module->getCfg('store_location_custom_search_in', array('country')),
			'ip_country'      => $this->helper->location->ipToCountryName(),
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

		if ($view === 'store')
		{
			return;
		}

		parent::renderForm();
	}
}
