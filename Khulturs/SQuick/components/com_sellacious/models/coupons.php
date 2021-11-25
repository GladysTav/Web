<?php
/**
 * @version     2.0.0
 * @package     sellacious
 *
 * @copyright   Copyright (C) 2012-2020 Bhartiy Web Technologies. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Aditya Chakraborty <info@bhartiy.com> - http://www.bhartiy.com
 */
// no direct access.
defined('_JEXEC') or die;

/**
 * Methods supporting a list of Coupons
 *
 * @since   2.0.0
 */
class SellaciousModelCoupons extends SellaciousModelList
{
	/**
	 * Constructor
	 *
	 * @param   array  $config  An optional associative array of configuration settings
	 *
	 * @see     JControllerLegacy
	 *
	 * @throws  Exception
	 *
	 * @since   2.0.0
	 */
	public function __construct($config = array())
	{
		if (empty($config['filter_fields']))
		{
			$config['filter_fields'] = array(
				'id',
				'a.id',
				'title',
				'a.title',
				'state',
				'a.state',
			);
		}

		parent::__construct($config);
	}

	/**
	 * Build an SQL query to load the list data
	 *
	 * @return  JDatabaseQuery
	 *
	 * @since   1.5.3
	 */
	protected function getListQuery()
	{
		// Create a new query object
		$db    = $this->getDbo();
		$query = $db->getQuery(true);

		$query->select('a.*')
			->from($db->qn('#__sellacious_coupons', 'a'))
			->where('a.state = 1');

		$sellerUid = $this->state->get('filter.seller_uid');

		if ($sellerUid != '')
		{
			$query->where('a.seller_uid = ' . (int) $sellerUid);
		}

		return $query;
	}

	/**
	 * Pre-process loaded list before returning if needed
	 *
	 * @param   stdClass[]  $items  The items loaded from the database using the list query
	 *
	 * @return  stdClass[]
	 *
	 * @since   2.0.0
	 */
	public function processList($items)
	{
		foreach ($items as $item)
		{
			$this->helper->translation->translateRecord($item, 'sellacious_coupon');
		}

		return parent::processList($items);
	}
}
