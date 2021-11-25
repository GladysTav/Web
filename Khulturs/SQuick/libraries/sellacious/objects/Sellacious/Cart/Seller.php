<?php
/**
 * @version     2.0.0
 * @package     sellacious
 *
 * @copyright   Copyright (C) 2012-2020 Bhartiy Web Technologies. All rights reserved.
 * @license     SPL Sellacious Private License; see http://www.sellacious.com/spl.html
 * @author      Aditya Chakraborty <info@bhartiy.com> - http://www.bhartiy.com
 */

namespace Sellacious\Cart;

// no direct access
use Joomla\Registry\Registry;
use Sellacious\Cart;
use Sellacious\Shipping\ShippingQuote;

defined('_JEXEC') or die;

class Seller
{
	/**
	 * Seller user id
	 *
	 * @var   int
	 *
	 * @since   2.0.0
	 */
	public $seller_uid = 0;

	/**
	 * The linked cart object instance
	 *
	 * @var   Cart
	 *
	 * @since   2.0.0
	 */
	protected $cart;

	/**
	 * The sellacious application helper global instance
	 *
	 * @var   \SellaciousHelper
	 *
	 * @since   2.0.0
	 */
	protected $helper;

	/**
	 * Cart seller configuration options.
	 *
	 * @var    Registry
	 *
	 * @since  2.0.0
	 */
	protected $options;

	/**
	 * Seller items list in Cart.
	 *
	 * @var    Cart\Item[]
	 *
	 * @since  2.0.0
	 */
	protected $items = array();

	/**
	 * The shipping cost object
	 *
	 * @var   ShippingQuote
	 *
	 * @since   2.0.0
	 */
	protected $shipping;

	/**
	 * List of available shipping quotes for this seller
	 *
	 * @var   ShippingQuote[]
	 *
	 * @since   2.0.0
	 */
	protected $shipQuotes;

	/**
	 * Selected shipping quote's id for Cart seller
	 *
	 * @var   string
	 *
	 * @since   2.0.0
	 */
	protected $shipQuoteId;

	/**
	 * The seller name
	 *
	 * @var   string
	 *
	 * @since   2.0.0
	 */
	protected $seller_name;

	/**
	 * The store link
	 *
	 * @var   string
	 *
	 * @since   2.0.0
	 */
	protected $store_link;

	/**
	 * The total quantity of items for this seller in Cart
	 *
	 * @var   int
	 *
	 * @since   2.0.0
	 */
	protected $quantity = 0;

	/**
	 * Constructor
	 *
	 * @param   int   $seller_uid  The seller user id
	 * @param   Cart  $cart        The cart calling this instance
	 *
	 * @throws  \Exception
	 *
	 * @since   2.0.0
	 */
	public function __construct($seller_uid, $cart)
	{
		$this->seller_uid = $seller_uid;
		$this->cart       = $cart;
		$this->helper     = \SellaciousHelper::getInstance();
		$this->store_link = \JRoute::_('index.php?option=com_sellacious&view=store&id=' . $this->seller_uid);

		$this->options = $cart->getOptions();

		$this->options->def('product_select_shipping', $this->helper->config->get('product_select_shipping', 1));
	}

	/**
	 * Get the cart instance attached to this item.
	 *
	 * @return  Cart
	 *
	 * @since   2.0.0
	 */
	public function getCart()
	{
		return $this->cart;
	}

	/**
	 * Get a fully qualified objects list of items in the cart for this seller
	 *
	 * @param   bool  $activeOnly  Only get active (state = 1) cart items
	 *
	 * @return  Cart\Item[]
	 *
	 * @since   2.0.0
	 */
	public function getItems($activeOnly = false)
	{
		if (empty($this->items))
		{
			$items = $this->cart->getItems();

			foreach ($items as $item)
			{
				$sellerUid = $item->getProperty('seller_uid');

				if ($item instanceof \Sellacious\Cart\Item\Internal && $sellerUid == $this->seller_uid && (!$activeOnly || $item->state == 1))
				{
					$this->items[] = $item;
				}
			}
		}

		return $this->items;
	}

	/**
	 * @param   string  $seller_name
	 *
	 * @since   2.0.0
	 */
	public function setSellerName($seller_name)
	{
		$this->seller_name = $seller_name;
	}

	/**
	 * @param   int  $quantity
	 *
	 * @since   2.0.0
	 */
	public function setQuantity($quantity)
	{
		$this->quantity = $quantity;
	}

	/**
	 * @return  string
	 *
	 * @since   2.0.0
	 */
	public function getSellerName()
	{
		return $this->seller_name;
	}

	/**
	 * @return   string
	 *
	 * @since    2.0.0
	 */
	public function getStoreLink()
	{
		return $this->store_link;
	}

	/**
	 * @return   int
	 *
	 * @since   2.0.0
	 */
	public function getQuantity()
	{
		return $this->quantity;
	}

	/**
	 * Get the shipping quotes for the seller in cart
	 *
	 * @return  ShippingQuote[]
	 *
	 * @throws  \Exception
	 *
	 * @since   2.0.0
	 */
	public function getShipQuotes()
	{
		if (empty($this->shipQuotes))
		{
			$this->getShipping();
		}

		return $this->shipQuotes ?: array();
	}

	/**
	 * Get the the shipping cost and parameters
	 *
	 * @param   string  $key  The value to get from the shipping object
	 *
	 * @return  mixed
	 * @throws  \Exception
	 *
	 * @since   2.0.0
	 */
	public function getShipping($key = null)
	{
		if ($this->cart->getOptions()->get('itemised_shipping') == \SellaciousHelperShipping::SHIPPING_SELECTION_CART || $this->cart->getOptions()->get('itemised_shipping') == \SellaciousHelperShipping::SHIPPING_SELECTION_PRODUCT)
		{
			$this->shipQuotes  = array();
			$this->shipQuoteId = null;
			$this->shipping    = null;
		}
		elseif (empty($this->shipping))
		{
			$items   = $this->getItems();
			$objects = array();

			foreach ($items as $item)
			{
				if (!$item instanceof Cart\Item\Internal || !$item->isShippable() || $item->state != 1)
				{
					continue;
				}

				$shippedBy = $this->helper->config->get('shipped_by');
				$flatShip  = ($shippedBy == 'shop') && $this->helper->config->get('flat_shipping');

				if ($flatShip)
				{
					$c_currency = $this->cart->getCurrency();
					$g_currency = $this->helper->currency->getGlobal('code_3');

					$shipFee  = $this->helper->config->get('shipping_flat_fee');
					$shipFee  = $this->helper->currency->convert($shipFee, $g_currency, $c_currency);
					$shipping = $this->helper->shipping->flat($item->getQuantity(), $shipFee, $this->seller_uid);

					if ($this->options->get('product_select_shipping') == 1)
					{
						$this->shipQuotes[$item->getUid()] = array($shipping);
					}
					else
					{
						$this->shipQuotes = array($shipping);
					}
				}
				else
				{
					$objects[] = $item;
				}
			}

			if (!empty($objects))
			{
				try
				{
					$shipTo = $this->cart->getShipTo();
					$origin = $this->helper->shipping->getShipOrigin($this->seller_uid);
					$quotes = array();

					if ($this->options->get('product_select_shipping') == 1)
					{
						/** @var \Sellacious\Cart\Item\Internal $object */
						foreach ($objects as $object)
						{
							$quotes[$object->getUid()] = $this->helper->shipping->getItemQuotes($object, $origin, $shipTo);
						}
					}
					else
					{
						$quotes = $this->helper->shipping->getItemsQuotes($objects, $origin, $shipTo, $this->seller_uid);
					}

					$this->shipQuotes = $quotes;
				}
				catch (\Exception $e)
				{
					// Ignored exception
				}
			}

			$this->shipping = $this->helper->shipping->lookup($this->shipQuotes, $this->shipQuoteId);
		}

		if (!$key)
		{
			return $this->shipping;
		}
		elseif (is_object($this->shipping) && isset($this->shipping->$key))
		{
			return $this->shipping->$key;
		}
		else
		{
			return null;
		}
	}
}
