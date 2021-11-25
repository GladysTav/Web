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

use JPluginHelper;

/**
 * Filter Class
 *
 * @package  Sellacious\ProductsFilter
 *
 * @since    2.0.0
 */
class StoreAvailability extends AbstractFilter
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
			'open'     => $state->get('filter.show_open_stores'),
			'delivery' => $state->get('filter.delivery_available'),
			'pickup'   => $state->get('filter.pickup_available'),
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

		if ($view === 'store' || !JPluginHelper::isEnabled('system', 'sellacioushyperlocal'))
		{
			return;
		}

		parent::renderForm();
	}
}
