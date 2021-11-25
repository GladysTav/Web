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
 * Adapter class for product categories search
 *
 * @since   1.7.0
 */
class CategoriesSearchAdapter extends AbstractSearchAdapter
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

		$query->select('a.id, a.title')
		      ->from($db->qn('#__sellacious_categories', 'a'))
		      ->where('a.type LIKE ' . $db->q('product/%', false))
		      ->where('a.state = 1');

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
		$sid      = $this->filters->get('seller');

		if ($this->start > 0 || $this->limit > 0)
		{
			$iterator = new \LimitIterator($this->getIterator(), $this->start, $this->limit);
		}
		else
		{
			$iterator = $this->getIterator();
		}

		foreach ($iterator as $category)
		{
			try
			{
				$link  = 'index.php?option=com_sellacious&view=categories&category_id=' . $category->id;
				$plink = 'index.php?option=com_sellacious&view=products&category_id=' . $category->id;

				if ($sid)
				{
					$link  .= '&store_id=' . $sid;
					$plink .= '&shop_uid=' . $sid;
				}

				$item = new \stdClass;

				$item->type        = 'categories';
				$item->category_id = $category->id;
				$item->image       = $this->helper->media->getImage('categories', $category->id);
				$item->q           = $this->filters->get('q');
				$item->value       = $category->title;
				$item->link        = \JRoute::_($link, false);
				$item->plink       = \JRoute::_($plink, false);

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
	 * @since   1.7.0
	 */
	protected function execute()
	{
		$sid     = $this->filters->get('seller');
		$filters = array(
			'list.select' => 'a.id, a.title',
			'id'          => $this->keys,
			'list.where'  => array('a.parent_id > 0', 'a.level > 0'),
		);

		if ($sid)
		{
			$filters['list.join'][]  = array('inner', $this->db->qn('#__sellacious_product_categories', 'c') . ' ON c.category_id = a.id');
			$filters['list.join'][]  = array('inner', $this->db->qn('#__sellacious_product_sellers', 'p') . ' ON p.product_id = c.product_id');
			$filters['list.where'][] = 'p.seller_uid = ' . (int) $sid;
		}

		$iterator = $this->helper->category->getIterator($filters);

		return $iterator;
	}
}
