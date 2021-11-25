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
class FreeShipping extends AbstractFilter
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
		$loader = $this->module->getLoader();
		$free   = $this->module->getState()->get('filter.free_shipping');

		if ($free)
		{
			$loader->filterValue('product_type', 'electronic', '!=');

			if ($this->helper->config->get('shipped_by') === 'shop')
			{
				$flat_shipping     = $this->helper->config->get('flat_shipping');
				$shipping_flat_fee = $this->helper->config->get('shipping_flat_fee');

				if ($flat_shipping && abs($shipping_flat_fee) >= 0.01)
				{
					$loader->fallacy();
				}
			}
			elseif ($this->helper->config->get('itemised_shipping'))
			{
				$loader->filterValue('flat_shipping', '1');
				$loader->filterValue('shipping_flat_fee', '0');
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
		return array(
			'value' => $this->module->getState()->get('filter.free_shipping'),
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
}
