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
defined('_JEXEC') or die;

/**
 * Methods supporting a list of Sellacious records.
 *
 * @since   1.2.0
 */
class SellaciousModelRatings extends SellaciousModelList
{
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
				'a.id',
				'a.state',
				'a.author_name',
				'a.type',
				'a.title',
				'a.rating',
				'a.created',
				'product_title',
				'seller_company',
			);
		}

		parent::__construct($config);
	}

	/**
	 * Get the filter form
	 *
	 * @param   array    $data      Data
	 * @param   boolean  $loadData  Load current data
	 *
	 * @return  JForm|bool  The JForm object or false
	 *
	 * @since   3.2
	 */
	public function getFilterForm($data = array(), $loadData = true)
	{
		$form = parent::getFilterForm($data, $loadData);

		if ($form instanceof JForm)
		{
			if (!$this->helper->access->check('rating.list'))
			{
				$form->removeField('seller_uid', 'filter');
			}
		}

		return $form;
	}

	/**
	 * Build an SQL query to load the list data
	 *
	 * @return  JDatabaseQuery
	 *
	 * @since   1.2.0
	 */
	protected function getListQuery()
	{
		$db    = $this->getDbo();
		$query = $db->getQuery(true);

		$query->select($this->getState('list.select', 'a.*'))->from($db->qn('#__sellacious_ratings', 'a'));

		$query->select('CONCAT(p.title, " ", v.title) AS product_title')
			->join('left', $db->qn('#__sellacious_products', 'p') . ' ON p.id = a.product_id')
			->join('left', $db->qn('#__sellacious_variants', 'v') . ' ON v.id = a.variant_id');

		$query->select('COALESCE(s.title, su.name) AS seller_company')
			->join('left', '#__sellacious_sellers AS s ON s.user_id = a.seller_uid')
			->join('left', '#__users AS su ON su.id = a.seller_uid');

		$search = $this->getState('filter.search');

		if (stripos($search, 'id:') === 0)
		{
			$query->where('a.id = ' . (int) substr($search, 3));
		}
		elseif ($this->helper->product->parseCode($search, $productId, $variantId, $sellerUid))
		{
			$query->where('a.product_id = ' . (int) $productId);

			if ($variantId)
			{
				$query->where('a.variant_id = ' . (int) $variantId);
			}

			if ($sellerUid)
			{
				$query->where('a.seller_uid = ' . (int) $sellerUid);
			}
		}
		elseif ($search)
		{
			$query->where('a.title LIKE ' . $db->q('%' . $db->escape($search, true) . '%'));
		}

		$catId = $this->getState('filter.category');

		if ($catId)
		{
			$sub = $this->getDbo()->getQuery(true);

			$sub->select('product_id, GROUP_CONCAT(category_id) AS category_ids')
				->from('#__sellacious_product_categories')
				->group('product_id');

			$query->where('find_in_set('. (int) $catId . ', pc.category_ids)')
				->join('left', '(' . $sub . ') AS pc ON pc.product_id = a.product_id');
		}

		if ($this->helper->access->check('rating.list'))
		{
			if ($sellerUid = $this->getState('filter.seller_uid'))
			{
				$query->where('a.seller_uid = ' . (int) $sellerUid);
			}
		}
		elseif ($this->helper->access->check('rating.list.own'))
		{
			$me = JFactory::getUser();

			$query->where('a.seller_uid = ' . (int) $me->id);
		}

		$type = $this->getState('filter.type');

		if ($type == 'product' || $type == 'seller' || $type == 'shipment' || $type == 'packaging')
		{
			$query->where('a.type = ' . $db->q($type));
		}

		$state = $this->getState('filter.state');

		if (is_numeric($state))
		{
			$query->where('a.state = ' . (int) $state);
		}

		// Rating filter?
		$ordering = $this->state->get('list.fullordering', 'a.created DESC');

		if (trim($ordering))
		{
			$query->order($db->escape($ordering));
		}

		return $query;
	}
}
