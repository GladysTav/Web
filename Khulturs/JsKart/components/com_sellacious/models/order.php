<?php
/**
 * @version     2.0.0
 * @package     sellacious
 *
 * @copyright   Copyright (C) 2012-2020 Bhartiy Web Technologies. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Izhar Aazmi <info@bhartiy.com> - http://www.bhartiy.com
 */
// no direct access.
use Joomla\Registry\Registry;
use Sellacious\Order\Invoice;

defined('_JEXEC') or die;

/**
 * Sellacious model.
 */
class SellaciousModelOrder extends SellaciousModelAdmin
{
	/**
	 * Abstract method for getting the form from the model.
	 *
	 * @param   array    $data      Data for the form.
	 * @param   boolean  $loadData  True if the form is to load its own data (default case), false if not.
	 *
	 * @return  mixed  A JForm object on success, false on failure
	 *
	 * @since   12.2
	 */
	public function getForm($data = array(), $loadData = true)
	{
		return false;
	}

	/**
	 * Method to return a single record. Joomla model doesn't use caching, we use.
	 *
	 * @param   int  $pk  (optional) The record id of desired item.
	 *
	 * @return  JObject
	 */
	public function getItem($pk = null)
	{
		$item = parent::getItem($pk);
		$now  = JFactory::getDate();

		if ($item)
		{
			$order_id     = $item->get('id');
			$order_status = $this->helper->order->getStatus($order_id);
			$seller_wise  = false;

			// If order+seller status exists, then use seller wise status
			if ($order_status->seller_uid)
			{
				$seller_wise = true;
			}

			$oItems       = $this->helper->order->getOrderItems($order_id);
			$orderContext = $seller_wise ? 'order' : 'order.physical';
			$core         = $seller_wise ? false : true;

			$return_status   = $this->helper->order->getStatusId('return_placed', $core, $orderContext);
			$exchange_status = $this->helper->order->getStatusId('exchange_placed', $core, $orderContext);

			foreach ($oItems as $oi)
			{
				$oi->return_available   = false;
				$oi->exchange_available = false;

				if ($seller_wise)
				{
					$i_status = $this->helper->order->getStatus($oi->order_id, null, $oi->seller_uid);
				}
				else
				{
					$i_status = $this->helper->order->getStatus($oi->order_id, $oi->item_uid);
				}

				// Return and exchange is only available after certain status such as 'delivered'. We need to check! Not just last updated!!
				if ($last_updated = $i_status->created)
				{
					$item_context = $seller_wise ? 'order' : 'order.' . $oi->product_type;
					$statuses     = $this->helper->order->getStatuses($item_context, $i_status->status, true);

					if (in_array($return_status, $statuses))
					{
						$o_date               = JFactory::getDate($last_updated);
						$return_date          = $o_date->add(new DateInterval('P' . (int) $oi->return_days . 'D'));
						$oi->return_date      = $return_date->format('Y-m-d H:i:s');
						$oi->return_available = strtotime($return_date) > strtotime($now);
					}

					if (in_array($exchange_status, $statuses))
					{
						$o_date                 = JFactory::getDate($last_updated);
						$exchange_date          = $o_date->add(new DateInterval('P' . (int) $oi->exchange_days . 'D'));
						$oi->exchange_date      = $exchange_date->format('Y-m-d H:i:s');
						$oi->exchange_available = strtotime($exchange_date) > strtotime($now);
					}
				}
			}

			$item->set('items', $oItems);
			$item->set('status', $this->helper->order->getStatus($order_id));
			$item->set('coupon', $this->helper->order->getCoupon($order_id));

			$keys = array(
				'context'    => 'order',
				'order_id'   => $order_id,
				'list.where' => 'a.state > 0',
			);

			$item->set('payment', $this->helper->payment->loadObject($keys));

			$item->set('eproduct_delivery', $this->helper->order->getEproductDelivery($order_id));

			// Translate shipping
			if ($item->get('shipping_rule_id') > 0)
			{
				$shippingRule = $item->get('shipping_rule');
				$this->helper->translation->translateValue($item->get('shipping_rule_id'), 'sellacious_shippingrule', 'title', $shippingRule);

				$item->set('shipping_rule', $shippingRule);
			}

			// Invoices
			$seller_separate_invoice = $this->helper->config->get('seller_separate_invoice', 0);
			$invoiceIds                = array();

			if ($seller_separate_invoice == 1)
			{
				$sellers = $this->helper->order->getSellers($order_id);

				foreach ($sellers as $seller)
				{
					$orderInvoice = new Invoice($order_id, $seller->seller_uid);
					$invoice      = $orderInvoice->getItem();

					if ($invoice->get('id'))
					{
						$invoiceIds[] = $invoice->get('id');
					}
				}
			}
			else
			{
				$orderInvoice = new Invoice($order_id);
				$invoice      = $orderInvoice->getItem();

				if ($invoice->get('id'))
				{
					$invoiceIds[] = $invoice->get('id');
				}
			}

			$item->set('invoice_ids', $invoiceIds);

			if ($checkoutForms = $item->get('checkout_forms'))
			{
				foreach ($checkoutForms as &$checkoutForm)
				{
					$checkoutForm = (object) $checkoutForm;

					if (isset($checkoutForm->field_id) && $checkoutForm->value)
					{
						$field               = $this->helper->field->getItem($checkoutForm->field_id);
						$checkoutForm->value = $this->helper->field->renderValue($checkoutForm->value, $field->type, $field);
					}
				}
			}
		}

		return $item;
	}
}
