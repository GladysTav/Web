<?php
/**
 * @version     2.0.0
 * @package     Sellacious Filters Module
 *
 * @copyright   Copyright (C) 2012-2020 Bhartiy Web Technologies. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Izhar Aazmi <info@bhartiy.com> - http://www.bhartiy.com
 */
// no direct access
defined('_JEXEC') or die('Restricted access');

use Joomla\Utilities\ArrayHelper;
use Sellacious\Cache\Reader\ProductsCacheReader;
use Sellacious\Config\Config;
use Sellacious\Config\ConfigHelper;
use Sellacious\Event\EventHelper;
use Sellacious\ProductsFilter\AbstractFilter;

JLoader::registerNamespace('Sellacious\ProductsFilter', __DIR__ . '/filters', false, false, 'psr4');

/**
 * @package  Sellacious\ProductsFilter
 *
 * @since   2.0.0
 */
class ModSellaciousFilters
{
	/**
	 * This object's singleton instance
	 *
	 * @var  ModSellaciousFilters
	 *
	 * @since   2.0.0
	 */
	protected static $instance;
	/**
	 * The list of filters assigned
	 *
	 * @var   AbstractFilter[]
	 *
	 * @since   2.0.0
	 */
	protected $filters = array();
	/**
	 * Global application instance
	 *
	 * @var  JApplicationCms
	 *
	 * @since   2.0.0
	 */
	protected $app;

	/**
	 * The active component
	 *
	 * @var  string
	 *
	 * @since   2.0.0
	 */
	protected $option;

	/**
	 * The active view
	 *
	 * @var  string
	 *
	 * @since   2.0.0
	 */
	protected $view;

	/**
	 * The module state
	 *
	 * @var   JObject
	 *
	 * @since   2.0.0
	 */
	protected $state;

	/**
	 * The module's configuration, currently scoped in com_sellacious centrally shared by all instances
	 *
	 * @var   Config
	 *
	 * @since   2.0.0
	 */
	protected $config;

	/**
	 * The cache reader object instance
	 *
	 * @var   ProductsCacheReader
	 *
	 * @since   2.0.0
	 */
	protected $loader;

	/**
	 * Constructor
	 *
	 * @since   2.0.0
	 */
	public function __construct()
	{
		try
		{
			// This should be modules configuration
			$this->config = ConfigHelper::getInstance('com_sellacious');
			$this->app    = JFactory::getApplication();
			$this->option = $this->app->input->get('option');
			$this->view   = $this->app->input->get('view');
			$this->state  = new JObject;

			$this->loadFilters();
		}
		catch (Exception $e)
		{
		}
	}

	/**
	 * Get the object's singleton instance
	 *
	 * @return  ModSellaciousFilters
	 *
	 * @since   2.0.0
	 */
	public static function getInstance()
	{
		if (!static::$instance)
		{
			static::$instance = new static;
		}

		return static::$instance;
	}

	/**
	 * Method to perform the rendering of the module
	 *
	 * @return  void
	 *
	 * @throws  Exception
	 *
	 * @since   2.0.0
	 */
	public function render()
	{
		foreach ($this->filters as $filter)
		{
			$filter->renderForm();
		}
	}

	/**
	 * Check whether the module should be displayed on current page
	 *
	 * @return  bool
	 *
	 * @since   2.0.0
	 */
	public function isValid()
	{
		return $this->option === 'com_sellacious' && in_array($this->view, array('products', 'store', 'stores'), true);
	}

	/**
	 * Method to load all supported filters
	 *
	 * @return  void
	 *
	 * @throws  Exception
	 *
	 * @since   2.0.0
	 */
	public function loadFilters()
	{
		$enabled  = $this->getCfg('sections.enabled');
		$disabled = $this->getCfg('sections.disabled');
		$enabled  = array_filter(explode(':', $enabled), 'strlen');
		$disabled = array_filter(explode(':', $disabled), 'strlen');

		$filters = array(
			'search'             => 'Sellacious\ProductsFilter\Search',
			'attributes'         => 'Sellacious\ProductsFilter\Attributes',
			'category'           => 'Sellacious\ProductsFilter\Category',
			'free_shipping'      => 'Sellacious\ProductsFilter\FreeShipping',
			'item_condition'     => 'Sellacious\ProductsFilter\ItemCondition',
			'listing_type'       => 'Sellacious\ProductsFilter\ListingType',
			'price'              => 'Sellacious\ProductsFilter\Price',
			'shippable_location' => 'Sellacious\ProductsFilter\ShippableLocation',
			'special_category'   => 'Sellacious\ProductsFilter\SpecialCategory',
		//	'special_offer'      => 'Sellacious\ProductsFilter\SpecialOffer',
			'stock'              => 'Sellacious\ProductsFilter\Stock',
			'store_availability' => 'Sellacious\ProductsFilter\StoreAvailability',
			'store_location'     => 'Sellacious\ProductsFilter\StoreLocation',
			'store_name'         => 'Sellacious\ProductsFilter\StoreName',
		);

		EventHelper::trigger('onLoadProductFilters', array('context' => 'com_sellacious.products', 'filters' => &$filters));

		// If enabled and disabled both list is empty use all, else
		if (!$enabled && !$disabled)
		{
			$enabled = array_keys($filters);
		}

		foreach ($enabled as $name)
		{
			if (isset($filters[$name]))
			{
				if (class_exists($filters[$name]))
				{
					$className            = $filters[$name];
					$this->filters[$name] = new $className($this);
				}
			}
		}
	}

	/**
	 * Method to get the filter configuration setting
	 *
	 * @param   string  $key      Registry path format identifier (e.g. 'user.category.default')
	 * @param   mixed   $default  Optional default value, returned if the internal value is null.
	 *
	 * @return  mixed  Value of entry or null
	 *
	 * @since   2.0.0
	 */
	public function getCfg($key, $default = null)
	{
		return $this->config->get('sellacious_filters.'. $key, $default);
	}

	/**
	 * Method to get the filter state object
	 *
	 * @return  JObject
	 *
	 * @since   2.0.0
	 */
	public function getState()
	{
		return $this->state;
	}

	/**
	 * Method to get the cache reader object
	 *
	 * @return  ProductsCacheReader
	 *
	 * @since   2.0.0
	 */
	public function getLoader()
	{
		return $this->loader;
	}

	/**
	 * Method to populate the filter state object
	 *
	 * @param   JObject  $state  The module state
	 *
	 * @return  void
	 *
	 * @throws  Exception
	 *
	 * @since   2.0.0
	 */
	public function populateState($state)
	{
		$this->state = $state;

		foreach ($this->filters as $filter)
		{
			try
			{
				$filter->populateState();
			}
			catch (Exception $e)
			{
				// Ignore single element
			}
		}
	}

	/**
	 * Method to set query filter populate the filter state object
	 *
	 * @param   ProductsCacheReader  $loader  The cache reader object instance
	 * @param   JObject              $state   The module state
	 *
	 * @return  void
	 *
	 * @throws  Exception
	 *
	 * @since   2.0.0
	 */
	public function setQueryFilter($loader, $state)
	{
		$this->state  = $state;
		$this->loader = $loader;

		foreach ($this->filters as $filter)
		{
			try
			{
				$filter->addFilter();
			}
			catch (Exception $e)
			{
				// Ignore single element
			}
		}
	}

	/**
	 * Get the filter instance by name if it exists
	 *
	 * @param   string  $name
	 *
	 * @return  AbstractFilter
	 *
	 * @since   2.0.0
	 */
	public function getFilter($name)
	{
		return ArrayHelper::getValue($this->filters, $name);
	}
}
