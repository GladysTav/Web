<?php
/**
 * @version     2.0.0
 * @package     sellacious
 *
 * @copyright   Copyright (C) 2012-2020 Bhartiy Web Technologies. All rights reserved.
 * @license     SPL Sellacious Private License; see http://www.sellacious.com/spl.html
 * @author      Izhar Aazmi <info@bhartiy.com> - http://www.bhartiy.com
 */
// no direct access.
namespace Sellacious\Search\Adapter;

defined('_JEXEC') or die;

use Exception;
use JDatabaseIterator;
use JDatabaseQuery;
use JFactory;
use JRoute;
use LimitIterator;
use Sellacious\Cache\Reader\ProductsCacheReader;
use Sellacious\Product as ProductObject;
use Sellacious\Search\SearchHelper;
use stdClass;

/**
 * Adapter class for product variants search
 *
 * @since   1.7.0
 */
class VariantsSearchAdapter extends AbstractSearchAdapter
{
	/**
	 * Generate search index for TNT Search API based searches
	 *
	 * @return  void
	 *
	 * @throws  Exception
	 *
	 * @since   1.7.0
	 */
	public function buildIndex()
	{
		$db    = JFactory::getDbo();
		$query = $db->getQuery(true);

		$query->select('a.id, a.title, a.local_sku')
		      ->from($db->qn('#__sellacious_variants', 'a'))
		      ->where('a.state = 1');

		$query->select("CONCAT(p.local_sku, ' ', a.local_sku) AS variant_sku")
		      ->join('left', $db->qn('#__sellacious_products', 'p') . ' ON p.id = a.product_id');

		SearchHelper::createIndex($this->getName(), (string) $query, 'id');
	}

	/**
	 * Perform a search for the relevant entity
	 *
	 * @return  stdClass[]
	 *
	 * @throws  Exception
	 *
	 * @since   1.7.0
	 */
	public function load()
	{
		$results = array();
		$sid     = $this->filters->get('seller');

		if ($this->start > 0 || $this->limit > 0)
		{
			$iterator = new LimitIterator($this->getIterator(), $this->start, $this->limit);
		}
		else
		{
			$iterator = $this->getIterator();
		}

		foreach ($iterator as $p)
		{
			$product   = new ProductObject($p->product_id, $p->variant_id);
			$price     = $product->getPrice($sid);
			$sCurrency = $this->helper->currency->forSeller($price->seller_uid, 'code_3');
			$dPrice    = $this->helper->currency->display($price->basic_price, $sCurrency, '');

			$item = new stdClass;

			$item->type       = 'product';
			$item->value      = $p->product_title . ' ' . $p->variant_title;
			$item->price      = $price->no_price ? null : $dPrice;
			$item->code       = $product->getCode($price->seller_uid);
			$item->image      = $this->helper->product->getImage($p->product_id, $p->variant_id);
			$item->link       = JRoute::_('index.php?option=com_sellacious&view=product&p=' . $item->code);
			$item->categories = implode(', ', json_decode($p->category_ext, true));

			$results[] = $item;
		}

		return $results;
	}

	/**
	 * Execute the search query to get an iterator instance
	 *
	 * @return  JDatabaseIterator
	 *
	 * @throws  Exception
	 *
	 * @since   1.7.0
	 */
	protected function execute()
	{
		$cache = new ProductsCacheReader;

		$cache->getQuery();

		$cache->filterValue('is_selling', 1);
		$cache->filterValue('listing_active', 1);
		$cache->filterValue('product_active', 1);
		$cache->filterValue('seller_active', 1);

		$catId  = $this->filters->get('category');
		$catIdP = $this->filters->get('parent_category');
		$sid    = $this->filters->get('seller');

		if ($this->keys)
		{
			$cache->filterValue('variant_id', $this->keys, 'IN');
		}
		else
		{
			$cache->fallacy();
		}

		if ($catId)
		{
			$cache->filterInJsonKey('category_ext', null, $catId);
		}

		if ($catIdP)
		{
			$cache->filterInJsonKey('category_ext', null, $catIdP);
		}

		if ($sid)
		{
			$cache->filterValue('seller_uid', (int) $sid);
		}

		if ($this->helper->config->get('hide_out_of_stock'))
		{
			$cache->filterValue('stock_capacity', 0, '>');
		}

		if ($this->helper->config->get('hide_zero_priced'))
		{
			// $cache->getQuery()->where('(sales_price > 0 OR price_display > 0)');
		}

		return $cache->getIterator();
	}
}
