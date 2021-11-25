<?php
/**
 * @version     1.7.4
 * @package     sellacious
 *
 * @copyright   Copyright (C) 2012-2019 Bhartiy Web Technologies. All rights reserved.
 * @license     SPL Sellacious Private License; see http://www.sellacious.com/spl.html
 * @author      Izhar Aazmi <info@bhartiy.com> - http://www.bhartiy.com
 */
// no direct access.
namespace Sellacious\Search\Adapter;

defined('_JEXEC') or die;

use Sellacious\Product as ProductObject;
use Sellacious\Search\SearchHelper;

/**
 * Adapter class for products search
 *
 * @since   1.7.0
 */
class ProductsSearchAdapter extends AbstractSearchAdapter
{
	/**
	 * Generate search index for TNT Search API based searches
	 *
	 * @return  void
	 *
	 * @throws  \Exception
	 *
	 * @since   1.7.0
	 */
	public function buildIndex()
	{
		$db    = \JFactory::getDbo();
		$query = $db->getQuery(true);

		$query->select('a.id, a.title, a.local_sku, a.metakey, a.tags')
		      ->from($db->qn('#__sellacious_products', 'a'))
		      ->where('a.state = 1');

		SearchHelper::createIndex($this->getName(), (string) $query, 'id');
	}

	/**
	 * Perform a search for the relevant entity
	 *
	 * @return  \stdClass[]
	 *
	 * @throws  \Exception
	 *
	 * @since   1.7.0
	 */
	public function load()
	{
		$results = array();
		$sid     = $this->filters->get('seller');

		if ($this->start > 0 || $this->limit > 0)
		{
			$iterator = new \LimitIterator($this->getIterator(), $this->start, $this->limit);
		}
		else
		{
			$iterator = $this->getIterator();
		}

		foreach ($iterator as $p)
		{
			$product    = new ProductObject($p->product_id);
			$price      = $product->getPrice($sid);
			$sCurrency  = $this->helper->currency->forSeller($price->seller_uid, 'code_3');
			$dPrice     = $this->helper->currency->display($price->basic_price, $sCurrency, '');

			$item = new \stdClass;

			$item->type       = 'product';
			$item->value      = $p->product_title;
			$item->price      = $price->no_price ? null : $dPrice;
			$item->code       = $product->getCode($price->seller_uid);
			$item->image      = $this->helper->product->getImage($p->product_id);
			$item->link       = \JRoute::_('index.php?option=com_sellacious&view=product&p=' . $item->code);
			$item->categories = implode(', ', explode('|:|', $p->category_titles));

			$results[] = $item;
		}

		return $results;
	}

	/**
	 * Execute the search query.
	 *
	 * @return  \JDatabaseIterator
	 *
	 * @since   1.7.0
	 */
	protected function execute()
	{
		$query    = $this->getListQuery();
		$iterator = $this->db->setQuery($query)->getIterator();

		return $iterator;
	}

	/**
	 * Method to build the list query.
	 *
	 * @return  \JDatabaseQuery  A JDatabaseQuery object
	 *
	 * @since   1.7.0
	 */
	protected function getListQuery()
	{
		$query = $this->db->getQuery(true);

		$query->select('a.product_id, a.product_title, a.category_titles')
		      ->from($this->db->qn('#__sellacious_cache_products', 'a'))
		      ->where('a.is_selling = 1')
		      ->where('a.listing_active = 1')
		      ->where('a.product_active = 1')
		      ->where('a.seller_active = 1');

		if ($this->helper->config->get('hide_out_of_stock'))
		{
			$query->where('a.stock + a.over_stock > 0');
		}

		$catid  = $this->filters->get('category');
		$catidP = $this->filters->get('parent_category');
		$sid    = $this->filters->get('seller');

		if ($this->keys)
		{
			$query->where('a.product_id IN (' . implode(', ', $this->keys) . ')')->where('a.variant_id = 0');
		}
		else
		{
			$query->where('0');
		}

		if ($catid)
		{
			$query->where('FIND_IN_SET(' . (int) $catid . ', a.category_ids)');
		}

		if ($catidP)
		{
			$cats = $this->helper->category->getChildren($catidP, true);

			if ($cats)
			{
				$wh = array();

				foreach ($cats as $cid)
				{
					$wh[] = 'FIND_IN_SET(' . (int) $cid . ', a.category_ids)';
				}

				$query->where('(' . implode(' OR ', $wh) . ')');
			}
			else
			{
				$query->where('0');
			}
		}

		if ($sid)
		{
			$query->where('a.seller_uid = ' . (int) $sid);
		}

		// $this->filterPrice($query);

		if ($this->helper->config->get('hide_zero_priced'))
		{
			$query->where('(a.sales_price > 0 ' . 'OR' . ' a.price_display > 0)');
		}

		return $query;
	}

	private function filterPrice(\JDatabaseQuery $query)
	{
		// @external entity used - start
		$me    = \JFactory::getUser();
		$catId = $this->helper->client->getCategory($me->id, true);
		// @external entity used - end

		$sub = $this->db->getQuery(true);
		$sub->select('pr.product_id, pr.seller_uid')
		    ->from($this->db->qn('#__sellacious_cache_prices', 'pr'))
		    ->where('(pr.client_catid = ' . $catId . ' OR COALESCE(pr.client_catid, 0) = 0)');

		$query->join('left', "($sub) AS p " . ' ON p.product_id = a.product_id AND p.seller_uid = a.seller_uid');

		if ($this->helper->config->get('hide_zero_priced'))
		{
			$query->where('(p.product_price > 0 ' . 'OR' . ' a.price_display > 0)');
		}
	}
}
