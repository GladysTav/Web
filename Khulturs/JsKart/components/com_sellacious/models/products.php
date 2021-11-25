<?php
/**
 * @version     2.0.0
 * @package     sellacious
 *
 * @copyright   Copyright (C) 2012-2020 Bhartiy Web Technologies. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Izhar Aazmi <info@bhartiy.com> - http://www.bhartiy.com
 */
// no direct access.
defined('_JEXEC') or die;

use Sellacious\Cache\Exception\CacheMissingException;
use Sellacious\Cache\Reader\ProductsCacheReader;
use Sellacious\Config\Config;
use Sellacious\Config\ConfigHelper;
use Sellacious\Event\EventHelper;
use Sellacious\Price\PriceHelper;

/**
 * Methods supporting a list of Products
 *
 * @since   1.0.0
 */
class SellaciousModelProducts extends SellaciousModelList
{
	/**
	 * The handler to load products records from cache db
	 *
	 * @var   ProductsCacheReader
	 *
	 * @since   2.0.0
	 */
	protected $loader;

	/**
	 * The configuration values for component
	 *
	 * @var   Config
	 *
	 * @since   2.0.0
	 */
	protected $config;

	/**
	 * Constructor
	 *
	 * @param   array  $config  An optional associative array of configuration settings.
	 *
	 * @see     JControllerLegacy
	 *
	 * @throws  CacheMissingException|Exception
	 *
	 * @since   1.0.0
	 */
	public function __construct($config = array())
	{
		if (empty($config['filter_fields']))
		{
			$config['filter_fields'] = array(
				'id',
				'product_id',
				'title',
				'product_title',
			);
		}

		parent::__construct($config);

		$this->loader = new ProductsCacheReader;
		$this->config = ConfigHelper::getInstance('com_sellacious');
	}

	/**
	 * Method to auto-populate the model state
	 *
	 * This method should only be called once per instantiation and is designed
	 * to be called on the first call to the getState() method unless the model
	 * configuration flag to ignore the request is set
	 *
	 * Note: Calling getState in this method will result in recursion
	 *
	 * @param   string  $ordering   An optional ordering field
	 * @param   string  $direction  An optional direction (asc|desc)
	 *
	 * @return  void
	 *
	 * @since   1.0.0
	 */
	protected function populateState($ordering = null, $direction = null)
	{
		parent::populateState($ordering, $direction);
		
		$value = $this->app->getUserStateFromRequest('com_sellacious.products.filter.search', 'q', '', 'string');

		if ($value)
		{
			$this->state->set('filter.search', $value);
		}

		if ($value = $this->app->input->getInt('category_id'))
		{
			$this->state->set('filter.category_id', $value);
		}

		if ($value = $this->app->input->getInt('id'))
		{
			$this->state->set('store.id', $value);
		}

		$value = $this->app->input->getString('custom_ordering');

		if ($value)
		{
			$this->state->set('list.custom_ordering', $value);
		}

		$arguments = array(
			'context' => 'com_sellacious.products',
			'loader'  => &$this->loader,
			'state'   => &$this->state,
		);

		EventHelper::trigger('onPopulateState', $arguments);
	}

	/**
	 * Method to get an array of data items
	 *
	 * @return  stdClass[]|bool  An array of data items on success, false on failure
	 *
	 * @since   2.0.0
	 */
	public function getItems()
	{
		$state = $this->getState();
		$store = serialize($state->getProperties());

		if (isset($this->cache[$store]))
		{
			return $this->cache[$store];
		}

		try
		{
			$query = $this->loader->getQuery(true);

			$query->where('product_active = 1');
			$query->where('(variant_id = 0 OR variant_active = 1)');
			$query->where('seller_active = 1');
			$query->where('listing_active = 1');
			$query->where('is_selling = 1');

			$arguments = array(
				'context' => 'com_sellacious.products',
				'loader'  => $this->loader,
				'query'   => $query,
				'state'   => $state,
			);

			$this->processQuery($query);

			EventHelper::trigger('onFetchCacheItems', $arguments);

			$items = $this->loader->getItems($this->getStart(), $state->get('list.limit', 20));

			$items = $this->processList($items);

			$this->cache[$store] = $items;
		}
		catch (Exception $e)
		{
			$this->setError($e->getMessage());

			return false;
		}

		return $this->cache[$store];
	}

	/**
	 * Returns a record count for the query
	 *
	 * @param   JDatabaseQuery  $query  The query
	 *
	 * @return  int  Number of rows for query
	 *
	 * @since   2.0.0
	 */
	protected function _getListCount($query)
	{
		return (int) $this->loader->getTotal();
	}

	/**
	 * Method to process the list query with filters and sorting
	 *
	 * @param   JDatabaseQuerySqlite  $query  A JDatabaseQuery object
	 *
	 * @return  void
	 *
	 * @throws  Exception
	 *
	 * @since   2.0.0
	 */
	protected function processQuery($query)
	{
		$tag = JFactory::getLanguage()->getTag();

		$this->loader->filterValue('language', array($tag, '*', ''));

		$catId = $this->getState('filter.category_id', 1);

		$this->loader->filterInJsonKey('category_ext', null, $catId);

		$storeId = $this->getState('store.id', 0);

		if ($storeId > 0)
		{
			// This filter exists in Filter module (store filter) as well but its duplicated here
			// because store.id is fetched from "store" model state but store filter is fetched from user state
			$this->loader->filterValue('seller_uid', (int) $storeId);
		}

		// Hide zero price to be done using cache column 'price_valid'

		// The no-stock items are always at end
		$query->order('stock_capacity = 0 ASC');

		$ordering = $this->getState('list.custom_ordering');

		switch ($ordering)
		{
			case 'newest':
				$query->order('listing_start DESC');
				break;
			case 'order_max':
				$query->order('order_units DESC');
				break;
			case 'rating_max':
				$query->order('product_rating DESC');
				break;
			case 'price_min':
				$query->order('sales_price * forex_rate ASC');
				break;
			case 'price_max':
				$query->order('sales_price * forex_rate DESC');
				break;
		}

		$query->where('is_visible > 0');

		$query->order('is_visible DESC');
	}

	/**
	 * Pre-process loaded list before returning if needed
	 *
	 * @param   stdClass[]  $items  List loaded from the listQuery
	 *
	 * @return  stdClass[]
	 *
	 * @throws  Exception
	 *
	 * @since   1.3.0
	 */
	protected function processList($items)
	{
		foreach ($items as &$item)
		{
			$item->category         = json_decode($item->category, true);
			$item->category_ext     = json_decode($item->category_ext, true);
			$item->spl_category     = json_decode($item->spl_category, true);
			$item->specifications   = json_decode($item->specifications, true);
			$item->product_rating   = json_decode($item->product_rating);
			$item->product_features = json_decode($item->product_features);

			$handler = PriceHelper::getHandler($item->pricing_type);

			$handler->processCacheProduct($item);

			$item->rendered_attributes = $this->helper->product->getRenderedAttributes($item->code, $item->product_id, $item->variant_id, $item->seller_uid);
		}

		return $items;
	}
}
