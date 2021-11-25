<?php
/**
 * @version     2.0.0
 * @package     sellacious
 *
 * @copyright   Copyright (C) 2012-2020 Bhartiy Web Technologies. All rights reserved.
 * @license     SPL Sellacious Private License; see http://www.sellacious.com/spl.html
 * @author      Aditya Chakraborty <info@bhartiy.com> - http://www.bhartiy.com
 */
// no direct access.
use Joomla\Utilities\ArrayHelper;

defined('_JEXEC') or die;

/**
 * Delivery Orders
 *
 * @since  1.7.0
 */
class SellaciousdeliveryModelOrders extends SellaciousModelList
{
	/**
	 * @var  stdClass[]
	 *
	 * @since   1.7.0
	 */
	protected $items;

	/**
	 * Constructor
	 *
	 * @param   array  $config  An array of configuration options (name, state, dbo, table_path, ignore_request).
	 *
	 * @see     JModelList
	 * @since   1.0.0
	 */
	public function __construct(array $config)
	{
		if (empty($config['filter_fields']))
		{
			$config['filter_fields'] = array(
				'a.id',
				'a.order_number',
				'oi.product_title',
				'a.quantity',
				'a.customer_name',
				'a.created',
				'ods.slot_from_time',
				'ss.title',
				'a.sub_total',
			);
		}

		parent::__construct($config);
	}

	/**
	 * Get the filter form
	 *
	 * @param   array   $data     data
	 * @param   boolean $loadData load current data
	 *
	 * @return  JForm/false  the JForm object or false
	 *
	 * @since   1.7.0
	 */
	public function getFilterForm($data = array(), $loadData = true)
	{
		$form = parent::getFilterForm($data, $loadData);

		if ($form instanceof JForm)
		{
			if (!$this->helper->access->check('order.list'))
			{
				$form->removeField('seller_uid', 'filter');
			}
		}

		return $form;
	}

	/**
	 * Build an SQL query to load the list data.
	 *
	 * @return  JDatabaseQuery
	 *
	 * @since   1.7.0
	 */
	protected function getListQuery()
	{
		$me    = JFactory::getUser();
		$db    = JFactory::getDbo();
		$today = JFactory::getDate();
		$query = $db->getQuery(true);

		$query->select('a.*, oi.*, a.id as order_id, oi.id as oi_id, ods.slot_from_time, ods.slot_to_time')
			->from($db->qn('#__sellacious_order_items', 'oi'));

		$query->join('left', '#__sellacious_orders a ON a.id = oi.order_id');

		if ($this->helper->access->check('order.list'))
		{
			if ($seller_uid = $this->state->get('filter.seller_uid'))
			{
				$query->where('oi.seller_uid = ' . (int) $seller_uid)
					->group('a.id');
			}
		}
		elseif ($this->helper->access->check('order.list.own'))
		{
			$query->where('oi.seller_uid = ' . (int) $me->id);
		}
		else
		{
			// Fallacy
			return $query->where('0 = 1');
		}

		// Order status
		$query->select('os.status AS order_status_id, os.created as status_last_changed')
			->join('left', '#__sellacious_order_status AS os ON os.order_id = a.id AND os.state = 1 AND (os.item_uid = oi.item_uid OR os.item_uid = ' . $db->q('') . ')');

		$query->select('ss.title AS order_status, ss.type as status_type')
			->join('left', '#__sellacious_statuses AS ss ON ss.id = os.status');

		$query->join('left', '#__sellacious_order_delivery_slot ods ON ods.order_item_id = oi.id');

		if ($search = $this->getState('filter.search'))
		{
			if (stripos($search, 'id:') === 0)
			{
				$query->where('a.id = ' . (int) substr($search, 3));
			}
			else
			{
				$textSearch = $db->quote('%' . str_replace(' ', '%', $db->escape(trim($search), true) . '%'));
				$query->where('(a.order_number = '. $db->q($search) .
					' OR a.customer_name LIKE '. $textSearch .
					' OR ss.title LIKE '. $textSearch . ')');
			}
		}

		if ($os = $this->getState('filter.status'))
		{
			$query->where('os.status = '. (int) $os);
		}
		else
		{
			// Show all statuses by default, since @2020-02-10@
			//$query->where('ss.type != ' . $db->q('order_placed'));
		}

		if ($delivery_due = $this->getState('list.delivery_due'))
		{
			switch ($delivery_due)
			{
				case 'today':
					$query->where('DATE_FORMAT(ods.slot_from_time, "%Y-%m-%d") = ' . $this->_db->q($today->format('Y-m-d')));
					break;
				case 'hours_6':
					$query->where('DATE_FORMAT(ods.slot_from_time, "%Y-%m-%d") = ' . $this->_db->q($today->format('Y-m-d')));
					$query->where('TIMESTAMPDIFF(HOUR, ods.slot_from_time, ' . $this->_db->q($today->format('Y-m-d H:i:s')) . ') <= 6');
					break;
				case 'select_date':
					if ($delivery_due_date = $this->getState('list.delivery_due_date'))
					{
						$query->where('DATE_FORMAT(ods.slot_from_time, "%Y-%m-%d") = ' . $this->_db->q($delivery_due_date));
					}
					break;
			}
		}

		$query->where('ods.id > 0'); // Only delivery orders

		$notIn = array(
			$this->_db->q('authorized'),
			$this->_db->q('payment_failed'),
		);

		$query->where('ss.type NOT IN (' . implode(',', $notIn) .  ')');

		$query->group('oi.item_uid, oi.order_id');

		$ordering = $this->state->get('list.fullordering', 'CASE WHEN DATE_FORMAT(ods.slot_from_time, "%Y-%m-%d") = ' . $this->_db->q($today->format('Y-m-d')) . ' THEN 1 ELSE 2 END, DATE(ods.slot_from_time) DESC, TIME(ods.slot_from_time) ASC');

		if (trim($ordering))
		{
			$query->order($ordering);
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
	 * @throws  Exception
	 *
	 * @since   2.0.0
	 */
	protected function processList($items)
	{
		foreach ($items as $item)
		{
			$status             = $this->helper->order->getStatus($item->order_id, $item->item_uid);
			$item->order_status = $status->s_title;
		}

		return parent::processList($items);
	}

	/**
	 * Method to get order item delivery details
	 *
	 * @param   int     $orderId  The order id
	 * @param   string  $itemUid  Unique identifier for order item
	 *
	 * @return  \stdClass
	 *
	 * @since   1.7.0
	 */
	public function getOrderItem($orderId, $itemUid)
	{
		$query = $this->_db->getQuery(true);

		$query->select('oi.*, ods.slot_from_time, ods.slot_to_time')
			->from($this->_db->qn('#__sellacious_order_items', 'oi'));

		$query->join('left', '#__sellacious_orders a ON a.id = oi.order_id');

		// Order status
		$query->select('os.status AS order_status_id, os.created as status_last_changed')
			->join('left', '#__sellacious_order_status AS os ON os.order_id = a.id AND os.state = 1 AND (os.item_uid = oi.item_uid OR os.item_uid = ' . $this->_db->q('') . ')');

		$query->join('left', '#__sellacious_order_delivery_slot ods ON ods.order_item_id = oi.id');

		$query->where('oi.item_uid = ' . $this->_db->q($itemUid). ' AND oi.order_id = ' . $orderId);

		$this->_db->setQuery($query);

		$orderItem = $this->_db->loadObject();

		return $orderItem;
	}

	/**
	 * Method to order item delivery status
	 *
	 * @param   \stdClass  orderItem  The object containing details of the order item
	 *
	 * @return  array
	 *
	 * @since   1.7.0
	 */
	public function getDeliveryStatus($orderItem)
	{
		$today             = JFactory::getDate();
		$fromTime          = JFactory::getDate($orderItem->slot_from_time);
		$ddClass           = 'delivery-status';
		$dStatus           = '';
		$statusLastChanged = JFactory::getDate($orderItem->status_last_changed);

		$status   = $this->helper->order->getStatus($orderItem->order_id, $orderItem->item_uid);
		$seller   = JFactory::getUser($orderItem->seller_uid);
		$timezone = '';
		$tzOffset = 'UTC';

		if ($seller->id)
		{
			$timezone = $seller->id;
			$tzOffset = $seller->getTimezone()->getName();
		}

		if ($timezone)
		{
			$statusLastChanged = $this->helper->core->fixDate($statusLastChanged->toSql(true), 'UTC', $timezone);
		}

		$statusLastChanged = $statusLastChanged->format('M d, Y g:i A', true);

		if (strtotime($today) > strtotime($orderItem->slot_from_time))
		{
			if ($status->s_type != 'delivered')
			{
				$dStatus = JText::_('COM_SELLACIOUSDELIVERY_ORDER_ITEM_DELIVERY_DUE_PASSED');
			}
			else
			{
				$ddClass .= ' delivery-done';
				$dStatus = JText::sprintf('COM_SELLACIOUSDELIVERY_ORDER_ITEM_DELIVERY_DONE', $tzOffset, $statusLastChanged);
			}
		}
		elseif (strtotime($today) <= strtotime($orderItem->slot_from_time))
		{
			$ddClass .= ' delivery-today';
			$diff    = $fromTime->diff($today);
			$day     = $diff->days;

			$statuses = array(
				'approved', 'completed', 'order_placed', 'packaged', 'paid', 'shipped', 'waiting_pickup'
			);

			$deliveryStatuses = array(
				'delivered', 'refund_placed', 'refunded', 'refund_cancelled', 'return_placed', 'returned', 'return_cancelled', 'exchange_placed', 'exchanged', 'exchange_cancelled', 'payment_failed'
			);

			if (in_array($status->s_type, $deliveryStatuses))
			{
				$ddClass .= ' delivery-done';
				$dStatus = JText::sprintf('COM_SELLACIOUSDELIVERY_ORDER_ITEM_DELIVERY_DONE', $tzOffset, $statusLastChanged);
			}
			elseif (in_array($status->s_type, $statuses))
			{
				if ($day > 0)
				{
					$dStatus = JText::sprintf('COM_SELLACIOUSDELIVERY_ORDER_ITEM_DELIVERY_DUE_IN', $day, $diff->format('%h'));
				}
				else
				{
					$dStatus = JText::sprintf('COM_SELLACIOUSDELIVERY_ORDER_ITEM_DELIVERY_DUE_TODAY', $diff->format('%h'));
				}
			}
			else
			{
				$dStatus = JText::_('COM_SELLACIOUSDELIVERY_ORDER_ITEM_DELIVERY_DUE_NA');
			}
		}

		return array('class' => $ddClass, 'status' => $dStatus);
	}
}
