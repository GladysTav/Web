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
 * Methods supporting a list of Sellacious records.
 *
 * @since  2.0.0
 */
class SellaciousModelUserQuestions extends SellaciousModelList
{
	/**
	 * @var  array
	 *
	 * @since   2.0.0
	 */
	protected $items;

	/**
	 * Build an SQL query to load the list data.
	 *
	 * @return  JDatabaseQuery
	 *
	 * @since   2.0.0
	 */
	protected function getListQuery()
	{
		$me    = JFactory::getUser();
		$db    = JFactory::getDbo();
		$query = $db->getQuery(true);

		$query->select('a.*')->from($db->qn('#__sellacious_product_questions', 'a'));

		$query->select('p.title AS product_title')
			->join('left', '#__sellacious_products AS p ON p.id = a.product_id');

		$query->select('v.title AS variant_title')
			->join('left', '#__sellacious_variants AS v ON v.id = a.variant_id');

		$query->where('a.questioner_email = ' . $db->q($me->get('email')));

		// Add the list ordering clause.
		$query->order('a.created DESC');

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
	protected function processList($items)
	{
		foreach ($items as &$item)
		{
			$item->product_code = $this->helper->product->getCode($item->product_id, $item->variant_id, $item->seller_uid);

			if ($item->replied_by > 0)
			{
				$query = $this->_db->getQuery(true);
				$query->select('a.*, u.name, u.username, u.email')
					->from($this->_db->quoteName('#__sellacious_sellers') . ' AS a')
					->join('LEFT', '#__users AS u ON a.user_id = u.id');

				$query->where($this->_db->quoteName('a.user_id') . ' = ' . (int) $item->replied_by);

				$this->_db->setQuery($query);

				$seller       = $this->_db->loadObject();
				$item->seller = $seller;
			}
		}

		return parent::processList($items);
	}
}
