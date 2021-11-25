<?php
/**
 * @version     2.0.0
 * @package     sellacious
 *
 * @copyright   Copyright (C) 2012-2020 Bhartiy Web Technologies. All rights reserved.
 * @license     SPL Sellacious Private License; see http://www.sellacious.com/spl.html
 * @author      Aditya Chakraborty <info@bhartiy.com> - http://www.bhartiy.com
 */

namespace Sellacious\Form;

// no direct access.
defined('_JEXEC') or die;

use Exception;
use JForm;
use Joomla\Utilities\ArrayHelper;
use JText;
use Sellacious\Cart;
use Sellacious\Cart\Item\Internal;
use Sellacious\Form\Handler\AbstractCheckoutQuestionsFormHandler;
use SellaciousHelper;
use stdClass;

/**
 * Helper class for Checkout Questions Form
 *
 * @since   2.0.0
 */
class CheckoutQuestionsFormHelper
{
	/**
	 * List of known check questions handlers
	 *
	 * @var   stdClass[]
	 *
	 * @since   2.0.0
	 */
	protected static $handlers = array();

	/**
	 * Loaded checkout questions handler instances
	 *
	 * @var    AbstractCheckoutQuestionsFormHandler[]
	 *
	 * @since   2.0.0
	 */
	protected static $instances = array();

	/**
	 * @var  array
	 *
	 * @since   2.0.0
	 */
	protected static $errors;

	/**
	 * Load internal handlers
	 *
	 * @return  void
	 *
	 * @since   2.0.0
	 */
	protected static function load()
	{
		static $loaded;

		if (!$loaded)
		{
			$loaded = true;
		}
	}

	/**
	 * Add a checkout questions form handler
	 *
	 * @param   string  $name       System identifier for the checkout questions form handler
	 * @param   string  $className  Class name for the checkout questions form handler to be instantiated
	 *
	 * @return  void
	 *
	 * @since   2.0.0
	 */
	public static function addHandler($name, $className)
	{
		static::load();

		static::$handlers[$name] = (object) array('name' => $name, 'class' => $className);
	}

	/**
	 * Get a checkout questions form handler instance by name, create new instance only if not already exists
	 *
	 * @param   string  $name  System identifier for the checkout questions form handler to load
	 *
	 * @return  AbstractCheckoutQuestionsFormHandler
	 *
	 * @throws  Exception
	 *
	 * @since   2.0.0
	 */
	public static function getHandler($name)
	{
		static::load();

		if (!isset(static::$instances[$name]))
		{
			$handler = ArrayHelper::getValue(static::$handlers, $name);

			if (!is_object($handler) || !class_exists($handler->class))
			{
				throw new Exception(JText::sprintf('COM_SELLACIOUS_CHECKOUT_QUESTIONS_FORM_EXCEPTION_INVALID_HANDLER', $name));
			}

			static::$instances[$name] = new $handler->class($handler->name);
		}

		return static::$instances[$name];
	}

	/**
	 * Get a list of all known checkout questions form handlers
	 *
	 * @return  stdClass[]
	 *
	 * @since   2.0.0
	 */
	public static function getHandlers()
	{
		static::load();

		return static::$handlers;
	}

	/**
	 * Method to prepare the checkout form
	 *
	 * @param   string  $context  The context on which the checkout form will be displayed
	 * @param   JForm   $form     The checkout form
	 * @param   string  $itemUid  Product code
	 *
	 * @throws  Exception
	 *
	 * @since   2.0.0
	 */
	public static function prepareCheckOutForm($context, $form, $itemUid = null)
	{
		if (!$form)
		{
			return;
		}

		$handlers = static::getHandlers();

		foreach ($handlers as $handler)
		{
			$instance = static::getHandler($handler->name);

			if ($instance->isContextAllowed($context))
			{
				$instance->setForm($form);
				$instance->prepareForm($itemUid);
			}
		}
	}

	/**
	 * Method to prepare the checkout form data
	 *
	 * @param   string    $context  The context on which the checkout form will be displayed
	 * @param   JForm     $form     The checkout form
	 * @param   stdClass  $data     The checkout form data
	 * @param   Cart      $cart     The cart
	 * @param   string    $itemUid  Product code
	 *
	 * @throws  Exception
	 *
	 * @since   2.0.0
	 */
	public static function prepareCheckoutFormData($context, $form, &$data, $cart, $itemUid = null)
	{
		if (!$form)
		{
			return;
		}

		$coqData = static::getData($context, $cart, $itemUid);

		if ($coqData)
		{
			$data = array_merge((array)$data, $coqData);
			$data = ArrayHelper::toObject($data);
		}
	}

	/**
	 * Method to get checkout form data
	 *
	 * @param   string  $context    The context on which the checkout form will be displayed
	 * @param   Cart    $cart       The cart
	 * @param   string  $itemUid    Product code
	 * @param   bool    $buildData  Build form data
	 *
	 * @return  array
	 *
	 * @throws  Exception
	 *
	 * @since   2.0.0
	 */
	public static function getData($context, $cart, $itemUid = null, $buildData = false)
	{
		$handlers = static::getHandlers();
		$data     = array();

		foreach ($handlers as $handler)
		{
			$instance = static::getHandler($handler->name);

			// If Item uid is provided but the handler does not support itemised data, then no point of getting its data
			if ($itemUid && !$instance->isItemised())
			{
				continue;
			}

			if ($instance->isContextAllowed($context))
			{
				$handlerData = $instance->getData($cart, $itemUid);
				$handlerData = is_object($handlerData) ? (array)$handlerData : $handlerData;

				if ($buildData)
				{
					$items = $instance->buildData($handlerData);

					if ($items)
					{
						$handlerData = $items;
					}
				}

				$data = array_merge($data, $handlerData);
			}
		}

		return $data;
	}

	/**
	 * Method to validate the checkout form
	 *
	 * @param   JForm   $form     The checkout form
	 * @param   array   $data     The checkout form data
	 * @param   string  $context  The context on which the checkout form will be displayed
	 *
	 * @throws  Exception
	 *
	 * @return  bool
	 *
	 * @since   2.0.0
	 */
	public static function validateCheckoutForm($form, $data, $context)
	{
		if (!$form)
		{
			return false;
		}

		$handlers = static::getHandlers();

		foreach ($handlers as $handler)
		{
			$instance = static::getHandler($handler->name);
			$instance->setForm($form);

			if ($instance->isContextAllowed($context) && !$instance->validateForm($data))
			{
				if ($errors = $instance->getErrors())
				{
					static::resetFormErrors();

					foreach ($errors as $error)
					{
						static::setFormError($error);
					}
				}
			}
		}

		if ($errors = static::getFormErrors())
		{
			return false;
		}

		return true;
	}

	/**
	 * Method to save checkout questions form in cart
	 *
	 * @param   string    $context  The context on which the checkout form will be displayed
	 * @param   JForm     $form     The checkout form
	 * @param   array     $data     The checkout form data
	 * @param   Cart      $cart     The cart
	 * @param   Internal  $item     The cart item object
	 *
	 * @throws  Exception
	 *
	 * @since   2.0.0
	 */
	public static function saveInCart($context, $form, $data, $cart, $item = null)
	{
		if (!$form)
		{
			return;
		}

		$handlers = static::getHandlers();

		foreach ($handlers as $handler)
		{
			$instance = static::getHandler($handler->name);
			$instance->setForm($form);

			if ($instance->isContextAllowed($context))
			{
				$instance->saveFormInCart($data, $cart, $item);
			}
		}
	}

	/**
	 * Method to save checkout questions form
	 *
	 * @param   string    $context  The context on which the checkout form will be displayed
	 * @param   array     $data     The checkout form data
	 * @param   Cart      $cart     The cart
	 * @param   Internal  $item     The cart item object
	 *
	 * @throws  Exception
	 *
	 * @since   2.0.0
	 */
	public static function saveForm($context, $data, $cart, $item = null)
	{
		$helper = SellaciousHelper::getInstance();
		$form   = $helper->cart->getCheckoutForm(false, $context, $item ? $item->getUid() : null);

		if (!$form)
		{
			throw new Exception(JText::_('COM_SELLACIOUS_PRODUCT_CHECKOUT_QUESTIONS_FORM_INVALID_FORM'));
		}

		if (!static::validateCheckoutForm($form, $data, $context))
		{
			$errs = static::getFormErrors();

			foreach ($errs as $ei => $error)
			{
				if ($error instanceof Exception)
				{
					$errs[$ei] = $error->getMessage();
				}
			}

			throw new Exception(implode('<br>', $errs));
		}

		self::saveInCart($context, $form, $data, $cart, $item);
	}

	/**
	 * Method to save checkout questions in order
	 *
	 * @param   string  $context  The context on which the checkout form will be displayed
	 * @param   Cart    $cart     The cart
	 * @param   int     $orderId  The order id
	 *
	 * @throws  Exception
	 *
	 * @since   2.0.0
	 */
	public static function saveFormInOrder($context, $cart, $orderId)
	{
		$handlers = static::getHandlers();

		foreach ($handlers as $handler)
		{
			$instance = static::getHandler($handler->name);

			if ($instance->isContextAllowed($context))
			{
				$instance->saveFormInOrder($cart, $orderId);
			}
		}
	}

	/**
	 * Method to get any form errors
	 *
	 * @return  array
	 *
	 * @since   2.0.0
	 */
	public static function getFormErrors()
	{
		return static::$errors;
	}

	/**
	 * Method to set a Form error
	 *
	 * @param   string  $error
	 *
	 * @since   2.0.0
	 */
	public static function setFormError($error)
	{
		static::$errors[] = $error;
	}

	/**
	 * Method to reset Form errors
	 *
	 * @since   2.0.0
	 */
	public static function resetFormErrors()
	{
		static::$errors = array();
	}
}
