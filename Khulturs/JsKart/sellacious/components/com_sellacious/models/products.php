<?php
/**
 * @version     2.0.0
 * @package     sellacious
 *
 * @copyright   Copyright (C) 2012-2020 Bhartiy Web Technologies. All rights reserved.
 * @license     SPL Sellacious Private License; see http://www.sellacious.com/spl.html
 * @author      Izhar Aazmi <info@bhartiy.com> - http://www.bhartiy.com
 */
// no direct access
defined('_JEXEC') or die;

use Joomla\Utilities\ArrayHelper;
use Sellacious\Cache\Reader\ProductsCacheReader;

/**
 * Methods supporting a list of products records
 *
 * @since  1.0.0
 */
class SellaciousModelProducts extends SellaciousModelList
{
	/**
	 * @var   ProductsCacheReader
	 *
	 * @since   2.0.0
	 */
	protected $loader;

	/**
	 * Constructor
	 *
	 * @param   array  $config  An optional associative array of configuration settings
	 *
	 * @see     JControllerLegacy
	 *
	 * @throws  Exception
	 *
	 * @since   1.6
	 */
	public function __construct($config = array())
	{
		if (empty($config['filter_fields']))
		{
			$config['filter_fields'] = array(
				'product_id',
				'product_sku',
				'product_title',
				'product_active',
				'created_by',
				'seller_company',
				'product_price',
				'listing_start',
				'listing_end',
				'stock',
				'language',
				'category_title',
				'variant_count',
			);
		}

		parent::__construct($config);
	}

	/**
	 * Method to auto-populate the model state
	 *
	 * This method should only be called once per instantiation and is designed
	 * to be called on the first call to the getState() method unless the model
	 * configuration flag to ignore the request is set.
	 *
	 * Note: Calling getState in this method will result in recursion
	 *
	 * @param   string  $ordering   An optional ordering field
	 * @param   string  $direction  An optional direction (asc|desc)
	 *
	 * @return  void
	 *
	 * @since   12.2
	 */
	protected function populateState($ordering = null, $direction = null)
	{
		parent::populateState($ordering, $direction);

		$seller_uid   = $this->state->get('filter.seller_uid');
		$multi_seller = $this->helper->config->get('multi_seller', 0);

		if (!$multi_seller && !$seller_uid)
		{
			// Removed force selection of default seller when multi-seller is off. Now it will be a fallback if not filtered by
			$seller_uid = $this->helper->config->get('default_seller');

			$this->state->set('filter.seller_uid', $seller_uid);
		}

		$this->state->set('layout', $this->app->input->get('layout'));
	}

	/**
	 * Get the filter form
	 *
	 * @param   array    $data      Data
	 * @param   boolean  $loadData  Load current data
	 *
	 * @return  JForm|bool  The JForm object or false
	 *
	 * @since   1.5.3
	 */
	public function getFilterForm($data = array(), $loadData = true)
	{
		$form = parent::getFilterForm($data, $loadData);

		if ($form instanceof JForm)
		{
			if (!$this->helper->access->check('product.list'))
			{
				$form->removeField('seller_uid', 'filter');
			}

			$defLanguage = JFactory::getLanguage();
			$tag         = $defLanguage->getTag();
			$languages   = JLanguageHelper::getContentLanguages();

			$languages = array_filter($languages, function ($item) use ($tag) { return ($item->lang_code != $tag); });

			if (!count($languages))
			{
				$form->removeField('language', 'filter');
			}
		}

		return $form;
	}

	/**
	 * Build an SQL query to load the list data [ONLY used for state != published / unpublished]
	 *
	 * @return  JDatabaseQuery
	 *
	 * @since   1.6
	 */
	protected function getListQuery()
	{
		$db    = $this->getDbo();
		$query = $db->getQuery(true);

		$query->select('a.*')
			->select('a.owned_by as owner_uid, a.title as product_title, a.local_sku as product_sku, a.type as product_type, a.state as product_active')
			->from($db->qn('#__sellacious_products', 'a'));

		// Build category list as a sub-query to avoid grouping collisions.
		$cQuery = $this->_db->getQuery(true);
		$cQuery->select("GROUP_CONCAT(c.id ORDER BY c.lft SEPARATOR ',') AS category_ids")
			->select("GROUP_CONCAT(c.title ORDER BY c.lft SEPARATOR '|:|') AS category_titles")
			->from($db->qn('#__sellacious_categories', 'c'))
			->select('pc.product_id')
			->join('LEFT', $db->qn('#__sellacious_product_categories', 'pc') . ' ON c.id = pc.category_id')
			->group('pc.product_id');

		$query->select('cc.category_ids, cc.category_titles')
			->join('left', "({$cQuery}) AS cc ON cc.product_id = a.id");

		// Filter by search in name
		$search = $this->getState('filter.search');

		if (!empty($search))
		{
			if (stripos($search, 'id:') === 0)
			{
				$query->where('a.id = ' . (int) substr($search, 3));
			}
			elseif (stripos(strtolower($search), 'sku:') === 0)
			{
				$search = $this->_db->q('%' . $this->_db->escape(substr($search, 4), true) . '%', false);
				$query->where('(a.local_sku LIKE ' . $search . ')');
			}
			elseif (stripos(strtolower($search), 't:') === 0)
			{
				$search = $this->_db->q('%' . $this->_db->escape(substr($search, 2), true) . '%', false);
				$query->where('(a.title LIKE ' . $search . ')');
			}
			else
			{
				$search = $this->_db->q('%' . $this->_db->escape($search, true) . '%', false);
				$where  = array(
					'a.local_sku LIKE ' . $search,
					'a.title LIKE ' . $search,
					'a.description LIKE ' . $search,
				);
				$query->where('(' . implode(' OR ', $where) . ')');
			}
		}

		$pricing_type = $this->getState('filter.pricing_type');

		if ($pricing_type)
		{
			$query->where('psx.pricing_type = ' . $db->q($pricing_type));
		}

		$selling = $this->getState('list.selling');

		if (is_numeric($selling))
		{
			$query->where('psx.state = ' . (int) $selling);
		}

		// Filter by published state
		$query->where('a.state = ' . (int) $this->getState('filter.state'));

		// Filter by published state
		if ($category = $this->getState('filter.category'))
		{
			$query->where('a.id IN (SELECT product_id FROM #__sellacious_product_categories WHERE category_id = ' . (int) $category . ')');
		}

		if ($type = $this->getState('filter.type'))
		{
			$query->where('a.type = ' . $db->q($type));
		}

		// Filter by published state
		if ($manufacturer = $this->getState('filter.manufacturer'))
		{
			$query->where('a.manufacturer_id = ' . (int) $manufacturer);
		}

		if ($this->helper->access->check('product.list'))
		{
			if ($seller_uid = $this->getState('filter.seller_uid'))
			{
				$query->where('p.seller_uid = ' . (int) $seller_uid);
			}
		}
		elseif ($this->helper->access->check('product.list.own'))
		{
			$me = JFactory::getUser();
			$query->where('(p.seller_uid = ' . (int) $me->id . ' OR a.owned_by = ' . (int) $me->id . ')');
		}
		else
		{
			$query->where('0');
		}

		$this->extendItemQuery($query);
		$this->specialListingQuery($query);
		$this->basicListingQuery($query);

		// The ordering columns names needs to be mapped here as this does not match with the main query columns.
		$ordering = $this->state->get('list.fullordering', 'l.publish_up DESC');

		if (trim($ordering))
		{
			$orderCols = array(
				'a.product_id'     => 'a.id',
				'a.product_title'  => 'a.title',
				'a.product_active' => 'a.state',
				'a.seller_company' => 'p.seller_company',
				'a.stock'          => 'p.stock',
				'a.product_price'  => 'p.product_price',
				'a.listing_start'  => 'l.listing_start',
				'a.listing_end'    => 'l.listing_end',
			);

			@list($orderCol, $orderDir) = explode(' ', $ordering);

			$orderCol = ArrayHelper::getValue($orderCols, $orderCol, null);
			$orderDir = in_array(strtoupper($orderDir), array('ASC', 'DESC')) ? $orderDir : 'ASC';

			if ($orderCol && $orderDir)
			{
				$ordering = $orderCol . ' ' . $orderDir;

				$query->order($db->escape($ordering));
			}
		}

		return $query;
	}

	/**
	 * Extend items seller etc properties to basic query
	 *
	 * @param   JDatabaseQuery  $query
	 *
	 * @return  void
	 *
	 * @since   1.0.0
	 */
	protected function extendItemQuery($query)
	{
		// PSX(+T) applied
		$pQuery = $this->_db->getQuery(true);

		// Add price and stock info
		$pQuery->select('pp.product_id, pp.seller_uid, pp.id AS price_id, pp.cost_price, pp.margin, pp.margin_type')
			->select('pp.list_price, pp.calculated_price, pp.product_price, pp.product_price AS sales_price, pp.ovr_price, pp.is_fallback')
			->from($this->_db->qn('#__sellacious_product_prices', 'pp'))
			->where('pp.is_fallback = 1');

		// Add seller information
		$pQuery->select('ss.title AS seller_company, ss.store_name AS seller_store')
			->select("IF (ss.state = 1 AND u.block = 0, 1, 0) AS seller_active")
			->join('LEFT', $this->_db->qn('#__sellacious_sellers', 'ss') . ' ON ss.user_id = pp.seller_uid');

		$pQuery->select('u.name AS seller_name, u.username AS seller_username, u.email AS seller_email')
			->join('INNER', $this->_db->qn('#__users', 'u') . ' ON u.id = pp.seller_uid');

		$g_currency       = $this->helper->currency->getGlobal('code_3');
		$listing_currency = $this->helper->config->get('listing_currency');
		$seller_currency  = $listing_currency ? $this->_db->qn('ss.currency') : $this->_db->q($g_currency);

		$pQuery->select($seller_currency . ' AS seller_currency')
			->join('LEFT', $this->_db->qn('#__sellacious_profiles', 'su') . ' ON su.user_id = pp.seller_uid');

		// Now append everything to the main query
		$query->select('p.*');

		if ($this->helper->config->get('shipped_by') == 'seller')
		{
			$query->select("CASE a.type WHEN 'physical' THEN psp.flat_shipping WHEN 'package' THEN psk.flat_shipping END AS flat_shipping")
				->select("CASE a.type WHEN 'physical' THEN psp.shipping_flat_fee WHEN 'package' THEN psk.shipping_flat_fee END AS shipping_flat_fee");
		}
		else
		{
			$flat_shipping     = $this->helper->config->get('flat_shipping');
			$shipping_flat_fee = $flat_shipping ? $this->helper->config->get('shipping_flat_fee') : 0;

			$query->select($this->_db->q($flat_shipping) . ' AS flat_shipping')->select($this->_db->q($shipping_flat_fee) . ' AS shipping_flat_fee');
		}

		$query->select('psx.pricing_type, psx.stock, psx.over_stock, psx.stock + psx.over_stock AS stock_capacity, psx.state AS is_selling')
			->select("CASE a.type WHEN 'physical' THEN psp.listing_type WHEN 'package' THEN psk.listing_type END AS listing_type")
			->select("CASE a.type WHEN 'physical' THEN psp.item_condition WHEN 'package' THEN psk.item_condition END AS item_condition")
			->select("CASE a.type WHEN 'physical' THEN psp.whats_in_box WHEN 'package' THEN psk.whats_in_box END AS whats_in_box")
			->select("CASE a.type WHEN 'physical' THEN psp.return_days WHEN 'package' THEN psk.return_days END AS return_days")
			->select("CASE a.type WHEN 'physical' THEN psp.return_tnc WHEN 'package' THEN psk.return_tnc END AS return_tnc")
			->select("CASE a.type WHEN 'physical' THEN psp.exchange_days WHEN 'package' THEN psk.exchange_days END AS exchange_days")
			->select("CASE a.type WHEN 'physical' THEN psp.exchange_tnc WHEN 'package' THEN psk.exchange_tnc END AS exchange_tnc");

		$query->select('0 AS multi_price');

		$query->join('LEFT', '(' . $pQuery . ') AS p ON p.product_id = a.id')
			->join('LEFT', $this->_db->qn('#__sellacious_product_sellers', 'psx') . ' ON psx.product_id = p.product_id AND psx.seller_uid = p.seller_uid')
			->join('LEFT', $this->_db->qn('#__sellacious_physical_sellers', 'psp') . ' ON psp.psx_id = psx.id')
			->join('LEFT', $this->_db->qn('#__sellacious_package_sellers', 'psk') . ' ON psk.psx_id = psx.id');
	}

	/**
	 * Add a comma separated list of subscribed Special Category IDs
	 *
	 * @param   JDatabaseQuery  $query
	 *
	 * @return  void
	 *
	 * @since   1.0.0
	 */
	protected function basicListingQuery($query)
	{
		$db     = $this->getDbo();
		$now    = JFactory::getDate()->toSql();
		$nullDt = $db->getNullDate();

		// Special category listing
		$sub = $db->getQuery(true);
		$sub->select('l.seller_uid, l.product_id')
			->select('l.publish_up AS listing_start, l.publish_down AS listing_end, l.state AS listing_active')
			->from($db->qn('#__sellacious_seller_listing', 'l'))
			->where('l.category_id = 0')
			->where('l.publish_up != ' . $db->q($nullDt))
			->where('l.publish_down != ' . $db->q($nullDt))
			->where('l.publish_up < ' . $db->q($now))
			->where('l.publish_down > ' . $db->q($now))
			->where('l.state = 1')
			->group('l.seller_uid, l.product_id');

		$query->select('l.listing_start, l.listing_end, l.listing_active AS listing_state')
			->join('LEFT', "($sub) AS l ON l.product_id = a.id AND l.seller_uid = p.seller_uid");
	}

	/**
	 * Add a comma separated list of subscribed Special Category IDs
	 *
	 * @param   JDatabaseQuery  $query
	 *
	 * @return  void
	 *
	 * @since   1.0.0
	 */
	protected function specialListingQuery($query)
	{
		$db     = $this->getDbo();
		$now    = JFactory::getDate()->toSql();
		$nullDt = $db->getNullDate();

		// Special category listing
		$sub = $db->getQuery(true);
		$sub->select('l.seller_uid, l.product_id')
			->select('GROUP_CONCAT(l.category_id) AS spl_category_ids')
			->from($db->qn('#__sellacious_seller_listing', 'l'))
			->where('l.category_id > 0')
			->where('l.publish_up != ' . $db->q($nullDt))
			->where('l.publish_down != ' . $db->q($nullDt))
			->where('l.publish_up < ' . $db->q($now))
			->where('l.publish_down > ' . $db->q($now))
			->where('l.state = 1')
			->group('l.seller_uid, l.product_id');

		$query->select('spl_category_ids, null AS spl_category_titles')
			->join('LEFT', "($sub) AS spl ON spl.product_id = a.id AND spl.seller_uid = p.seller_uid");
	}

	/**
	 * Method to get an array of data items.
	 *
	 * @return  mixed  An array of data items on success, false on failure.
	 *
	 * @since   2.0.0
	 */
	public function getItems()
	{
		$state = $this->getState('filter.state');

		// We have to use cache for published and unpublished items items
		if (!is_numeric($state) || $state == 0 || $state == 1)
		{
			try
			{
				return self::getCacheItems();
			}
			catch (Exception $e)
			{
				$this->setError($e->getMessage());

				return false;
			}
		}

		return parent::getItems();
	}

	/**
	 * Method to get the total number of items for the data set.
	 *
	 * @return  integer  The total number of items available in the data set.
	 *
	 * @since   2.0.0
	 */
	public function getTotal()
	{
		$state = $this->getState('filter.state');

		// We have to use cache for published and unpublished items items
		if (isset($this->loader) && (!is_numeric($state) || $state == 0 || $state == 1))
		{
			try
			{
				return $this->loader->getTotal();
			}
			catch (Exception $e)
			{
				$this->app->enqueueMessage($e->getMessage());

				return 0;
			}
		}

		return parent::getTotal();
	}

	/**
	 * Get list items from products cache
	 *
	 * @return   stdClass[]
	 *
	 * @throws   Exception
	 *
	 * @since    2.0.0
	 */
	public function getCacheItems()
	{
		$this->loader = new ProductsCacheReader;

		$query  = $this->loader->getQuery(true);

		$this->loader->filterValue('variant_id', 0);

		$this->filterSearch($this->loader);

		$ordering = $this->state->get('list.fullordering', 'listing_start DESC');

		if (trim($ordering))
		{
			$query->order($query->escape($ordering));
		}

		$items = $this->loader->getItems($this->getStart(), $this->getState('list.limit'));

		$items = $this->processList($items);

		return $items;
	}

	/**
	 * Filter the list query by search text and other filters
	 *
	 * @param   ProductsCacheReader  $loader
	 *
	 * @return  void
	 *
	 * @since   2.0.0
	 */
	protected function filterSearch($loader)
	{
		$search = $this->getState('filter.search');

		if ($search)
		{
			if (stripos($search, 'id:') === 0)
			{
				$loader->filterValue('product_id', (int) substr($search, 3));
			}
			elseif (stripos(strtolower($search), 'sku:') === 0)
			{
				$loader->filterValue('product_sku', substr($search, 4), 'LIKE');
			}
			elseif (stripos(strtolower($search), 't:') === 0)
			{
				$loader->filterValue('product_title', substr($search, 2), 'LIKE');
			}
			else
			{
				$query  = $loader->getQuery();
				$search = $query->q('%' . $query->e($search, true) . '%', false);

				$query->where(sprintf('(product_sku LIKE %1$s OR product_title LIKE %1$s OR product_description LIKE %1$s OR unique_field_value LIKE %1$s)', $search));
			}
		}

		// Filter by price display
		$pricing_type = $this->getState('filter.pricing_type');

		if ($pricing_type)
		{
			$loader->filterValue('pricing_type', $pricing_type);
		}

		// Filter by selling state
		$selling = $this->getState('list.selling');

		if (is_numeric($selling))
		{
			$loader->filterValue('is_selling', (int) $selling);
		}

		// Filter by published state
		$state = $this->getState('filter.state');

		if (is_numeric($state))
		{
			$loader->filterValue('product_active', (int) $state);
		}

		// Filter by category
		if ($categoryId = $this->getState('filter.category'))
		{
			$loader->filterInJsonKey('category_ext', null, $categoryId);
		}

		if ($type = $this->getState('filter.type'))
		{
			$loader->filterValue('product_type', $type);
		}

		// Filter by manufacturer
		if ($manufacturerId = $this->getState('filter.manufacturer'))
		{
			$loader->filterValue('manufacturer_id', (int) $manufacturerId);
		}

		// Filter by seller
		if ($this->helper->access->check('product.list'))
		{
			if ($seller_uid = $this->getState('filter.seller_uid'))
			{
				$loader->filterValue('seller_uid', (int) $seller_uid);
			}
		}
		elseif ($this->helper->access->check('product.list.own'))
		{
			$loader->getQuery()->where(sprintf('(seller_uid = %1$d OR owner_uid = %1$d)', JFactory::getUser()->id));
		}
		else
		{
			$loader->fallacy();
		}

		// Filter by language
		if ($lang = $this->getState('filter.language'))
		{
			$loader->filterValue('language', $lang);
		}
	}

	/**
	 * Pre-process loaded list before returning if needed
	 *
	 * @param   stdClass[]  $items
	 *
	 * @return  stdClass[]
	 *
	 * @since   1.5.0
	 */
	protected function processList($items)
	{
		if ($items)
		{
			$g_currency = $this->helper->currency->getGlobal('code_3');
			$inventory  = $this->getState('layout') == 'bulk';

			$adv = array(
				'price_id',
				'qty_min',
				'qty_max',
				'sdate',
				'edate',
				'cost_price',
				'margin',
				'margin_type',
				'list_price',
				'calculated_price',
				'ovr_price',
				'product_price',
				'is_fallback',
				'state',
			);

			foreach ($items as &$item)
			{
				// New cache vars mapping as quick fix
				if (!isset($item->category_ids) && property_exists($item, 'category'))
				{
					$cats = json_decode($item->category, true) ?: array();

					$item->categories      = array_keys($cats);
					$item->category_ids    = implode(',', $item->categories);
					$item->category_titles = array_values($cats);
				}
				else
				{
					$item->categories      = explode(',', $item->category_ids);
					$item->category_titles = explode('|:|', $item->category_titles);
				}

				if (!isset($item->spl_category_titles) && property_exists($item, 'spl_category'))
				{
					$cat2 = json_decode($item->spl_category, true) ?: array();

					$item->spl_categories      = array_keys($cat2);
					$item->spl_category_titles = array_values($cat2);
				}
				else
				{
					$item->spl_categories = explode(',', $item->spl_category_ids);
				}

				if (!isset($item->code))
				{
					$item->code = $this->helper->product->getCode($item->product_id, 0, $item->seller_uid);
				}

				$item->owned_by             = $item->owner_uid;
				$item->title                = $item->product_title;
				$item->local_sku            = $item->product_sku;
				$item->type                 = $item->product_type;
				$item->state                = $item->product_active;
				$item->listing_publish_up   = $item->listing_start;
				$item->listing_publish_down = $item->listing_end;
				$item->advance_prices       = isset($item->advance_prices) ? json_decode($item->advance_prices) : array();

				if ($inventory)
				{
					foreach ($adv as $k)
					{
						$item->$k = null;
					}

					foreach ($item->advance_prices as $price)
					{
						if ($price->is_fallback)
						{
							foreach ($price as $pi => $pv)
							{
								$item->$pi = $pv;
							}

							break;
						}
					}
				}

				// Translate categories
				if ($item->language)
				{
					$categoryTitles = array();

					foreach ($item->categories as $key => $category)
					{
						$transCategory        = new stdClass;
						$transCategory->id    = $category;
						$transCategory->title = $item->category_titles[$key];

						$this->helper->translation->translateRecord($transCategory, 'sellacious_categories', $item->language);

						$categoryTitles[] = $transCategory->title;
					}

					$item->category_titles = $categoryTitles;
				}

				if (!$item->seller_currency)
				{
					$item->seller_currency = $g_currency;
				}

				if (!isset($item->order_count))
				{
					$item->order_count = $this->helper->order->getOrderCount($item->product_id, 0, $item->seller_uid);
					$item->order_units = $this->helper->order->getOrderCount($item->product_id, 0, $item->seller_uid, true);
				}
			}
		}

		return $items;
	}
}
