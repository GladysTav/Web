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
defined('_JEXEC') or die;

use Joomla\Utilities\ArrayHelper;
use Sellacious\Form\CheckoutQuestionsFormHelper;

/**
 * Cart controller class
 *
 * @since  1.4.6
 */
class SellaciousControllerCart extends SellaciousControllerBase
{
	/**
	 * @var    string  The prefix to use with controller messages.
	 *
	 * @since  1.4.6
	 */
	protected $text_prefix = 'COM_SELLACIOUS_CART';

	/**
	 * Add a product item to the shopping cart via Ajax call
	 *
	 * @return  void
	 *
	 * @since   1.4.6
	 */
	public function addAjax()
	{
		try
		{
			$code     = $this->input->getString('p');
			$quantity = $this->input->post->getInt('quantity');
			$options  = $this->input->post->get('options', array(), 'array');

			$this->helper->product->parseCode($code, $product_id, $variant_id, $seller_uid);

			if (!$product_id)
			{
				throw new Exception(JText::_($this->text_prefix . '_INVALID_PRODUCT_SELECTED'));
			}

			$cart = $this->helper->cart->getCart();

			// Whether to empty cart first
			if (isset($options['empty_cart']) && $options['empty_cart'])
			{
				$cart->clear();
			}
			elseif (!$cart->checkSellerLimit($seller_uid))
			{
				$response = array(
					'state'   => 1000,
					'message' => JText::_($this->text_prefix . '_CONFIRM_SWITCH_PRODUCT_SELLER'),
					'data'    => null,
				);
				echo json_encode($response);
				jexit();
			}

			$uid = $cart->add('internal', $code, $quantity, array('options' => $options));

			// Explicit commit is needed to commit the cart update
			$cart->commit();

			$response = array(
				'state'   => 1,
				'message' => JText::_($this->text_prefix . '_ADD_PRODUCT_SUCCESS'),
				'data'    => array(
					'uid'      => $uid,
					'redirect' => JRoute::_('index.php?option=com_sellacious&view=cart', false),
					'token'    => JSession::getFormToken(),
				),
			);
		}
		catch (Exception $e)
		{
			$response = array(
				'state'   => 0,
				'message' => $e->getMessage(),
				'data'    => null,
			);
		}

		echo json_encode($response);
		jexit();
	}

	/**
	 * Add an external/custom product item to the shopping cart via Ajax call
	 *
	 * @return  void
	 *
	 * @since   1.4.6
	 */
	public function addExternalAjax()
	{
		try
		{
			// The format of $identifier = 'source_id/transaction_id/item_code', all parts are mandatory and must not contain a slash (/)
			$identifier = $this->input->getString('i');
			$quantity   = $this->input->post->getInt('quantity');
			$options    = $this->input->post->get('options', array(), 'array');

			$cart = $this->helper->cart->getCart();
			$uid  = $cart->add('external', $identifier, $quantity, $options);

			// Explicit commit is needed to commit the cart update
			$cart->commit();

			$response = array(
				'state'   => 1,
				'message' => JText::_($this->text_prefix . '_ADD_PRODUCT_SUCCESS'),
				'data'    => array(
					'uid'      => $uid,
					'redirect' => JRoute::_('index.php?option=com_sellacious&view=cart', false),
					'token'    => JSession::getFormToken(),
				),
			);
		}
		catch (Exception $e)
		{
			$response = array(
				'state'   => 0,
				'message' => sprintf('%s at %s:%d', $e->getMessage(), $e->getFile(), $e->getLine()),
				'data'    => null,
			);
		}

		echo json_encode($response);

		jexit();
	}

	/**
	 * Method to save checkout form data for a product item in cart summary
	 *
	 * @since   2.0.0
	 */
	public function saveItemCheckoutFormAjax()
	{
		try
		{
			$code     = $this->input->getString('p');
			$formData = $this->app->input->post->get('jform', array(), 'array');
			$this->helper->product->parseCode($code, $product_id, $variant_id, $seller_uid);

			if (!$product_id)
			{
				throw new Exception(JText::_($this->text_prefix . '_INVALID_PRODUCT_SELECTED'));
			}

			$cart = $this->helper->cart->getCart();
			$item = $cart->getItem($code);

			// Save checkout form data for product (if there is any)
			if ($formData && $item)
			{
				CheckoutQuestionsFormHelper::saveForm('cart_summary', $formData, $cart, $item);
			}

			$cart->commit();

			$coqData = CheckoutQuestionsFormHelper::getData('cart_summary', $cart, $code, true);
			$html    = JLayoutHelper::render('com_sellacious.cart.aio.items_summary.checkout_data', array('uid' => $code, 'checkout_data' => $coqData));

			echo new JResponseJson(array('html' => $html), JText::_($this->text_prefix . '_UPDATE_SUCCESS_N_REFRESH'));
		}
		catch (Exception $e)
		{
			echo new JResponseJson($e);
		}

		jexit();
	}

	/**
	 * Set the guest checkout flag for the cart
	 *
	 * @return  void
	 *
	 * @since   1.4.6
	 */
	public function guestAjax()
	{
		try
		{
			if (!JSession::checkToken())
			{
				throw new Exception(JText::_('JINVALID_TOKEN'));
			}

			$email = $this->input->post->getString('email');
			$regex = chr(1) . '^[a-zA-Z0-9.!#$%&’*+/=?^_`{|}~-]+@[a-zA-Z0-9-]+(?:\.[a-zA-Z0-9-]+)*$' . chr(1);

			/*
			 * If the current user is not guest, we must not logout.
			 * Return warning so that the calling page may act appropriately.
			 */
			$user  = JFactory::getUser();

			if (!$user->guest)
			{
				$response = array(
					'message' => JText::sprintf('COM_SELLACIOUS_USER_ALREADY_LOGGED_IN', $user->email),
					'data'    => array(
						'id'       => $user->id,
						'name'     => $user->name,
						'username' => $user->username,
						'email'    => $user->email,
						'token'    => JSession::getFormToken(),
					),
					'status'  => 1,
				);
			}
			elseif ($email == '' || !preg_match($regex, $email))
			{
				$response = array(
					'message' => JText::_($this->text_prefix . '_INVALID_EMAIL_FORMAT'),
					'data'    => array(
						'email'  => $email,
					),
					'status'  => 1012,
				);
			}
			else
			{
				$cart = $this->helper->cart->getCart();
				$cart->setParam('guest_checkout', true);
				$cart->setParam('guest_checkout_email', $email);
				$cart->commit(true);

				$response = array(
					'message' => JText::sprintf($this->text_prefix . '_AIO_GUEST_CHECKOUT_SUCCESS', $user->email),
					'data'    => array(
						'id'       => 0,
						'name'     => $email,
						'username' => $email,
						'email'    => $email,
						'token'    => JSession::getFormToken(),
					),
					'status'  => 1,
				);
			}
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
	 * Return list of addresses of current user, in case of guest user return a subset of it as enlisted in the userState.
	 *
	 * @return  void
	 *
	 * @since   1.4.6
	 */
	public function getAddressesHtmlAjax()
	{
		try
		{
			if (!JSession::checkToken())
			{
				throw new Exception(JText::_('JINVALID_TOKEN'));
			}

			$cart      = $this->helper->cart->getCart();
			$user      = JFactory::getUser();
			$addresses = array();

			if (!$user->guest)
			{
				$addresses = $this->helper->user->getAddresses($user->id, 1);
			}
			else
			{
				if (!$cart->getParam('guest_checkout'))
				{
					throw new Exception(JText::_($this->text_prefix . '_NOT_LOGGED_IN'));
				}

				$pks = (array) $cart->getParam('guest_addresses', array());
				$pks = ArrayHelper::toInteger($pks);

				foreach ($pks as $pk)
				{
					$address = $this->helper->user->getAddressById($pk);

					if (is_object($address) && $address->user_id == 0)
					{
						$addresses[] = $address;
					}
				}
			}

			$hasShippable = $cart->hasShippable();

			foreach ($addresses as $address)
			{
				$address->bill_to = $this->helper->location->isAddressAllowed($address, 'BT');
				$address->ship_to = $this->helper->location->isAddressAllowed($address, 'ST');
				$address->show_bt = true;
				$address->show_st = $hasShippable;
			}

			$html     = JLayoutHelper::render('com_sellacious.user.addresses', $addresses, '', array('debug' => 0));
			$modals   = JLayoutHelper::render('com_sellacious.user.modals', $addresses, '', array('debug' => 0));
			$response = array(
				'message' => '',
				'data'    => array(preg_replace('/\s+/', ' ', $html), preg_replace('/\s+/', ' ', $modals), $hasShippable),
				'status'  => 1032,
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
	 * Saves a new address for the current user
	 *
	 * @return  void
	 *
	 * @since   1.4.6
	 */
	public function saveAddressAjax()
	{
		try
		{
			if (!JSession::checkToken())
			{
				throw new Exception(JText::_('JINVALID_TOKEN'));
			}

			$this->validateCheckout();

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

			$cart = $this->helper->cart->getCart();

			// If this is a guest checkout, allow maximum two addresses and declare the in-session address list.
			if ($cart->getUser()->guest && $cart->getParam('guest_checkout'))
			{
				$pks = (array) $cart->getParam('guest_addresses', array());

				if (count($pks) >= 2 && !in_array($address['id'], $pks))
				{
					throw new Exception(JText::_($this->text_prefix . '_GUEST_CHECKOUT_ADDRESS_LIMIT_MESSAGE'));
				}

				$data = $this->helper->user->saveAddress($address);

				if ($data->id)
				{
					$pks[] = $data->id;

					$cart->setParam('guest_addresses', array_unique($pks));
					$cart->commit(true);
				}
			}
			else
			{
				$data = $this->helper->user->saveAddress($address);
			}

			if (!$data->id)
			{
				throw new Exception(JText::_($this->text_prefix . '_ADDRESS_SAVE_FAILED'));
			}

			$response = array(
				'message' => JText::_($this->text_prefix . '_ADDRESS_SAVE_SUCCESS'),
				'data'    => $data,
				'status'  => 1035,
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
	 * @since   1.4.6
	 */
	public function setAddressesAjax()
	{
		try
		{
			if (!JSession::checkToken())
			{
				throw new Exception(JText::_('JINVALID_TOKEN'));
			}

			$this->validateCheckout();

			$billing  = $this->input->post->getInt('billing');
			$shipping = $this->input->post->getInt('shipping');

			$cart = $this->helper->cart->getCart();

			if ($shipping)
			{
				$cart->setShipTo($shipping);
			}

			if ($billing)
			{
				$cart->setBillTo($billing);
			}

			$cart->commit();

			$response = array(
				'message' => JText::_($this->text_prefix . '_ADDRESS_SAVE_SUCCESS'),
				'data'    => array('billing' => $billing, 'shipping' => $shipping),
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
	 * Get the checkout form via ajax
	 *
	 * @return  void
	 *
	 * @since   1.4.6
	 */
	public function getShippingFormAjax()
	{
		try
		{
			if (!JSession::checkToken())
			{
				throw new Exception(JText::_('JINVALID_TOKEN'));
			}

			$this->validateCheckout();

			$itemisedShip = $this->helper->config->get('itemised_shipping', SellaciousHelperShipping::SHIPPING_SELECTION_PRODUCT);
			$shipSelect   = $this->helper->config->get('product_select_shipping', true);
			$shippedBy    = $this->helper->config->get('shipped_by');
			$flatShip     = $this->helper->config->get('flat_shipping');

			if (!$this->helper->cart->getCart()->hasShippable())
			{
				$html = false;
			}
			elseif ($itemisedShip == SellaciousHelperShipping::SHIPPING_SELECTION_CART && $shippedBy == 'shop' && $flatShip)
			{
				$args        = new stdClass;
				$args->cart  = $this->helper->cart->getCart();

				$html = JLayoutHelper::render('com_sellacious.cart.shippingform.flat_ship', $args, '', array('debug' => 0));
			}
			else
			{
				$args        = new stdClass;
				$args->cart  = $this->helper->cart->getCart();
				$args->forms = $this->helper->cart->getShippingForms();

				if ($itemisedShip == SellaciousHelperShipping::SHIPPING_SELECTION_PRODUCT)
				{
					$layout = 'item_quotes';
				}
				elseif ($itemisedShip == SellaciousHelperShipping::SHIPPING_SELECTION_SELLER)
				{
					$layout = $shipSelect ? 'seller_item_quotes' : 'seller_quotes';
				}
				else
				{
					$layout = 'cart_quotes';
				}


				$html = JLayoutHelper::render('com_sellacious.cart.shippingform.' . $layout, $args, '', array('debug' => 0));
			}

			$response = array(
				'message' => '',
				'data'    => $html ?: false,
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
	 * Save the shipping form submitted by the user
	 *
	 * @return  void
	 *
	 * @since   1.4.6
	 */
	public function saveShippingFormAjax()
	{
		try
		{
			if (!JSession::checkToken())
			{
				throw new Exception(JText::_('JINVALID_TOKEN'));
			}

			$this->validateCheckout();

			$cart         = $this->helper->cart->getCart();
			$allForms     = $this->helper->cart->getShippingForms();
			$itemisedShip = $this->helper->config->get('itemised_shipping', SellaciousHelperShipping::SHIPPING_SELECTION_PRODUCT);
			$shippedBy    = $this->helper->config->get('shipped_by');

			$sellerShippingInProduct = $this->helper->config->get('product_select_shipping', true);

			$files   = $this->input->files->get('cart', array(), 'array');
			$post    = $this->input->post->get('cart', array(), 'array');
			$allData = !empty($files) ? array_merge_recursive($post, $files) : $post;

			if ($itemisedShip == SellaciousHelperShipping::SHIPPING_SELECTION_PRODUCT)
			{
				// Fixme: For an ItemisedShip setup flat fee can be selected by the seller as well
				$flatFee  = $shippedBy == 'shop' && $this->helper->config->get('flat_shipping');

				$items    = $cart->getItems();
				$quoteIds = $this->input->get('shipment', array(), 'array');

				foreach ($items as $uid => $item)
				{
					if (!$flatFee)
					{
						$flatFee = $shippedBy == 'seller' && $item->getProperty('flat_shipping');
					}

					if ($flatFee || !$item->isShippable())
					{
						// NO selection required for this item
					}
					elseif ($quoteId = ArrayHelper::getValue($quoteIds, $uid))
					{
						$data = ArrayHelper::getValue($allData, $uid, array(), 'array');
						$data = ArrayHelper::getValue($data, $quoteId, array(), 'array');
						$form = ArrayHelper::getValue($allForms, $uid, array(), 'array');
						$form = ArrayHelper::getValue($form, $quoteId);

						if (isset($form) && !$form instanceof JForm)
						{
							throw new Exception(JText::_('COM_SELLACIOUS_CART_SHIPRULE_FORM_VALIDATE_LOAD_FAILED'));
						}

						if (isset($form) && !$form->validate($data))
						{
							$errs = $form->getErrors();

							foreach ($errs as $ei => $error)
							{
								if ($error instanceof Exception)
								{
									$errs[$ei] = $error->getMessage();
								}
							}

							if (count($errs))
							{
								throw new Exception(implode('<br>', $errs));
							}
						}

						$object     = (object) $data;
						$dispatcher = $this->helper->core->loadPlugins();
						$dispatcher->trigger('onContentBeforeSave', array('com_sellacious.cart.shippingform', &$object, false));

						if ($errors = $dispatcher->getErrors())
						{
							throw new Exception(implode('<br/>', $errors));
						}

						$formData = ArrayHelper::fromObject($object);
						$shipData = ArrayHelper::getValue($formData, 'shipmentform', array());
						$values   = $this->helper->cart->buildShipmentFormData($shipData);

						$cart->setShipment($quoteId, $uid);
						$cart->setItemParam($uid, 'shippingform', $object);
						$cart->setItemParam($uid, 'shippingformdata', $values);
						$cart->commit();

						// @2017-01-03@ Why this force reload was here?
						// $cart->getItems(true);

						$dispatcher->trigger('onContentAfterSave', array('com_sellacious.cart.shippingform', &$object, false));
					}
					else
					{
						// NO method was selected for this item
						throw new Exception(JText::_($this->text_prefix . '_SELECT_SHIPMENT_REQUIRED'));
					}
				}

				$args       = new stdClass;
				$args->cart = $cart;
				$html       = JLayoutHelper::render('com_sellacious.cart.aio.shipping.itemised', $args);
				$response   = array(
					'message' => JText::_($this->text_prefix . '_SHIPPINGFORM_SAVE_SUCCESS'),
					'data'    => $html,
					'status'  => 1,
				);
			}
			elseif ($itemisedShip == SellaciousHelperShipping::SHIPPING_SELECTION_CART && ($quoteId = $this->input->get('shipment')) && is_scalar($quoteId))
			{
				$data = ArrayHelper::getValue($allData, $quoteId);
				$form = ArrayHelper::getValue($allForms, $quoteId);

				if ($form)
				{
					if (!$form instanceof JForm)
					{
						throw new Exception(JText::_('COM_SELLACIOUS_CART_SHIPRULE_FORM_VALIDATE_LOAD_FAILED'));
					}

					if (!$form->validate($data))
					{
						$errs = $form->getErrors();

						foreach ($errs as $ei => $error)
						{
							if ($error instanceof Exception)
							{
								$errs[$ei] = $error->getMessage();
							}
						}

						if (count($errs))
						{
							throw new Exception(implode('<br/>', $errs));
						}
					}

					$object     = (object) $data;
					$dispatcher = $this->helper->core->loadPlugins();
					$dispatcher->trigger('onContentBeforeSave', array('com_sellacious.cart.shippingform', &$object, false));

					if ($errors = $dispatcher->getErrors())
					{
						throw new Exception(implode('<br/>', $errors));
					}

					$formData = ArrayHelper::fromObject($object) ?: (array) $object;
					$shipData = ArrayHelper::getValue($formData, 'shipmentform', array());
					$values   = $this->helper->cart->buildShipmentFormData($shipData);

					$cart->setParam('shippingform', $object);
					$cart->setParam('shippingformdata', $values);

					$dispatcher->trigger('onContentAfterSave', array('com_sellacious.cart.shippingform', &$object, false));
				}

				$cart->setShipment($quoteId, null);
				$cart->commit();

				// Recalculate totals
				$cart->getTotals();

				$args       = new stdClass;
				$args->cart = $cart;
				$html       = JLayoutHelper::render('com_sellacious.cart.aio.shipping.cart', $args);

				$response = array(
					'message' => JText::_($this->text_prefix . '_SHIPPINGFORM_SAVE_SUCCESS'),
					'data'    => $html,
					'status'  => 1,
				);
			}
			elseif ($itemisedShip == SellaciousHelperShipping::SHIPPING_SELECTION_SELLER)
			{
				$flatFee      = $shippedBy == 'shop' && $this->helper->config->get('flat_shipping');
				$sellers      = $cart->getSellers();
				$quoteIds     = array_filter($this->input->get('seller_shipment', array(), 'array'));
				$quoteObjects = array();
				$quoteValues  = array();

				if ($sellerShippingInProduct && is_array($quoteIds))
				{
					$quoteSellers = array();

					foreach ($quoteIds as $sellerUid => $items)
					{
						if (is_array($items) && count(array_filter($items)) == count($items))
						{
							$quoteSellers[] = $sellerUid;
						}
					}

					$empty = count($quoteSellers) < count($sellers);
				}
				else
				{
					$empty = count($quoteIds) < count($sellers);
				}

				if ($flatFee)
				{
					// NO selection required for this item
				}
				elseif ($empty)
				{
					// NO method was selected for this item
					throw new Exception(JText::_($this->text_prefix . '_SELECT_SHIPMENT_REQUIRED'));
				}
				elseif (!empty($quoteIds))
				{
					if ($sellerShippingInProduct)
					{
						foreach ($quoteIds as $sellerUid => $itemQuotes)
						{
							if (!empty($itemQuotes))
							{
								foreach ($itemQuotes as $itemUid => $quoteId)
								{
									$data = ArrayHelper::getValue($allData, $sellerUid, array(), 'array');
									$form = ArrayHelper::getValue($allForms, $sellerUid, array(), 'array');

									$data = ArrayHelper::getValue($data, $itemUid, array(), 'array');
									$data = ArrayHelper::getValue($data, $quoteIds[$sellerUid][$itemUid], array(), 'array');
									$form = ArrayHelper::getValue($form, $itemUid, array(), 'array');
									$form = ArrayHelper::getValue($form, $quoteIds[$sellerUid][$itemUid]);

									if (isset($form) && !$form instanceof JForm)
									{
										throw new Exception(JText::_('COM_SELLACIOUS_CART_SHIPRULE_FORM_VALIDATE_LOAD_FAILED'));
									}

									// Validate shipment form
									if (isset($form) && !$form->validate($data))
									{
										$errs = $form->getErrors();

										foreach ($errs as $ei => $error)
										{
											if ($error instanceof Exception)
											{
												$errs[$ei] = $error->getMessage();
											}
										}

										if (count($errs))
										{
											throw new Exception(implode('<br>', $errs));
										}
									}

									$cart->setShipment($quoteId, $itemUid, null, $sellerUid);

									$object   = (object)$data;
									$formData = ArrayHelper::fromObject($object);
									$formData = ArrayHelper::getValue($formData, 'shipmentform', array());
									$values   = $this->helper->cart->buildShipmentFormData($formData);

									$quoteObjects[$sellerUid][$itemUid] = $object;
									$quoteValues[$sellerUid][$itemUid]  = $values;
								}
							}
						}

					}
					else
					{
						foreach ($quoteIds as $sellerUid => $quoteId)
						{
							$data    = ArrayHelper::getValue($allData, $sellerUid, array(), 'array');
							$data    = ArrayHelper::getValue($data, $quoteIds[$sellerUid], array(), 'array');
							$form    = ArrayHelper::getValue($allForms, $sellerUid, array(), 'array');
							$form    = ArrayHelper::getValue($form, $quoteIds[$sellerUid]);
							$quoteId = ArrayHelper::getValue($quoteIds, $sellerUid);

							if (isset($form) && !$form instanceof JForm)
							{
								throw new Exception(JText::_('COM_SELLACIOUS_CART_SHIPRULE_FORM_VALIDATE_LOAD_FAILED'));
							}

							// Validate shipment form
							if (isset($form) && !$form->validate($data))
							{
								$errs = $form->getErrors();

								foreach ($errs as $ei => $error)
								{
									if ($error instanceof Exception)
									{
										$errs[$ei] = $error->getMessage();
									}
								}

								if (count($errs))
								{
									throw new Exception(implode('<br>', $errs));
								}
							}

							$cart->setShipment($quoteId, null, null, $sellerUid);

							$object                   = (object)$data;
							$quoteObjects[$sellerUid] = $object;

							$formData                = ArrayHelper::fromObject($object);
							$formData                = ArrayHelper::getValue($formData, 'shipmentform', array());
							$values                  = $this->helper->cart->buildShipmentFormData($formData);
							$quoteValues[$sellerUid] = $values;
						}
					}

					$dispatcher = $this->helper->core->loadPlugins();
					$dispatcher->trigger('onContentBeforeSave', array('com_sellacious.cart.shippingform', &$quoteObjects, false));

					if ($errors = $dispatcher->getErrors())
					{
						throw new Exception(implode('<br/>', $errors));
					}

					$cart->setParam('shippingform', $quoteObjects);
					$cart->setParam('shippingformdata', $quoteValues);
					$cart->commit();

					$dispatcher->trigger('onContentAfterSave', array('com_sellacious.cart.shippingform', &$object, false));
				}

				$args       = new stdClass;
				$args->cart = $cart;
				$html       = '';
				$response   = array(
					'message' => JText::_($this->text_prefix . '_SHIPPINGFORM_SAVE_SUCCESS'),
					'data'    => $html,
					'status'  => 1,
				);
			}
			elseif ($flatFee = ($shippedBy == 'shop') && $this->helper->config->get('flat_shipping'))
			{
				$args       = new stdClass;
				$args->cart = $cart;
				$html       = JLayoutHelper::render('com_sellacious.cart.aio.shipping.cart', $args);

				$response = array(
					'message' => JText::_($this->text_prefix . '_SHIPPINGFORM_SAVE_SUCCESS'),
					'data'    => $html,
					'status'  => 1,
				);
			}
			else
			{
				throw new Exception(JText::_($this->text_prefix . '_SELECT_ORDER_SHIPMENT_REQUIRED'));
			}
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
	 * AJAX method to get a preview of selected shipment for a seller in cart
	 *
	 * @since   2.0.0
	 */
	public function previewSellerShippingTotalAjax()
	{
		try
		{
			if (!JSession::checkToken())
			{
				throw new Exception(JText::_('JINVALID_TOKEN'));
			}

			$this->validateCheckout();

			$quotesHtml = array();

			$cart     = $this->helper->cart->getCart();
			$quoteIds = array_filter($this->input->get('seller_shipment', array(), 'array'));
			$quotes   = $cart->lookupSellerShipping($quoteIds);

			if ($quotes)
			{
				foreach ($quotes as $sellerUid => $rules)
				{
					$quotesHtml[$sellerUid] = array();

					if (is_array($rules))
					{
						foreach ($rules as $ruleId => $rule)
						{
							$args        = new stdClass;
							$args->cart  = $cart;
							$args->quote = $rule;

							$quotesHtml[$sellerUid][] = JLayoutHelper::render('com_sellacious.cart.shippingform.seller_item_quotes_preview', $args);

						}
					}
				}
			}

			echo new JResponseJson($quotesHtml);
		}
		catch (Exception $e)
		{
			echo new JResponseJson($e);
		}

		jexit();
	}

	/**
	 * Get the checkout form via ajax
	 *
	 * @return  void
	 *
	 * @since   1.4.6
	 */
	public function getCheckoutFormAjax()
	{
		try
		{
			if (!JSession::checkToken())
			{
				throw new Exception(JText::_('JINVALID_TOKEN'));
			}

			$this->validateCheckout();

			$html = false;
			$form = $this->helper->cart->getCheckoutForm(true);

			if ($form)
			{
				$args       = new stdClass;
				$args->form = $form;
				$html       = JLayoutHelper::render('com_sellacious.cart.checkoutform', $args, '', array('debug' => 0));
			}

			$response = array(
				'message' => '',
				'data'    => $html,
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
	 * Save the checkout form submitted by the user
	 *
	 * @return  void
	 *
	 * @since   1.4.6
	 */
	public function saveCheckoutFormAjax()
	{
		try
		{
			if (!JSession::checkToken())
			{
				throw new Exception(JText::_('JINVALID_TOKEN'));
			}

			$this->validateCheckout();

			$form = $this->helper->cart->getCheckoutForm(true);

			if ($form)
			{
				// We don't know if there are any files inputs, we'd process if any
				$files = $this->input->files->get('jform', array(), 'array');
				$post  = $this->input->post->get('jform', array(), 'array');
				$data  = array_merge_recursive($post, $files);
				$cart  = $this->helper->cart->getCart();

				if (!CheckoutQuestionsFormHelper::validateCheckoutForm($form, $data, 'checkout_questions'))
				{
					$errs = CheckoutQuestionsFormHelper::getFormErrors();

					foreach ($errs as $ei => $error)
					{
						if ($error instanceof Exception)
						{
							$errs[$ei] = $error->getMessage();
						}
					}

					throw new Exception(implode('<br>', $errs));
				}

				$object     = (object) $data;
				$dispatcher = $this->helper->core->loadPlugins();
				$dispatcher->trigger('onContentBeforeSave', array('com_sellacious.cart.checkoutform', &$object, false));

				if ($errors = $dispatcher->getErrors())
				{
					throw new Exception(implode('<br/>', $errors));
				}

				$formData = ArrayHelper::fromObject($object);
				$values   = $this->helper->cart->buildCheckoutformData($formData);

				CheckoutQuestionsFormHelper::saveInCart('checkout_questions', $form, $formData, $cart);

				$cart->setParam('checkoutform', $object);
				$cart->setParam('checkoutformdata', $values);
				$cart->commit(true);

				$dispatcher->trigger('onContentAfterSave', array('com_sellacious.cart.checkoutform', &$object, false));

				$args         = new stdClass;
				$args->cart   = $cart;
				$args->values = $values;
				$html         = JLayoutHelper::render('com_sellacious.cart.aio.checkoutform.viewer', $args, '', array('debug' => false));

				$response = array(
					'message' => JText::_($this->text_prefix . '_CHECKOUTFORM_SAVE_SUCCESS'),
					'data'    => $html,
					'status'  => 1,
				);
			}
			else
			{
				$response = array(
					'message' => '',
					'data'    => null,
					'status'  => 1,
				);
			}
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
	 * Get cart items html via ajax
	 *
	 * @return  void
	 *
	 * @since   1.4.6
	 */
	public function getItemsHtmlAjax()
	{
		try
		{
			if (!JSession::checkToken())
			{
				throw new Exception(JText::_('JINVALID_TOKEN'));
			}

			// Do not call `validateCheckout()` here, so that we allow showing cart items always.

			$modal    = $this->input->getBool('modal', false);
			$readonly = $this->input->getBool('readonly', false);

			$options    = array('debug' => false);
			$args       = new stdClass;
			$args->cart = $this->helper->cart->getCart();

			if (!$args->cart->count())
			{
				$layout = 'empty';
			}
			elseif ($modal)
			{
				$layout = 'items_modal';
			}
			elseif ($readonly)
			{
				$layout = 'items_summary';
			}
			else
			{
				$layout = 'items';
			}

			$html     = JLayoutHelper::render('com_sellacious.cart.aio.' . $layout, $args, '', $options);
			$response = array(
				'message' => '',
				'data'    => preg_replace('/[\n\t ]+/', ' ', $html),
				'status'  => 1,
				'hash'    => $args->cart->getHashCode(),
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
	 * Remove selected cart item via Ajax
	 *
	 * @return  void
	 *
	 * @since   1.4.6
	 */
	public function removeItemAjax()
	{
		try
		{
			if (!JSession::checkToken())
			{
				throw new Exception(JText::_('JINVALID_TOKEN'));
			}

			$uid  = $this->input->post->getString('uid');
			$cart = $this->helper->cart->getCart();
			$cart->remove($uid);
			$cart->commit();

			$response = array(
				'message' => JText::_($this->text_prefix . '_REMOVE_SUCCESS'),
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
	 * Remove selected cart item via Ajax
	 *
	 * @return  void
	 *
	 * @since   1.4.6
	 */
	public function clearAjax()
	{
		try
		{
			if (!JSession::checkToken())
			{
				throw new Exception(JText::_('JINVALID_TOKEN'));
			}

			$cart = $this->helper->cart->getCart();
			$cart->clear();
			$cart->commit();

			$response = array(
				'message' => JText::_($this->text_prefix . '_CLEAR_SUCCESS'),
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
	 * Set quantity of the selected item via Ajax
	 *
	 * @return  void
	 *
	 * @since   1.4.6
	 */
	public function setQuantityAjax()
	{
		try
		{
			if (!JSession::checkToken())
			{
				throw new Exception(JText::_('JINVALID_TOKEN'));
			}

			$uid  = $this->input->post->getString('uid');
			$qty  = $this->input->post->getInt('quantity');
			$cart = $this->helper->cart->getCart();

			$cart->setQuantity($uid, $qty);
			$cart->commit();

			$response = array(
				'message' => JText::_($this->text_prefix . '_QUANTITY_UPDATE_SUCCESS'),
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
	 * Set Shiprule selected for the selected item via Ajax
	 *
	 * @return  void
	 *
	 * @since   1.4.6
	 */
	public function setShipruleAjax()
	{
		try
		{
			if (!JSession::checkToken())
			{
				throw new Exception(JText::_('JINVALID_TOKEN'));
			}

			$uid     = $this->input->post->getString('uid');
			$quoteId = $this->input->post->getString('quote_id');
			$cart    = $this->helper->cart->getCart();

			$cart->setShipment($quoteId, $uid);
			$cart->commit();

			$response = array(
				'message' => JText::_($this->text_prefix . '_SHIPRULE_UPDATE_SUCCESS'),
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
	 * Set coupon code to the user's cart
	 *
	 * @return  void
	 *
	 * @since   1.4.6
	 */
	public function setCouponAjax()
	{
		try
		{
			if (!JSession::checkToken())
			{
				throw new Exception(JText::_('JINVALID_TOKEN'));
			}

			// Should we call `validateCheckout()` here? may be later.

			$code = $this->input->post->getString('code');
			$cart = $this->helper->cart->getCart();

			$cart->setCoupon($code);
			$cart->commit();

			$response = array(
				'message' => JText::_($this->text_prefix . '_COUPON_' . (strlen($code) ? 'APPLY_SUCCESS' : 'REMOVE_SUCCESS')),
				'data'    => $this->helper->coupon->loadObject(array('coupon_code' => $code)),
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
	 * Get cart summary like total item count, total payable
	 *
	 * @return  void
	 *
	 * @since   1.4.6
	 */
	public function getSummaryAjax()
	{
		try
		{
			if (!JSession::checkToken())
			{
				throw new Exception(JText::_('JINVALID_TOKEN'));
			}

			$this->validateCheckout();

			$cart    = $this->helper->cart->getCart();
			$errorsC = array();
			$errorsI = array();

			if (!$cart->validate($errorsC, $errorsI))
			{
				throw new Exception(implode('<br/>', $errorsC));
			}

			$count = $cart->count();
			$total = $cart->getTotals();
			$hash  = $cart->getHashCode();

			$grandTotal    = $total->get('grand_total');
			$round_enabled = $this->helper->config->get('round_grand_total', 0);

			if ($round_enabled)
			{
				$grandTotal = $this->helper->cart->getRoundedTotal($grandTotal);
			}

			$response = array(
				'message' => JText::_($this->text_prefix . '_SUMMARY_SAVE_SUCCESS'),
				'data'    => array(
					'hash'            => $hash,
					'count'           => $count,
					'total'           => $total->get('grand_total'),
					'total_formatted' => $this->helper->currency->display($grandTotal, $cart->getCurrency(), '', true),
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
	 * Get the forms for each payment method available for the cart
	 *
	 * @return  void
	 *
	 * @since   1.4.6
	 */
	public function getPaymentFormsAjax()
	{
		try
		{
			if (!JSession::checkToken())
			{
				throw new Exception(JText::_('JINVALID_TOKEN'));
			}

			$this->validateCheckout();

			$args       = new stdClass;
			$args->cart = $this->helper->cart->getCart();

			if ($args->cart->count() == 0)
			{
				$layout = 'com_sellacious.cart.aio.empty';
			}
			else
			{
				$totals = $args->cart->getTotals();
				$gTotal = $totals->get('grand_total');

				if (abs($gTotal) < 0.01)
				{
					$layout = 'com_sellacious.payment.zero';
				}
				else
				{
					//Guests will be allowed here only if guest checkout active. Set 'false' UserId
					$userId = $args->cart->getUser()->id ?: false;
					$layout = 'com_sellacious.payment.forms';

					$args->methods = $this->helper->paymentMethod->getMethods('cart', true, $userId);
				}
			}

			$html     = JLayoutHelper::render($layout, $args, '', array('debug' => 0));
			$response = array(
				'message' => '',
				'data'    => preg_replace(array('/[\n\t]+/', '/\r/', '/\s+/'), array('', "\r\n", ' '), $html),
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
	 * Checkout cart and place order
	 *
	 * @return  void
	 *
	 * @since   1.4.6
	 */
	public function placeOrderAjax()
	{
		try
		{
			if (!JSession::checkToken())
			{
				throw new Exception(JText::_('JINVALID_TOKEN'));
			}

			$this->validateCheckout();

			$hash  = $this->input->post->getString('hash');
			$cart  = $this->helper->cart->getCart();
			$cHash = $cart->getHashCode();

			if ($hash == '' || $hash != $cHash)
			{
				throw new Exception(JText::_($this->text_prefix . '_HASH_MISMATCH'), 1041);
			}

			$errors = array();

			if (!$cart->validate($errors))
			{
				throw new Exception(implode('<br/>', $errors));
			}

			$orderId = $this->helper->cart->makeOrder();
			$order   = $this->helper->order->getItem($orderId);

			if (!$order->id)
			{
				throw new Exception(JText::_($this->text_prefix . '_PLACE_ORDER_FAILED'));
			}

			$cart->clear();
			$cart->commit();

			// Add this order to in-session view authorised orders list, to prevent view order access deny
			if ($order->customer_uid == 0)
			{
				$pks   = $this->app->getUserState('com_sellacious.order.view.authorised', array());
				$pks[] = (int) $orderId;

				$this->app->setUserState('com_sellacious.order.view.authorised', array_unique($pks));
			}

			$response = array(
				'message' => JText::_($this->text_prefix . '_PLACE_ORDER_SUCCESS'),
				'data'    => $order->id,
				'status'  => 1,
			);
		}
		catch (Exception $e)
		{
			$response = array(
				'message' => $e->getMessage(),
				'data'    => null,
				'status'  => $e->getCode(),
			);
		}

		echo json_encode($response);

		jexit();
	}

	/**
	 * Get Cart content from helper.
	 *
	 * @return  void
	 *
	 * @since   1.4.6
	 */
	public function getCartAjax()
	{
		try
		{
			if (!JSession::checkToken())
			{
				throw new Exception(JText::_('JINVALID_TOKEN'));
			}

			$cart   = $this->helper->cart->getCart();
			$items  = $cart->getItems();
			$totals = $cart->getTotals();

			$g_currency = $cart->getCurrency();
			$c_currency = $this->helper->currency->current('code_3');

			$data = array();
			foreach ($items as $i => $item)
			{
				$obj           = new stdClass();
				$obj->title    = trim($item->getProperty('title') . ' - ' . $item->getProperty('variant_title'), '- ');
				$obj->link     = $item->getLinkUrl();
				$obj->quantity = $item->getQuantity();
				$obj->total    = $this->helper->currency->display($item->getPrice('sub_total'), $g_currency, $c_currency, true);
				$obj->image    = $item->getImageUrl();

				$data[] = $obj;
			}

			$grandTotal = $this->helper->currency->display($totals->get('grand_total'), $g_currency, $c_currency, true);

			$response = array(
				'message' => '',
				'data'    => $data,
				'total'   => $grandTotal,
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
	 * Check whether the user is either logged-in or is this a guest checkout.
	 * If none of these is true then an exception is thrown.
	 *
	 * @return  void
	 *
	 * @throws  Exception
	 *
	 * @since   1.4.6
	 */
	protected function validateCheckout()
	{
		$user = JFactory::getUser();

		if ($user->guest)
		{
			$cart = $this->helper->cart->getCart();

			if (!$cart->getParam('guest_checkout'))
			{
				throw new Exception(JText::_($this->text_prefix . '_NOT_LOGGED_IN'));
			}
		}
	}
}
