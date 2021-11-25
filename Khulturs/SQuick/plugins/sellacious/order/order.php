<?php
/**
 * @version     2.0.0
 * @package     sellacious
 *
 * @copyright   Copyright (C) 2012-2020 Bhartiy Web Technologies. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Izhar Aazmi <info@bhartiy.com> - http://www.bhartiy.com
 */
// no direct access
defined('_JEXEC') or die('Restricted access');

use Joomla\Event\Event;
use Joomla\Registry\Registry;
use Joomla\Utilities\ArrayHelper;
use Sellacious\Cart;
use Sellacious\Communication\CommunicationHelper;
use Sellacious\Template\OrderNotificationTemplate;
use Sellacious\Template\OrderPaymentFailureNotificationTemplate;
use Sellacious\Template\OrderPaymentSuccessNotificationTemplate;
use Sellacious\Template\OrderStatusNotificationTemplate;

/**
 * Class plgSellaciousOrder
 *
 * @since   1.0.0
 */
class plgSellaciousOrder extends JPlugin
{
	/**
	 * Load the language file on instantiation.
	 *
	 * @var    boolean
	 * @since  3.1
	 */
	protected $autoloadLanguage = true;

	/**
	 * Register library for template classes
	 *
	 * @since   2.0.0
	 */
	public function onAfterInitialise()
	{
		JLoader::registerNamespace('Sellacious', __DIR__ . '/libraries/src', false, false, 'psr4');
	}

	/**
	 * Adds order email template fields to the sellacious form for creating email templates
	 *
	 * @param   JForm  $form  The form to be altered.
	 * @param   array  $data  The associated data for the form.
	 *
	 * @return  boolean
	 *
	 * @since   1.0.0
	 */
	public function onContentPrepareForm($form, $data)
	{
		if (!$form instanceof JForm)
		{
			$this->_subject->setError('JERROR_NOT_A_FORM');

			return false;
		}

		if ($form->getName() != 'com_sellacious.emailtemplate')
		{
			return true;
		}

		$contexts = array();

		$this->onFetchEmailContext('com_sellacious.emailtemplate', $contexts);

		if (!empty($contexts))
		{
			$array = is_object($data) ? ArrayHelper::fromObject($data) : (array) $data;

			if (array_key_exists($array['context'], $contexts))
			{
				$form->loadFile(__DIR__ . '/forms/order.xml', false);
				$form->setFieldAttribute('short_codes', 'template_class', OrderNotificationTemplate::class);

				if ($array['context'] != 'order_payment_success.self')
				{
					$form->removeField('send_attachment');
				}
			}
		}

		return true;
	}

	/**
	 * Fetch the available context of email template
	 *
	 * @param   string    $context   The calling context
	 * @param   string[]  $contexts  The list of email context the should be populated
	 *
	 * @return  void
	 *
	 * @since   1.5.0
	 */
	public function onFetchEmailContext($context, array &$contexts = array())
	{
		if ($context == 'com_sellacious.emailtemplate')
		{
			$contexts['order_initiated.self']         = JText::_('PLG_SELLACIOUS_EMAILTEMPLATE_CONTEXT_ORDER_INITIATED_USER');
			$contexts['order_initiated.seller']       = JText::_('PLG_SELLACIOUS_EMAILTEMPLATE_CONTEXT_ORDER_INITIATED_SELLER');
			$contexts['order_initiated.admin']        = JText::_('PLG_SELLACIOUS_EMAILTEMPLATE_CONTEXT_ORDER_INITIATED_ADMIN');
			$contexts['order_payment_success.self']   = JText::_('PLG_SELLACIOUS_EMAILTEMPLATE_CONTEXT_ORDER_PAYMENT_SUCCESS_USER');
			$contexts['order_payment_success.seller'] = JText::_('PLG_SELLACIOUS_EMAILTEMPLATE_CONTEXT_ORDER_PAYMENT_SUCCESS_SELLER');
			$contexts['order_payment_success.admin']  = JText::_('PLG_SELLACIOUS_EMAILTEMPLATE_CONTEXT_ORDER_PAYMENT_SUCCESS_ADMIN');
			$contexts['order_payment_failure.self']   = JText::_('PLG_SELLACIOUS_EMAILTEMPLATE_CONTEXT_ORDER_PAYMENT_FAILURE_USER');
			$contexts['order_payment_failure.admin']  = JText::_('PLG_SELLACIOUS_EMAILTEMPLATE_CONTEXT_ORDER_PAYMENT_FAILURE_ADMIN');
			$contexts['order_status.self']            = JText::_('PLG_SELLACIOUS_EMAILTEMPLATE_CONTEXT_ORDER_STATUS_USER');
			$contexts['order_status.seller']          = JText::_('PLG_SELLACIOUS_EMAILTEMPLATE_CONTEXT_ORDER_STATUS_SELLER');
			$contexts['order_status.admin']           = JText::_('PLG_SELLACIOUS_EMAILTEMPLATE_CONTEXT_ORDER_STATUS_ADMIN');
		}
	}

	/**
	 * This method sends a registration email when a order payment finishes on both failure or success.
	 *
	 * @param   string  $context  The calling context
	 * @param   object  $payment  Holds the payment object from the payments table for the target order
	 *
	 * @throws  Exception
	 *
	 * @since   1.0.0
	 */
	public function onAfterOrderPayment($context, $payment)
	{
		jimport('sellacious.loader');

		if ($context == 'com_sellacious.order' && class_exists('SellaciousHelper'))
		{
			if ($payment->context === 'order' && !empty($payment->order_id))
			{
				$helper = SellaciousHelper::getInstance();
				$order  = $this->getOrder($payment->order_id);

				$event = new Event('onAfterOrderPayment');
				$event->setArgument('order', $order);

				$sellers = $helper->order->getSellers($payment->order_id);

				if ($order->get('payment.state') >= 1)
				{
					// Payment success
					CommunicationHelper::eventNotification($event, OrderPaymentSuccessNotificationTemplate::class, array('self', 'admin'));

					foreach ($sellers as $seller)
					{
						$event->setArgument('seller', $seller);
						CommunicationHelper::eventNotification($event, OrderPaymentSuccessNotificationTemplate::class, array('seller'));
					}
				}
				else
				{
					// Payment failure
					CommunicationHelper::eventNotification($event, OrderPaymentFailureNotificationTemplate::class, array('self', 'admin'));
				}
			}
		}
	}

	/**
	 * This method sends a registration email when a order payment finishes on both failure or success.
	 *
	 * @param   string  $context   The calling context
	 * @param   int     $order_id  The concerned order id
	 *
	 * @return  void
	 *
	 * @throws  Exception
	 *
	 * @since   1.0.0
	 */
	public function onAfterOrderChange($context, $order_id)
	{
		jimport('sellacious.loader');

		if ($context == 'com_sellacious.order' && class_exists('SellaciousHelper'))
		{
			$helper = SellaciousHelper::getInstance();
			$order  = $this->getOrder($order_id);

			$event = new Event('onAfterOrderChange');
			$event->setArgument('order', $order);

			$sellers = $helper->order->getSellers($order_id);

			CommunicationHelper::eventNotification($event, OrderStatusNotificationTemplate::class, array('self', 'admin'));

			foreach ($sellers as $seller)
			{
				$event->setArgument('seller', $seller);
				CommunicationHelper::eventNotification($event, OrderStatusNotificationTemplate::class, array('seller'));
			}
		}
	}

	/**
	 * This method sends a registration email when a order placed.
	 *
	 * @param   string  $context
	 * @param   object  $order
	 * @param   array   $products
	 * @param   Cart    $cart
	 *
	 * @return  void
	 *
	 * @since   1.5.0
	 */
	public function onAfterPlaceOrder($context, $order, $products, $cart)
	{
		jimport('sellacious.loader');

		if ($context == 'com_sellacious.cart' && class_exists('SellaciousHelper'))
		{
			$helper    = SellaciousHelper::getInstance();
			$orderData = $this->getOrder($order->id);

			$event = new Event('onAfterPlaceOrder');
			$event->setArgument('order', $orderData);

			$sellers = $helper->order->getSellers($order->id);

			CommunicationHelper::eventNotification($event, OrderNotificationTemplate::class, array('self', 'admin'));

			foreach ($sellers as $seller)
			{
				$event->setArgument('seller', $seller);
				CommunicationHelper::eventNotification($event, OrderNotificationTemplate::class, array('seller'));
			}
		}
	}

	/**
	 * Method to get order object
	 *
	 * @param   int  $orderId  The id of the order
	 *
	 * @return  Registry
	 *
	 * @throws  Exception
	 *
	 * @since   2.0.0
	 */
	protected function getOrder($orderId)
	{
		$helper = SellaciousHelper::getInstance();
		$order  = $helper->order->getItem($orderId);
		$items  = $helper->order->getOrderItems($orderId);
		$order  = new Registry($order);

		$order->set('items', $items);

		/*
		 * Get the latest (and possibly successful) payment record of this order, no matter failed or success
		 *
		 * State descending (2, 1, 0, -1): so that successful comes at top just in case response inserted in random order
		 * Id descending: so that for any specific state the latest one is prioritized
		 */
		$keys    = array('context' => 'order', 'order_id' => $orderId, 'list.order' => 'a.state DESC, a.id DESC');
		$payment = $helper->payment->loadObject($keys);

		$order->set('payment', $payment);

		// Coupon
		$coupon = $helper->order->getCoupon($orderId);
		$order->set('coupon', $coupon);

		// Order status Log
		$log = $helper->order->getStatusLog($orderId);

		if (count($log) == 0)
		{
			$order->set('status_old', 'NA');
			$order->set('status_new', 'NA');
		}
		elseif (count($log) == 1)
		{
			$order->set('status_old', 'NA');
			$order->set('status_new', $log[0]->s_title);
		}
		else
		{
			$order->set('status_old', $log[1]->s_title);
			$order->set('status_new', $log[0]->s_title);
		}

		return $order;
	}
}
