<?php
/**
 * @version     2.0.0
 * @package     sellacious
 *
 * @copyright   Copyright (C) 2012-2020 Bhartiy Web Technologies. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Aditya Chakraborty <info@bhartiy.com> - http://www.bhartiy.com
 */

// No direct access
use Joomla\Registry\Registry;
use Joomla\Utilities\ArrayHelper;
use Sellacious\Cart;

defined('_JEXEC') or die('Restricted access');

// Include dependencies
jimport('sellacious.loader');

/**
 * Shipping Free Rule Plugin
 *
 * @since  1.0.0
 */
class plgSystemSellaciousfreeshipping extends SellaciousPlugin
{
	/**
	 * Load the language file on instantiation.
	 *
	 * @var    boolean
	 * @since  1.0.0
	 */
	protected $autoloadLanguage = true;

	/**
	 * Constructor
	 *
	 * @param   object &$subject  The object to observe
	 * @param   array  $config    An optional associative array of configuration settings.
	 *                            Recognized key values include 'name', 'group', 'params', 'language'
	 *                            (this list is not meant to be comprehensive).
	 *
	 * @throws  Exception
	 *
	 * @since   1.0.0
	 */
	public function __construct($subject, array $config)
	{
		parent::__construct($subject, $config);

		JTable::addIncludePath(__DIR__ . '/tables');
	}

	/**
	 * Adds configuration
	 *
	 * @param   JForm $form The form to be altered.
	 * @param   array $data The associated data for the form.
	 *
	 * @return  void
	 *
	 * @since   1.0.0
	 */
	public function onContentPrepareForm($form, $data)
	{
		parent::onContentPrepareForm($form, $data);

		if ($form instanceof JForm)
		{
			$name = $form->getName();
			$obj  = is_array($data) ? ArrayHelper::toObject($data) : $data;

			// Include config
			if ($name == 'com_sellacious.config')
			{
				$formPath = $this->pluginPath . '/' . $this->_name . '.xml';

				// Inject plugin configuration into config form.
				$form->loadFile($formPath, false, '//config');
			}
			elseif ($name == 'com_sellacious.shippingrule')
			{
				$params = $this->helper->config->getParams('plg_system_sellaciousfreeshipping');

				if ($params->get('shipping_extra_freerule', 1) && $obj->method_name != 'slabs.price')
				{
					$formPath = $this->pluginPath . '/forms/shippingrule.xml';

					// Inject plugin configuration into config form.
					$form->loadFile($formPath, false);
				}
			}
			elseif ($name == 'com_plugins.plugin')
			{
				// Don't let the plugin form show up in the Joomla plugin manager config page.
				$form->removeGroup($this->pluginName);
			}
		}
	}

	/**
	 * Runs on content preparation
	 *
	 * @param   string $context The context for the data
	 * @param   object $data    An object containing the data for the form.
	 *
	 * @return  void
	 *
	 * @since   1.0.0
	 *
	 * @throws  \Exception
	 */
	public function onContentPrepareData($context, $data)
	{
		if ($context == 'com_sellacious.config')
		{
			$config = $this->helper->config->loadColumn(array('context' => 'plg_system_sellaciousfreeshipping'), 3);

			if (is_object($data) && !empty($config) && isset($config[0]))
			{
				$registry = new Registry();
				$registry->loadString($config[0]);
				$params = $registry->toArray();

				$data->plg_system_sellaciousfreeshipping = $params;
			}
		}
		elseif ($context == 'com_sellacious.shippingrule')
		{
			$params = $this->helper->config->getParams('plg_system_sellaciousfreeshipping');

			if ($params->get('shipping_extra_freerule', 1))
			{
				$registry = new Registry($data);
				$id       = $registry->get('id', 0);

				$table = JTable::getInstance('ShippingFreerule', 'SellaciousTable');
				$table->load(array('rule_id' => $id));

				$freerule = $table->getProperties(1);

				if (is_object($data))
				{
					$data->freerule = $freerule;
				}
				else
				{
					$data['freerule'] = $freerule;
				}
			}
		}
	}

	/**
	 * Method is called right after an item is saved
	 *
	 * @param   string $context The calling context
	 * @param   object $table   A JTable object
	 * @param   bool   $isNew   If the content is just about to be created
	 *
	 * @return  void
	 *
	 * @since   1.0.0
	 *
	 * @throws  \Exception
	 */
	public function onContentAfterSave($context, $table, $isNew)
	{
		$data = $this->app->input->get('jform', array(), 'array');

		if ($context == 'com_sellacious.shippingrule')
		{
			if (isset($data['freerule']) && !empty($data['freerule']))
			{
				$freerule = $data['freerule'];

				$table = JTable::getInstance('ShippingFreerule', 'SellaciousTable');

				if ($freerule['id'])
				{
					$table->load($freerule['id']);
				}
				else
				{
					$freerule['rule_id'] = $data['id'];
				}

				$table->bind($freerule);
				$table->check();
				$table->store();
			}
		}
	}

	/**
	 * Method to check for free rule slab
	 *
	 * @param    string     $context  The calling context
	 * @param    \stdClass  $match    The matching rule
	 * @param    \stdClass  $rule     The shipping rule
	 *
	 * @throws   \Exception
	 *
	 * @since    1.7.0
	 */
	public function onMatchRuleSlab($context, &$match, $rule)
	{
		$params = $this->helper->config->getParams('plg_system_sellaciousfreeshipping');

		if ($context == 'com_sellacious.rule' && $params->get('shipping_extra_freerule', 1) && $rule->method_name != 'slabs.price')
		{
			$cart           = $this->helper->cart->getCart();
			$items          = $cart->getItems();
			$subTotal       = 0;
			$discounts      = 0;
			$couponDiscount = 0;

			foreach ($items as $item)
			{
				$quantity = $item->getQuantity();
				$price    = $item->getPrice();

				if (is_object($price))
				{
					$subTotal = $subTotal + ($price->basic_price * $quantity);
					$discounts = $discounts + ($price->discount_amount * $quantity);
				}
			}

			if ($coupon = $cart->getCoupon())
			{
				$coupon_amount  = $coupon->get('value');
				$g_currency     = $this->helper->currency->getGlobal('code_3');
				$couponDiscount = $this->helper->currency->convert($coupon_amount, $g_currency, $cart->getCurrency());
			}

			$subTotal = $subTotal - $discounts - $couponDiscount;

			$table = JTable::getInstance('ShippingFreerule', 'SellaciousTable');
			$table->load(array('rule_id' => $rule->id));

			if ($table->get('id') && $table->get('state'))
			{
				$min = $table->get('amount_min');
				$max = $table->get('amount_max');

				if (($min > 0 && $max == 0 && $subTotal >= $min)
				|| ($min >= 0 && $max > 0 && $min < $max && $subTotal >= $min && $subTotal <= $max))
				{
					if (!$match)
					{
						$match = new stdClass();
					}

					if ($rule->method_name == '*')
					{
						$match->amount = 0;
						$match->amount_additional = 0;
					}
					else
					{
						$match->price = 0;
					}
				}
			}
		}
	}
}
