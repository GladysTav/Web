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
defined('_JEXEC') or die;

use Joomla\Utilities\ArrayHelper;

/**
 * list controller class
 *
 * @since  2.0.0
 */
class SellaciousControllerOrder extends SellaciousControllerBase
{
	/**
	 * The prefix to use with controller messages.
	 *
	 * @var  string
	 *
	 * @since   2.0.0
	 */
	protected $text_prefix = 'COM_SELLACIOUS_ORDER';

	/**
	 * Saves a new address for the current user
	 *
	 * @return void
	 *
	 * @since   2.0.0
	 */
	public function saveAddressAjax()
	{
		try
		{
			if (!JSession::checkToken())
			{
				throw new Exception(JText::_('JINVALID_TOKEN'));
			}

			$address = $this->input->post->get('address', array(), 'array');
			$options = array('control' => 'jform', 'name' => 'com_sellacious.address.form');
			$form    = $this->helper->user->getAddressForm($options, $address);

			if (!$form->validate($address))
			{
				$errors = $form->getErrors();

				foreach ($errors as $ei => $e)
				{
					if ($e instanceof Exception)
					{
						$errors[$ei] = $e->getMessage();
					}
				}

				if (count($errors))
				{
					throw new Exception(implode("\n", $errors));
				}
			}

			$addr = $this->helper->user->saveAddress($address);
			$user = JFactory::getUser();

			if (!$addr)
			{
				throw new Exception(JText::_($this->text_prefix . '_ADDRESS_SAVE_FAILED'));
			}

			if ($user->guest)
			{
				$pks = (array) $this->app->getUserState('com_sellacious.order.guest_addresses', array());
				$pks = ArrayHelper::toInteger($pks);

				$pks[] = $addr->id;

				$this->app->setUserState('com_sellacious.order.guest_addresses', $pks);
			}

			$data = array(
				'message' => JText::_($this->text_prefix . '_ADDRESS_SAVE_SUCCESS'),
				'data'    => $addr,
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

		jexit();
	}

	/**
	 * Remove an address as specified for current user
	 *
	 * @return  void
	 *
	 * @since   2.0.0
	 */
	public function removeAddressAjax()
	{
		try
		{
			if (!JSession::checkToken())
			{
				throw new Exception(JText::_('JINVALID_TOKEN'));
			}

			$user = JFactory::getUser();
			$cid  = $this->input->get('id');
			$addr = $this->helper->user->getAddressById($cid);

			if (!$addr)
			{
				throw new Exception($this->text_prefix . '_ADDRESS_REMOVE_FAILED1');
			}

			if ($user->guest)
			{
				$pks = (array) $this->app->getUserState('com_sellacious.order.guest_addresses', array());
				$pks = ArrayHelper::toInteger($pks);

				if ($addr->user_id || !in_array($addr->id, $pks))
				{
					throw new Exception($this->text_prefix . '_ADDRESS_REMOVE_FAILED2');
				}
			}
			elseif ($addr->user_id != $user->id)
			{
				throw new Exception($this->text_prefix . '_ADDRESS_REMOVE_FAILED3');
			}

			if (!$this->helper->user->removeAddress($cid, $user->id))
			{
				throw new Exception($this->text_prefix . '_ADDRESS_REMOVE_FAILED4');
			}

			$data = array(
				'message' => JText::_($this->text_prefix . '_ADDRESS_REMOVE_SUCCESS'),
				'data'    => $cid,
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

		jexit();
	}

	/**
	 * Return list of addresses of current user, in case of guest user
	 * return a subset of it as enlisted in the userState.
	 *
	 * @return  void
	 *
	 * @since   2.0.0
	 */
	public function getAddressesHtmlAjax()
	{
		try
		{
			if (!JSession::checkToken())
			{
				throw new Exception(JText::_('JINVALID_TOKEN'));
			}

			$orderId   = $this->app->input->getInt('order_id');
			$addresses = $this->getAddresses();

			$hasShippable = $orderId ? $this->helper->order->hasShippable() : true;

			foreach ($addresses as $address)
			{
				$address->bill_to = $this->helper->location->isAddressAllowed($address, 'BT');
				$address->ship_to = $this->helper->location->isAddressAllowed($address, 'ST');
				$address->show_bt = true;
				$address->show_st = $hasShippable;
			}

			$html     = JLayoutHelper::render('com_sellacious.user.addresses', $addresses);
			$modals   = JLayoutHelper::render('com_sellacious.user.modals', $addresses);
			$response = array(
				'message' => '',
				'data'    => array(
					preg_replace('/\s+/', ' ', $html),
					preg_replace('/\s+/', ' ', $modals),
					$hasShippable,
				),
				'status'  => 1,
			);
		}
		catch (Exception $e)
		{
			$response = array(
				'message' => $e->getMessage(),
				'data'    => null,
				'status'  => 0,
			);
		}

		echo json_encode($response);

		jexit();
	}

	/**
	 * Add shipping and billing info to cart via Ajax call
	 *
	 * @return  void
	 *
	 * @since   2.0.0
	 */
	public function setAddressesAjax()
	{
		try
		{
			if (!JSession::checkToken())
			{
				throw new Exception(JText::_('JINVALID_TOKEN'));
			}

			$orderId = $this->input->post->getInt('order_id');
			$billTo  = $this->input->post->getInt('bt');
			$shipTo  = $this->input->post->getInt('st');

			$this->helper->order->setAddresses($orderId, $billTo, $shipTo);

			$response = array(
				'message' => JText::_($this->text_prefix . '_ADDRESS_SAVE_SUCCESS'),
				'data'    => null,
				'status'  => 1,
			);
		}
		catch (Exception $e)
		{
			$response = array(
				'message' => $e->getMessage(),
				'data'    => null,
				'status'  => 0,
			);
		}

		echo json_encode($response);

		jexit();
	}

	/**
	 * Get the addresses linked to current user / guest
	 *
	 * @return  array
	 *
	 * @throws  Exception
	 *
	 * @since   2.0.0
	 */
	protected function getAddresses()
	{
		$user = JFactory::getUser();

		if (!$user->guest)
		{
			return $this->helper->user->getAddresses($user->id, 1);
		}

		$pks   = (array) $this->app->getUserState('com_sellacious.order.guest_addresses', array());
		$pks   = ArrayHelper::toInteger($pks);
		$items = array();

		foreach ($pks as $pk)
		{
			$address = $this->helper->user->getAddressById($pk);

			if (is_object($address) && $address->user_id == 0)
			{
				$items[] = $address;
			}
		}

		return $items;
	}
}
