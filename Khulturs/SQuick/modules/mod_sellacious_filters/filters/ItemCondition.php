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
class ItemCondition extends AbstractFilter
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
		$value   = $this->module->getState()->get('filter.item_condition');
		$lstType = $this->module->getState()->get('filter.listing_type');

		if ($value > 0 && in_array($lstType, array(2, 3)))
		{
			$this->module->getLoader()->filterValue('item_condition', (int) $value);
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
			'listingType' => (int) $state->get('filter.listing_type'),
			'value'       => (int) $state->get('filter.item_condition'),
		);
	}
}
