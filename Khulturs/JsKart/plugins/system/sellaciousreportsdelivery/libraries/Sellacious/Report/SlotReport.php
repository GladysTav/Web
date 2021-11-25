<?php
/**
 * @version     2.0.0
 * @package     sellacious
 *
 * @copyright   Copyright (C) 2012-2020 Bhartiy Web Technologies. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Aditya Chakraborty <info@bhartiy.com> - http://www.bhartiy.com
 */

namespace Sellacious\Report;

// no direct access
defined('_JEXEC') or die;

/**
 * Report handler for seller report
 *
 * @package   Sellacious\Report
 *
 * @since   1.7.0
 */
class SlotReport extends ReportHandler
{
	/**
	 * Constructor
	 *
	 * @since   1.7.0
	 */
	public function __construct()
	{
		$this->manifestPath = JPATH_PLUGINS . '/system/sellaciousreportsdelivery/manifests/slot-report.xml';

		parent::__construct();
	}

	/**
	 * Get total records
	 *
	 * @return  int
	 *
	 * @since   1.7.0
	 */
	public function getTotal()
	{
		if (!$this->total)
		{
			$query = $this->getListQuery();
			$this->db->setQuery($query);
			$list = $this->db->loadObjectList();

			$this->processData($list);

			$this->total = count($list);
		}

		return $this->total;
	}

	/**
	 * Get Report List Items
	 *
	 * @param   int $start Starting index of the items
	 * @param   int $limit Number of records to show
	 *
	 * @return  \stdClass[]
	 *
	 * @throws  \Exception
	 *
	 * @since   1.7.0
	 */
	public function getList($start = 0, $limit = 0)
	{
		// Create query
		$query = $this->getListQuery();

		// Get List
		$this->db->setQuery($query, $start, $limit);

		$list = $this->db->loadObjectList();

		$this->processData($list);

		return $list;
	}

	/**
	 * Method to get a JDatabaseQuery object for retrieving the data set from a database.
	 *
	 * @return  \JDatabaseQuery  A JDatabaseQuery object to retrieve the data set.
	 *
	 * @since   1.7.0
	 */
	public function getListQuery()
	{
		$db    = $this->db;
		$query = $db->getQuery(true);

		// Report Filters
		$reportFilters = $this->getFilter();

		// User Filters
		$userFilters = $this->getUserFilter();

		// Selected columns
		$columns = $this->getColumns();

		// Subquery to get total sales
		$salesQuery = $db->getQuery(true);
		$salesQuery->select('SUM(e.sub_total)');
		$salesQuery->from($db->qn('#__sellacious_order_items', 'e'));
		$salesQuery->join('INNER', $db->quoteName('#__sellacious_order_delivery_slot', 'd') . ' ON (' . $db->quoteName('d.order_item_id') . ' = ' . $db->quoteName('e.id') . ')');
		$salesQuery->where('d.slot_from_time = a.slot_from_time AND d.slot_to_time = a.slot_to_time');

		// Subquery to get last sale
		$lastSaleQuery = $db->getQuery(true);
		$lastSaleQuery->select('CASE f.modified WHEN ' . $db->q('0000-00-00 00:00:00') . ' THEN f.created ELSE f.modified END');
		$lastSaleQuery->from($db->qn('#__sellacious_product_seller_slot_limits', 'f'));
		$lastSaleQuery->where('f.id = a.id');

		// Exact values to select for each column
		$queryColumns = array(
			"seller_name"    => 'b.title as seller_name',
			"product_name"   => 'c.title as product_name',
			"slot_from_time" => 'a.slot_from_time',
			"slot_limit"     => 'a.slot_limit',
			"slot_used"      => '(a.slot_limit - a.slot_count) AS slot_used',
			"slot_count"     => 'a.slot_count',
			"total_sales"    => '(' . $salesQuery->__toString() . ') as total_sales',
			"last_sale"    => '(' . $lastSaleQuery->__toString() . ') as last_sale',
		);

		$processedColumns  = $this->processColumns($columns, $queryColumns);
		$selectedColumns   = array_values($processedColumns);
		$selectedColumns[] = 'a.slot_to_time';
		$selectedColumns[] = 'a.full_day';

		$query->select($selectedColumns);
		$query->from($db->quoteName('#__sellacious_product_seller_slot_limits', 'a'));
		$query->join('INNER', $db->quoteName('#__sellacious_sellers', 'b') . ' ON (' . $db->quoteName('b.user_id') . ' = ' . $db->quoteName('a.seller_uid') . ')');
		$query->join('INNER', $db->quoteName('#__sellacious_products', 'c') . ' ON (' . $db->quoteName('c.id') . ' = ' . $db->quoteName('a.product_id') . ')');
		$query->join('INNER', $db->quoteName('#__sellacious_order_delivery_slot', 'd') . ' ON (' . $db->quoteName('d.slot_from_time') . ' = ' . $db->quoteName('a.slot_from_time') . ' AND ' . $db->quoteName('d.slot_to_time') . ' = ' . $db->quoteName('a.slot_to_time') . ')');
		$query->join('INNER', $db->quoteName('#__sellacious_order_items', 'e') . ' ON (' . $db->quoteName('e.id') . ' = ' . $db->quoteName('d.order_item_id') . ')');

		// Applying user search filters
		if (isset($userFilters['search']) && !empty($userFilters['search']))
		{
			$where[] = 'b.title LIKE ' . $db->quote('%' . $userFilters['search'] . '%');
			$where[] = 'c.title LIKE ' . $db->quote('%' . $userFilters['search'] . '%');
			$query->where('(' . implode(' OR ', $where) . ')');
		}

		if (isset($userFilters['sellerid']) && $userFilters['sellerid'] > 0)
		{
			$query->where('b.user_id = ' . (int) $userFilters['sellerid']);
		}

		if (isset($userFilters['date']) && !empty($userFilters['date']))
		{
			$query->where('DATE_FORMAT(a.slot_from_time, "%Y-%m-%d") = ' . $db->q($userFilters['date']));
		}

		$query->group('a.id');

		$ordering = $this->getOrdering();

		if (!empty(trim($ordering)))
		{
			$query->order($ordering);
		}

		return $query;
	}

	/**
	 * Method to get report data.
	 *
	 * @param   \stdClass[]  $selectedColumns  Selected Report Columns
	 * @param   array        $queryColumns     Columns for Query
	 *
	 * @return  mixed   Report data.
	 *
	 * @since   1.7.0
	 */
	public function processColumns($selectedColumns, $queryColumns)
	{
		$columns = array();

		foreach ($selectedColumns as $column)
		{
			$queryColNames = array_keys($queryColumns);

			if (in_array($column->name, $queryColNames))
			{
				$columns[$column->name] = $queryColumns[$column->name];
			}
		}

		return $columns;
	}

	/**
	 * Method to process report data.
	 *
	 * @param   array  $items Report data
	 *
	 * @return  null
	 *
	 * @since   1.7.0
	 */
	public function processData(&$items)
	{
		if (!empty($items))
		{
			foreach ($items as $key => $item)
			{
				if (property_exists($item, 'slot_from_time'))
				{
					$fromDateTime = \JFactory::getDate($item->slot_from_time);
					$toDateTime   = \JFactory::getDate($item->slot_to_time);
					$fromTime     = $fromDateTime->format('g:i A');
					$toTime       = $toDateTime->format('g:i A');
					$timeRange    = $item->full_day ? \JText::_('PLG_SYSTEM_SELLACIOUSREPORTSDELIVERY_FULL_DAY') : $fromTime . ' - ' . $toTime;

					$items[$key]->slot_from_time = $fromDateTime->format('d M, Y') . ' <b>(' . $timeRange . ')</b>';
				}

				if (property_exists($item, 'last_sale'))
				{
					$items[$key]->last_sale = $this->helper->core->relativeDateTime($items[$key]->last_sale);
				}

				// Unset all columns that were just added for processing data
				unset($items[$key]->slot_to_time);
				unset($items[$key]->full_day);
			}
		}
	}
}
