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
 * Methods supporting a list of EWallet Transactions.
 *
 * @since   2.0.0
 */
class SellaciousModelTransactions extends SellaciousModelList
{
	/**
	 * Constructor.
	 *
	 * @param  array $config An optional associative array of configuration settings.
	 *
	 * @see    JController
	 * @since  2.0.0
	 */
	public function __construct($config = array())
	{
		if (empty($config['filter_fields']))
		{
			$config['filter_fields'] = array(
				'id', 'a.id',
				'state', 'a.state',
			);
		}

		parent::__construct($config);
	}

	/**
	 * Build an SQL query to load the list data.
	 *
	 * @return  JDatabaseQuery
	 *
	 * @since   2.0.0
	 */
	protected function getListQuery()
	{
		// Create a new query object.
		$db    = $this->getDbo();
		$query = $db->getQuery(true);

		// Select the required fields from the table.
		$query->select($this->getState('list.select', 'a.*'))
			->from($db->qn('#__sellacious_transactions', 'a'));

		if ($this->helper->access->check('transaction.list.own'))
		{
			// Force user filter to limit to own items.
			$me      = JFactory::getUser();
			$filterU = sprintf(
				'((%s AND %s) OR %s)',
				'a.context = ' . $db->q('user.id'),
				'a.context_id = ' . (int) $me->id,
				'a.user_id = ' . (int) $me->id
			);

			$query->where($filterU);
		}
		else
		{
			$query->where('0');
		}

		// Add the list ordering clause.
		$ordering = $this->state->get('list.fullordering', 'a.txn_date DESC');

		if (trim($ordering))
		{
			$query->order($db->escape($ordering));
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
	protected function processList($items)
	{
		$items = parent::processList($items);

		if (count($items) > 0)
		{
			foreach ($items as $item)
			{
				// Example of $item->reason: addfund.gateway
				$reason   = strtoupper(str_replace('.', '_', rtrim($item->reason, '.')));
				$item->reason = JText::_('COM_SELLACIOUS_TRANSACTION_REASON_' . $reason);
			}
		}

		return $items;
	}
}
