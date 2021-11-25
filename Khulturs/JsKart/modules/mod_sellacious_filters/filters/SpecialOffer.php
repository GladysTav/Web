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
class SpecialOffer extends AbstractFilter
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
		$value = $this->module->getState()->get('filter.offer_id');

		if ($value)
		{
			$this->module->getLoader()->filterInJsonKey('discounts', null, $value);
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
		$showAll = in_array('offer', $showAll) && $this->app->input->getString('showall') !== 'offer';
		$state   = $this->module->getState();

		return array(
			'offers'      => $this->getOffers(),
			'value'       => $state->get('filter.offer_id'),
			'storeId'     => $state->get('store.id'),
			'showShowAll' => $showAll,
		);
	}

	/**
	 * Get all valid discounts list
	 *
	 * @return  stdClass[]
	 *
	 * @since   2.0.0
	 */
	public function getOffers()
	{
		$filter = array(
			'list.where'     => array('a.state = 1', "a.type = 'discount'", 'a.level > 0', 'a.filterable = 1'),
			'list.published' => true,
		);

		if ($this->app->input->getString('showall') != 'offer')
		{
			$filter['list.limit'] = $this->helper->config->get('special_offer_limit', 1);
		}

		return $this->helper->shopRule->loadObjectList($filter);
	}
}
