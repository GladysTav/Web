<?php
/**
 * @version     2.0.0
 * @package     sellacious
 *
 * @copyright   Copyright (C) 2012-2020 Bhartiy Web Technologies. All rights reserved.
 * @license     SPL Sellacious Private License; see http://www.sellacious.com/spl.html
 * @author      Izhar Aazmi <info@bhartiy.com> - http://www.bhartiy.com
 */
// no direct access
use Joomla\CMS\Form\Form;
use Joomla\Registry\Registry;
use Joomla\Utilities\ArrayHelper;
use Sellacious\Cart;
use Sellacious\Cart\Item;
use Sellacious\Shipping\ShippingHandler;
use Sellacious\Shipping\ShippingQuote;

defined('_JEXEC') or die;

/**
 * Sellacious product option helper
 *
 * @since  3.0
 */
class SellaciousHelperShippingRule extends SellaciousHelperBase
{
	/**
	 * Get all active handlers from sellacious shipment plugins
	 *
	 * @return  ShippingHandler[]
	 *
	 * @throws  Exception
	 *
	 * @since   1.4.4
	 *
	 * @deprecated   Use shipping helper
	 */
	public function getHandlers()
	{
		return $this->helper->shipping->getHandlers();
	}

	/**
	 * Check the shipping method for the item
	 *
	 * @param   stdClass   $rule   The shipping rule to test
	 * @param   Cart\Item  $cItem  The product item for the shipping rate quote
	 *
	 * @return  bool
	 *
	 * @since   1.4.4
	 */
	public function checkRule($rule, $cItem)
	{
		// If shipped by shop, the seller specific rules are ineffective
		if ($rule->owned_by && $this->helper->config->get('shipped_by') == 'shop')
		{
			return false;
		}

		try
		{
			$item = (object) $cItem->getAttributes();

			$item->quantity = $cItem->getQuantity();

			$registry   = new Registry($rule);
			$dispatcher = $this->helper->core->loadPlugins('sellaciousrules');
			$pluginArgs = array('com_sellacious.shippingrule.product', &$registry, $item, $useCart = true);
			$responses  = $dispatcher->trigger('onValidateProductShippingrule', $pluginArgs);
		}
		catch (Exception $e)
		{
			// Todo: decide how to handle this here
			$responses = array();
		}

		// Rule is valid if there are no filters OR none of the filters disallow…
		$valid = count($responses) == 0 || !in_array(false, $responses, true);

		return $valid;
	}

	/**
	 * Check the shipping method for the  cart
	 *
	 * @param   stdClass  $rule  The shipping rule to test
	 * @param   Cart      $cart  The Cart object
	 *
	 * @return  bool
	 *
	 * @since   1.5.2
	 */
	public function checkCartRule($rule, $cart)
	{
		// See if the seller specific rules are ineffective
		if ($rule->owned_by)
		{
			// If shipped by shop
			if ($this->helper->config->get('shipped_by') == 'shop')
			{
				return false;
			}

			$itemisedShipping = $this->helper->config->get('itemised_shipping', 1);

			// If shipped by seller and its cart level rule and cart has items of other sellers than the rule owner
			if (!$itemisedShipping && count(array_diff($cart->getSellerIds(), array($rule->owned_by))))
			{
				return false;
			}
		}

		try
		{
			$registry   = new Registry($rule);
			$dispatcher = $this->helper->core->loadPlugins('sellaciousrules');
			$pluginArgs = array('com_sellacious.shippingrule.cart', &$registry, $cart);
			$responses  = $dispatcher->trigger('onValidateCartShippingrule', $pluginArgs);
		}
		catch (Exception $e)
		{
			// Todo: decide how to handle this here
			$responses = array();
		}

		// Rule is valid if there are no filters OR none of the filters disallow...
		$valid = count($responses) == 0 || !in_array(false, $responses, true);

		return $valid;
	}

	/**
	 * Apply the selected shipping rule to the items with given shipment origin and destination.
	 * All items will be considered as a single shipment package and quotes will be estimated for the entire set.
	 * To get the quotes for each individual item pass each of them in separate calls to this method.
	 *
	 * @param   stdClass         $rule       The shipping rule to be applied
	 * @param   Item[]           $items      The product items to be applied to rule to
	 * @param   Registry         $origin     The shipment origin location that will be used if the API needs it to evaluate rates.
	 * @param   Registry         $ship       The shipment destination location that will be used if the API needs it to evaluate rates.
	 * @param   ShippingQuote[]  $quotes     The return value will be pushed into this array by reference
	 * @param   int              $sellerUid  Seller user id (Provided when quote is for a seller)
	 *
	 * @return  mixed
	 *
	 * @throws  Exception
	 *
	 * @since   1.5.2
	 */
	public function getQuotes($rule, $items, $origin, $ship, &$quotes, $sellerUid = null)
	{
		$dispatcher = $this->helper->core->loadPlugins();
		$rQuotes    = array();

		// The basic rule is an internal type
		if ($rule->method_name == '*')
		{
			$dispatcher = $this->helper->core->loadPlugins();
			$dispatcher->trigger('onMatchRuleSlab', array('com_sellacious.rule', &$rule, $rule));

			$quantity = 0;

			foreach ($items as $item)
			{
				$quantity += $item->getQuantity();
			}

			$quote = (object) array('amount' => $rule->amount, 'amount2' => $rule->amount_additional);

			if (isset($quote->amount2) && $quantity > 1)
			{
				$quote->total = $rule->amount + $rule->amount_additional * ($quantity - 1);
			}
			else
			{
				$quote->total = $rule->amount * $quantity;
			}

			$rQuotes[] = $quote;
		}
		else
		{
			$handlers = $this->helper->shipping->getHandlers();
			$handler  = ArrayHelper::getValue($handlers, $rule->method_name);

			if ($handler instanceof ShippingHandler)
			{
				if ($handler->name == 'slabs.quantity')
				{
					$value = 0;

					foreach ($items as $item)
					{
						$value += $item->getQuantity();
					}

					$slab = $this->matchSlab($rule, $value, $origin);

					if ($slab)
					{
						$rQuotes[] = (object) array(
							'amount' => $slab->price,
							'total'  => $slab->price * (empty($slab->u) ? 1 : $value),
						);
					}
				}
				elseif ($handler->name == 'slabs.weight')
				{
					$value  = 0;
					$params = new Registry($rule->params);
					$wtUnit = $params->get('weight_unit');

					foreach ($items as $item)
					{
						$productId  = $item->getProperty('product_id');
						$variantId  = $item->getProperty('variant_id');
						$iSellerUid = $item->getProperty('seller_uid');

						// Calculate weight and convert to the configured unit for rule
						$dim  = $this->helper->product->getShippingDimensions($productId, $variantId, $iSellerUid);
						$prop = new Registry($dim);
						$wt   = (float) $prop->get('weight.value');
						$ws   = (string) $prop->get('weight.symbol');

						if ($wt && $ws)
						{
							$value += $this->helper->unit->convert($wt * $item->getQuantity(), $ws, $wtUnit);
						}
					}

					$slab = $this->matchSlab($rule, $value, $origin);

					if ($slab)
					{
						$rQuotes[] = (object) array(
							'amount' => $slab->price,
							'total'  => $slab->price,
						);
					}
				}
				elseif ($handler->name == 'slabs.price')
				{
					$value = 0;

					foreach ($items as $item)
					{
						$value += $item->getRawPrice('sales_price', true) * $item->getQuantity();
					}

					$slab = $this->matchSlab($rule, $value, $origin);

					if ($slab)
					{
						$rQuotes[] = (object) array(
							'amount' => $slab->price,
							'total'  => $slab->price,
						);
					}
				}
				elseif ($handler->rateQuoteSupported)
				{
					try
					{
						$objects = array();

						foreach ($items as $item)
						{
							$o           = (object) $item->getAttributes();
							$o->quantity = $item->getQuantity();

							$objects[] = $o;
						}

						$dispatcher->trigger('onRequestFreightQuote', array('com_sellacious.shipment', $rule, $objects, $origin, $ship, &$rQuotes));
					}
					catch (Exception $e)
					{
						// Feedback the received exception somehow.
					}
				}
			}
		}

		foreach ($rQuotes as $quote)
		{
			$quotation = $this->createQuote($rule, $quote, $sellerUid);

			$quotes[$quotation->id] = $quotation;
		}

		return $quotes;
	}

	/**
	 * Create a quote object from given standard object returned by the plugin
	 *
	 * @param   stdClass                $rule       The shipping rule object
	 * @param   ShippingQuote|stdClass  $quote      The object received as plugin response
	 * @param   int                     $sellerUid  Seller user id
	 *
	 * @return  ShippingQuote
	 *
	 * @since   1.5.2
	 */
	protected function createQuote($rule, $quote, $sellerUid = null)
	{
		if ($quote instanceof ShippingQuote)
		{
			return $quote;
		}

		$identity = 'quote___' . $rule->id . (isset($quote->service) ? '___' . $quote->service : '');
		$identity = strtolower($identity);
		$qObj     = new ShippingQuote($identity);

		$filters = array('list.select' => 'a.title', 'list.where' => array('a.user_id = ' . (int) $sellerUid));

		$creditTo       = $this->helper->config->get('shipping_credit_to', 'seller');
		$creditToGlobal = $this->helper->config->get('shipping_use_global_credit_to');
		$creditAmountTo = $rule->credit_to && !$creditToGlobal ? $rule->credit_to : $creditTo;

		$qObj->id             = $identity;
		$qObj->ruleId         = $rule->id;
		$qObj->ruleTitle      = $rule->title;
		$qObj->ruleHandler    = $rule->method_name;
		$qObj->ruleOwner      = $rule->owned_by;
		$qObj->sellerUid      = $sellerUid;
		$qObj->sellerName     = $sellerUid ? $this->helper->seller->loadResult($filters) : null;
		$qObj->label          = isset($quote->label) ? $quote->label : null;
		$qObj->service        = isset($quote->service) ? $quote->service : null;
		$qObj->serviceName    = isset($quote->serviceName) ? $quote->serviceName : null;
		$qObj->tbd            = isset($quote->tbd) ? $quote->tbd : false;
		$qObj->free           = isset($quote->free) ? $quote->free : round($quote->amount, 2) < 0.01;
		$qObj->amount         = round($quote->amount, 2);
		$qObj->amount2        = isset($quote->amount2) ? round($quote->amount2, 2) : 0.00;
		$qObj->total          = isset($quote->total) ? round($quote->total, 2) : round($quote->amount, 2);
		$qObj->deliveryDate   = isset($quote->deliveryDate) ? $quote->deliveryDate : null;
		$qObj->transitTime    = isset($quote->transitTime) ? $quote->transitTime : null;
		$qObj->note           = isset($quote->note) ? $quote->note : null;
		$qObj->creditAmountTo = $creditAmountTo;

		return $qObj;
	}

	/**
	 * Get a shipping method form from plugins and/or custom defined fields in rule
	 *
	 * @param   int     $ruleId       Method id for which to load the form
	 * @param   string  $formControl  Form control name
	 * @param   string  $service      Any specific service to load the form for (unused)
	 *
	 * @return  Form
	 *
	 * @since   1.4.4
	 */
	public function getForm($ruleId, $formControl, $service = null)
	{
		$form = null;
		$rule = $this->getItem($ruleId);

		if ($rule->id)
		{
			// Create skeleton form
			$formName = 'com_sellacious.cart.shippingform.' . md5(serialize(func_get_args()));
			$form     = JForm::getInstance($formName, '<form> </form>', array('control' => $formControl));

			// Append the custom defined fields in the rule to the form
			$params = new Registry($rule->params);
			$fields = (array) $params->get('form_fields');

			$globalFields = $this->helper->field->getGlobalFields('shippingmethod');

			if ($globalFields)
			{
				foreach ($globalFields as $globalField)
				{
					array_unshift($fields, $globalField);
				}
			}

			$fields  = $this->helper->field->getListWithGroup($fields);
			$formXml = $this->helper->field->createFormXml($fields, 'shipment', 'shipmentform');

			$form->load($formXml->asXML());

			// Load form (if any) from the shipment method plugin
			$dispatcher = $this->helper->core->loadPlugins();
			$dispatcher->trigger('onLoadShippingForm', array('com_sellacious.cart.shippingform', $form, $rule, $service));
		}

		return $form;
	}

	/**
	 * Extract shipping slabs from a given CSV
	 *
	 * @param   string    $file           The source csv file
	 * @param   array     $columns        Array of column names to search in the csv
	 * @param   bool|int  $roundToDigits  Round values to a number of digits
	 *
	 * @return  array
	 *
	 * @throws  Exception
	 *
	 * @since   1.5.3
	 */
	public function csvToSlabs($file, $columns = array(), $roundToDigits = false)
	{
		$rows     = array();
		$rateCol  = ArrayHelper::getValue($columns, 'rate', 'shipping');
		$locField = $this->helper->config->get('csv_import_location_field', 'iso_code');

		if ($fp = fopen($file, 'r'))
		{
			$cols = fgetcsv($fp);
			$cols = array_map('strtolower', $cols);

			$hasMin           = in_array('min', $cols);
			$hasMax           = in_array('max', $cols);
			$hasOriginCountry = in_array('origin_country', $cols);
			$hasOriginState   = in_array('origin_state', $cols);
			$hasOriginZip     = in_array('origin_zip', $cols);
			$hasCountry       = in_array('delivery_country', $cols);
			$hasState         = in_array('delivery_state', $cols);
			$hasZip           = in_array('delivery_zip', $cols);
			$hasRate          = in_array($rateCol, $cols);
			$hasU             = in_array('per_unit', $cols);
			$hasUnit          = in_array('unit', $cols);

			if (!$hasMin && !$hasMax)
			{
				throw new Exception(JText::_('COM_SELLACIOUS_SLABS_CSV_MISSING_MIN_MAX'));
			}

			if (!$hasRate)
			{
				throw new Exception(JText::sprintf('COM_SELLACIOUS_SLABS_CSV_MISSING_RATE', ucfirst($rateCol)));
			}

			$index = 0;

			while ($row = fgetcsv($fp))
			{
				$prev = isset($rows[$index - 1]);

				if (count($cols) == count($row))
				{
					$row     = array_combine($cols, $row);
					$current = array('min' => null, 'max' => null);

					if ($hasMin)
					{
						$current['min'] = $roundToDigits !== false ? round($row['min'], $roundToDigits) : $row['min'];
					}

					if ($hasMax)
					{
						$current['max'] = $roundToDigits !== false ? round($row['max'], $roundToDigits) : $row['max'];
					}

					if ($hasOriginCountry && $row['origin_country'] != '*' && $row['origin_country'] != '')
					{
						$current['origin_country'] = $this->helper->location->getIdByValue($row['origin_country'], 'country', $locField);
					}

					if ($hasCountry && $row['delivery_country'] != '*' && $row['delivery_country'] != '')
					{
						$current['country'] = $this->helper->location->getIdByValue($row['delivery_country'], 'country', $locField);
					}

					if ($hasOriginState && $row['origin_state'] != '*' && $row['origin_state'] != '')
					{
						$pid = empty($current['origin_country']) ? null : $current['origin_country'];

						$current['origin_state'] = $this->helper->location->getIdByValue($row['origin_state'], 'state', $locField, $pid);
					}

					if ($hasState && $row['delivery_state'] != '*' && $row['delivery_state'] != '')
					{
						$pid = empty($current['country']) ? null : $current['country'];

						$current['state'] = $this->helper->location->getIdByValue($row['delivery_state'], 'state', $locField, $pid);
					}

					// ZIP must be backed by a parent entity
					if ($hasOriginZip && ($hasOriginCountry || $hasOriginState) && $row['origin_zip'] != '*' && $row['origin_zip'] != '')
					{
						$pid = empty($current['origin_state']) ? null : $current['origin_state'];

						$current['origin_zip'] = $this->helper->location->getIdByValue($row['origin_zip'], 'zip', $locField, $pid);
					}

					if ($hasZip && ($hasCountry || $hasState) && $row['delivery_zip'] != '*' && $row['delivery_zip'] != '')
					{
						$pid = empty($current['state']) ? null : $current['state'];

						$current['zip'] = $this->helper->location->getIdByValue($row['delivery_zip'], 'zip', $locField, $pid);
					}

					$current['price'] = $row[$rateCol];

					if ($hasU)
					{
						$current['u'] = $row['per_unit'];
					}

					if ($hasUnit)
					{
						$current['unit'] = $row['unit'];
					}

					$rows[$index] = $current;
				}

				$index++;
			}
		}

		return $rows;
	}

	/**
	 * Remove any existing shipping slabs from the database for the given shipping rule
	 *
	 * @param   int  $ruleId  The rule id
	 *
	 * @return  bool
	 *
	 * @throws  Exception
	 *
	 * @since   1.5.3
	 */
	public function clearSlabs($ruleId)
	{
		$query = $this->db->getQuery(true);
		$query->delete('#__sellacious_shippingrule_slabs')->where('rule_id = ' . (int) $ruleId);

		$this->db->setQuery($query)->execute();

		return true;
	}

	/**
	 * Add a new shipping slabs to the given shipping rule
	 *
	 * @param   int       $ruleId  The rule id
	 * @param   stdClass  $slab    The slab object
	 *
	 * @return  bool
	 *
	 * @throws  Exception
	 *
	 * @since   1.5.3
	 */
	public function addSlab($ruleId, $slab)
	{
		$slab->rule_id = $ruleId;

		return $this->db->insertObject('#__sellacious_shippingrule_slabs', $slab);
	}

	/**
	 * Get a list of all shipping slabs for the given shipping rule
	 *
	 * @param   int  $ruleId  The rule id
	 *
	 * @return  stdClass[]
	 *
	 * @throws  Exception
	 *
	 * @since   1.5.3
	 */
	public function getSlabs($ruleId)
	{
		$query = $this->db->getQuery(true);
		$query->select('*')->from('#__sellacious_shippingrule_slabs')->where('rule_id = ' . (int) $ruleId);

		return (array) $this->db->setQuery($query)->loadObjectList();
	}


	/**
	 * Method to get shipping rules for product
	 *
	 * @param   int  $productId  The product id
	 * @param   int  $sellerUid  The seller user id
	 *
	 * @return  array
	 *
	 * @throws  \Exception
	 *
	 * @since   1.7.0
	 */
	public function getShippingRules($productId, $sellerUid)
	{
		$shippingRules = array();

		if (empty($productId))
		{
			return $shippingRules;
		}

		$rules = $this->helper->shippingRule->loadObjectList(array('list.where' => array('a.state = 1', 'a.apply_on_all_products = 0')));

		foreach ($rules as $rule)
		{
			$params  = new Registry($rule->params);
			$product = $params->get('product');

			if (isset($product->products))
			{
				// For backward compatibility, when selected products for shipping rule were saved in params
				$filterProducts = array_filter(explode(',', $product->products));
			}
			else
			{
				// Fetching selected products for the shipping rule
				$filterProducts = $this->helper->product->getRuleProducts((int) $rule->id, 'shippingrule', 0, 'product_id');
			}

			foreach ($filterProducts as $filterProduct)
			{
				if (is_numeric($productId) && $productId == $filterProduct)
				{
					$shippingRules[] = $rule->id;
					break;
				}
				elseif ($parsed = $this->helper->product->parseCode($filterProduct, $p_id, $v_id, $s_uid) && $p_id == $productId && $s_uid == $sellerUid)
				{
					$shippingRules[] = $rule->id;
					break;
				}
			}
		}

		return $shippingRules;
	}

	/**
	 * Method to save shipping rules for product
	 *
	 * @param   array  $rules      Array of rule ids
	 * @param   int    $productId  The product id
	 * @param   int    $sellerUid  The seller user id
	 *
	 * @throws  \Exception
	 *
	 * @since   1.7.0
	 */
	public function saveShippingRules($rules, $productId, $sellerUid)
	{
		$rules         = (array) $rules;
		$shippingRules = $this->getShippingRules($productId, $sellerUid);
		$removedRules  = array_values(array_diff($shippingRules, $rules));
		$code          = $this->helper->product->getCode($productId, 0, $sellerUid);

		foreach ($rules as $rule)
		{
			$table = JTable::getInstance('ShippingRule', 'SellaciousTable');
			$table->load($rule);

			$products         = array($code);
			$existingProducts = $this->helper->product->getRuleProducts($rule, 'shippingrule', 0, 'product_id');
			$products         = array_unique(array_merge($products, $existingProducts));

			$params   = new Registry($table->get('params'));
			$product  = $params->get('product');

			// For backward compatibility, when selected products of shipping rule were saved in params
			if (isset($product->products))
			{
				// Remove selected products from shipping rule params, as we're saving in separate table from now on
				unset($product->products);

				$products = array_unique(array_merge($products, array_filter(explode(',', $product->products))));

				$table->save(array('params' => $params->toString()));
			}

			$this->helper->product->setRuleProducts($rule, 'shippingrule', $products, 0);
		}

		foreach ($removedRules as $rule)
		{
			$products = array($code, $productId);

			$this->helper->product->delRuleProducts($rule, $products, 0, 'shippingrule');
		}
	}

	/**
	 * Method to find a suitable slab for the given cart item(s) shipment quote
	 *
	 * @param   \stdClass  $rule
	 * @param   int        $value
	 * @param   Registry   $origin
	 *
	 * @return  \stdClass
	 *
	 * @throws  \Exception
	 *
	 * @since   2.0.0
	 */
	public function matchSlab($rule, $value, $origin = null)
	{
		$ruleId  = $rule->id;
		$cart    = $this->helper->cart->getCart();
		$address = $cart->getShipTo(true);

		$query = $this->db->getQuery(true);
		$query->select('a.*')->from($this->db->qn('#__sellacious_shippingrule_slabs', 'a'))->where('rule_id = ' . (int) $ruleId);

		// If country filter set, it must match
		$country = $address->get('country');
		$country = !empty($country) ? $country : 0;
		$query->where('CASE WHEN a.country > 0 THEN a.country = ' . $this->db->q($country) . ' ELSE TRUE END');

		// If state filter set, it must match
		$state = $address->get('state_loc');
		$state = !empty($state) ? $state : 0;
		$query->where('CASE WHEN a.state > 0 THEN (a.country = 0 OR a.country = ' . $this->db->q($country) . ') AND a.state = ' . $this->db->q($state) . ' ELSE TRUE END');

		// If zip filter set, it must match
		$zip = $address->get('zip');
		$zip = $this->helper->location->getIdByName($zip, 'zip', $state);
		$zip = !empty($zip) ? $zip : 0;
		$query->where('CASE WHEN a.zip > 0 THEN (a.country = 0 OR a.country = ' . $this->db->q($country) . ') AND (a.state = 0 OR a.state = ' . $this->db->q($state) . ') AND a.zip = ' . $this->db->q($zip) . ' ELSE TRUE END');

		if (array_filter($origin->toArray()))
		{
			// If origin country is set, it must match
			$o_country = $origin->get('country');
			$o_country = !empty($o_country) ? $o_country : 0;
			$query->where('CASE WHEN a.origin_country > 0 THEN a.origin_country = ' . $this->db->q($o_country) . ' ELSE TRUE END');

			// If origin state is set, it must match
			$o_state = $origin->get('state');
			$o_state = !empty($o_state) ? $o_state : 0;
			$query->where('CASE WHEN a.origin_state > 0 THEN (a.origin_country = 0 OR a.origin_country = ' . $this->db->q($o_country) . ') AND a.origin_state = ' . $this->db->q($o_state) . ' ELSE TRUE END');

			// If origin zip is set, it must match
			$o_zip = $origin->get('zip');
			$o_zip = $this->helper->location->getIdByName($o_zip, 'zip', $o_state);
			$o_zip = !empty($o_zip) ? $o_zip : 0;
			$query->where('CASE WHEN a.origin_zip > 0 THEN (a.origin_country = 0 OR a.origin_country = ' . $this->db->q($o_country) . ') AND (a.origin_state = 0 OR a.origin_state = ' . $this->db->q($o_state) . ') AND a.origin_zip = ' . $this->db->q($o_zip) . ' ELSE TRUE END');
		}

		// Min is min limit
		$query->where('CASE WHEN a.min > 0 THEN ' . $value . ' >= a.min ELSE TRUE END');

		// Max is max limit
		$query->where('CASE WHEN a.max > 0 THEN ' . $value . ' <= a.max ELSE TRUE END');

		$query->order('a.min DESC, a.max DESC, a.price ASC');

		$this->db->setQuery($query, 0, 1);

		$match = $this->db->loadObject();

		$dispatcher = $this->helper->core->loadPlugins();
		$dispatcher->trigger('onMatchRuleSlab', array('com_sellacious.rule', &$match, $rule));

		return $match;
	}
}
