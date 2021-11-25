<?php
/**
 * @version     2.0.0
 * @package     sellacious
 *
 * @copyright   Copyright (C) 2012-2020 Bhartiy Web Technologies. All rights reserved.
 * @license     SPL Sellacious Private License; see http://www.sellacious.com/spl.html
 * @author      Aditya Chakraborty <info@bhartiy.com> - http://www.bhartiy.com
 */

namespace Sellacious\Order;

// no direct access.
use Joomla\Registry\Registry;
use Sellacious\BaseObject;

defined('_JEXEC') or die;

/**
 * Sellacious Invoice Object.
 *
 * @since   1.7.0
 */
class Invoice extends BaseObject
{
	/**
	 * @var  int
	 *
	 * @since   1.7.0
	 */
	protected $order_id;

	/**
	 * @var  int
	 *
	 * @since   1.7.0
	 */
	protected $seller_uid;

	/**
	 * @var  Registry
	 *
	 * @since   1.7.0
	 */
	protected $invoice_data;

	/**
	 * Product constructor.
	 *
	 * @param   int  $order_id    The order id
	 * @param   int  $seller_uid  The seller user id
	 *
	 * @since   1.7.0
	 */
	public function __construct($order_id, $seller_uid = 0)
	{
		$this->order_id   = $order_id;
		$this->seller_uid = $seller_uid;

		parent::__construct();
	}

	/**
	 * load the relevant information for this object instance
	 *
	 * @return  void
	 *
	 * @since   1.7.0
	 */
	protected function load()
	{
		// nothing to do here
	}

	/**
	 * Method to create invoice
	 *
	 * @throws  \Exception
	 *
	 * @return  \JTable
	 *
	 * @since   1.7.0
	 */
	public function createInvoice()
	{
		$now             = \JFactory::getDate();
		$return_status   = $this->helper->order->getStatusId('return_placed', true, 'order.physical');
		$exchange_status = $this->helper->order->getStatusId('exchange_placed', true, 'order.physical');

		$filters = array();

		if ($this->seller_uid)
		{
			$filters[] = 'a.seller_uid = ' . $this->seller_uid;
		}

		$item   = $this->helper->order->getItem($this->order_id);
		$oItems = $this->helper->order->getOrderItems($this->order_id, null, $filters);

		// Re-calculate for each seller product
		$item->product_total     = 0;
		$item->product_taxes     = 0;
		$item->product_discounts = 0;
		$item->product_subtotal  = 0;

		foreach ($oItems as $oi)
		{
			$item->product_total     += $oi->basic_price;
			$item->product_taxes     += $oi->tax_amount;
			$item->product_discounts += $oi->discount_amount;
			$item->product_subtotal  += $oi->sub_total;

			$oi->return_available   = false;
			$oi->exchange_available = false;

			$i_status = $this->helper->order->getStatus($oi->order_id, $oi->item_uid);

			// Return and exchange is only available after certain status such as 'delivered'. We need to check! Not just last updated!!
			if ($last_updated = $i_status->created)
			{
				$statuses = $this->helper->order->getStatuses('order.' . $oi->product_type, $i_status->status, true);

				if (in_array($return_status, $statuses))
				{
					$o_date               = \JFactory::getDate($last_updated);
					$return_date          = $o_date->add(new \DateInterval('P' . (int) $oi->return_days . 'D'));
					$oi->return_date      = $return_date->format('Y-m-d H:i:s');
					$oi->return_available = strtotime($return_date) > strtotime($now);
				}

				if (in_array($exchange_status, $statuses))
				{
					$o_date                 = \JFactory::getDate($last_updated);
					$exchange_date          = $o_date->add(new \DateInterval('P' . (int) $oi->exchange_days . 'D'));
					$oi->exchange_date      = $exchange_date->format('Y-m-d H:i:s');
					$oi->exchange_available = strtotime($exchange_date) > strtotime($now);
				}
			}
		}

		$item = new Registry($item);

		$item->set('items', $oItems);
		$item->set('status', $this->helper->order->getStatus($this->order_id));
		$item->set('coupon', $this->helper->order->getCoupon($this->order_id));

		$keys = array(
			'context'    => 'order',
			'order_id'   => $this->order_id,
			'list.where' => 'a.state > 0',
		);

		$item->set('payment', $this->helper->payment->loadObject($keys));
		$item->set('eproduct_delivery', $this->helper->order->getEproductDelivery($this->order_id));

		$invoice = $this->getItem();
		$data    = array(
			'order_id'     => $this->order_id,
			'seller_uid'   => $this->seller_uid,
			'invoice_data' => $item->toString(),
		);

		$invoice->bind($data);
		$invoice->check();
		$invoice->store();

		return $invoice;
	}

	/**
	 * Method to get invoice
	 *
	 * @return  \Joomla\Registry\Registry
	 *
	 * @throws  \Exception
	 *
	 * @since   1.7.0
	 */
	public function getInvoiceData()
	{
		if (isset($this->invoice_data))
		{
			return $this->invoice_data;
		}

		$invoice = $this->getItem();

		if (!$invoice->get('id'))
		{
			$invoice = $this->createInvoice();
		}

		$this->invoice_data = new Registry($invoice->get('invoice_data', ''));

		return $this->invoice_data;
	}

	/**
	 * Method to get invoice record
	 *
	 * @return  \JTable
	 *
	 * @since   2.0.0
	 */
	public function getItem()
	{
		$invoice = \SellaciousTable::getInstance('Invoice');
		$invoice->load(array('order_id' => $this->order_id, 'seller_uid' => $this->seller_uid));

		return $invoice;
	}
}
