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

use Exception;
use JFactory;
use SellaciousHyperlocal;
use Sellacious\Config\Config;
use Sellacious\Config\ConfigHelper;
use stdClass;

\JLoader::register('SellaciousHyperlocal', dirname(dirname(__DIR__)) . '/mod_sellacious_hyperlocal/libraries/module.php');

/**
 * Filter Class
 *
 * @package  Sellacious\ProductsFilter
 *
 * @since    2.0.0
 */
class ShippableLocation extends AbstractFilter
{
	/**
	 * Hyperlocal plugin configuration
	 *
	 * @var   Config
	 *
	 * @since   2.0.0
	 */
	protected $config;

	/**
	 * Whether to enable/disable this filter
	 *
	 * @var   bool
	 *
	 * @since   2.0.0
	 */
	protected $enable = true;

	/**
	 * ShippableLocation constructor
	 *
	 * @param   stdClass  $module
	 *
	 * @throws  Exception
	 *
	 * @since   2.0.0
	 */
	public function __construct($module)
	{
		parent::__construct($module);

		$this->config = ConfigHelper::getInstance('mod_sellacious_hyperlocal');

		if (!defined('SellaciousHyperlocal::BY_REGION') || $this->config->get('hyperlocal_type') !== SellaciousHyperlocal::BY_REGION)
		{
			$this->enable = false;
		}
	}

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
		if (!$this->enable)
		{
			return;
		}

		$state = $this->module->getState();

		$value = $this->app->getUserState('com_sellacious.products.filter.store_location_custom');

		if ($value)
		{
			$state->set('filter.shippable', $value);
		}

		$value = $this->app->getUserState('com_sellacious.products.filter.store_location_custom_text');

		if ($value)
		{
			$state->set('filter.shippable_text', $value);
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
		if (!$this->enable)
		{
			return;
		}

		$value  = $this->module->getState()->get('filter.shippable');
		$loader = $this->module->getLoader();

		if ($value)
		{
			$pks = array($value);
		}
		else
		{
			$name = $this->module->getState()->get('filter.shippable_text');
			$name = array_filter(explode(',', $name), 'trim');
			$name = reset($name);

			if (!$name)
			{
				return;
			}

			$options = $this->helper->config->get('shippable_location_search_in', array('country'));
			$filter  = array(
				'list.select' => 'a.id',
				'list.where'  => sprintf('(a.title = %1$s OR a.iso_code = %1$s)', JFactory::getDbo()->q($name)),
				'type'        => $options,
				'state'       => 1,
			);

			$pks = $this->helper->location->loadColumn($filter);

			// No information available for given location, can't ship
			if (!$pks)
			{
				$loader->fallacy();

				return;
			}
		}

		/**
		 ****************************************************************************************
		 * Get all ancestor locations, we'll match them with the allowed locations              *
		 * Do not forget that a larger region set in configuration always allows it sub-regions *
		 ****************************************************************************************
		 */
		$queried = $this->helper->location->getAncestry($pks, 'A');
		$global  = $this->helper->location->getShipping();
		$global  = array_reduce($global, 'array_merge', array());

		$allowed = empty($global) ? $queried : array_intersect((array) $global, (array) $queried);

		// No match with global, meaning we can't ship
		if (count($allowed) === 0)
		{
			$loader->fallacy();
		}

		/**
		 ****************************************************************************************
		 * Match with queried hierarchy list as it may contain wider scope than global          *
		 ****************************************************************************************
		 */
		$shippedBy  = $this->helper->config->get('shipped_by');
		$sellerPref = $this->helper->config->get('shippable_location_by_seller');

		if ($shippedBy === 'seller' && $sellerPref)
		{
			$queried[] = 0;

			$loader->filterValue('seller_gl_id', $queried);
		}
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
		if (!$this->enable)
		{
			return;
		}

		parent::renderForm();
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
			'Itemid'         => $this->app->input->getInt('Itemid', 0),
			'search_in'      => $this->module->getCfg('shippable_location_search_in', array('country')),
			'shippable'      => $state->get('filter.shippable'),
			'shippable_text' => $state->get('filter.shippable_text'),
		);
	}
}
