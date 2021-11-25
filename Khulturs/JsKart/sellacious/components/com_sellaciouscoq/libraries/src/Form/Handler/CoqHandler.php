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

// No direct access
defined('_JEXEC') or die;

use Exception;
use Sellacious\Cart;
use Sellacious\Cart\Item\Internal;
use stdClass;

/**
 * Class for Checkout Questions
 *
 * @since   2.0.0
 */
class CoqHandler extends AbstractCheckoutQuestionsFormHandler
{
	/**
	 * Class Constructor
	 *
	 * @param   string  $name  The handler name
	 *
	 * @throws  Exception
	 *
	 * @since   2.0.0
	 */
	public function __construct($name)
	{
		parent::__construct($name);

		$this->contexts->set('checkout_questions', true);
	}

	/**
	 * Method to get the name of this checkout questions form object. Must be unique for each context
	 *
	 * @return  string
	 *
	 * @since   2.0.0
	 */
	public function getName()
	{
		return 'checkout_questions';
	}

	/**
	 * Method to prepare checkout questions form
	 *
	 * @param   string  $itemUid  Product code
	 *
	 * @since   2.0.0
	 */
	public function prepareForm($itemUid = null)
	{
		$coFields     = (array) $this->helper->config->get('checkoutform');
		$globalFields = $this->helper->field->getGlobalFields('checkoutform');

		if ($globalFields)
		{
			foreach ($globalFields as $globalField)
			{
				array_unshift($coFields, $globalField);
			}
		}

		$coFields = $this->helper->field->getListWithGroup($coFields);

		if ($coFields)
		{
			$xml = $this->helper->field->createFormXml($coFields, 'checkoutform', 'checkoutform')->asXML();
			$this->form->load($xml);
		}
	}

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
	public function getData($cart, $itemUid = null)
	{
		return (object) array();
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
	public function saveFormInCart($data, $cart, $item = null)
	{
		// Nothing to do here
	}

	/**
	 * Method to prepare checkoutform data for rendering
	 *
	 * @param   array  $data  The data
	 *
	 * @return  stdClass[]
	 *
	 * @since   2.0.0
	 */
	public function buildData($data)
	{
		return null;
	}

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
	public function saveFormInOrder($cart, $orderId)
	{
		// Nothing to do here
		return true;
	}
}
