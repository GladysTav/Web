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

use Joomla\Registry\Registry;
use Joomla\Utilities\ArrayHelper;
use Sellacious\Order\Invoice;

/**
 * Methods supporting a list of Sellacious records.
 *
 * @since  1.0.0
 */
class SellaciousModelOrders extends SellaciousModelList
{
	/**
	 * @var  stdClass[]
	 *
	 * @since   1.0.0
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
				'a.customer_name',
				'a.created',
				'ss.title',
				'cu.amount',
				'a.cart_taxes',
				'a.cart_discounts',
				'a.product_subtotal',
				'a.product_shipping',
				'a.grand_total',
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
	 * @since   1.0.0
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
	 * @since   1.0.0
	 */
	protected function getListQuery()
	{
		$me    = JFactory::getUser();
		$db    = JFactory::getDbo();
		$query = $db->getQuery(true);

		$query->select('a.*')
			->from($db->qn('#__sellacious_orders', 'a'));

		if ($this->helper->access->check('order.list'))
		{
			if ($seller_uid = $this->state->get('filter.seller_uid'))
			{
				$query->join('left', '#__sellacious_order_items oi ON oi.order_id = a.id')
					->where('oi.seller_uid = ' . (int) $seller_uid)
					->group('a.id');
			}
		}
		elseif ($this->helper->access->check('order.list.own'))
		{
			$query->join('left', '#__sellacious_order_items oi ON oi.order_id = a.id')
				->where('oi.seller_uid = ' . (int) $me->id)
				->group('a.id');
		}
		else
		{
			// Fallacy
			return $query->where('0 = 1');
		}

		$query->select('cu.amount AS coupon_amount, cu.code AS coupon_code')
			->join('left', '#__sellacious_coupon_usage AS cu ON cu.order_id = a.id');

		// Order status
		$query->select('os.status AS order_status_id')
			->join('left', '#__sellacious_order_status AS os ON os.order_id = a.id AND os.state = 1 AND os.item_uid = ' . $db->q(''));

		$query->select('ss.title AS order_status')
			->join('left', '#__sellacious_statuses AS ss ON ss.id = os.status');

		// Payment method name
		$query->select('op.method_id as payment_method_id, op.method_name AS payment_method')
			->join('left', '#__sellacious_payments AS op ON op.order_id = a.id AND op.context = ' . $db->q('order') . ' AND op.state > 0');

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
					' OR ss.title LIKE '. $textSearch .
					' OR op.method_name LIKE '. $textSearch . ')');
			}
		}

		if ($os = $this->getState('filter.status'))
		{
			$query->where('os.status = '. (int) $os);
		}
		else
		{
			// Show all statuses by default, since @2020-01-09@
			// $query->where('ss.type != ' . $db->q('order_placed'));
		}

		$query->group('a.id');

		$ordering = $this->state->get('list.fullordering', 'a.created DESC');

		if (trim($ordering))
		{
			$query->order($db->escape($ordering));
		}

		return $query;
	}

	/**
	 * Process list to add items and order status in each order record
	 *
	 * @param   stdClass[]  $orders
	 *
	 * @return  stdClass[]
	 *
	 * @since   1.0.0
	 */
	protected function processList($orders)
	{
		if (count($orders) == 0)
		{
			return array();
		}

		/** @var  SellaciousTableOrder  $oTable */
		$me     = JFactory::getUser();
		$oTable = $this->getTable('Order');

		array_walk($orders, array($oTable, 'parseJson'));

		$allowAll = $this->helper->access->check('order.list');
		$allowOwn = $this->helper->access->check('order.list.own');

		// This test is probably not required here coz,
		// If this would be false there would be no items in the first place.
		if ($allowAll || $allowOwn)
		{
			$oid   = ArrayHelper::getColumn($orders, 'id');
			$db    = JFactory::getDbo();
			$query = $db->getQuery(true);

			$query->select('a.*')
				->from($db->qn('#__sellacious_order_items', 'a'))
				->where('a.order_id IN (' . implode(', ', $db->q($oid)) . ')');

			$query->select('shipper, tracking_url, tracking_number, ship_date, item_serial, ship_notes')
				->select('source_address, source_district, source_state, source_country, source_zip')
				->join('left', '#__sellacious_order_shipments s USING (order_id, item_uid)')
				->group('a.id, a.order_id, a.item_uid');

			if ($seller_uid = $allowAll ? $this->state->get('filter.seller_uid') : (int) $me->id)
			{
				$query->where('a.seller_uid = ' . (int) $seller_uid);
			}

			try
			{
				$db->setQuery($query);
				$oItemsList  = array();
				$ordersItems = $db->loadObjectList();

				/** @var  SellaciousTableOrderItem  $oiTable */
				$oiTable = $this->getTable('OrderItem');

				foreach ($ordersItems as $ordersItem)
				{
					$oiTable->parseJson($ordersItem);

					$ordersItem->package_items = null;

					if ($ordersItem->product_type == 'package')
					{
						$filters = array(
							'list.select' => 'a.*',
							'list.from'   => '#__sellacious_order_package_items',
							'list.where'  => 'a.order_item_id = ' . (int) $ordersItem->id,
						);

						$ordersItem->package_items = (array) $this->helper->order->loadObjectList($filters);
					}

					$oItemsList[$ordersItem->order_id][] = $ordersItem;
				}

				foreach ($orders as $order)
				{
					$params = new Registry($order->params);
					$selectShipping = $params->get('product_select_shipping', 0);

					$order->product_select_shipping  = $selectShipping;
					$order->seller_shipping_rules    = $order->seller_shipping_rules ? (array)$order->seller_shipping_rules : '';
					$order->seller_shipping_rule_ids = $order->seller_shipping_rule_ids ? (array) $order->seller_shipping_rule_ids : '';

					if (!$selectShipping)
					{
						if (!empty($order->seller_shipping_rules))
						{
							$seller_shipping_rules = array();

							foreach ($order->seller_shipping_rules as $sellerUid => $sellerShippingRule)
							{
								$sellerUid = (int)$sellerUid;
								$seller    = $this->helper->seller->getItem(array('user_id' => $sellerUid));

								$sellerShippingRule->seller_name   = $seller->title;
								$seller_shipping_rules[$sellerUid] = $sellerShippingRule;
							}

							$order->seller_shipping_rules = $seller_shipping_rules;

						}

						if (!empty($order->seller_shipping_rule_ids))
						{
							$seller_shipping_rule_ids = array();

							foreach ($order->seller_shipping_rule_ids as $sellerUid => $sellerShippingRuleId)
							{
								$sellerUid = (int)$sellerUid;
								$seller    = $this->helper->seller->getItem(array('user_id' => $sellerUid));

								$seller_shipping_rule_ids[$sellerUid]                 = new stdClass();
								$seller_shipping_rule_ids[$sellerUid]->rule_id        = $sellerShippingRuleId;
								$seller_shipping_rule_ids[$sellerUid]->seller_name    = $seller->title;
								$seller_shipping_rule_ids[$sellerUid]->shipping_title = $order->seller_shipping_rules[$sellerUid]->ruleTitle;
							}

							$order->seller_shipping_rule_ids = $seller_shipping_rule_ids;
						}
					}

					$order->items       = ArrayHelper::getValue($oItemsList, $order->id, array());
					$order->sellers     = $this->helper->order->getSellers($order->id);

					$statusIds = array();

					foreach ($order->items as $item)
					{
						$item->status = $this->helper->order->getStatus($item->order_id, $item->item_uid);

						if ($item->status->id)
						{
							$order->status_edit = 0;
						}
						else
						{
							$order->status_edit = 1;
							$item->status       = $this->helper->order->getStatus($item->order_id, null, $item->seller_uid);
						}

						$statusIds[] = $item->status->status;
					}

					$order->status = $this->helper->order->getStatus($order->id, null, 0);

					if ($order->status_edit)
					{
						if (!$this->helper->access->check('order.item.edit.status') && $this->helper->access->check('order.item.edit.status.own'))
						{
							$order->status        = $this->helper->order->getStatus($order->id, null, $me->id);
							$order->order_status = $order->status->s_title;
						}
						elseif (count($order->sellers) == 1)
						{
							$order->status       = $this->helper->order->getStatus($order->id, null, $order->sellers[0]->seller_uid);
							$order->order_status = $order->status->s_title;
						}
						elseif (count(array_unique($statusIds)) > 1)
						{
							$order->order_status = JText::_('COM_SELLACIOUS_ORDERS_STATUS_EXPAND');
						}
					}

					$itemTypes = array_unique(ArrayHelper::getColumn($order->items, 'product_type'));
					$context   = count($itemTypes) == 1 ? 'order.' . $itemTypes[0] : 'order';

					$order->statuses = $this->helper->order->getStatuses($context, $order->status->s_id, false, true);

					// Translate shipping rule
					if ($order->shipping_rule_id > 0)
					{
						$shippingRule = $order->shipping_rule;
						$this->helper->translation->translateValue($order->shipping_rule_id, 'sellacious_shippingrule', 'title', $shippingRule);

						$order->shipping_rule = $shippingRule;
					}

					// Translate payment method
					if ($order->payment_method_id > 0)
					{
						$paymentMethod = $order->payment_method;
						$this->helper->translation->translateValue($order->payment_method_id, 'sellacious_paymentmethod', 'title', $paymentMethod);

						$order->payment_method = $paymentMethod;
					}

					// Translate order status
					if ($order->order_status_id > 0)
					{
						$orderStatus = $order->order_status;
						$this->helper->translation->translateValue($order->order_status_id, 'sellacious_status', 'title', $orderStatus);

						$order->order_status = $orderStatus;
					}

					// Invoices
					$seller_separate_invoice = $this->helper->config->get('seller_separate_invoice', 0);
					$invoiceIds                = array();

					if ($seller_separate_invoice == 1)
					{
						$sellers = $this->helper->order->getSellers($order->id);

						foreach ($sellers as $seller)
						{
							$orderInvoice = new Invoice($order->id, $seller->seller_uid);
							$invoice      = $orderInvoice->getItem();

							if ($invoice->get('id'))
							{
								$invoiceIds[] = $invoice->get('id');
							}
						}
					}
					else
					{
						$orderInvoice = new Invoice($order->id);
						$invoice      = $orderInvoice->getItem();

						if ($invoice->get('id'))
						{
							$invoiceIds[] = $invoice->get('id');
						}
					}

					$order->invoice_ids = $invoiceIds;
				}
			}
			catch (Exception $e)
			{
				JLog::add($e->getMessage(), JLog::WARNING, 'jerror');
			}
		}

		return parent::processList($orders);
	}
}
