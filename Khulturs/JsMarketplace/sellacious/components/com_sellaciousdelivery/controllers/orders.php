<?php
/**
 * @version     1.7.4
 * @package     sellacious
 *
 * @copyright   Copyright (C) 2012-2019 Bhartiy Web Technologies. All rights reserved.
 * @license     SPL Sellacious Private License; see http://www.sellacious.com/spl.html
 * @author      Aditya Chakraborty <info@bhartiy.com> - http://www.bhartiy.com
 */
// no direct access.
use Joomla\Utilities\ArrayHelper;

defined('_JEXEC') or die;

/**
 * Orders list controller class
 *
 * @since   1.7.0
 */
class SellaciousdeliveryControllerOrders extends SellaciousControllerAdmin
{
	/**
	 * @var     string  The prefix to use with controller messages.
	 *
	 * @since   1.7.0
	 */
	protected $text_prefix = 'COM_SELLACIOUS_ORDERS';

	/**
	 * Proxy for getModel.
	 *
	 * @param   string  $name    The model name
	 * @param   string  $prefix  The model prefix
	 * @param   null    $config  The configuration options for the model instance
	 *
	 * @since   1.7.0
	 *
	 * @return  JModelLegacy
	 */
	public function getModel($name = 'Order', $prefix = 'SellaciousModel', $config = null)
	{
		return parent::getModel($name, $prefix, array('ignore_request' => true));
	}

	/**
	 * Update status information for an order item
	 *
	 * @return  void
	 *
	 * @since   1.7.0
	 */
	public function setItemStatusAjax()
	{
		$me    = JFactory::getUser();
		/** @var \SellaciousdeliveryModelOrders $model */
		$model = $this->getModel('Orders', 'SellaciousdeliveryModel');

		try
		{
			if (!JSession::checkToken())
			{
				throw new Exception(JText::_('JINVALID_TOKEN'));
			}

			if ($me->guest)
			{
				throw new Exception(JText::_('COM_SELLACIOUS_ACCESS_NOT_ALLOWED'));
			}

			$post = $this->input->post->get('jform', array(), 'array');

			$order_id = ArrayHelper::getValue($post, 'order_id');
			$item_uid = ArrayHelper::getValue($post, 'item_uid');

			$table = $this->helper->order->getTable('OrderItem');
			$table->load(array('order_id' => $order_id, 'item_uid' => $item_uid));

			if (!$order_id || !$item_uid || !$table->get('id'))
			{
				throw new Exception(JText::_($this->text_prefix . '_ORDER_ITEM_INVALID'));
			}

			if ($this->helper->access->check('order.item.edit.status') ||
				($this->helper->access->check('order.item.edit.status.own') && $table->get('seller_uid') == $me->id))
			{
				$this->helper->order->setStatus($post);
				$status = $this->helper->order->getStatus($order_id, $item_uid);

				try
				{
					$dispatcher = JEventDispatcher::getInstance();
					JPluginHelper::importPlugin('sellacious');
					$dispatcher->trigger('onAfterOrderChange', array('com_sellacious.order', $order_id));
				}
				catch (Exception $e)
				{
					// Email sending failed. Ignore for now
				}
			}
			else
			{
				throw new Exception(JText::_('COM_SELLACIOUS_ACCESS_NOT_ALLOWED'));
			}

			$status->next_status = $this->helper->order->getStatuses(null, $status->status, false, true);

			$orderItem               = $model->getOrderItem($order_id, $item_uid);
			$status->delivery_status = $model->getDeliveryStatus($orderItem);

			$data = array(
				'message' => JText::_($this->text_prefix . '_SHIPMENT_ORDER_ITEM_UPDATE_SUCCESS'),
				'data'    => $status,
				'status'  => 1,
			);
		}
		catch (Exception $e)
		{
			$data = array(
				'message' => $e->getMessage(),
				'data'    => null,
				'status'  => 0,
			);
		}

		echo json_encode($data);

		$this->app->close();
	}
}
