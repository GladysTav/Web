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
use Joomla\Utilities\ArrayHelper;
use Sellacious\Cart;
use Sellacious\Cart\Item;

class plgSellaciousRulesProduct extends JPlugin
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
	 * @var   SellaciousHelper
	 *
	 * @since  1.2.0
	 */
	protected $helper;

	/**
	 * @var   \JApplicationCms
	 */
	protected $app;

	/**
	 * @var   \JDatabaseDriver
	 */
	protected $db;

	/**
	 * plgSellaciousRulesProduct constructor.
	 *
	 * @param   object  $subject
	 * @param   array   $config
	 *
	 * @throws  Exception
	 *
	 * @since   1.2.0
	 */
	public function __construct($subject, array $config)
	{
		parent::__construct($subject, $config);

		jimport('sellacious.loader');

		if (class_exists('SellaciousHelper'))
		{
			$this->helper = SellaciousHelper::getInstance();
		}
	}

	/**
	 * Adds additional fields to the sellacious rules editing form
	 *
	 * @param   JForm  $form  The form to be altered.
	 * @param   mixed  $data  The associated data for the form.
	 *
	 * @return  boolean
	 *
	 * @throws  Exception
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

		$name  = $form->getName();
		$array = is_object($data) ? ArrayHelper::fromObject($data) : (array) $data;

		// Check we are manipulating a valid form.
		if (($name == 'com_sellacious.shoprule' && ArrayHelper::getValue($array, 'sum_method') == 2) ||
			($name == 'com_sellacious.shippingrule' && $this->helper->config->get('itemised_shipping')) ||
			($name == 'com_sellacious.coupon'))
		{
			JHtml::_('script', 'plg_sellaciousrules_product/product.js', array('version' => S_VERSION_CORE, 'relative' => true));

			$form->loadFile(__DIR__ . '/forms/product.xml', false);

			// Must already exist otherwise this plugin wont be called anyway.
			$helper = SellaciousHelper::getInstance();

			if (!$helper->config->get('multi_seller', 0))
			{
				$form->removeField('seller', 'params.product');
			}

			$allowed = $helper->config->get('allowed_product_type', 'both');

			if ($allowed != 'both')
			{
				// Load only allowed type categories
				$form->setFieldAttribute('categories', 'group', 'product/' . $allowed, 'params.product');
			}

			$seller_uid = ArrayHelper::getValue($array, ($name == 'com_sellacious.shippingrule' ? 'owned_by' : 'seller_uid'));

			if ($name == 'com_sellacious.coupon' && $helper->config->get('multi_seller'))
			{
				$canEdit = $this->helper->access->check('coupon.edit');

				if (!$canEdit || $seller_uid)
				{
					$form->removeField('seller', 'params.product');
				}
			}

			if ($seller_uid)
			{
				$form->setFieldAttribute('products', 'seller_uid', $seller_uid, 'product');
			}

			if ($name == 'com_sellacious.shoprule')
			{
				// Remove volume/weight fields from shoprule form.
				$form->removeField('min_weight', 'params.product');
				$form->removeField('max_weight', 'params.product');
				$form->removeField('min_volume', 'params.product');
				$form->removeField('max_volume', 'params.product');

				// Restore lost data due to page load for change and revert of sum_method
				$rule_id  = ArrayHelper::getValue($array, 'id');
				$method   = ArrayHelper::getValue($array, 'sum_method');
				$x_params = ArrayHelper::getValue($array, 'params', array(), 'array');
				$type     = ArrayHelper::getValue($array, 'type');
				$classes  = ArrayHelper::getValue($array, $type . '_class_groups');

				$post = $this->app->input->get('jform', array(), 'Array');

				if (!empty($post[$type . '_class_groups']))
				{
					$classes = array_unique(array_merge($classes, explode(',', $post[$type . '_class_groups'])));
				}

				if (!empty($classes))
				{
					foreach ($classes as $class)
					{
						$this->addRuleClassField($form, is_object($class) ? $class->title : $class, 'product', $seller_uid);
					}
				}

				if ($method == 2 && $rule_id > 0 && empty($x_params['product']))
				{
					$params = $this->helper->shopRule->loadResult(array('id' => $rule_id, 'list.select'=> 'a.params'));
					$params = new Registry($params);

					$form->bind(array('params' => array('product' => $params->get('product'))));
				}

				$apply_on_all_products = ArrayHelper::getValue($array, 'apply_on_all_products', 0);

				if ($apply_on_all_products)
				{
					$form->removeField('products', 'product');
					$form->removeField('selections_group', 'params.product');
				}
			}

			if ($name == 'com_sellacious.coupon')
			{
				// Remove volume/weight fields from coupon form.
				$form->removeField('min_weight', 'params.product');
				$form->removeField('max_weight', 'params.product');
				$form->removeField('min_volume', 'params.product');
				$form->removeField('max_volume', 'params.product');

				$apply_on_all_products = ArrayHelper::getValue($array, 'apply_on_all_products', 0);

				if ($apply_on_all_products)
				{
					$form->removeField('products', 'product');
					$form->removeField('selections_group', 'params.product');
				}
			}

			if ($name == 'com_sellacious.shippingrule')
			{
				if (!$this->helper->access->check('shippingrule.edit') && $this->helper->access->check('shippingrule.edit.own'))
				{
					$form->removeField('seller', 'params.product');
				}

				$apply_on_all_products = ArrayHelper::getValue($array, 'apply_on_all_products', 0);

				if ($apply_on_all_products)
				{
					$form->removeField('products', 'product');
					$form->removeField('selections_group', 'params.product');
				}
			}
		}
		elseif ($name == 'com_sellacious.product' && !empty($array['id']))
		{
			$discountRules = array_filter($this->helper->shopRuleClass->getAllShopRulesClasses('discount'));
			$taxRules      = array_filter($this->helper->shopRuleClass->getAllShopRulesClasses('tax'));

			if (!empty($discountRules) || !empty($taxRules))
			{
				// Inject plugin configuration into config form
				$form->loadFile(__DIR__ . '/forms/product_shoprule.xml', false);
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
	 * Called right after save
	 *
	 * @param   string   $context  The context
	 * @param   JTable   $item     The table
	 * @param   boolean  $isNew    Is new item
	 * @param   array    $data     The validated data
	 *
	 * @return  void
	 *
	 * @since   1.7.0
	 *
	 * @throws  \Exception
	 */
	public function onContentAfterSave($context, $item, $isNew, $data = array())
	{
		if ($context == 'com_sellacious.product')
		{
			if ($item->get('id'))
			{
				$app  = $this->app;
				$post = $app->input->get('jform', array(), 'Array');

				$productId = $item->get('id', 0);
				$registry  = new Registry($data);
				$me        = JFactory::getUser();

				if ($productId && $seller_uid = $registry->get('seller_uid'))
				{
					$sellerEdit = $this->helper->access->checkAny(array('shipping', 'seller'), 'product.edit.')
						|| ($this->helper->access->checkAny(array('shipping.own', 'seller.own'), 'product.edit.') && $seller_uid == $me->id);

					if ($sellerEdit)
					{
						$shipppingRules = $registry->get('seller.rules.shipping_rules');
						$this->helper->shippingRule->saveShippingRules($shipppingRules, $productId, $seller_uid);
					}
				}

				$productId = $item->get('id');
				$sellerUid = $app->getUserState('com_sellacious.edit.product.seller_uid');
				$code      = $this->helper->product->getCode($productId, 0, $sellerUid);

				// Discount rules and classes
				$discountRules    = isset($post['shoprule']['discount_rules']) ? explode(',', $post['shoprule']['discount_rules']) : array();
				$discRuleIds      = array();
				$discClassRuleIds = array();
				$classes          = $this->helper->shopRuleClass->getProductShopRuleClasses($productId, $sellerUid, 'discount', 1);
				$classIds         = ArrayHelper::getColumn(array_filter($classes, function ($item) {
					return !($item->id > 0);
				}), 'idx');
				$ruleIds          = ArrayHelper::getColumn(array_filter($classes, function ($item) {
					return $item->id > 0;
				}), 'idx');

				if (!empty($discountRules))
				{
					foreach ($discountRules as $rule)
					{
						if (is_numeric($rule))
						{
							$discRuleIds[] = $rule;
							$discRuleIds   = array_unique($discRuleIds);
						}
						else
						{
							$discountClass      = $this->helper->shopRuleClass->getAliasFromClass($rule);
							$discClassId        = $this->helper->shopRuleClass->getClassFromAlias($discountClass, 'id');
							$discClassRuleIds[] = $discClassId;
							$discClassRuleIds   = array_unique($discClassRuleIds);

						}
					}
				}

				$removeRuleIds = array_diff($ruleIds, $discRuleIds);

				if (!empty($removeRuleIds))
				{
					foreach ($removeRuleIds as $removeRuleId)
					{
						$this->helper->product->delRuleProducts($removeRuleId, array($productId), 0);
					}
				}

				$removeDiscClassRuleIds = array_diff($classIds, $discClassRuleIds);

				if (!empty($removeDiscClassRuleIds))
				{
					foreach ($removeDiscClassRuleIds as $removeDiscClassRuleId)
					{
						$this->helper->product->delRuleProducts(0, array($code), $removeDiscClassRuleId);
					}
				}

				$discRuleIds = array_filter($discRuleIds);

				if (!empty($discRuleIds))
				{
					foreach ($discRuleIds as $discRuleId)
					{
						$this->helper->product->setRuleProducts($discRuleId, 'shoprule', array($productId), 0, 1);
					}
				}

				$discClassRuleIds = array_filter($discClassRuleIds);

				if (!empty($discClassRuleIds))
				{
					foreach ($discClassRuleIds as $discClassRuleId)
					{
						$this->helper->product->setRuleProducts(0, 'shoprule', array($code), $discClassRuleId, 1);
					}
				}

				// Tax rule and class
				$taxRules        = isset($post['shoprule']['tax_rules']) ? explode(',', $post['shoprule']['tax_rules']) : array();
				$taxRuleIds      = array();
				$taxClassRuleIds = array();

				$classes  = $this->helper->shopRuleClass->getProductShopRuleClasses($productId, $sellerUid, 'tax', 1);
				$classIds = ArrayHelper::getColumn(array_filter($classes, function ($item) {
					return !($item->id > 0);
				}), 'idx');
				$ruleIds  = ArrayHelper::getColumn(array_filter($classes, function ($item) {
					return $item->id > 0;
				}), 'idx');

				if (!empty($taxRules))
				{
					foreach ($taxRules as $rule)
					{
						if (is_numeric($rule))
						{
							$taxRuleIds[] = $rule;
							$taxRuleIds   = array_unique($taxRuleIds);
						}
						else
						{
							$taxClass          = $this->helper->shopRuleClass->getAliasFromClass($rule);
							$taxClassId        = $this->helper->shopRuleClass->getClassFromAlias($taxClass, 'id');
							$taxClassRuleIds[] = $taxClassId;
							$taxClassRuleIds   = array_unique($taxClassRuleIds);

						}
					}
				}

				$removeRuleIds = array_diff($ruleIds, $taxRuleIds);

				if (!empty($removeRuleIds))
				{
					foreach ($removeRuleIds as $removeRuleId)
					{
						$this->helper->product->delRuleProducts($removeRuleId, array($productId), 0);
					}
				}

				$removetaxClassRuleIds = array_diff($classIds, $taxClassRuleIds);

				if (!empty($removetaxClassRuleIds))
				{
					foreach ($removetaxClassRuleIds as $removetaxClassRuleId)
					{
						$this->helper->product->delRuleProducts(0, array($code), $removetaxClassRuleId);
					}
				}

				$taxRuleIds = array_filter($taxRuleIds);

				if (!empty($taxRuleIds))
				{
					foreach ($taxRuleIds as $taxRuleId)
					{
						$this->helper->product->setRuleProducts($taxRuleId, 'shoprule', array($productId), 0, 1);
					}
				}

				$taxClassRuleIds = array_filter($taxClassRuleIds);

				if (!empty($taxClassRuleIds))
				{
					foreach ($taxClassRuleIds as $taxClassRuleId)
					{
						$this->helper->product->setRuleProducts(0, 'shoprule', array($code), $taxClassRuleId, 1);
					}
				}
			}
		}
		elseif ($context == 'com_sellacious.shoprule')
		{
			$registry   = new Registry($data);
			$shopRuleId = $item->get('id');
			$productIds = $registry->get('product.products');
			$productIds = is_array($productIds) ? $productIds : array_filter(explode(',', $productIds));
			$type       = $registry->get('type');
			$classes    = array_filter(explode(',', $registry->get($type . '_class_groups')));

			$this->helper->product->setRuleProducts($shopRuleId, 'shoprule', $productIds, 0, 1);

			foreach ($classes as $class)
			{
				$alias           = $this->helper->shopRuleClass->getAliasFromClass($class);
				$classId         = $this->helper->shopRuleClass->getClassFromAlias($alias, 'id');

				$classProductIds = $registry->get('product.' . $alias);
				$classProductIds = is_array($classProductIds) ? $classProductIds : array_filter(explode(',', $classProductIds));

				$this->helper->product->setRuleProducts(0, 'shoprule', $classProductIds, $classId, 1);
			}
		}
		elseif ($context == 'com_sellacious.shippingrule')
		{
			$registry   = new Registry($data);
			$ruleId     = $item->get('id');
			$productIds = $registry->get('product.products');
			$productIds = is_array($productIds) ? $productIds : array_filter(explode(',', $productIds));

			$this->helper->product->setRuleProducts($ruleId, 'shippingrule', $productIds, 0);
		}
		elseif ($context == 'com_sellacious.coupon')
		{
			$registry   = new Registry($data);
			$ruleId     = $item->get('id');
			$productIds = $registry->get('product.products');
			$productIds = is_array($productIds) ? $productIds : array_filter(explode(',', $productIds));

			$this->helper->product->setRuleProducts($ruleId, 'coupon', $productIds, 0);
		}
	}

	/**
	 * Adds additional data to the sellacious form data
	 *
	 * @param   string  $context  The context identifier
	 * @param   mixed   $data     The associated data for the form.
	 *
	 * @return  bool
	 *
	 * @throws  Exception
	 *
	 * @since   1.2.0
	 */
	public function onContentPrepareData($context, $data)
	{
		$plugin = sprintf('plg_%s_%s', $this->_type, $this->_name);

		if (is_object($data))
		{
			$registry = new Registry($data);

			if (empty($data->$plugin) && $context == 'com_sellacious.config')
			{
				$data->$plugin = $this->params->toArray();
			}
			elseif ($context == 'com_sellacious.shoprule')
			{
				$data->product           = !isset($data->product) ? new stdClass() : (object) $data->product;
				$data->product->products = $this->helper->product->getRuleProducts($data->id, 'shoprule', 0, 'product_id');

				$classes = $this->helper->shopRuleClass->getShopRuleClassIds($data->id, $data->type);

				foreach ($classes as $classId)
				{
					$alias           = $this->helper->shopRuleClass->getAliasFromClass($classId, 'id');
					$classProductIds = $this->helper->product->getRuleProducts(0, 'shoprule', $classId, 'product_id');

					$data->product->$alias = $classProductIds;
				}
			}
			elseif ($context == 'com_sellacious.shippingrule')
			{
				if (!isset($data->product))
				{
					$data->product = new stdClass();
				}

				$data->product->products = $this->helper->product->getRuleProducts($data->id, 'shippingrule', 0, 'product_id');
			}
			elseif ($context == 'com_sellacious.coupon')
			{
				if (!isset($data->product))
				{
					$data->product = new stdClass();
				}
				elseif (is_array($data->product))
				{
					$data->product = ArrayHelper::toObject($data->product);
				}

				$data->product->products = $this->helper->product->getRuleProducts($data->id, 'coupon', 0, 'product_id');
			}
			elseif ($context == 'com_sellacious.product' && $data->id)
			{
				if (isset($data->seller))
				{
					$data->seller = is_array($data->seller) ? ArrayHelper::toObject($data->seller) : $data->seller;
				}
				else
				{
					$data->seller = new stdClass();
				}

				if (isset($data->seller->rules))
				{
					$data->seller->rules = is_array($data->seller->rules) ? ArrayHelper::toObject($data->seller->rules) : $data->seller->rules;
				}
				else
				{
					$data->seller->rules = new stdClass();
				}

				$data->seller->rules->shipping_rules = $this->helper->shippingRule->getShippingRules($data->id, $data->seller_uid);

				$data->shoprule                 = new stdClass();
				$data->shoprule->discount_rules = $this->helper->shopRuleClass->getProductShopRuleClasses($data->id, $data->seller_uid, 'discount', 1);
				$data->shoprule->tax_rules      = $this->helper->shopRuleClass->getProductShopRuleClasses($data->id, $data->seller_uid, 'tax', 1);
			}
		}

		return true;
	}

	/**
	 * Validates given coupon against this filter.
	 * Plugins are passed a reference to the coupon and cart item objects. They are free to to the manipulation in any way.
	 *
	 * Plugin responses: true = apply
	 * If any of the plugins says FALSE - we'd not apply that coupon at all.
	 *
	 * @param   string    $context  The context identifier: 'com_sellacious.coupon'
	 * @param   Cart      $cart     The cart object
	 * @param   Item[]    $items    The cart items that are so far considered eligible, if this plugin determines
	 *                              that this item is not eligible this will remove it from the array
	 * @param   Registry  $coupon   The coupon object
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

		$filter = $coupon->extract('params.product');

		if (!$filter)
		{
			return true;
		}

		$products = array();

		foreach ($items as $product)
		{
			$item = (object) $product->getAttributes();

			$item->product_id = $item->id;

			if (!$this->checkCategory($item, $filter))
			{
				continue;
			}

			if (!$this->checkProduct($item, $coupon, 'coupon'))
			{
				continue;
			}

			if (!$this->checkManufacturer($item, $filter))
			{
				continue;
			}

			if (!$this->checkSeller($item, $filter))
			{
				continue;
			}

			if (!$this->checkLimit($item, $filter))
			{
				continue;
			}

			$products[] = $product;
		}

		$items = $products;

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
		if (!isset($item->pscode))
		{
			$item->pscode = $this->helper->product->getCode($item->product_id, 0, $item->seller_uid);
		}

		if ($context != 'com_sellacious.shoprule.product' || empty($item->product_id))
		{
			return true;
		}

		$filter = $rule->extract('params.product');

		if (!$filter)
		{
			return true;
		}

		if (!$this->checkCategory($item, $filter))
		{
			return false;
		}

		if (!$this->checkProduct($item, $rule))
		{
			return false;
		}

		if (!$this->checkManufacturer($item, $filter))
		{
			return false;
		}

		$checkSeller = $this->checkSeller($item, $filter);

		if ($checkSeller === null)
		{
			$rule->set('rule.inclusive', false);
		}
		elseif (!$checkSeller)
		{
			return false;
		}

		$checkLimit = $this->checkLimit($item, $filter);

		if ($checkLimit === null)
		{
			$rule->set('rule.inclusive', false);
		}
		elseif (!$checkLimit)
		{
			return false;
		}

		return true;
	}

	/**
	 * Validates given shippingrule against this filter
	 *
	 * If the decision was made based on internal logic already, then the plugin shall report whether to skip the
	 * rule or apply. This is identified by the return value of the plugin.
	 * Any plugin encountering similar state should simply return boolean true OR false.
	 *
	 * @param   string    $context   The context identifier: 'com_sellacious.shippingrule.product'
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
	public function onValidateProductShippingrule($context, Registry $rule, $item, $use_cart = null)
	{
		if (!isset($item->pscode))
		{
			$item->pscode = $this->helper->product->getCode($item->product_id, 0, $item->seller_uid);
		}

		if ($context != 'com_sellacious.shippingrule.product' || empty($item->product_id))
		{
			return true;
		}

		$filter = $rule->extract('params.product');

		if (!$filter)
		{
			return true;
		}

		// If category filter is set then determine
		$cat_filter = array_filter((array) $filter->get('categories'), 'intval');

		if (count($cat_filter) > 0)
		{
			$categories = (array) $this->helper->product->getCategories($item->product_id, true);
			$intersect  = array_intersect($cat_filter, $categories);

			if (count($intersect) == 0)
			{
				return false;
			}
		}

		// If products filter is set then determine
		if (!$this->checkProduct($item, $rule, 'shippingrule'))
		{
			return false;
		}

		// If manufacturer filter is set then determine
		$mfr_filter = array_filter((array) $filter->get('manufacturer'), 'intval');

		if (count($mfr_filter) > 0)
		{
			$filters         = array('id' => $item->product_id, 'list.select' => 'a.manufacturer_id');
			$manufacturer_id = isset($item->manufacturer_id) ? $item->manufacturer_id : $this->helper->product->loadResult($filters);

			if (!in_array($manufacturer_id, $mfr_filter))
			{
				return false;
			}
		}

		// If manufacturer filter is set then determine
		$seller_filter = array_filter((array) $filter->get('seller'), 'intval');

		if ($owned_by = $rule->get('owned_by', 0))
		{
			$seller_filter[] = $owned_by;
			$seller_filter   = array_unique($seller_filter);
		}

		if (count($seller_filter) > 0)
		{
			if (!isset($item->seller_uid))
			{
				// We have filter but no value available to match with, dilemma!
				return false;
			}
			elseif (!in_array($item->seller_uid, $seller_filter))
			{
				return false;
			}
		}

		// If quantity is set and filter is also set check range
		$min = $filter->get('min_quantity', 0);

		$quantity = empty($item->quantity) ? 1 : $item->quantity;

		if ($min > 0 && $quantity < $min)
		{
			return false;
		}

		$max = $filter->get('max_quantity', 0);

		if ($max > 0 && $quantity > $max)
		{
			return false;
		}

		return true;
	}

	/**
	 * Ajax function to call methods
	 *
	 * @return  void
	 *
	 * @throws  \Exception
	 *
	 * @since   1.7.1
	 */
	public function onAjaxProduct()
	{
		$method = $this->app->input->getString('method', '');

		try
		{
			if (!empty($method) && method_exists(__CLASS__, $method))
			{
				$data = $this->$method();

				echo new JResponseJson($data, '');
			}
			else
			{
				throw new Exception(JText::_('PLG_SELLACIOUSRULES_ERROR_INVALID_AJAX_METHOD'));
			}
		}
		catch (Exception $e)
		{
			echo new JResponseJson($e);
		}

		JFactory::getApplication()->close();
	}

	/**
	 * Method to get product selection field for tax/discount class
	 *
	 * @return  array
	 *
	 * @since   1.7.1
	 */
	public function getProductField()
	{
		$class = $this->app->input->get('class_group', '', 'STRING');
		$form  = JForm::getInstance('product', __DIR__ . '/forms/product.xml', array('control' => 'jform'));

		$this->addRuleClassField($form, $class);
		$alias = $this->helper->shopRuleClass->getAliasFromClass($class);

		$displayData = array('form' => $form, 'class' => $class, 'alias' => $alias);
		$layout      = $this->renderLayout('productfield', $displayData);

		return array('layout' => $layout, 'alias' => str_replace('-', '_', $alias));
	}

	/**
	 * Method to inject product field for each rule class
	 *
	 * @param   \JForm  $form       The form
	 * @param   string  $class      The class name
	 * @param   string  $group      The form group
	 * @param   int     $sellerUid  The product seller user id
	 *
	 * @since   1.7.1
	 */
	public function addRuleClassField(&$form, $class, $group = 'product', $sellerUid = 0)
	{
		$alias = $this->helper->shopRuleClass->getAliasFromClass($class);

		JForm::addFieldPath(JPATH_SELLACIOUS . '/components/com_sellacious/models/fields');

		$field = new SimpleXMLElement('<field
				name="' . $alias . '"
				type="product"
				label="' . $class . '"
				description="PLG_SELLACIOUSRULES_PRODUCT_FIELD_PRODUCT_DESC"
				class="w100p"
				multiple="true"
				separate="true"
			/>
		');

		if ($sellerUid > 0)
		{
			$field->addAttribute('seller_uid', $sellerUid);
		}

		$form->setField($field, $group);
	}

	/**
	 * Check matching category
	 *
	 * @param   object    $item
	 * @param   Registry  $filter
	 *
	 * @return  bool
	 *
	 * @throws  Exception
	 *
	 * @since   1.2.0
	 */
	protected function checkCategory($item, $filter)
	{
		// If category filter is set then determine
		$cat_filter = array_filter((array) $filter->get('categories'), 'strlen');

		if (count($cat_filter) > 0)
		{
			$categories = (array) $this->helper->product->getCategories($item->product_id, true);
			$intersect  = array_intersect($cat_filter, $categories);

			if (count($intersect) == 0)
			{
				return false;
			}
		}

		return true;
	}

	/**
	 * Check matching product
	 *
	 * @param   object    $item         The cart item
	 * @param   Registry  $rule         The rule in question
	 * @param   string    $ruleContext  The rule type/context (shoprule, shippingrule, coupon)
	 *
	 * @return  bool
	 *
	 * @throws  \Exception
	 *
	 * @since   1.2.0
	 */
	protected function checkProduct($item, $rule, $ruleContext = 'shoprule')
	{
		// If products filter is set then determine
		$product_filter = $this->helper->product->getRuleProducts($rule->get('id'), $ruleContext, 0, 'product_id');
		$applyOnAll     = $rule->get('apply_on_all_products', 0);

		if ($ruleContext == 'shoprule')
		{
			$type     = $rule->get('type');
			$classIds = $this->helper->shopRuleClass->getShopRuleClassIds($rule->get('id'), $type);

			if (!empty($classIds))
			{
				foreach ($classIds as $classId)
				{
					$products       = $this->helper->product->getRuleProducts(0, $ruleContext, $classId, 'product_id');
					$product_filter = array_unique(array_merge($product_filter, $products));
				}
			}
		}

		if (!isset($item->pscode))
		{
			$item->pscode = $this->helper->product->getCode($item->product_id, 0, $item->seller_uid);
		}

		if (count($product_filter) > 0)
		{
			if (!in_array($item->product_id, $product_filter) && !in_array($item->pscode, $product_filter))
			{
				return false;
			}
		}
		elseif ($applyOnAll == 0)
		{
			// If there are no products to filter and rule is set for selected products
			return false;
		}

		return true;
	}

	/**
	 * Check matching manufacturer
	 *
	 * @param   object    $item
	 * @param   Registry  $filter
	 *
	 * @return  bool
	 *
	 * @since   1.2.0
	 */
	protected function checkManufacturer($item, $filter)
	{
		// If manufacturer filter is set then determine
		$mfr_filter = (array) $filter->get('manufacturer');
		$mfrId      = $this->helper->product->loadResult(array('list.select' => 'a.manufacturer_id', 'id' => $item->product_id));

		if ($mfr_filter)
		{
			if (!in_array($mfrId, $mfr_filter))
			{
				return false;
			}
		}

		return true;
	}

	/**
	 * Check matching seller
	 *
	 * @param   object    $item
	 * @param   Registry  $filter
	 *
	 * @return  bool
	 *
	 * @since   1.2.0
	 */
	protected function checkSeller($item, $filter)
	{
		// If seller filter is set then determine
		$seller_filter = (array) $filter->get('seller');

		if ($seller_filter)
		{
			// We have filter but no value available to match with
			if (!isset($item->seller_uid))
			{
				return null;
			}
			elseif (!in_array($item->seller_uid, $seller_filter))
			{
				return false;
			}
		}

		return true;
	}

	/**
	 * Check quantity limit
	 *
	 * @param   object    $item
	 * @param   Registry  $filter
	 *
	 * @return  bool
	 *
	 * @since   1.2.0
	 */
	protected function checkLimit($item, $filter)
	{
		// If quantity is set and filter is also set check range
		$min = $filter->get('min_quantity', 0);

		$quantity = empty($item->quantity) ? 1 : $item->quantity;

		if ($min > 0 && $quantity < $min)
		{
			return false;
		}

		$max = $filter->get('max_quantity', 0);

		if ($max > 0 && $quantity > $max)
		{
			return false;
		}

		return true;
	}

	/**
	 * Method to render a layout file from the plugin tmpl folder
	 *
	 * @param   string  $layout       The layout name to render
	 * @param   mixed   $displayData  The data required by the layout
	 * @param   string  $namespace    The scope of the layout to be loaded, omit to include default scope
	 *
	 * @return  string
	 *
	 * @since   1.7.1
	 */
	protected function renderLayout($layout = 'default', $displayData = null, $namespace = null)
	{
		$layout = $namespace ? $namespace . '_' . $layout : $layout;

		ob_start();

		$layoutPath = JPluginHelper::getLayoutPath($this->_type, $this->_name, $layout);

		if (is_file($layoutPath))
		{
			unset($namespace, $layout);

			/**
			 * Variables available to the layout
			 *
			 * @var  $this
			 * @var  $layoutPath
			 * @var  $displayData
			 */
			include $layoutPath;
		}

		return ob_get_clean();
	}
}
