<?php
/**
 * @version     2.0.0
 * @package     sellacious
 *
 * @copyright   Copyright (C) 2012-2020 Bhartiy Web Technologies. All rights reserved.
 * @license     SPL Sellacious Private License; see http://www.sellacious.com/spl.html
 * @author      Izhar Aazmi <info@bhartiy.com> - http://www.bhartiy.com
 */
namespace Sellacious\Price;

use Exception;
use JFactory;
use Joomla\Utilities\ArrayHelper;
use JText;
use Sellacious\Config\ConfigHelper;
use Sellacious\Price\Exception\InvalidPriceHandlerException;
use Sellacious\Price\Handler\AbstractPriceHandler;
use SellaciousHelper;
use stdClass;

defined('_JEXEC') or die;

/**
 * Helper class for product pricing utility
 *
 * @since   2.0.0
 */
class PriceHelper
{
	/**
	 * List of known price handler
	 *
	 * @var   stdClass[]
	 *
	 * @since   2.0.0
	 */
	protected static $handlers = array();

	/**
	 * Loaded price handler instances
	 *
	 * @var   AbstractPriceHandler[]
	 *
	 * @since   2.0.0
	 */
	protected static $instances = array();

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

			PriceHelper::addHandler('hidden', 'Sellacious\Price\Handler\HiddenPriceHandler', JText::_('COM_SELLACIOUS_PRICE_HANDLER_HIDDEN_LABEL'), JText::_('COM_SELLACIOUS_PRICE_HANDLER_HIDDEN_DESC'));
			PriceHelper::addHandler('call', 'Sellacious\Price\Handler\CallPriceHandler', JText::_('COM_SELLACIOUS_PRICE_HANDLER_CALL_LABEL'), JText::_('COM_SELLACIOUS_PRICE_HANDLER_CALL_DESC'));
			PriceHelper::addHandler('email', 'Sellacious\Price\Handler\EmailPriceHandler', JText::_('COM_SELLACIOUS_PRICE_HANDLER_EMAIL_LABEL'), JText::_('COM_SELLACIOUS_PRICE_HANDLER_EMAIL_DESC'));
			PriceHelper::addHandler('free', 'Sellacious\Price\Handler\FreePriceHandler', JText::_('COM_SELLACIOUS_PRICE_HANDLER_FREE_LABEL'), JText::_('COM_SELLACIOUS_PRICE_HANDLER_FREE_DESC'));
			PriceHelper::addHandler('queryform', 'Sellacious\Price\Handler\QueryformPriceHandler', JText::_('COM_SELLACIOUS_PRICE_HANDLER_QUERYFORM_LABEL'), JText::_('COM_SELLACIOUS_PRICE_HANDLER_QUERYFORM_DESC'));
			PriceHelper::addHandler('flat', 'Sellacious\Price\Handler\FlatPriceHandler', JText::_('COM_SELLACIOUS_PRICE_HANDLER_FLAT_LABEL'), JText::_('COM_SELLACIOUS_PRICE_HANDLER_FLAT_DESC'));
			PriceHelper::addHandler('basic', 'Sellacious\Price\Handler\BasicPriceHandler', JText::_('COM_SELLACIOUS_PRICE_HANDLER_BASIC_LABEL'), JText::_('COM_SELLACIOUS_PRICE_HANDLER_BASIC_DESC'));
			PriceHelper::addHandler('dynamic', 'Sellacious\Price\Handler\DynamicPriceHandler', JText::_('COM_SELLACIOUS_PRICE_HANDLER_DYNAMIC_LABEL'), JText::_('COM_SELLACIOUS_PRICE_HANDLER_DYNAMIC_DESC'));
		}
	}

	/**
	 * Add a price handler
	 *
	 * @param   string  $name         System identifier for the price handler
	 * @param   string  $className    Class name for the price handler to be instantiated
	 * @param   string  $label        Text label as handler name for display
	 * @param   string  $description  Description for display
	 *
	 * @return  void
	 *
	 * @since   2.0.0
	 */
	public static function addHandler($name, $className, $label, $description = null)
	{
		static::load();

		static::$handlers[$name] = (object) array('name' => $name, 'class' => $className, 'label' => $label, 'description' => $description);
	}

	/**
	 * Get a price handler instance by name, create new instance only if not already exists
	 *
	 * @param   string  $name  System identifier for the price handler to load
	 *
	 * @return  AbstractPriceHandler
	 *
	 * @throws  InvalidPriceHandlerException
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
				throw new InvalidPriceHandlerException(JText::sprintf('COM_SELLACIOUS_PRICE_EXCEPTION_INVALID_HANDLER', $name));
			}

			static::$instances[$name] = new $handler->class($handler->name,  $handler->label, $handler->description);
		}

		return static::$instances[$name];
	}

	/**
	 * Get the price handler for defined pricing type for given product-seller
	 *
	 * @param   int  $productId  The product id
	 * @param   int  $sellerUid  The seller user id
	 *
	 * @return  AbstractPriceHandler
	 *
	 * @since   2.0.0
	 */
	public static function getPsxHandler($productId, $sellerUid)
	{
		$name = static::getPsxPricingType($productId, $sellerUid);

		return static::getHandler($name);
	}

	/**
	 * Get a list of all known price handlers
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
	 * Method to get selected price handlers
	 *
	 * @param   array  $selected  List of selected handlers
	 * @param   bool   $global    Whether to filter from global pricing types
	 *
	 * @return  stdClass[]
	 *
	 * @since   2.0.0
	 */
	public static function getSelectedHandlers($selected, $global = true)
	{
		if ($global)
		{
			try
			{
				// We have to limit the selected list with global as well
				$config  = ConfigHelper::getInstance('com_sellacious');
				$allowed = (array) $config->get('pricing_types');

				if ($allowed)
				{
					$selected = $selected ? array_intersect($allowed, $selected) : $allowed;
				}
			}
			catch (Exception $e)
			{
				echo $e->getMessage();
			}
		}

		$handlers = static::getHandlers();

		if ($selected)
		{
			$handlers = array_filter($handlers, function ($v) use ($selected) {
				return in_array($v->name, $selected);
			});
		}

		return $handlers;
	}

	/**
	 * Get the allowed pricing types for given categories
	 *
	 * @param   int[]  $pks  The category ids
	 *
	 * @return  string[]
	 *
	 * @throws  Exception
	 *
	 * @since   2.0.0
	 */
	public static function getAllowedForCategory($pks)
	{
		$helper = SellaciousHelper::getInstance();

		foreach ($pks as $pk)
		{
			$value = $helper->category->getCategoryParam($pk, 'pricing_types');

			if ($value)
			{
				return $value;
			}
		}

		foreach ($pks as $pk)
		{
			$value = $helper->category->getCategoryParam($pk, 'pricing_types', null, true);

			if ($value)
			{
				return $value;
			}
		}

		$config = ConfigHelper::getInstance('com_sellacious');

		return (array) $config->get('pricing_types', array_keys(static::getHandlers()));
	}

	/**
	 * Get the defined pricing type for given product-seller
	 *
	 * @param   int  $productId  The product id
	 * @param   int  $sellerUid  The seller user id
	 *
	 * @return  string
	 *
	 * @since   2.0.0
	 */
	public static function getPsxPricingType($productId, $sellerUid)
	{
		$db    = JFactory::getDbo();
		$query = $db->getQuery(true);

		$query->select('pricing_type')
		    ->from('#__sellacious_product_sellers')
			->where('product_id = ' . (int) $productId)
			->where('seller_uid = ' . (int) $sellerUid);

		$value = $db->setQuery($query)->loadResult();

		return $value ?: 'hidden';
	}
}
