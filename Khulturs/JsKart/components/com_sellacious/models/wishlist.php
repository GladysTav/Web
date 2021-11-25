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

use Sellacious\Cache\Reader\ProductsCacheReader;
use Sellacious\Price\PriceHelper;

/**
 * Methods supporting a list of Products
 *
 * @since   1.2.0
 */
class SellaciousModelWishlist extends SellaciousModelList
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
	 * Constructor
	 *
	 * @param   array  $config  An optional associative array of configuration settings.
	 *
	 * @see     JControllerLegacy
	 *
	 * @throws  Exception
	 *
	 * @since   1.2.0
	 */
	public function __construct($config = array())
	{
		if (empty($config['filter_fields']))
		{
			$config['filter_fields'] = array(
				'id', 'a.id',
				'title', 'a.title',
				'alias', 'a.alias',
				'state', 'a.state',
			);
		}

		$this->loader = new ProductsCacheReader;

		parent::__construct($config);
	}

	/**
	 * Method to auto-populate the model state.
	 *
	 * This method should only be called once per instantiation and is designed
	 * to be called on the first call to the getState() method unless the model
	 * configuration flag to ignore the request is set
	 *
	 * Note: Calling getState in this method will result in recursion
	 *
	 * @param   string  $ordering   An optional ordering field.
	 * @param   string  $direction  An optional direction (asc|desc)
	 *
	 * @return  void
	 *
	 * @since   12.2
	 */
	protected function populateState($ordering = null, $direction = null)
	{
		parent::populateState($ordering, $direction);

		$uid  = $this->app->input->get('user_id');
		$user = JFactory::getUser($uid);

		$this->state->set('wishlist.user_id', $user->id);
	}

	public function getItems()
	{
		$store = $this->getStoreId();

		if (isset($this->cache[$store]))
		{
			return $this->cache[$store];
		}

		try
		{
			$db     = $this->getDbo();
			$query  = $db->getQuery(true);
			$userId = $this->state->get('wishlist.user_id');

			$query->select('id, user_id, product_id, variant_id, seller_uid, created')
				->from($db->qn('#__sellacious_wishlist', 'a'))
				->where('a.user_id = ' . (int) $userId);

			$rows = $db->setQuery($query)->getIterator();
			$cond = array();

			$c = 'product_id = %1$d';

			if ($this->helper->config->get('multi_variant'))
			{
				$c .= ' AND variant_id = %2$d';
			}

			if ($this->helper->config->get('multi_seller'))
			{
				$c .= ' AND seller_uid = %3$d';
			}

			foreach ($rows as $row)
			{
				$cond[] = sprintf('(' . $c . ')', $row->product_id, $row->variant_id, $row->seller_uid);
			}

			$query = $this->loader->getQuery(true);

			if ($cond)
			{
				$query->where(implode(' OR ' , $cond));
			}
			else
			{
				$this->loader->fallacy();
			}

			$items = $this->loader->getItems($this->getStart(), $this->getTotal());
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
}
