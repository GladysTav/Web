<?php
/**
 * @version     2.0.0
 * @package     sellacious
 *
 * @copyright   Copyright (C) 2012-2020 Bhartiy Web Technologies. All rights reserved.
 * @license     SPL Sellacious Private License; see http://www.sellacious.com/spl.html
 * @author      Aditya Chakraborty <info@bhartiy.com> - http://www.bhartiy.com
 */

namespace Sellacious\Form\Handler;

// no direct access.
defined('_JEXEC') or die;

use Exception;
use JForm;
use Joomla\Registry\Registry;
use Sellacious\Cart;
use Sellacious\Cart\Item\Internal;
use SellaciousHelper;
use stdClass;

/**
 * Base Class for Checkout Questions Classes
 *
 * @since   2.0.0
 */
abstract class AbstractCheckoutQuestionsFormHandler
{
	/**
	 * The name of this checkout questions form object. Must be unique for each context
	 *
	 * @var   string
	 *
	 * @since   2.0.0
	 */
	protected $name;

	/**
	 * @var  JForm
	 *
	 * @since   2.0.0
	 */
	protected $form;

	/**
	 * @var  bool
	 *
	 * @since   2.0.0
	 */
	protected $itemised = false;

	/**
	 * @var  Registry
	 *
	 * @since   2.0.0
	 */
	protected $contexts;

	/**
	 * @var  SellaciousHelper
	 *
	 * @since   2.0.0
	 */
	protected $helper;

	/**
	 * @var  array
	 *
	 * @since   2.0.0
	 */
	protected $errors;

	/**
	 * Abstract class Constructor
	 *
	 * @param   string  $name  The handler name
	 *
	 * @throws  Exception
	 *
	 * @since   2.0.0
	 */
	public function __construct($name)
	{
		$this->name     = $name;
		$this->helper   = SellaciousHelper::getInstance();
		$this->contexts = new Registry();

		$this->contexts->set('products', false);
		$this->contexts->set('product', false);
		$this->contexts->set('checkout_questions', false);
		$this->contexts->set('cart_summary', false);

		// Context for saving in order
		$this->contexts->set('order', true);
	}

	/**
	 * Method to get the name of this checkout questions form object. Must be unique for each context
	 *
	 * @return  string
	 *
	 * @since   2.0.0
	 */
	abstract public function getName();

	/**
	 * Method to prepare checkout questions form
	 *
	 * @param   string  $itemUid  Product code
	 *
	 * @since   2.0.0
	 */
	abstract public function prepareForm($itemUid = null);

	/**
	 * Method to prepare checkout questions form data
	 *
	 * @param   Cart    $cart     The cart object
	 * @param   string  $itemUid  Product code
	 *
	 * @return  stdClass
	 *
	 * @since   2.0.0
	 */
	abstract public function getData($cart, $itemUid = null);

	/**
	 * Method to prepare checkoutform data for rendering
	 *
	 * @param   array  $data  The data
	 *
	 * @return  stdClass[]
	 *
	 * @since   2.0.0
	 */
	abstract public function buildData($data);

	/**
	 * Method to validate checkout questions form
	 *
	 * @param   array  $data  Checkout form data
	 *
	 * @return  bool
	 *
	 * @since   2.0.0
	 */
	public function validateForm($data)
	{
		if (!$this->form->validate($data))
		{
			if ($errs = $this->form->getErrors())
			{
				$this->resetErrors();

				foreach ($errs as $ei => $error)
				{
					if ($error instanceof Exception)
					{
						$this->setError($error->getMessage());
					}
				}

				return false;
			}
		}

		return true;
	}

	/**
	 * Method to save form data in cart
	 *
	 * @param   array     $data  The valid form data
	 * @param   Cart      $cart  The cart object
	 * @param   Internal  $item  The cart item object
	 *
	 * @since   2.0.0
	 */
	abstract public function saveFormInCart($data, $cart, $item = null);

	/**
	 * Method to save checkout questions in order
	 *
	 * @param   Cart  $cart     The cart object
	 * @param   int   $orderId  The order id
	 *
	 * @return  bool
	 *
	 * @since   2.0.0
	 */
	abstract public function saveFormInOrder($cart, $orderId);

	/**
	 * Method to check if context is allowed
	 *
	 * @param   string  $context  Context on which the checkout questions form has to be shown
	 *
	 * @return  bool
	 *
	 * @since   2.0.0
	 */
	public function isContextAllowed($context)
	{
		return $this->contexts->get($context, false);
	}

	/**
	 * Method to set form
	 *
	 * @param   JForm  $form  The form to set
	 *
	 * @since   2.0.0
	 */
	public function setForm($form)
	{
		$this->form = $form;
	}

	/**
	 * Method to set a form error
	 *
	 * @param   string  $error
	 *
	 * @since   2.0.0
	 */
	public function setError($error)
	{
		$this->errors[] = $error;
	}

	/**
	 * Method to reset form errors
	 *
	 * @since   2.0.0
	 */
	public function resetErrors()
	{
		$this->errors = array();
	}

	/**
	 * Method to get any form errors
	 *
	 * @return  array
	 *
	 * @since   2.0.0
	 */
	public function getErrors()
	{
		return $this->errors;
	}

	/**
	 * Check whether the form is itemised
	 *
	 * @return  bool
	 *
	 * @since   2.0.0
	 */
	public function isItemised()
	{
		return $this->itemised;
	}

	/**
	 * Set the form as itemised
	 *
	 * @param   bool  $itemised
	 *
	 * @since   2.0.0
	 */
	public function setItemised($itemised)
	{
		$this->itemised = $itemised;
	}
}
