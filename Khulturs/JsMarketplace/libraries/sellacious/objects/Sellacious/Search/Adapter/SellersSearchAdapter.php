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

use Sellacious\Search\SearchHelper;

/**
 * Adapter class for product sellers search
 *
 * @since   1.7.0
 */
class SellersSearchAdapter extends AbstractSearchAdapter
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

		$query->select('a.title, a.store_name')
		      ->from($db->qn('#__sellacious_sellers', 'a'))
		      ->where('a.state = 1');

		$query->select('u.id')
		      ->join('inner', $db->qn('#__users', 'u') . ' ON u.id = a.user_id');

		SearchHelper::createIndex($this->getName(), (string) $query, 'id');
	}

	/**
	 * Perform a search for the relevant entity
	 *
	 * @return  \stdClass[]
	 *
	 * @since   1.7.0
	 */
	public function load()
	{
		$results  = array();
		$catid    = $this->filters->get('parent_category');

		if ($this->start > 0 || $this->limit > 0)
		{
			$iterator = new \LimitIterator($this->getIterator(), $this->start, $this->limit);
		}
		else
		{
			$iterator = $this->getIterator();
		}

		foreach ($iterator as $seller)
		{
			try
			{
				$link  = 'index.php?option=com_sellacious&view=store&id=' . $seller->user_id;
				$plink = 'index.php?option=com_sellacious&view=products&shop_uid=' . $seller->user_id;

				if ($catid)
				{
					$link  .= '&category_id=' . $catid;
					$plink .= '&category_id=' . $catid;
				}

				$item = new \stdClass;

				$item->type  = 'sellers';
				$item->uid   = $seller->user_id;
				$item->image = $this->helper->media->getImage('sellers.logo', $seller->id);
				$item->q     = $this->filters->get('q');
				$item->value = !empty($seller->store_name) ? $seller->store_name : $seller->title;
				$item->link  = \JRoute::_($link, false);
				$item->plink = \JRoute::_($plink, false);

				$results[] = $item;
			}
			catch (\Exception $e)
			{
			}
		}

		return $results;
	}

	/**
	 * Execute the search query. Also set number of matching records found
	 *
	 * @return  \JDatabaseIterator
	 *
	 * @throws  \Exception
	 *
	 * @since   1.7.0
	 */
	protected function execute()
	{
		$catid   = $this->filters->get('parent_category');
		$filters = array(
			'list.select' => 'a.id, a.user_id, a.title, a.store_name',
			'user_id'     => $this->keys,
		);

		if ($catid)
		{
			$cats = $this->helper->category->getChildren($catid, true);

			if ($cats)
			{
				$filters['list.join'][]  = array('inner', $this->db->qn('#__sellacious_product_sellers', 'p') . ' ON p.seller_uid = a.user_id');
				$filters['list.join'][]  = array('inner', $this->db->qn('#__sellacious_product_categories', 'c') . ' ON c.product_id = p.product_id');
				$filters['list.where'][] = 'c.category_id IN (' . implode(',', $cats) . ')';
			}
			else
			{
				$filters['list.where'][] = '0';
			}
		}

		$iterator = $this->helper->seller->getIterator($filters);

		return $iterator;
	}
}
