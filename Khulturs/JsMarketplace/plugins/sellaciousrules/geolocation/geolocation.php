<?php
/**
 * @version     1.7.4
 * @package     sellacious
 *
 * @copyright   Copyright (C) 2012-2019 Bhartiy Web Technologies. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Izhar Aazmi <info@bhartiy.com> - http://www.bhartiy.com
 */
// no direct access
defined('_JEXEC') or die;

use Joomla\Registry\Registry;
use Sellacious\Cart;
use Sellacious\Cart\Item;
use Sellacious\Shipping\ShippingQuote;

class plgSellaciousRulesGeolocation extends JPlugin
{
	/**
	 * Load the language file on instantiation.
	 *
	 * @var    boolean
	 *
	 * @since  1.2.0
	 */
	protected $autoloadLanguage = true;

	/**
	 * Adds additional fields to the sellacious rules editing form
	 *
	 * @param   JForm  $form  The form to be altered.
	 * @param   mixed  $data  The associated data for the form.
	 *
	 * @return  boolean
	 *
	 * @since   1.2.0
	 */
	public function onContentPrepareForm($form, $data)
	{
		if (!$form instanceof JForm)
		{
			$this->_subject->setError('JERROR_NOT_A_FORM');

			return false;
		}

		$name   = $form->getName();
		$helper = SellaciousHelper::getInstance();

		// Check we are manipulating a valid form.
		if ($name == 'com_sellacious.shoprule' || $name == 'com_sellacious.coupon' || $name == 'com_sellacious.shippingrule' || $name == 'com_sellacious.paymentmethod')
		{
			$registry = new Registry($data);

			$form->loadFile(__DIR__ . '/forms/geolocation.xml', false);

			$a_type = $registry->get('params.geolocation.address_type', 'billing');

			foreach (array('country', 'state', 'district', 'zip') as $field)
			{
				$form->setFieldAttribute($field, 'address_type', $a_type, 'params.geolocation');
			}

			if ($name == 'com_sellacious.shoprule' && $helper->config->get('multi_seller', 0))
			{
				JHtml::_('jquery.framework');
				JHtml::_('script', 'plg_sellaciousrules_geolocation/default.js', false, true);
			}
			else
			{
				$form->removeField('seller_match', 'params.geolocation');
			}

		}
		elseif ($name == 'com_sellacious.config')
		{
			// Inject plugin configuration into config form
			$form->loadFile(__DIR__ . '/' . $this->_name . '.xml', false, '//config');
		}

		return true;
	}

	/**
	 * Adds additional data to the sellacious form data
	 *
	 * @param   string  $context  The context identifier
	 * @param   array   $data     The associated data for the form.
	 *
	 * @return  bool
	 *
	 * @since   1.2.0
	 */
	public function onContentPrepareData($context, $data)
	{
		$plugin = sprintf("plg_%s_%s", $this->_type, $this->_name);

		if (is_object($data) && empty($data->$plugin))
		{
			if ($context == 'com_sellacious.config')
			{
				$data->$plugin = $this->params->toArray();
			}
		}

		return true;
	}

	/**
	 * Validates given shoprule against this filter
	 * Plugins are passed a reference to the shoprule registry object. They are free to manipulate it in any way.
	 *
	 * If a plugin cannot determine with the available data, the rules shall not be applied but shall be listed as
	 * possibility. This is identified as: $rule->set('rule.inclusive', false);
	 * Any plugin encountering similar state should simply make it false. It show however NEVER set this to true.
	 *
	 * If the decision was made based on internal logic already, then the plugin shall report whether to skip the
	 * rule or apply. This is identified by the return value of the plugin.
	 * Any plugin encountering similar state should simply return boolean true OR false.
	 *
	 * @param   string    $context   The context identifier: 'com_sellacious.shoprule.product'
	 * @param   Registry  $rule      Registry object for the shoprule to test against
	 * @param   stdClass  $item      The product item with Price data for the variant in question
	 * @param   bool      $use_cart  Whether to use cart attributes or ignore them
	 *
	 * @return  bool
	 *
	 * @throws  Exception
	 *
	 * @since   1.2.0
	 */
	public function onValidateProductShoprule($context, Registry $rule, $item, $use_cart = null)
	{
		if ($context != 'com_sellacious.shoprule.product' || empty($item->product_id))
		{
			return true;
		}

		$result = true;
		$filter = $rule->extract('params.geolocation');
		$helper = SellaciousHelper::getInstance();

		if ($filter && ($filter->get('country') || $filter->get('state') || $filter->get('district') || $filter->get('zip') || ($filter->get('seller_match') && $helper->config->get('multi_seller', 0))))
		{
			if (!$use_cart)
			{
				// Without cart attribute any geo location filter is meaningless
				$rule->set('rule.inclusive', false);

				return true;
			}

			$cart   = $helper->cart->getCart();
			$result = $this->checkFilter($rule, $cart, $item);
		}

		return $result;
	}

	/**
	 * Validates given shipping shoprule against this filter
	 * Plugins are passed a reference to the shoprule registry object. They are free to manipulate it in any way.
	 *
	 * If a plugin cannot determine with the available data, the rules shall not be applied but shall be listed as
	 * possibility. This is identified as: $rule->set('rule.inclusive', false);
	 * Any plugin encountering similar state should simply make it false. It show however NEVER set this to true.
	 *
	 * If the decision was made based on internal logic already, then the plugin shall report whether to skip the
	 * rule or apply. This is identified by the return value of the plugin.
	 * Any plugin encountering similar state should simply return boolean true OR false.
	 *
	 * @param   string         $context   The context identifier: 'com_sellacious.shoprule.product'
	 * @param   Registry       $rule      Registry object for the shoprule to test against
	 * @param   ShippingQuote  $shipping  The product item with Price data for the variant in question
	 *
	 * @return  bool
	 *
	 * @throws  \Exception
	 *
	 * @since   1.7.1
	 */
	public function onValidateShipmentShoprule($context, Registry $rule, $shipping)
	{
		if ($context != 'com_sellacious.shoprule.shipment' || empty($shipping))
		{
			return true;
		}

		$helper = SellaciousHelper::getInstance();
		$cart   = $helper->cart->getCart();

		return $this->checkShippingFilter($rule, $cart, $shipping);
	}

	/**
	 * Validates given shippingrule against this filter
	 *
	 * If a plugin cannot determine with the available data, the rules shall not be applied but shall be listed as
	 * possibility. This is identified as: $rule->set('rule.inclusive', false);
	 * Any plugin encountering similar state should simply make it false. It show however NEVER set this to true.
	 *
	 * If the decision was made based on internal logic already, then the plugin shall report whether to skip the
	 * rule or apply. This is identified by the return value of the plugin.
	 * Any plugin encountering similar state should simply return boolean true OR false.
	 *
	 * @param   string    $context   The context identifier: 'com_sellacious.shippingrule.product'
	 * @param   Registry  $rule      Registry object for the shippingrule to test against
	 * @param   stdClass  $item      The product item with Price data for the variant in question
	 * @param   bool      $use_cart  Whether to use cart attributes or ignore them
	 *
	 * @return  bool
	 *
	 * @throws  Exception
	 *
	 * @since   1.2.0
	 */
	public function onValidateProductShippingrule($context, Registry $rule, $item, $use_cart = null)
	{
		if ($context != 'com_sellacious.shippingrule.product' || empty($item->product_id))
		{
			return true;
		}

		$result = true;
		$filter = $rule->extract('params.geolocation');

		if ($filter && ($filter->get('country') || $filter->get('state') || $filter->get('district') || $filter->get('zip')))
		{
			// Without cart attribute any geo location filter is meaningless
			if (!$use_cart)
			{
				return false;
			}

			$helper = SellaciousHelper::getInstance();
			$cart   = $helper->cart->getCart();
			$result = $this->checkFilter($rule, $cart);
		}

		return $result;
	}

	/**
	 * Validates given shoprule against this filter.
	 * Plugins are passed a reference to the shoprule registry object. They are free to manipulate it in any way.
	 *
	 * Plugin responses: true = apply
	 * If any of the plugins says FALSE - we'd exclude that rule entirely.
	 * In all other case plugins should update the 'rule.inclusive' value to false = not decidable
	 *
	 * @param   string    $context  The context identifier: 'com_sellacious.shoprule.cart'
	 * @param   Registry  $rule     Registry object for the shoprule to test against
	 * @param   Cart      $cart     The cart object
	 *
	 * @return  bool
	 *
	 * @throws  Exception
	 *
	 * @since   1.2.0
	 */
	public function onValidateCartShoprule($context, Registry $rule, Cart $cart)
	{
		if ($context != 'com_sellacious.shoprule.cart')
		{
			return true;
		}

		return $this->checkFilter($rule, $cart);
	}

	/**
	 * Validates given shippingrule against this filter.
	 *
	 * Plugin responses: true = apply
	 * If any of the plugins says FALSE - we'd exclude that rule entirely.
	 * In all other case plugins should update the 'rule.inclusive' value to false = not decidable
	 *
	 * @param   string    $context  The context identifier: 'com_sellacious.shippingrule.cart'
	 * @param   Registry  $rule     Registry object for the shippingrule to test against
	 * @param   Cart      $cart     The cart object
	 *
	 * @return  bool
	 *
	 * @throws  Exception
	 *
	 * @since   1.2.0
	 */
	public function onValidateCartShippingrule($context, Registry $rule, Cart $cart)
	{
		if ($context != 'com_sellacious.shippingrule.cart')
		{
			return true;
		}

		return $this->checkFilter($rule, $cart);
	}

	/**
	 * Validates given coupon against this filter.
	 * Plugins are passed a reference to the coupon and cart item objects. They are free to to the manipulation in any way.
	 *
	 * Plugin responses: true = apply
	 * If any of the plugins says FALSE - we'd not apply that coupon at all.
	 *
	 * @param   string  $context   The context identifier: 'com_sellacious.coupon'
	 * @param   Cart    $cart      The cart object
	 * @param   Item[]  $items     The cart items that are so far considered eligible, if this plugin determines
	 *                             that this item is not eligible this will remove it from the array
	 * @param   Registry  $coupon  The coupon object
	 *
	 * @return  bool
	 *
	 * @throws  Exception
	 *
	 * @since   1.2.0
	 */
	public function onValidateCoupon($context, Cart $cart, &$items, &$coupon)
	{
		if ($context != 'com_sellacious.coupon')
		{
			return true;
		}

		return $this->checkFilter($coupon, $cart);
	}

	/**
	 * Validates given payment method against this filter.
	 * Plugins are passed a reference to the payment method registry object. They are free to manipulate it in any way.
	 *
	 * Plugin responses: true = apply
	 * If any of the plugins says FALSE - we'd exclude that method entirely.
	 * In all other case plugins should update the 'method.inclusive' value to false = not decidable
	 *
	 * @param   string    $context  The context identifier: 'com_sellacious.paymentmethod.cart' OR 'com_sellacious.paymentmethod.addfund'
	 * @param   Registry  $method   Registry object for the payment method to test against
	 * @param   int       $orderId  The order id/transaction id etc, whatever is relevant for the said context
	 *
	 * @return  bool
	 *
	 * @throws  Exception
	 *
	 * @since   1.2.0
	 */
	public function onBeforeLoadPaymentMethod($context, Registry $method, $orderId = 0)
	{
		if ($context == 'com_sellacious.paymentmethod.cart')
		{
			// Fixme: If $orderId > 0, then use addresses from order instead of cart
			$helper = SellaciousHelper::getInstance();

			if($orderId > 0)
			{
				//Geo location filters for payment method not possible for orders.
				return true;
			}
			else
			{
				$cart   = $helper->cart->getCart();
				return $this->checkFilter($method, $cart);
			}
		}
		elseif ($context == 'com_sellacious.paymentmethod.addfund')
		{
			return true;
		}
		else
		{
			return true;
		}
	}

	/**
	 * Verify the filter set for the given rule
	 *
	 * @param   Registry  $rule  The rule in question
	 * @param   Cart      $cart  The cart object
	 * @param   stdClass  $item  The cart item
	 *
	 * @return  bool
	 *
	 * @throws  \Exception
	 *
	 * @since   1.2.0
	 */
	protected function checkFilter(Registry $rule, Cart $cart, stdClass $item = null)
	{
		$result = true;
		$filter = $rule->extract('params.geolocation');
		$helper = SellaciousHelper::getInstance();

		if ($filter && ($filter->get('country') || $filter->get('state') || $filter->get('district') || $filter->get('zip') || ($filter->get('seller_match') && $helper->config->get('multi_seller', 0))))
		{
			$allowed = array(
				'continent' => array(),
				'country'   => array_filter(explode(',', $filter->get('country'))),
				'state'     => array_filter(explode(',', $filter->get('state'))),
				'district'  => array_filter(explode(',', $filter->get('district'))),
				'zip'       => array_filter(explode(',', $filter->get('zip'))),
			);

			if ($helper->config->get('multi_seller', 0))
			{
				$allowed['seller_match'] = array_filter($filter->get('seller_match', array()), function ($matchItem) {
					return !is_object($matchItem);
				});

				if (!empty($allowed['seller_match']))
				{
					$allowed['seller_match_type'] = 1;

					foreach ($filter->get('seller_match', array()) as $matchItem)
					{
						if (is_object($matchItem))
						{
							$allowed['seller_match_type'] = isset($matchItem->type) ? $matchItem->type : 1;
						}
					}
				}
			}

			$allowed['detect_geoip'] = $filter->get('detect_geoip', 0);

			switch ($filter->get('address_type'))
			{
				case 'billing':
					$billing = $cart->getBillTo(true);
					$result  = $this->checkAddress($allowed, $billing, $item);
					break;

				case 'shipping':
					$shipping = $cart->getShipTo(true);
					$result   = $this->checkAddress($allowed, $shipping, $item);
					break;

				case 'both':
					$billing  = $cart->getBillTo(true);
					$shipping = $cart->getShipTo(true);
					$result   = $this->checkAddress($allowed, $shipping, $item) && $this->checkAddress($allowed, $billing, $item);
					break;

				case 'any':
					$billing  = $cart->getBillTo(true);
					$shipping = $cart->getShipTo(true);
					$result   = $this->checkAddress($allowed, $shipping, $item) || $this->checkAddress($allowed, $billing, $item);
					break;
			}
		}

		return $result;
	}

	/**
	 * Verify the filter set for the given rule with shipping as the calculation method
	 *
	 * @param   Registry       $rule      The rule in question
	 * @param   Cart           $cart      The cart object
	 * @param   ShippingQuote  $shipment  The shipment object
	 *
	 * @return  bool
	 *
	 * @since   1.7.1
	 *
	 * @throws  Exception
	 */
	protected function checkShippingFilter(Registry $rule, Cart $cart, $shipment = null)
	{
		$result = true;
		$filter = $rule->extract('params.geolocation');
		$helper = SellaciousHelper::getInstance();

		if ($filter && ($filter->get('country') || $filter->get('state') || $filter->get('district') || $filter->get('zip') || ($filter->get('seller_match') && $helper->config->get('multi_seller', 0))))
		{
			$allowed = array(
				'continent' => array(),
				'country'   => array_filter(explode(',', $filter->get('country'))),
				'state'     => array_filter(explode(',', $filter->get('state'))),
				'district'  => array_filter(explode(',', $filter->get('district'))),
				'zip'       => array_filter(explode(',', $filter->get('zip'))),
			);

			if ($helper->config->get('multi_seller', 0))
			{
				$allowed['seller_match']      = array_filter($filter->get('seller_match', array()), function ($matchItem) {
					return !is_object($matchItem);
				});

				if (!empty($allowed['seller_match']))
				{
					$allowed['seller_match_type'] = 1;

					foreach ($filter->get('seller_match', array()) as $matchItem)
					{
						if (is_object($matchItem))
						{
							$allowed['seller_match_type'] = isset($matchItem->type) ? $matchItem->type : 1;
						}
					}
				}
			}

			$allowed['detect_geoip'] = $filter->get('detect_geoip', 0);

			switch ($filter->get('address_type'))
			{
				case 'billing':
					$billing = $cart->getBillTo(true);
					$result  = $this->checkShipmentAddress($allowed, $billing);
					break;

				case 'shipping':
					$shipping = $cart->getShipTo(true);
					$result   = $this->checkShipmentAddress($allowed, $shipping);
					break;

				case 'both':
					$billing  = $cart->getBillTo(true);
					$shipping = $cart->getShipTo(true);
					$result   = $this->checkShipmentAddress($allowed, $shipping) && $this->checkShipmentAddress($allowed, $billing);
					break;

				case 'any':
					$billing  = $cart->getBillTo(true);
					$shipping = $cart->getShipTo(true);
					$result   = $this->checkShipmentAddress($allowed, $shipping) || $this->checkShipmentAddress($allowed, $billing);
					break;
			}
		}

		return $result;
	}

	/**
	 * Check an address whether it is allowed for the given context
	 *
	 * @param   int[][]   $allowed  The filter values as set in the rule params
	 * @param   Registry  $address  The address object or address id
	 * @param   stdClass  $item     The cart item
	 *
	 * @return  bool
	 *
	 * @throws  Exception
	 *
	 * @since   1.4.0
	 *
	 * @deprecated  Use  SellaciousHelperLocation::isAddressAllowed();
	 * @see         SellaciousHelperLocation::isAddressAllowed();
	 */
	protected function checkAddress($allowed, $address, $item = null)
	{
		$helper = SellaciousHelper::getInstance();

		$detect_geoip = $allowed['detect_geoip'];
		unset($allowed['detect_geoip']);

		// If there is no address selected in cart, then condition won't match
		if (!$address->get('id'))
		{
			if ($detect_geoip && $allow = $this->checkGeoIp($allowed, $item))
			{
				return $allow;
			}

			return false;
		}

		if (is_bool($allow = $this->isAllowed($address->get('country'), $allowed, $item)))
		{
			return $allow;
		}

		if (is_bool($allow = $this->isAllowed($address->get('state_loc'), $allowed, $item)))
		{
			return $allow;
		}

		if (is_bool($allow = $this->isAllowed($address->get('district'), $allowed, $item)))
		{
			return $allow;
		}

		/*
		 * If no preference is set at all it would be allowed already and we won't reach here.
		 * Therefore, if we are here it means either all upper level fields are blank or allowed by selected zip.
		 * Hence any region constraint in upper level due to zip must have already checked against.
		 * Also selected zip would be blank as this would not bring inherit up to here.
		 */
		if ($address->get('zip'))
		{
			$zipCodes = (array) $helper->location->loadColumn(array('list.select' => 'a.title', 'id' => $allowed['zip']));

			return count($zipCodes) == 0 || in_array($address->get('zip'), $zipCodes);
		}

		// Default to exclude
		return false;
	}

	/**
	 * Check an address whether it is allowed for the given context
	 *
	 * @param   int[][]   $allowed  The filter values as set in the rule params
	 * @param   Registry  $address  The address object or address id
	 *
	 * @return  bool
	 *
	 * @throws  \Exception
	 *
	 * @since   1.7.1
	 */
	protected function checkShipmentAddress($allowed, $address)
	{
		$helper = SellaciousHelper::getInstance();

		$detect_geoip = $allowed['detect_geoip'];
		unset($allowed['detect_geoip']);

		if (!$address->get('id'))
		{
			if ($detect_geoip && $allow = $this->checkGeoIp($allowed, null))
			{
				return $allow;
			}

			return false;
		}

		if (is_bool($allow = $this->isAllowed($address->get('country'), $allowed, null)))
		{
			return $allow;
		}

		if (is_bool($allow = $this->isAllowed($address->get('state_loc'), $allowed, null)))
		{
			return $allow;
		}

		if (is_bool($allow = $this->isAllowed($address->get('district'), $allowed, null)))
		{
			return $allow;
		}

		/*
		 * If no preference is set at all it would be allowed already and we won't reach here.
		 * Therefore, if we are here it means either all upper level fields are blank or allowed by selected zip.
		 * Hence any region constraint in upper level due to zip must have already checked against.
		 * Also selected zip would be blank as this would not bring inherit up to here.
		 */
		if ($address->get('zip'))
		{
			$zipCodes = (array) $helper->location->loadColumn(array('list.select' => 'a.title', 'id' => $allowed['zip']));

			return count($zipCodes) == 0 || in_array($address->get('zip'), $zipCodes);
		}

		// Default to exclude
		return false;
	}

	/**
	 * If Cart address not available, match from Geolocated address
	 *
	 * @param   int[][]   $allowed  The allowed list of geolocations
	 * @param   stdClass  $item     The cart item
	 *
	 * @return  bool
	 *
	 * @throws  \Exception
	 *
	 * @since   1.7.1
	 */
	protected function checkGeoIp($allowed, $item = null)
	{
		$helper = SellaciousHelper::getInstance();

		$geoCountry = $helper->location->ipToCountryName();
		$geoState   = $helper->location->ipInfo(null, 'regionName') ?: null;

		if (!empty($allowed['seller_match']) && $helper->config->get('multi_seller', 0))
		{
			$this->matchFromSeller($allowed, $item);

			unset($allowed['seller_match']);
			unset($allowed['seller_match_type']);
		}

		if (!empty($geoCountry))
		{
			$countryId = $helper->location->loadResult(array('title' => $geoCountry, 'type' => 'country'));

			if ($countryId && $allowed['country'])
			{
				return in_array($countryId, $allowed['country']);
			}
		}

		if (!empty($geoState))
		{
			$stateId = $helper->location->loadResult(array('title' => $geoState, 'type' => 'state'));

			if ($stateId && $allowed['state'])
			{
				return in_array($stateId, $allowed['state']);
			}
		}

		return false;
	}

	/**
	 * Check a geolocation is whether it is allowed against the given set of selected geolocations
	 *
	 * @param   stdClass  $geo_id   The geolocation record id to check for
	 * @param   int[][]   $allowed  The allowed list of geolocations
	 * @param   stdClass  $item    The cart item
	 *
	 * @return  bool|null  Value null  = if a child is selected (implies - allowed but cannot be inherited),
	 *                     Value true  = if self or a parent is selected (implies - can be inherited),
	 *                     Value false = if not allowed
	 *
	 * @throws  Exception
	 *
	 * @since   1.4.0
	 *
	 * @deprecated  Use  SellaciousHelperLocation::isAllowed();
	 * @see         SellaciousHelperLocation::isAllowed();
	 */
	private function isAllowed($geo_id, $allowed, $item = null)
	{
		if (!$geo_id)
		{
			return null;
		}

		$helper = SellaciousHelper::getInstance();
		$geo    = $helper->location->loadObject(array('id' => $geo_id));

		if (!$geo)
		{
			return false;
		}

		// No filtering, allow everything and allow inherit
		if (count(array_filter($allowed)) == 0)
		{
			return true;
		}

		if (!empty($allowed['seller_match']) && $helper->config->get('multi_seller', 0))
		{
			$this->matchFromSeller($allowed, $item);

			unset($allowed['seller_match']);
			unset($allowed['seller_match_type']);
		}

		// Self or Parent is selected, can be further inherited as well
		if (in_array($geo->id, $allowed[$geo->type])
			|| in_array($geo->country_id, $allowed['country'])
			|| in_array($geo->state_id, $allowed['state'])
			|| in_array($geo->district_id, $allowed['district'])
			|| in_array($geo->zip_id, $allowed['zip'])
		)
		{
			return true;
		}

		// A child is selected, we allow selection **BUT** this cannot be inherited
		if (in_array($geo->type, array('continent', 'country', 'state', 'district', 'zip')))
		{
			$type_id = $geo->type . '_id';
			$filters = array(
				$type_id => $geo->id,
				'id'     => array_reduce($allowed, 'array_merge', array()),
			);

			if ($helper->location->count($filters) > 0)
			{
				return null;
			}
		}

		return false;
	}

	/**
	 * Method to match from seller address
	 *
	 * @param   int[][]   $allowed  The allowed list of geolocations
	 * @param   stdClass  $item     The cart item
	 *
	 * @throws  \Exception
	 *
	 * @since   1.7.1
	 */
	protected function matchFromSeller(&$allowed, $item)
	{
		$helper    = SellaciousHelper::getInstance();
		$shippedBy = $helper->config->get('shipped_by', 'seller');

		if (isset($item->seller_uid))
		{
			$seller = $helper->seller->getItem(array('user_id' => $item->seller_uid));

			if (!empty($allowed['seller_match']))
			{
				// First empty if any previous allowed location exists
				$allowed['continent'] = array();
				$allowed['country']   = array();
				$allowed['state']     = array();
				$allowed['district']  = array();
				$allowed['zip']       = array();

				// Set the address fields from Seller info
				foreach ($allowed['seller_match'] as $sellerMatch)
				{
					$shipping_location = null;

					if ($shippedBy == 'shop')
					{
						$shipping_location = $helper->config->get('shipping_' . $sellerMatch, 0);
					}
					elseif ($shippedBy == 'seller')
					{
						$shipOrigin        = "ship_origin_" . $sellerMatch;
						$shipping_location = $seller->$shipOrigin;
					}

					if (!empty($shipping_location))
					{
						if ($allowed['seller_match_type'] == 2)
						{
							// All other locations except the selected one
							$allowed[$sellerMatch] = $this->getOtherLocations($sellerMatch, $shipping_location);
						}
						else
						{
							$allowed[$sellerMatch][] = $shipping_location;
						}
					}
				}
			}
		}
		else
		{
			$cart = $helper->cart->getCart();
			$items = $cart->getItems();

			if (!empty($allowed['seller_match']) && !empty($items))
			{
				// First empty if any previous allowed location exists
				$allowed['continent'] = array();
				$allowed['country']   = array();
				$allowed['state']     = array();
				$allowed['district']  = array();
				$allowed['zip']       = array();
			}

			foreach ($items as $cartItem)
			{
				$seller = $helper->seller->getItem(array('user_id' => $cartItem->getProperty('seller_uid')));

				if (!empty($allowed['seller_match']))
				{
					// Set the address fields from Seller info
					foreach ($allowed['seller_match'] as $sellerMatch)
					{
						if ($shippedBy == 'shop')
						{
							$shipping_location = $helper->config->get('shipping_' . $sellerMatch, 0);
						}
						elseif ($shippedBy == 'seller')
						{
							$shipOrigin        = "ship_origin_" . $sellerMatch;
							$shipping_location = $seller->$shipOrigin;
						}

						if (!empty($shipping_location))
						{
							if ($allowed['seller_match_type'] == 2)
							{
								// All other locations except the selected one
								$allowed[$sellerMatch] = $this->getOtherLocations($sellerMatch, $shipping_location);
							}
							else
							{
								$allowed[$sellerMatch][] = $shipping_location;
								$allowed[$sellerMatch] = array_unique($allowed[$sellerMatch], SORT_REGULAR);
							}
						}
					}
				}
			}
		}
	}

	/**
	 * Method to get all other locations except the selected one
	 *
	 * @param   string  $type   The type of location (country, state, etc.)
	 * @param   int     $value  Id of the location
	 *
	 * @return  array
	 *
	 * @throws  \Exception
	 *
	 * @since   1.7.1
	 */
	protected function getOtherLocations($type, $value)
	{
		$helper  = SellaciousHelper::getInstance();
		$db      = JFactory::getDbo();
		$matches = array();

		switch ($type)
		{
			case 'country':
				$matches = $helper->location->loadColumn(array('list.select' => 'a.id', 'list.where' => array('a.id != ' . $value ,'a.type = ' . $db->q($type))));
				break;
			case 'state':
				$country = $helper->location->loadResult(array('list.select' => 'a.country_id', 'list.where' => array('a.id = ' . $value)));
				$matches = $helper->location->loadColumn(array('list.select' => 'a.id', 'list.where' => array('a.id != ' . $value ,'a.country_id = ' . $country,'a.type = ' . $db->q($type))));
				break;
			case 'district':
				$state = $helper->location->loadResult(array('list.select' => 'a.state_id', 'list.where' => array('a.id = ' . $value)));
				$matches = $helper->location->loadColumn(array('list.select' => 'a.id', 'list.where' => array('a.id != ' . $value ,'a.state_id = ' . $state,'a.type = ' . $db->q($type))));
				break;
			case 'zip':
				$country = $helper->location->loadResult(array('list.select' => 'a.country_id', 'list.where' => array('a.id = ' . $value)));
				$matches = $helper->location->loadColumn(array('list.select' => 'a.id', 'list.where' => array('a.id != ' . $value ,'a.country_id = ' . $country,'a.type = ' . $db->q($type))));
				break;
		}

		return $matches;
	}
}
