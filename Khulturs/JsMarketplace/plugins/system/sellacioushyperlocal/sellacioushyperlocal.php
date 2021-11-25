<?php
/**
 * @version     1.7.4
 * @package     sellacious
 *
 * @copyright   Copyright (C) 2012-2019 Bhartiy Web Technologies. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Aditya Chakraborty <info@bhartiy.com> - http://www.bhartiy.com
 */

// No direct access
use Joomla\Registry\Registry;
use Joomla\Utilities\ArrayHelper;
use Sellacious\Cache\Distances;
use Sellacious\Config\ConfigHelper;

defined('_JEXEC') or die('Restricted access');

// Include dependencies
jimport('sellacious.loader');

/**
 * Hyper Local Plugin
 *
 * @since  1.6.0
 */
class plgSystemSellaciousHyperlocal extends SellaciousPlugin
{
	/**
	 * Load the language file on instantiation.
	 *
	 * @var    boolean
	 * @since  1.6.0
	 */
	protected $autoloadLanguage = true;

	/**
	 * Stores the cache of calculated distances.
	 *
	 * @var    object
	 * @since  1.6.0
	 */
	protected $distanceCache;

	/**
	 * Constructor
	 *
	 * @param   object &$subject  The object to observe
	 * @param   array   $config   An optional associative array of configuration settings.
	 *                            Recognized key values include 'name', 'group', 'params', 'language'
	 *                            (this list is not meant to be comprehensive).
	 *
	 * @throws  Exception
	 *
	 * @since   1.6.0
	 */
	public function __construct($subject, array $config)
	{
		parent::__construct($subject, $config);

		JLoader::registerNamespace('Sellacious', __DIR__ . '/libraries');

		$this->distanceCache = new Distances;

		JTable::addIncludePath(__DIR__ . '/tables');

		JModelLegacy::addIncludePath(JPATH_SITE . '/components/com_sellacious/models/');
	}

	/**
	 * Adds hyperlocal configuration
	 *
	 * @param   JForm  $form  The form to be altered.
	 * @param   array  $data  The associated data for the form.
	 *
	 * @return  boolean
	 *
	 * @since   1.6.0
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
				JHtml::_('jquery.framework');
				JHtml::_('script', 'plg_system_sellacioushyperlocal/config.js', false, true);

				$formPath = $this->pluginPath . '/' . $this->_name . '.xml';

				// Inject plugin configuration into config form.
				$form->loadFile($formPath, false, '//config');

				JText::script('PLG_SYSTEM_SELLACIOUSHYPERLOCAL_PURGE_CACHE');
				JText::script('PLG_SYSTEM_SELLACIOUSHYPERLOCAL_PURGING');

				$spacer = '<a class="btn btn-primary btn-purge-distance-cache" href="#">' . JText::_('PLG_SYSTEM_SELLACIOUSHYPERLOCAL_PURGE_CACHE') . '</a>';

				$form->setFieldAttribute('cache_spacer', 'label', $spacer, 'plg_system_sellacioushyperlocal');

				$config   = ConfigHelper::getInstance('plg_system_sellacioushyperlocal');
				$hlParams = $config->getParams();
				$units    = $this->helper->unit->count(array('list.where' => array('a.state = 1', 'a.unit_group = ' . $this->db->quote('Length'))));

				if (!empty($hlParams->get('hyperlocal_type')))
				{
					$form->removeField('unit_note', 'plg_system_sellacioushyperlocal');
					$form->removeField('save_settings_note', 'plg_system_sellacioushyperlocal');

					// Check if the selected units have conversions for Meter unit
					$productRadius = $hlParams->get('product_radius');

					$meterUnit = $this->helper->unit->loadResult(array(
						'list.select' => 'a.id',
						'list.where'  => array(
							'a.title = ' . $this->db->quote('Meter'),
							'a.symbol = ' . $this->db->quote('m'),
							'a.unit_group = ' . $this->db->quote('Length'),
						),
					));
					$meterUnit = $meterUnit ?: null;

					$productRate = $this->helper->unit->getRate(isset($productRadius->u) ? $productRadius->u : 0, $meterUnit);

					if (!isset($productRadius->u) || !empty($productRate))
					{
						$form->removeField('unit_conversion_note', 'plg_system_sellacioushyperlocal');
					}
				}
				elseif (!empty($units))
				{
					$form->removeField('unit_note', 'plg_system_sellacioushyperlocal');
				}
				else
				{
					$form->removeField('save_settings_note', 'plg_system_sellacioushyperlocal');
				}
			}
			elseif (($name == 'com_sellacious.user' || $name == 'com_sellacious.profile') && isset($obj->seller) && $obj->seller->category_id)
			{
				JForm::addFieldPath(JPATH_SITE . '/sellacious/components/com_sellacious/models/fields');

				$registry = new Registry($data);
				JHtml::_('jquery.framework');

				$hlConfig              = ConfigHelper::getInstance('plg_system_sellacioushyperlocal');
				$hlParams              = $hlConfig->getParams();
				$key                   = $hlParams->get('google_api_key', '');
				$deliveryTimeSelection = $hlParams->get('delivery_time_selection', 0);

				if (!empty($key))
				{
					JHtml::_('script', 'https://maps.googleapis.com/maps/api/js?key=' . $key . '&libraries=places', false, false);
				}

				JHtml::_('script', 'plg_system_sellacioushyperlocal/seller.js', false, true);

				JForm::addFieldPath(__DIR__ . '/forms/fields');

				$form->removeField('shipping_geo_group', 'seller.shipping_geo');
				$form->removeField('country', 'seller.shipping_geo');
				$form->removeField('state', 'seller.shipping_geo');
				$form->removeField('district', 'seller.shipping_geo');
				$form->removeField('zip', 'seller.shipping_geo');

				$formPath = $this->pluginPath . '/forms/seller.xml';

				// Inject plugin configuration into config form.
				$form->loadFile($formPath, false);

				$form->setFieldAttribute('shipping_distance', 'layoutPath', JPATH_PLUGINS . '/system', 'seller.hyperlocal');

				// Check if the selected units have conversions for Meter unit
				$seller = $registry->get('seller');

				if (isset($seller->hyperlocal))
				{
					$hyperlocal   = $seller->hyperlocal;
					$locationType = $hyperlocal->shipping_location_type;

					$meterUnit = $this->helper->unit->loadResult(array(
						'list.select' => 'a.id',
						'list.where'  => array(
							'a.title = ' . $this->db->quote('Meter'),
							'a.symbol = ' . $this->db->quote('m'),
							'a.unit_group = ' . $this->db->quote('Length'),
						),
					));
					$meterUnit = $meterUnit ?: null;

					$rate = $this->helper->unit->getRate(isset($hyperlocal->shipping_distance->u) ? $hyperlocal->shipping_distance->u : 0, $meterUnit);

					if ($locationType == 1 || !empty($rate))
					{
						$form->removeField('unit_conversion_note', 'seller');
					}
				}
				else
				{
					$form->removeField('unit_conversion_note', 'seller');
				}

				// Front end seller profile page
				if ($this->app->isClient('site'))
				{
					JHtml::_('stylesheet', 'plg_system_sellacioushyperlocal/layout.profile.css', null, true);
				}

				if ($this->app->isClient('site') || !$this->helper->access->check('user.edit'))
				{
					if (empty($obj->client->category_id))
					{
						$cParams = new Registry;
					}
					else
					{
						$filter  = array('list.select' => 'a.params', 'id' => $obj->client->category_id);
						$cParams = $this->helper->category->loadResult($filter);
						$cParams = new Registry($cParams);
					}

					if (empty($obj->seller->category_id))
					{
						$sParams = $cParams;
					}
					else
					{
						$filter  = array('list.select' => 'a.params', 'id' => $obj->seller->category_id);
						$sParams = $this->helper->category->loadResult($filter);
						$sParams = new Registry($sParams);
					}

					if (!$sParams->get('seller.shipping_location_type', 1) == 1)
					{
						$form->removeField('shipping_location_type', 'seller.hyperlocal');
						$form->removeField('shipping_distance', 'seller.hyperlocal');
					}

					$shipping_geo_visible = 0;

					if (!$sParams->get('seller.available_countries', 1) == 1)
					{
						$form->removeField('country', 'seller.shipping_geo');
					}
					else
					{
						$shipping_geo_visible++;
					}

					if (!$sParams->get('seller.available_states', 1) == 1)
					{
						$form->removeField('state', 'seller.shipping_geo');
					}
					else
					{
						$shipping_geo_visible++;
					}

					if (!$sParams->get('seller.available_districts', 1) == 1)
					{
						$form->removeField('district', 'seller.shipping_geo');
					}
					else
					{
						$shipping_geo_visible++;
					}

					if (!$sParams->get('seller.available_zipcodes', 1) == 1)
					{
						$form->removeField('zip', 'seller.shipping_geo');
					}
					else
					{
						$shipping_geo_visible++;
					}

					if ($shipping_geo_visible == 0)
					{
						$form->removeField('shipping_geo_group', 'seller.shipping_geo');
					}

					$store_timings_visible = 0;

					if (!$sParams->get('seller.store_timings', 1) == 1)
					{
						$form->removeField('timings', 'seller');
					}
					else
					{
						$store_timings_visible++;
					}

					if (!$sParams->get('seller.delivery_timings', 1) == 1)
					{
						$form->removeField('delivery_hours', 'seller');
					}
					else
					{
						$store_timings_visible++;
					}

					if (!$sParams->get('seller.pickup_timings', 1) == 1)
					{
						$form->removeField('pickup_hours', 'seller');
					}
					else
					{
						$store_timings_visible++;
					}

					if ($store_timings_visible == 0)
					{
						$form->removeField('seller_timings_group', 'seller');
					}

					if (!$sParams->get('seller.store_timings_settings', 1) == 1)
					{
						$form->removeField('show_store_availability', 'seller.hyperlocal.params');
						$form->removeField('show_delivery_availability', 'seller.hyperlocal.params');
						$form->removeField('show_pickup_availability', 'seller.hyperlocal.params');
						$form->removeField('show_store_timings', 'seller.hyperlocal.params');
						$form->removeField('show_delivery_timings', 'seller.hyperlocal.params');
						$form->removeField('show_pickup_timings', 'seller.hyperlocal.params');

						$form->removeField('store_group', 'seller');
					}

					$form->setFieldAttribute('timings', 'layout', 'store_timings', 'seller');
					$form->setFieldAttribute('delivery_hours', 'layout', 'store_timings', 'seller');
					$form->setFieldAttribute('pickup_hours', 'layout', 'store_timings', 'seller');
				}

				if ($deliveryTimeSelection == 0)
				{
					$form->removeField('slot_window', 'seller.hyperlocal.params');
					$form->removeField('today_availability', 'seller.hyperlocal.params');
					$form->removeField('slot_limit', 'seller.hyperlocal.params');
					$form->removeField('delivery_prep_time', 'seller.hyperlocal.params');
				}
				elseif ($deliveryTimeSelection == 1)
				{
					$form->removeField('slot_window', 'seller.hyperlocal.params');
					$form->removeField('delivery_prep_time', 'seller.hyperlocal.params');
				}
				elseif ($deliveryTimeSelection == 3)
				{
					$form->removeField('slot_window', 'seller.hyperlocal.params');
				}
			}
			elseif ($name == 'com_plugins.plugin')
			{
				// Don't let the plugin form show up in the Joomla plugin manager config page.
				$form->removeGroup($this->pluginName);
			}
			elseif ($name == 'com_sellacious.category')
			{
				if ($obj->type == 'seller')
				{
					$formPath = $this->pluginPath . '/forms/category.xml';

					// Inject plugin configuration into config form.
					$form->loadFile($formPath, false);
				}
			}
			elseif ($name == 'com_sellacious.emailtemplate')
			{
				$array   = is_object($data) ? ArrayHelper::fromObject($data) : (array) $data;
				$context = isset($array['context']) ? explode('.', $array['context']) : array();

				if (isset($context[0]))
				{
					$prefix = explode('_', $context[0]);

					if ($prefix[0] == 'order')
					{
						$form->loadFile(__DIR__ . '/forms/template.xml', false);
					}
				}
			}
			elseif ($name == 'com_modules.module' && isset($obj->module) && $this->matchModule($obj->module))
			{
				$form->loadFile(__DIR__ . '/forms/module.xml', false);
			}
		}

		return true;
	}

	/**
	 * Runs on content preparation
	 *
	 * @param   string $context The context for the data
	 * @param   mixed  $data    An object containing the data for the form.
	 *
	 * @return  void
	 *
	 * @since   1.6.0
	 *
	 * @throws  \Exception
	 */
	public function onContentPrepareData($context, $data)
	{
		if ($wasArray = is_array($data))
		{
			$data = ArrayHelper::toObject($data);
		}

		if ($context == 'com_sellacious.config')
		{
			$config = ConfigHelper::getInstance('plg_system_sellacioushyperlocal');
			$params = $config->getParams();

			if (!empty($params->toArray()))
			{
				$data->plg_system_sellacioushyperlocal = $params;
			}
			else
			{
				$data->plg_system_sellacioushyperlocal = array('display_delivery_slots' => array('categories','products','product','product_modal','store'));
			}
		}
		elseif ($context == 'com_sellacious.user' || $context == 'com_sellacious.profile')
		{
			$registry = new Registry($data);
			$seller   = $registry->get('seller');

			if (!isset($seller->id))
			{
				return;
			}

			$sellerUser = JTable::getInstance('Seller', 'SellaciousTable');
			$sellerUser->load($seller->id);

			$sellerUid = $sellerUser->get('user_id');

			$hlTable = JTable::getInstance('SellerHyperlocal', 'SellaciousTable');
			$hlTable->load(array('seller_uid' => $sellerUid));

			$hyperlocal                      = $hlTable->getProperties(1);
			$hyperlocal['shipping_distance'] = json_decode($hyperlocal['shipping_distance']);
			$hyperlocal['params']            = json_decode($hlTable->get('params'));

			$timings        = $this->getTimings($sellerUid, 'timings');
			$delivery_hours = $this->getTimings($sellerUid, 'delivery_hours');
			$pickup_hours   = $this->getTimings($sellerUid, 'pickup_hours');

			if (is_object($data))
			{
				$data->seller->hyperlocal     = (object) $hyperlocal;
				$data->seller->timings        = (object) $timings;
				$data->seller->delivery_hours = (object) $delivery_hours;
				$data->seller->pickup_hours   = (object) $pickup_hours;

				if ($context == 'com_sellacious.profile')
				{
					$data->seller->shipping_geo = $this->helper->seller->getShipLocations($data->id, true);
				}
			}
		}

		if ($wasArray)
		{
			// Temporary workaround to reset data type to original
			$data = ArrayHelper::fromObject($data);
		}
	}

	/**
	 * Method is called right after an item is saved
	 *
	 * @param   string  $context  The calling context
	 * @param   object  $table    A JTable object
	 * @param   bool    $isNew    If the content is just about to be created
	 *
	 * @return  void
	 *
	 * @since   1.6.0
	 *
	 * @throws  \Exception
	 */
	public function onContentAfterSave($context, $table, $isNew)
	{
		$app  = JFactory::getApplication();
		$data = $app->input->get('jform', array(), 'array');

		if (isset($data['seller']) && ($context == 'com_sellacious.user' || $context == 'com_sellacious.profile'))
		{
			$hyperlocal = isset($data['seller']['hyperlocal']) ? $data['seller']['hyperlocal'] : array();

			if (!empty($hyperlocal))
			{
				$hlTable = JTable::getInstance('SellerHyperlocal', 'SellaciousTable');
				$params  = new JRegistry($hyperlocal['params']);

				if ($params->get('slot_window')->m < 0)
				{
					throw new Exception(JText::_('PLG_SYSTEM_SELLACIOUSHYPERLOCAL_SLOT_WINDOW_INVALID'));
				}

				if ($params->get('slot_limit') < 0)
				{
					throw new Exception(JText::_('PLG_SYSTEM_SELLACIOUSHYPERLOCAL_SLOT_LIMIT_INVALID'));
				}

				if ($params->get('delivery_prep_time')->m < 0)
				{
					throw new Exception(JText::_('PLG_SYSTEM_SELLACIOUSHYPERLOCAL_DELIVERY_PREP_TIME_INVALID'));
				}

				if ($params->get('slot_window')->m == '')
				{
					$slot_window    = $params->get('slot_window');
					$slot_window->m = 0;
					$params->set('slot_window', $slot_window);
				}

				if ($params->get('slot_limit') == '')
				{
					$params->set('slot_limit', 0);
				}

				if ($params->get('delivery_prep_time')->m == '')
				{
					$delivery_prep_time    = $params->get('delivery_prep_time');
					$delivery_prep_time->m = 0;
					$params->set('delivery_prep_time', $delivery_prep_time);
				}

				$hyperlocal['seller_uid']        = $table->get('id');
				$hyperlocal['shipping_distance'] = json_encode($hyperlocal['shipping_distance']);
				$hyperlocal['params']            = $params->toString();

				$hlTable->bind($hyperlocal);
				$hlTable->check();
				$hlTable->store();
			}

			$this->saveTimings($data['seller']['timings'], 'timings', $table->get('id'));
			$this->saveTimings($data['seller']['delivery_hours'], 'delivery_hours', $table->get('id'));
			$this->saveTimings($data['seller']['pickup_hours'], 'pickup_hours', $table->get('id'));

			if ($context == 'com_sellacious.profile')
			{
				$locations = ArrayHelper::getValue($data['seller'], 'shipping_geo', array(), 'array');

				foreach ($locations as &$location)
				{
					$location = strlen($location) ? explode(',', $location) : array();
				}

				$locations = array_reduce($locations, 'array_merge', array());

				$this->helper->seller->setShipLocations($table->get('id'), $locations);
			}
		}
	}

	/**
	 * Method is called right after an extension is saved
	 *
	 * @param   string  $context  The calling context
	 * @param   object  $table    A JTable object
	 * @param   bool    $isNew    If the content is just about to be created
	 *
	 * @return  void
	 *
	 * @since   1.7.1
	 *
	 * @throws  \Exception
	 */
	public function onExtensionBeforeSave($context, $table, $isNew)
	{
		if ($context == 'com_modules.module' && $this->matchModule($table->get('module')))
		{
			$params    = new Registry($table->get('params'));
			$data      = new Registry($this->app->input->get('jform', array(), 'Array'));
			$dispSlots = $data->get('params.display_delivery_slots');

			if ($dispSlots != '')
			{
				$params->set('display_delivery_slots', $dispSlots);
				$table->set('params', $params->toString());
			}
		}
	}

	/**
	 * Method to add custom layout for Tour Tab
	 *
	 * @param    string          $context The context
	 * @param    \SellaciousView $view    The view data
	 *
	 * @return   void
	 *
	 * @throws   \Exception
	 *
	 * @since    1.7.0
	 */
	public function onBeforeDisplayView($context, $view)
	{
		if ($context == 'com_sellacious.product' && $this->app->isClient('site'))
		{
			$hlConfig   = ConfigHelper::getInstance('plg_system_sellacioushyperlocal');
			$hlParams   = $hlConfig->getParams();
			$item       = $view->get('item');
			$layout     = $view->get('_layout');
			$user       = JFactory::getUser();
			$now        = JFactory::getDate();
			$hyperlocal = $this->app->getUserState('mod_sellacious_hyperlocal.user.location', array());
			$timezone   = isset($hyperlocal['timezone']) && !empty($hyperlocal['timezone']) ? $hyperlocal['timezone'] : $this->app->get('offset', 'UTC');

			if ($user->id)
			{
				$timezone = $user->getParam('timezone', $timezone);
			}

			$timezone = new DateTimeZone($timezone);
			$now->setTimezone($timezone);
			$weekDay = $now->format('w', true);
			$weekDay  = $weekDay == 0 ? 6 : ($weekDay - 1);

			$timeSelection = $hlParams->get('delivery_time_selection', 0);
			$displaySlots  = empty($hlParams->toArray()) ? array('categories','products','product','product_modal','store') : (array) $hlParams->get('display_delivery_slots');

			if ($layout == 'modal' && !in_array('product_modal', $displaySlots))
			{
				return;
			}

			if ($layout == 'default' && !in_array('product', $displaySlots))
			{
				return;
			}

			$hlTable = JTable::getInstance('SellerHyperlocal', 'SellaciousTable');
			$hlTable->load(array('seller_uid' => $item->seller_uid));
			$params    = new Registry($hlTable->get('params'));
			$slotLimit = $params->get('slot_limit', 0);

			if ($slotLimit > 0 && $timeSelection >= 1)
			{
				$view->addTemplatePath(__DIR__ . '/tmpl');
				$today_until   = '';
				$disabledDates = array();

				$deliveryHour = $this->getTiming($item->seller_uid, $weekDay);

				if (!empty($deliveryHour))
				{
					$from_time          = JFactory::getDate($now->format('Y-m-d', true) . ' ' . $deliveryHour->from_time);
					$to_time            = JFactory::getDate($now->format('Y-m-d', true) . ' ' . $deliveryHour->to_time);
					$today_availability = JFactory::getDate($now->format('Y-m-d', true) . ' ' . $params->get('today_availability', '12:00 AM'));
					$todayNow           = JFactory::getDate($now->format('Y-m-d H:i:s', true));

					if ($today_availability >= $from_time && $today_availability <= $to_time && $todayNow < $today_availability)
					{
						$until_time   = $today_availability->diff($todayNow);
						$order_within = $until_time->i . ' mins';

						if ($hr = $until_time->h)
						{
							$order_within = $hr . ' hrs ' . $order_within;
						}

						$today_until = JText::sprintf('PLG_SYSTEM_SELLACIOUSHYPERLOCAL_ORDER_WITHIN', $order_within);
					}
				}

				$view->set('today_until', $today_until);

				// Dates to select
				$todaySlots = $this->getAvailableSlots($item->id, $item->seller_uid, $now->format('Y-m-d', true), $now->format('H:i:s', true));
				$view->set('today', JFactory::getDate($now->format('Y-m-d H:i:s', true)));
				$view->set('today_slots', $todaySlots);

				if (empty($todaySlots))
				{
					$disabledDates[] = $now->format('Y-m-d', true);
				}

				$selectedDate = !empty($todaySlots) ? JFactory::getDate($now)->format('Y-m-d 00:00:00', true) : null;

				$dates = array();

				for ($i = 0; $i < 2; $i++)
				{
					$now->modify('+1 day');
					$slots    = $this->getAvailableSlots($item->id, $item->seller_uid, $now->format('Y-m-d', true));
					$slotDate = JFactory::getDate($now->format('Y-m-d H:i:s', true));
					$dates[]  = array(
						'date'  => $slotDate,
						'slots' => $slots,
					);

					if (!$selectedDate && !empty($slots))
					{
						$selectedDate = $slotDate->format('Y-m-d 00:00:00', true);
					}

					if (empty($slots))
					{
						$disabledDates[] = $slotDate->format('Y-m-d', true);
					}
				}

				$unavailableDates = $this->getUnavailableDates($item->id, $item->seller_uid);
				$disabledDates    = array_unique(array_merge($disabledDates, $unavailableDates));

				// Check for enabled delivery week days
				$deliveryTimings  = $this->getTimings($item->seller_uid, 'delivery_hours', 1);
				$deliveryWeekDays = ArrayHelper::getColumn($deliveryTimings, 'week_day');

				foreach ($deliveryWeekDays as &$deliveryWeekDay)
				{
					if ($deliveryWeekDay == 6)
					{
						$deliveryWeekDay = 0;
					}
					else
					{
						$deliveryWeekDay += 1;
					}
				}

				$disabledDays = array_values(array_diff(array(0, 1, 2, 3, 4, 5, 6), $deliveryWeekDays));

				// Max Date
				$maxDate = JFactory::getDate('Dec 31')->modify('+1 year');

				$view->set('params', $hlParams);
				$view->set('max_date', $maxDate);
				$view->set('selected_date', $selectedDate);
				$view->set('dates', $dates);
				$view->set('disabled_dates', '["' . implode('", "', $disabledDates) . '"]');
				$view->set('disabled_days', '[' . implode(', ', $disabledDays) . ']');
			}
		}
		elseif (($context == 'com_sellacious.store' || strpos($context, 'com_sellacious.product') !== false || $context == 'com_sellacious.categories') && $this->app->isClient('site'))
		{
			JHtml::_('jquery.framework');

			JHtml::_('script', 'media/com_sellacious/js/plugin/select2-3.5/select2.js', false, false);
			JHtml::_('stylesheet', 'media/com_sellacious/js/plugin/select2-3.5/select2.css', null, false);

			JHtml::_('stylesheet', 'plg_system_sellacioushyperlocal/glyphicons.css', null, true);
			JHtml::_('stylesheet', 'plg_system_sellacioushyperlocal/bootstrap-datetimepicker.css', null, true);
			JHtml::_('stylesheet', 'plg_system_sellacioushyperlocal/layout.item_attributes.css', null, true);
			JHtml::_('script', 'plg_system_sellacioushyperlocal/moment-with-locales.js', false, true);
			JHtml::_('script', 'plg_system_sellacioushyperlocal/bootstrap-datetimepicker.js', false, true);
			JHtml::_('script', 'plg_system_sellacioushyperlocal/layout.item_attributes.js', false, true);
		}
	}

	/**
	 * Method to call after a module is rendered
	 *
	 * @param   \stdClass  $module   The module object
	 * @param   array      $attribs  An array of attributes for the module (probably from the XML).
	 *
	 * @since   1.7.0
	 */
	public function onAfterRenderModule($module, $attribs)
	{
		if ($this->matchModule($module->module) && $this->app->isClient('site'))
		{
			JHtml::_('jquery.framework');

			JHtml::_('script', 'media/com_sellacious/js/plugin/select2-3.5/select2.js', false, false);
			JHtml::_('stylesheet', 'media/com_sellacious/js/plugin/select2-3.5/select2.css', null, false);

			JHtml::_('stylesheet', 'plg_system_sellacioushyperlocal/glyphicons.css', null, true);
			JHtml::_('stylesheet', 'plg_system_sellacioushyperlocal/bootstrap-datetimepicker.css', null, true);
			JHtml::_('stylesheet', 'plg_system_sellacioushyperlocal/layout.item_attributes.css', null, true);
			JHtml::_('script', 'plg_system_sellacioushyperlocal/moment-with-locales.js', false, true);
			JHtml::_('script', 'plg_system_sellacioushyperlocal/bootstrap-datetimepicker.js', false, true);
			JHtml::_('script', 'plg_system_sellacioushyperlocal/layout.item_attributes.js', false, true);
		}
	}

	/**
	 * Method to manipulate filters before building query
	 *
	 * @param   string  $context  The context
	 * @param   array   $filters  The query filters
	 * @param   string  $method   The Database/Helper Method
	 *
	 * @throws  \Exception
	 *
	 * @since   1.6.0
	 */
	public function onBeforeBuildQuery($context, &$filters, $method)
	{
		$app = JFactory::getApplication();

		if ($method == 'loadObjectList' && $app->isClient('site'))
		{
			$app        = JFactory::getApplication();
			$hyperlocal = $app->getUserState('mod_sellacious_hyperlocal.user.location', array());

			$hlConfig = ConfigHelper::getInstance('plg_system_sellacioushyperlocal');
			$hlParams = $hlConfig->getParams();

			if (empty($hlParams->get('hyperlocal_type')))
			{
				return;
			}

			$productBounds    = isset($hyperlocal['product_bounds']) ? $hyperlocal['product_bounds'] : array();
			$productBoundsMin = isset($hyperlocal['product_bounds_min']) ? $hyperlocal['product_bounds_min'] : array();
			$storeBounds      = isset($hyperlocal['store_bounds']) ? $hyperlocal['store_bounds'] : array();
			$storeBoundsMin   = isset($hyperlocal['store_bounds_min']) ? $hyperlocal['store_bounds_min'] : array();
			$addrMatching     = isset($hyperlocal['address_matching']) ? $hyperlocal['address_matching'] : '';
			$addrMatchingArea = isset($hyperlocal['address_matching_area']) ? $hyperlocal['address_matching_area'] : '';

			if ($context == 'com_sellacious.helper.product' && (isset($filters['list.from']) && $filters['list.from'] == '#__sellacious_cache_products'))
			{
				if ($hlParams->get('hyperlocal_type') == 1 && array_filter($productBounds) && array_filter($productBoundsMin))
				{
					// Filter by radius
					$filters['list.join'][] = array('inner', '#__sellacious_sellers AS ss ON ss.user_id = ps.seller_uid');

					$filters['list.where'][] = '((CASE WHEN a.product_location != \'\' THEN SUBSTRING_INDEX(a.product_location, ",", 1) < ' . $productBounds['north'] . ' ELSE SUBSTRING_INDEX(a.store_location, ",", 1) < ' . $productBounds['north'] . ' END)';
					$filters['list.where'][] = '(CASE WHEN a.product_location != \'\' THEN SUBSTRING_INDEX(a.product_location, ",", 1) > ' . $productBounds['south'] . ' ELSE SUBSTRING_INDEX(a.store_location, ",", 1) > ' . $productBounds['south'] . ' END)';
					$filters['list.where'][] = '(CASE WHEN a.product_location != \'\' THEN SUBSTRING_INDEX(a.product_location, ",", -1) < ' . $productBounds['east'] . ' ELSE SUBSTRING_INDEX(a.store_location, ",", -1) < ' . $productBounds['east'] . ' END)';
					$filters['list.where'][] = '(CASE WHEN a.product_location != \'\' THEN SUBSTRING_INDEX(a.product_location, ",", -1) > ' . $productBounds['west'] . ' ELSE SUBSTRING_INDEX(a.store_location, ",", -1) > ' . $productBounds['west'] . ' END))';

					$filters['list.where'][] = '!((CASE WHEN a.product_location != \'\' THEN SUBSTRING_INDEX(a.product_location, ",", 1) < ' . $productBoundsMin['north'] . ' ELSE SUBSTRING_INDEX(a.store_location, ",", 1) < ' . $productBoundsMin['north'] . ' END)';
					$filters['list.where'][] = '(CASE WHEN a.product_location != \'\' THEN SUBSTRING_INDEX(a.product_location, ",", 1) > ' . $productBoundsMin['south'] . ' ELSE SUBSTRING_INDEX(a.store_location, ",", 1) > ' . $productBoundsMin['south'] . ' END)';
					$filters['list.where'][] = '(CASE WHEN a.product_location != \'\' THEN SUBSTRING_INDEX(a.product_location, ",", -1) < ' . $productBoundsMin['east'] . ' ELSE SUBSTRING_INDEX(a.store_location, ",", -1) < ' . $productBoundsMin['east'] . ' END)';
					$filters['list.where'][] = '(CASE WHEN a.product_location != \'\' THEN SUBSTRING_INDEX(a.product_location, ",", -1) > ' . $productBoundsMin['west'] . ' ELSE SUBSTRING_INDEX(a.store_location, ",", -1) > ' . $productBoundsMin['west'] . ' END))';
				}
				elseif ($hlParams->get('hyperlocal_type') == 2 && isset($hyperlocal['id']))
				{
					// Filter by shippable location
					$location      = $this->getLocation($hyperlocal['id']);
					$locationWhere = array();

					foreach ($location as $type => $loc)
					{
						$subQuery = $this->getLocationSubQuery($type);

						$filters['list.join'][] = array(
							'left',
							'(' . $subQuery->__toString() . ') AS hl' . $type . ' ON hl' . $type . '.seller_uid = ps.seller_uid',
						);

						$locationWhere[] = 'hl' . $type . '.gl_id = ' . $location[$type];
					}

					if (!empty($locationWhere))
					{
						$filters['list.where'][] = '(' . implode(' OR ', $locationWhere) . ')';
					}
				}

				if ($addrMatching == 2 && !empty($addrMatchingArea) && isset($hyperlocal[$addrMatchingArea]))
				{
					$filters['list.join'][]  = array(
						'left',
						$this->db->qn('#__sellacious_product_sellers', 'psx') . ' ON (psx.product_id = a.product_id AND psx.seller_uid = a.seller_uid)',
						$this->db->qn('#__sellacious_geolocation', 'g') . ' ON (g.record_id = psx.id AND g.context = ' . $this->db->q('product_sellers') . ')',
						$this->db->qn('#__sellacious_geolocation', 'h') . ' ON (h.record_id = a.seller_uid AND h.context = ' . $this->db->q('seller') . ')',
					);
					$filters['list.where'][] = '(CASE WHEN ' . $this->db->qn('g.' . $addrMatchingArea) . ' = ' . $this->db->q($hyperlocal[$addrMatchingArea]) . ' THEN 1 WHEN ' . $this->db->qn('h.' . $addrMatchingArea) . ' = ' . $this->db->q($hyperlocal[$addrMatchingArea]) . ' THEN 1 ELSE 0 END)';
				}
			}
			elseif ($context == 'com_sellacious.helper.seller')
			{
				if ($hlParams->get('hyperlocal_type') == 1 && array_filter($storeBounds) && array_filter($storeBoundsMin))
				{
					$filters['list.where'][] = '(SUBSTRING_INDEX(a.store_location, ",", 1) < ' . $storeBounds['north'];
					$filters['list.where'][] = 'SUBSTRING_INDEX(a.store_location, ",", 1) > ' . $storeBounds['south'];
					$filters['list.where'][] = 'SUBSTRING_INDEX(a.store_location, ",", -1) < ' . $storeBounds['east'];
					$filters['list.where'][] = 'SUBSTRING_INDEX(a.store_location, ",", -1) > ' . $storeBounds['west'] . ')';

					$filters['list.where'][] = '!(SUBSTRING_INDEX(a.store_location, ",", 1) < ' . $storeBoundsMin['north'];
					$filters['list.where'][] = 'SUBSTRING_INDEX(a.store_location, ",", 1) > ' . $storeBoundsMin['south'];
					$filters['list.where'][] = 'SUBSTRING_INDEX(a.store_location, ",", -1) < ' . $storeBoundsMin['east'];
					$filters['list.where'][] = 'SUBSTRING_INDEX(a.store_location, ",", -1) > ' . $storeBoundsMin['west'] . ')';
				}
				elseif ($hlParams->get('hyperlocal_type') == 2 && isset($hyperlocal['id']))
				{
					// Filter by shippable location
					$location      = $this->getLocation($hyperlocal['id']);
					$locationWhere = array();

					foreach ($location as $type => $loc)
					{
						$subQuery = $this->getLocationSubQuery($type);

						$filters['list.join'][] = array(
							'left',
							'(' . $subQuery->__toString() . ') AS hl' . $type . ' ON hl' . $type . '.seller_uid = a.user_id',
						);

						$locationWhere[] = 'hl' . $type . '.gl_id = ' . $location[$type];
					}

					if (!empty($locationWhere))
					{
						$filters['list.where'][] = '(' . implode(' OR ', $locationWhere) . ')';
					}

					$filters['list.group'][] = 'a.user_id';
				}

				if ($addrMatching == 2 && !empty($addrMatchingArea) && isset($hyperlocal[$addrMatchingArea]))
				{
					$filters['list.join'][]  = array(
						'left',
						$this->db->qn('#__sellacious_geolocation', 'g') . ' ON (g.record_id = a.user_id AND g.context = ' . $this->db->q('seller') . ')',
					);
					$filters['list.where'][] = 'g.' . $addrMatchingArea . ' = ' . $this->db->q($hyperlocal[$addrMatchingArea]);
				}
			}
		}
	}

	/**
	 * Method to manipulate query after building it
	 *
	 * @param   string           $context  The context
	 * @param   \JDatabaseQuery  $query    The Query
	 *
	 * @throws  \Exception
	 * @since   1.6.0
	 */
	public function onAfterBuildQuery($context, &$query)
	{
		$app = JFactory::getApplication();

		$module_contexts = array(
			'com_sellacious.module.latest',
			'com_sellacious.module.sellerproducts',
			'com_sellacious.module.recentlyviewedproducts',
			'com_sellacious.module.specialcatsproducts',
			'com_sellacious.module.relatedproducts',
			'com_sellacious.module.products',
			'com_sellacious.module.bestselling',
		);

		if ($context == 'com_sellacious.model.search' && $app->isClient('site'))
		{
			$hyperlocal = $app->getUserState('mod_sellacious_hyperlocal.user.location', array());

			$hlConfig = ConfigHelper::getInstance('plg_system_sellacioushyperlocal');
			$hlParams = $hlConfig->getParams();

			if (empty($hlParams->get('hyperlocal_type')))
			{
				return;
			}

			$productBounds    = isset($hyperlocal['product_bounds']) ? $hyperlocal['product_bounds'] : array();
			$productBoundsMin = isset($hyperlocal['product_bounds_min']) ? $hyperlocal['product_bounds_min'] : array();

			if ($hlParams->get('hyperlocal_type') == 1 && array_filter($productBounds) && array_filter($productBoundsMin))
			{
				$query->where('((CASE WHEN a.product_location != \'\' THEN SUBSTRING_INDEX(a.product_location, ",", 1) < ' . $productBounds['north'] . ' ELSE SUBSTRING_INDEX(a.store_location, ",", 1) < ' . $productBounds['north'] . ' END)');
				$query->where('(CASE WHEN a.product_location != \'\' THEN SUBSTRING_INDEX(a.product_location, ",", 1) > ' . $productBounds['south'] . ' ELSE SUBSTRING_INDEX(a.store_location, ",", 1) > ' . $productBounds['south'] . ' END)');
				$query->where('(CASE WHEN a.product_location != \'\' THEN SUBSTRING_INDEX(a.product_location, ",", -1) < ' . $productBounds['east'] . ' ELSE SUBSTRING_INDEX(a.store_location, ",", -1) < ' . $productBounds['east'] . ' END)');
				$query->where('(CASE WHEN a.product_location != \'\' THEN SUBSTRING_INDEX(a.product_location, ",", -1) > ' . $productBounds['west'] . ' ELSE SUBSTRING_INDEX(a.store_location, ",", -1) > ' . $productBounds['west'] . ' END))');

				$query->where('!((CASE WHEN a.product_location != \'\' THEN SUBSTRING_INDEX(a.product_location, ",", 1) < ' . $productBoundsMin['north'] . ' ELSE SUBSTRING_INDEX(a.store_location, ",", 1) < ' . $productBoundsMin['north'] . ' END)');
				$query->where('(CASE WHEN a.product_location != \'\' THEN SUBSTRING_INDEX(a.product_location, ",", 1) > ' . $productBoundsMin['south'] . ' ELSE SUBSTRING_INDEX(a.store_location, ",", 1) > ' . $productBoundsMin['south'] . ' END)');
				$query->where('(CASE WHEN a.product_location != \'\' THEN SUBSTRING_INDEX(a.product_location, ",", -1) < ' . $productBoundsMin['east'] . ' ELSE SUBSTRING_INDEX(a.store_location, ",", -1) < ' . $productBoundsMin['east'] . ' END)');
				$query->where('(CASE WHEN a.product_location != \'\' THEN SUBSTRING_INDEX(a.product_location, ",", -1) > ' . $productBoundsMin['west'] . ' ELSE SUBSTRING_INDEX(a.store_location, ",", -1) > ' . $productBoundsMin['west'] . ' END))');
			}
			elseif ($hlParams->get('hyperlocal_type') == 2 && isset($hyperlocal['id']))
			{
				// Filter by shippable location
				$location      = $this->getLocation($hyperlocal['id']);
				$locationWhere = array();

				foreach ($location as $type => $loc)
				{
					$subQuery = $this->getLocationSubQuery($type);

					$query->join('left', '(' . $subQuery->__toString() . ') AS hl' . $type . ' ON hl' . $type . '.seller_uid = a.seller_uid');
					$locationWhere[] = 'hl' . $type . '.gl_id = ' . $location[$type];
				}

				if (!empty($locationWhere))
				{
					$query->where('(' . implode(' OR ', $locationWhere) . ')');
				}
			}
		}
		elseif ($context == 'com_sellacious.model.products' && $app->isClient('site'))
		{
			$hyperlocal = $app->getUserState('mod_sellacious_hyperlocal.user.location', array());

			$hlConfig = ConfigHelper::getInstance('plg_system_sellacioushyperlocal');
			$hlParams = $hlConfig->getParams();

			if (empty($hlParams->get('hyperlocal_type')))
			{
				return;
			}

			$productBounds    = isset($hyperlocal['product_bounds']) ? $hyperlocal['product_bounds'] : array();
			$productBoundsMin = isset($hyperlocal['product_bounds_min']) ? $hyperlocal['product_bounds_min'] : array();
			$addrMatching     = isset($hyperlocal['address_matching']) ? $hyperlocal['address_matching'] : '';
			$addrMatchingArea = isset($hyperlocal['address_matching_area']) ? $hyperlocal['address_matching_area'] : '';

			if ($addrMatching == 2 && !empty($addrMatchingArea) && isset($hyperlocal[$addrMatchingArea]))
			{
				$query->join('LEFT', $this->db->qn('#__sellacious_product_sellers', 'psx') . ' ON (psx.product_id = a.product_id AND psx.seller_uid = a.seller_uid)');
				$query->join('LEFT', $this->db->qn('#__sellacious_geolocation', 'g') . ' ON (g.record_id = psx.id AND g.context = ' . $this->db->q('product_sellers') . ')');
				$query->join('LEFT', $this->db->qn('#__sellacious_geolocation', 'h') . ' ON (h.record_id = a.seller_uid AND h.context = ' . $this->db->q('seller') . ')');
				$query->where('(CASE WHEN ' . $this->db->qn('g.' . $addrMatchingArea) . ' = ' . $this->db->q($hyperlocal[$addrMatchingArea]) . ' THEN 1 WHEN ' . $this->db->qn('h.' . $addrMatchingArea) . ' = ' . $this->db->q($hyperlocal[$addrMatchingArea]) . ' THEN 1 ELSE 0 END)');
			}
			elseif ($hlParams->get('hyperlocal_type') == 1 && array_filter($productBounds) && array_filter($productBoundsMin))
			{
				$query->where('((CASE WHEN a.product_location != \'\' THEN SUBSTRING_INDEX(a.product_location, ",", 1) < ' . $productBounds['north'] . ' ELSE SUBSTRING_INDEX(a.store_location, ",", 1) < ' . $productBounds['north'] . ' END)');
				$query->where('(CASE WHEN a.product_location != \'\' THEN SUBSTRING_INDEX(a.product_location, ",", 1) > ' . $productBounds['south'] . ' ELSE SUBSTRING_INDEX(a.store_location, ",", 1) > ' . $productBounds['south'] . ' END)');
				$query->where('(CASE WHEN a.product_location != \'\' THEN SUBSTRING_INDEX(a.product_location, ",", -1) < ' . $productBounds['east'] . ' ELSE SUBSTRING_INDEX(a.store_location, ",", -1) < ' . $productBounds['east'] . ' END)');
				$query->where('(CASE WHEN a.product_location != \'\' THEN SUBSTRING_INDEX(a.product_location, ",", -1) > ' . $productBounds['west'] . ' ELSE SUBSTRING_INDEX(a.store_location, ",", -1) > ' . $productBounds['west'] . ' END))');

				$query->where('!((CASE WHEN a.product_location != \'\' THEN SUBSTRING_INDEX(a.product_location, ",", 1) < ' . $productBoundsMin['north'] . ' ELSE SUBSTRING_INDEX(a.store_location, ",", 1) < ' . $productBoundsMin['north'] . ' END)');
				$query->where('(CASE WHEN a.product_location != \'\' THEN SUBSTRING_INDEX(a.product_location, ",", 1) > ' . $productBoundsMin['south'] . ' ELSE SUBSTRING_INDEX(a.store_location, ",", 1) > ' . $productBoundsMin['south'] . ' END)');
				$query->where('(CASE WHEN a.product_location != \'\' THEN SUBSTRING_INDEX(a.product_location, ",", -1) < ' . $productBoundsMin['east'] . ' ELSE SUBSTRING_INDEX(a.store_location, ",", -1) < ' . $productBoundsMin['east'] . ' END)');
				$query->where('(CASE WHEN a.product_location != \'\' THEN SUBSTRING_INDEX(a.product_location, ",", -1) > ' . $productBoundsMin['west'] . ' ELSE SUBSTRING_INDEX(a.store_location, ",", -1) > ' . $productBoundsMin['west'] . ' END))');
			}
			elseif ($hlParams->get('hyperlocal_type') == 2 && isset($hyperlocal['id']))
			{
				// Filter by shippable location
				$location      = $this->getLocation($hyperlocal['id']);
				$locationWhere = array();

				foreach ($location as $type => $loc)
				{
					$subQuery = $this->getLocationSubQuery($type);

					$query->join('left', '(' . $subQuery->__toString() . ') AS hl' . $type . ' ON hl' . $type . '.seller_uid = a.seller_uid');
					$locationWhere[] = 'hl' . $type . '.gl_id = ' . $location[$type];
				}

				if (!empty($locationWhere))
				{
					$query->where('(' . implode(' OR ', $locationWhere) . ')');
				}
			}

			$view = $app->input->get('view');

			if ($view != 'store')
			{
				$model   = JModelLegacy::getInstance('Products', 'SellaciousModel');
				$date    = JFactory::getDate();
				$now     = $date->format('H:i:s');
				$weekDay = $date->format('N') - 1;

				// Filter by open stores
				$openStores = $model->getState('filter.show_open_stores', 0);

				if ($openStores)
				{
					$query->join('left', $this->db->qn('#__sellacious_seller_timings', 'to') . ' ON to.seller_uid = a.seller_uid AND to.week_day = ' . $weekDay . ' AND to.state = 1 AND to.type = ' . $this->db->quote('timings'));
					$query->where('to.from_time <= ' . $this->db->quote($now) . ' AND to.to_time >= ' . $this->db->quote($now));
				}

				// Filter by store delivery availability
				$delivery = $model->getState('filter.delivery_available', 0);

				if ($delivery)
				{
					$query->join('left', $this->db->qn('#__sellacious_seller_timings', 'td') . ' ON td.seller_uid = a.seller_uid AND td.week_day = ' . $weekDay . ' AND td.state = 1 AND td.type = ' . $this->db->quote('delivery_hours'));
					$query->where('td.from_time <= ' . $this->db->quote($now) . ' AND td.to_time >= ' . $this->db->quote($now));
				}

				// Filter by store pickup availability
				$pickup = $model->getState('filter.pickup_available', 0);

				if ($pickup)
				{
					$query->join('left', $this->db->qn('#__sellacious_seller_timings', 'tp') . ' ON tp.seller_uid = a.seller_uid AND tp.week_day = ' . $weekDay . ' AND tp.state = 1 AND tp.type = ' . $this->db->quote('pickup_hours'));
					$query->where('tp.from_time <= ' . $this->db->quote($now) . ' AND tp.to_time >= ' . $this->db->quote($now));
				}
			}
		}
		elseif (in_array($context, $module_contexts))
		{
			$hyperlocal = $app->getUserState('mod_sellacious_hyperlocal.user.location', array());

			$hlConfig = ConfigHelper::getInstance('plg_system_sellacioushyperlocal');
			$hlParams = $hlConfig->getParams();

			if (empty($hlParams->get('hyperlocal_type')))
			{
				return;
			}

			$productBounds    = isset($hyperlocal['product_bounds']) ? $hyperlocal['product_bounds'] : array();
			$productBoundsMin = isset($hyperlocal['product_bounds_min']) ? $hyperlocal['product_bounds_min'] : array();
			$addrMatching     = isset($hyperlocal['address_matching']) ? $hyperlocal['address_matching'] : '';
			$addrMatchingArea = isset($hyperlocal['address_matching_area']) ? $hyperlocal['address_matching_area'] : '';

			if ($addrMatching == 2 && !empty($addrMatchingArea) && isset($hyperlocal[$addrMatchingArea]))
			{
				$query->join('LEFT', $this->db->qn('#__sellacious_product_sellers', 'psx') . ' ON (psx.product_id = a.product_id AND psx.seller_uid = a.seller_uid)');
				$query->join('LEFT', $this->db->qn('#__sellacious_geolocation', 'g') . ' ON (g.record_id = psx.id AND g.context = ' . $this->db->q('product_sellers') . ')');
				$query->join('LEFT', $this->db->qn('#__sellacious_geolocation', 'h') . ' ON (h.record_id = a.seller_uid AND h.context = ' . $this->db->q('seller') . ')');
				$query->where('(CASE WHEN ' . $this->db->qn('g.' . $addrMatchingArea) . ' = ' . $this->db->q($hyperlocal[$addrMatchingArea]) . ' THEN 1 WHEN ' . $this->db->qn('h.' . $addrMatchingArea) . ' = ' . $this->db->q($hyperlocal[$addrMatchingArea]) . ' THEN 1 ELSE 0 END)');
			}
			elseif ($hlParams->get('hyperlocal_type') == 1 && array_filter($productBounds) && array_filter($productBoundsMin))
			{
				$query->where('((CASE WHEN a.product_location != \'\' THEN SUBSTRING_INDEX(a.product_location, ",", 1) < ' . $productBounds['north'] . ' ELSE SUBSTRING_INDEX(a.store_location, ",", 1) < ' . $productBounds['north'] . ' END)');
				$query->where('(CASE WHEN a.product_location != \'\' THEN SUBSTRING_INDEX(a.product_location, ",", 1) > ' . $productBounds['south'] . ' ELSE SUBSTRING_INDEX(a.store_location, ",", 1) > ' . $productBounds['south'] . ' END)');
				$query->where('(CASE WHEN a.product_location != \'\' THEN SUBSTRING_INDEX(a.product_location, ",", -1) < ' . $productBounds['east'] . ' ELSE SUBSTRING_INDEX(a.store_location, ",", -1) < ' . $productBounds['east'] . ' END)');
				$query->where('(CASE WHEN a.product_location != \'\' THEN SUBSTRING_INDEX(a.product_location, ",", -1) > ' . $productBounds['west'] . ' ELSE SUBSTRING_INDEX(a.store_location, ",", -1) > ' . $productBounds['west'] . ' END))');

				$query->where('!((CASE WHEN a.product_location != \'\' THEN SUBSTRING_INDEX(a.product_location, ",", 1) < ' . $productBoundsMin['north'] . ' ELSE SUBSTRING_INDEX(a.store_location, ",", 1) < ' . $productBoundsMin['north'] . ' END)');
				$query->where('(CASE WHEN a.product_location != \'\' THEN SUBSTRING_INDEX(a.product_location, ",", 1) > ' . $productBoundsMin['south'] . ' ELSE SUBSTRING_INDEX(a.store_location, ",", 1) > ' . $productBoundsMin['south'] . ' END)');
				$query->where('(CASE WHEN a.product_location != \'\' THEN SUBSTRING_INDEX(a.product_location, ",", -1) < ' . $productBoundsMin['east'] . ' ELSE SUBSTRING_INDEX(a.store_location, ",", -1) < ' . $productBoundsMin['east'] . ' END)');
				$query->where('(CASE WHEN a.product_location != \'\' THEN SUBSTRING_INDEX(a.product_location, ",", -1) > ' . $productBoundsMin['west'] . ' ELSE SUBSTRING_INDEX(a.store_location, ",", -1) > ' . $productBoundsMin['west'] . ' END))');
			}
			elseif ($hlParams->get('hyperlocal_type') == 2 && isset($hyperlocal['id']))
			{
				// Filter by shippable location
				$location      = $this->getLocation($hyperlocal['id']);
				$locationWhere = array();

				foreach ($location as $type => $loc)
				{
					$subQuery = $this->getLocationSubQuery($type);

					$query->join('left', '(' . $subQuery->__toString() . ') AS hl' . $type . ' ON hl' . $type . '.seller_uid = a.seller_uid');
					$locationWhere[] = 'hl' . $type . '.gl_id = ' . $location[$type];
				}

				if (!empty($locationWhere))
				{
					$query->where('(' . implode(' OR ', $locationWhere) . ')');
				}
			}
		}
		elseif ($context == 'com_sellacious.model.stores' && $app->isClient('site'))
		{
			$hyperlocal = $app->getUserState('mod_sellacious_hyperlocal.user.location', array());

			$hlConfig = ConfigHelper::getInstance('plg_system_sellacioushyperlocal');
			$hlParams = $hlConfig->getParams();

			if (empty($hlParams->get('hyperlocal_type')))
			{
				return;
			}

			$storeBounds      = isset($hyperlocal['store_bounds']) ? $hyperlocal['store_bounds'] : array();
			$storeBoundsMin   = isset($hyperlocal['store_bounds_min']) ? $hyperlocal['store_bounds_min'] : array();
			$addrMatching     = isset($hyperlocal['address_matching']) ? $hyperlocal['address_matching'] : '';
			$addrMatchingArea = isset($hyperlocal['address_matching_area']) ? $hyperlocal['address_matching_area'] : '';


			if ($hlParams->get('hyperlocal_type') == 1 && array_filter($storeBounds) && array_filter($storeBoundsMin))
			{
				$query->where('(SUBSTRING_INDEX(a.store_location, ",", 1) < ' . $storeBounds['north']);
				$query->where('SUBSTRING_INDEX(a.store_location, ",", 1) > ' . $storeBounds['south']);
				$query->where('SUBSTRING_INDEX(a.store_location, ",", -1) < ' . $storeBounds['east']);
				$query->where('SUBSTRING_INDEX(a.store_location, ",", -1) > ' . $storeBounds['west'] . ')');

				$query->where('!(SUBSTRING_INDEX(a.store_location, ",", 1) < ' . $storeBoundsMin['north']);
				$query->where('SUBSTRING_INDEX(a.store_location, ",", 1) > ' . $storeBoundsMin['south']);
				$query->where('SUBSTRING_INDEX(a.store_location, ",", -1) < ' . $storeBoundsMin['east']);
				$query->where('SUBSTRING_INDEX(a.store_location, ",", -1) > ' . $storeBoundsMin['west'] . ')');
			}
			elseif ($hlParams->get('hyperlocal_type') == 2 && isset($hyperlocal['id']))
			{
				// Filter by shippable location
				$location      = $this->getLocation($hyperlocal['id']);
				$locationWhere = array();

				foreach ($location as $type => $loc)
				{
					$subQuery = $this->getLocationSubQuery($type);

					$query->join(
						'left',
						'(' . $subQuery->__toString() . ') AS hl' . $type . ' ON hl' . $type . '.seller_uid = a.user_id'
					);
					$locationWhere[] = 'hl' . $type . '.gl_id = ' . $location[$type];
				}

				if (!empty($locationWhere))
				{
					$query->where('(' . implode(' OR ', $locationWhere) . ')');
				}
			}

			$model   = JModelLegacy::getInstance('Stores', 'SellaciousModel');
			$date    = JFactory::getDate();
			$now     = $date->format('H:i:s');
			$weekDay = $date->format('N') - 1;

			// Filter by open stores
			$openStores = $model->getState('filter.show_open_stores', 0);

			if ($openStores)
			{
				$query->join('left', $this->db->qn('#__sellacious_seller_timings', 'to') . ' ON to.seller_uid = a.user_id AND to.week_day = ' . $weekDay . ' AND to.state = 1 AND to.type = ' . $this->db->quote('timings'));
				$query->where('to.from_time <= ' . $this->db->quote($now) . ' AND to.to_time >= ' . $this->db->quote($now));
			}

			// Filter by store delivery availability
			$delivery = $model->getState('filter.delivery_available', 0);

			if ($delivery)
			{
				$query->join('left', $this->db->qn('#__sellacious_seller_timings', 'td') . ' ON td.seller_uid = a.user_id AND td.week_day = ' . $weekDay . ' AND td.state = 1 AND td.type = ' . $this->db->quote('delivery_hours'));
				$query->where('td.from_time <= ' . $this->db->quote($now) . ' AND td.to_time >= ' . $this->db->quote($now));
			}

			// Filter by store pickup availability
			$pickup = $model->getState('filter.pickup_available', 0);

			if ($pickup)
			{
				$query->join('left', $this->db->qn('#__sellacious_seller_timings', 'tp') . ' ON tp.seller_uid = a.user_id AND tp.week_day = ' . $weekDay . ' AND tp.state = 1 AND tp.type = ' . $this->db->quote('pickup_hours'));
				$query->where('tp.from_time <= ' . $this->db->quote($now) . ' AND tp.to_time >= ' . $this->db->quote($now));
			}

			if ($addrMatching == 2 && !empty($addrMatchingArea) && isset($hyperlocal[$addrMatchingArea]))
			{
				$query->join('LEFT', $this->db->qn('#__sellacious_geolocation', 'g') . ' ON (g.record_id = a.user_id AND g.context = ' . $this->db->q('seller') . ')');
				$query->where($this->db->qn('g.' . $addrMatchingArea) . ' = ' . $this->db->q($hyperlocal[$addrMatchingArea]));
			}

			$query->group('a.user_id');
		}
	}

	/**
	 * Method to get shipping sellers
	 *
	 * @param    string  $context     The context
	 * @param    array   $sellerUids  The seller user ids
	 *
	 * @throws   \Exception
	 *
	 * @since    1.6.0
	 */
	public function onLoadShippingSellers($context, &$sellerUids)
	{
		$app = JFactory::getApplication();

		if ($context == 'com_sellacious.products' && $app->isClient('site'))
		{
			$hlConfig = ConfigHelper::getInstance('plg_system_sellacioushyperlocal');
			$hlParams = $hlConfig->getParams();

			if (empty($hlParams->get('hyperlocal_type')))
			{
				return;
			}

			if ($hlParams->get('hyperlocal_type') == 1)
			{
				$shippable_coordinates = $app->getUserState('filter.shippable_coordinates', array());

				$shipped_by        = $this->helper->config->get('shipped_by');
				$seller_preferable = $this->helper->config->get('shippable_location_by_seller');

				$key = $hlParams->get('google_api_key', '');

				// Seller cannot set preference, meaning allow all as global test already passed
				if ($shipped_by != 'seller' || !$seller_preferable || empty($shippable_coordinates) || empty($key))
				{
					return;
				}

				$sellerDistances = $this->getSellerDistances($shippable_coordinates);

				if (!$sellerDistances)
				{
					return;
				}

				// Get Store distance (radius)
				$storeRadius = $hlParams->get('product_radius');
				$meterUnit   = $this->helper->unit->loadResult(array(
					'list.select' => 'a.id',
					'list.where'  => array(
						'a.title = ' . $this->db->quote('Meter'),
						'a.symbol = ' . $this->db->quote('m'),
						'a.unit_group = ' . $this->db->quote('Length'),
					),
				));
				$meterUnit   = $meterUnit ?: null;

				$storeDistance = 0;

				if (isset($storeRadius->m))
				{
					$storeDistance = $this->helper->unit->convert($storeRadius->m ?: 0, $storeRadius->u, $meterUnit);
				}

				if (!is_array($sellerUids))
				{
					$sellerUids = array();
				}

				foreach ($sellerDistances as $sellerDistance)
				{
					// Get shipping distance (radius)
					$shippingRadius   = json_decode($sellerDistance->seller_shipping_distance);
					$shippingDistance = $this->helper->unit->convert($shippingRadius->m ?: 0, $shippingRadius->u, $meterUnit);

					// If the distance between the two locations is less than the sum of the two radii, then they overlap/intersect/fall inside
					if ($sellerDistance->distance < ($shippingDistance + $storeDistance))
					{
						$sellerUids[] = $sellerDistance->seller_uid;
					}
				}

				if (empty($sellerUids))
				{
					$sellerUids = false;
				}
			}
		}
	}

	/**
	 * Set pricing plan before adding cart item
	 *
	 * @param   string                $context     The context for the data
	 * @param   Sellacious\Cart\Item  $item        The cart item
	 *
	 * @param   string                $identifier  The Product Code/Identifier
	 * @param   array                 $attributes  Product Attributes
	 *
	 * @return  void
	 *
	 * @since   1.7.0
	 */
	public function onBeforeProcessCartItem($context, &$item, $identifier, &$attributes)
	{
		if ($context == 'com_sellacious.cart.internal')
		{
			$options       = $attributes['options'] ?: array();
			$config        = ConfigHelper::getInstance('plg_system_sellacioushyperlocal');
			$hlParams      = $config->getParams();
			$timeSelection = $hlParams->get('delivery_time_selection', 0);

			if (isset($options['delivery_date']) && $timeSelection == 3)
			{
				$date          = str_replace(array('AM', 'PM'), '', $options['delivery_date']);
				$user          = JFactory::getUser();
				$now           = JFactory::getDate();
				$hyperlocal    = $this->app->getUserState('mod_sellacious_hyperlocal.user.location', array());
				$timezone      = isset($hyperlocal['timezone']) && !empty($hyperlocal['timezone']) ? $hyperlocal['timezone'] : $this->app->get('offset', 'UTC');

				$attributes['options']['delivery_date'] = $date;

				$this->helper->product->parseCode($identifier, $productId, $variantId, $sellerUid);

				if ($user->id)
				{
					$timezone = $user->getParam('timezone', $timezone);
				}

				$timezone = new DateTimeZone($timezone);
				$now->setTimezone($timezone);

				$slotDate = $date != '' ? JFactory::getDate($date) : $now;
				$weekDay  = $slotDate->format('w', true);
				$weekDay  = $weekDay == 0 ? 6 : ($weekDay - 1);

				// Get Delivery Timing of selected day
				$timings  = $this->getTiming($sellerUid, $weekDay);
				$fromTime = $timings->from_time ? $timings->from_time : '00:00:00';
				$toTime   = $timings->to_time;

				if ($slotDate->format('Y-m-d', true) == $now->format('Y-m-d', true))
				{
					$fromTime = $now->format('H:i:s');
				}

				$fromTime = JFactory::getDate($slotDate->format('Y-m-d', true) . ' ' . $fromTime);
				$toTime   = JFactory::getDate($slotDate->format('Y-m-d', true) . ' ' . $toTime);

				if (strtotime($slotDate) < strtotime($fromTime) || strtotime($slotDate) > strtotime($toTime))
				{
					throw new Exception(JText::_('PLG_SYSTEM_SELLACIOUSHYPERLOCAL_SLOT_DATE_UNAVAILABLE'));
				}
			}
		}
	}

	/**
	 * Method to save order delivery date
	 *
	 * @param    string          $context  The context
	 * @param    \stdClass       $order    The order object
	 * @param    \stdClass[]     $products The order products
	 * @param    Sellacious\Cart $cart     The cart object
	 *
	 * @return   void
	 *
	 * @throws   \Exception
	 *
	 * @since    1.7.0
	 */
	public function onAfterPlaceOrder($context, $order, $products, $cart)
	{
		if ($context == 'com_sellacious.cart')
		{
			$config        = ConfigHelper::getInstance('plg_system_sellacioushyperlocal');
			$hlParams      = $config->getParams();
			$timeSelection = $hlParams->get('delivery_time_selection', 0);
			$items         = $cart->getItems();

			foreach ($items as $item)
			{
				$options = $item->getParam('options');

				if (isset($options->delivery_date))
				{
					$orderItem = null;
					$this->helper->product->parseCode($item->getUid(), $product_id, $variant_id, $seller_uid);

					foreach ($products as $product)
					{
						if ($item->getUid() == $product->item_uid)
						{
							$orderItem = $product;
							break;
						}
					}

					if ($timeSelection == 3)
					{
						$fromDate = JFactory::getDate(str_replace(array('AM', 'PM'), '', $options->delivery_date))->format('Y-m-d H:i:s');
						$toDate   = $fromDate;
						$fullDay  = 0;
					}
					else
					{
						$fromDate = $options->delivery_date;
						$toDate   = $fromDate;
						$fromTime = '00:00:00';
						$toTime   = '00:00:00';
						$fullDay  = 0;

						if (isset($options->delivery_slot))
						{
							$delivery_slot = array_filter(explode(' - ', $options->delivery_slot));

							if (!empty($delivery_slot))
							{
								$fromTime = JFactory::getDate($delivery_slot[0])->format('H:i:s');
								$toTime   = JFactory::getDate($delivery_slot[1])->format('H:i:s');
							}
							else if (!empty($options->delivery_slot))
							{
								$fromTime = JFactory::getDate($options->delivery_slot)->format('H:i:s');
								$toTime   = $fromTime;
							}
						}
						else
						{
							$fullDay      = 1;
							$weekDay      = JFactory::getDate($fromDate)->format('w');
							$weekDay      = $weekDay == 0 ? 6 : ($weekDay - 1);
							$deliveryHour = $this->getTiming($seller_uid, $weekDay);
							$fromTime     = $deliveryHour->from_time;
							$toTime       = $deliveryHour->to_time;
						}

						$fromDate .= ' ' . $fromTime;
						$toDate   .= ' ' . $toTime;
					}

					$table = JTable::getInstance('OrderDeliverySlot', 'SellaciousTable');
					$data  = array(
						'order_item_id'  => $orderItem->id,
						'slot_from_time' => $fromDate,
						'slot_to_time'   => $toDate,
						'full_day'       => $fullDay,
					);

					$table->bind($data);
					$table->check();
					$table->store();
				}
			}
		}
	}

	/**
	 * Method to update order delivery date status on payment
	 *
	 * @param   string    $context The context
	 * @param   \stdClass $payment The payment object
	 *
	 * @return  void
	 *
	 * @throws  \Exception
	 *
	 * @since   1.7.0
	 */
	public function onAfterOrderPayment($context, $payment)
	{
		if ($context == 'com_sellacious.order' && $payment->state >= 1)
		{
			$orderId    = $payment->order_id;
			$orderItems = $this->helper->order->getOrderItems($orderId);

			foreach ($orderItems as $orderItem)
			{
				$table = JTable::getInstance('OrderDeliverySlot', 'SellaciousTable');
				$table->load(array('order_item_id' => $orderItem->id));

				if ($table->get('id'))
				{
					$table->set('status', 1);
					$table->check();
					$table->store();
				}

				$this->handleStock($orderId, $orderItem->id, $orderItem->item_uid);
			}
		}
	}

	/**
	 * Handle stock after order status change
	 *
	 * @param   string  $context   The calling context
	 * @param   int     $order_id  The order id
	 *
	 * @throws  \Exception
	 *
	 * @since 1.7.0
	 */
	public function onAfterOrderChange($context, $order_id)
	{
		if ($context == 'com_sellacious.order')
		{
			$orderItems = $this->helper->order->getOrderItems($order_id, 'a.id, a.item_uid');
			$lastItemUid = $this->helper->order->loadResult(array(
				'list.select' => 'a.item_uid',
				'list.from'   => '#__sellacious_order_status',
				'list.where'  => array(
					'a.order_id = ' . $order_id,
					'a.item_uid <> ""',
				),
				'list.order'  => 'a.id DESC',
			));

			foreach ($orderItems as $orderItem)
			{
				$item_uid = $orderItem->item_uid;

				if ($item_uid == $lastItemUid)
				{
					$this->handleStock($order_id, $orderItem->id, $item_uid);
				}
			}
		}
	}

	/**
	 * Method to delete delivery data with respect to the order
	 *
	 * @param   string  $context The context
	 * @param   JTable  $order   The order table object
	 * @param   integer $all     Whether to delete all or without transactions.
	 *
	 * @since   1.7.0
	 */
	public function onContentAfterDelete($context, $order, $all = 0)
	{
		if ($context == 'com_sellacious.order')
		{
			$pk = $order->get('id');

			$query = 'DELETE a FROM #__sellacious_order_delivery_slot AS a ';
			$query .= 'INNER JOIN #__sellacious_order_items AS b ON b.id = a.order_item_id ';
			$query .= 'WHERE b.order_id = ' . $pk;

			$this->db->setQuery($query)->execute();
		}
	}

	/**
	 * Method to add replacements related to delivery to email template
	 *
	 * @param   string   $context      The context for the data
	 * @param   Registry $data         The data object
	 * @param   array    $replacements Array of replacements by code
	 *
	 * @since   1.7.0
	 */
	public function onParseTemplate($context, $data, &$replacements)
	{
		if ($context == 'com_sellacious.email.order.product')
		{
			if (!$data instanceof Registry)
			{
				$data = new Registry($data);
			}

			$orderslot = JTable::getInstance('OrderDeliverySlot', 'SellaciousTable');
			$orderslot->load(array('order_item_id' => $data->get('id')));

			$fromTime = JFactory::getDate($orderslot->get('slot_from_time'));
			$toTime   = JFactory::getDate($orderslot->get('slot_to_time'));

			$values       = array(
				'product_delivery_date' => $fromTime->format('d M, Y'),
				'product_delivery_slot' => $fromTime->format('g:i A') . ' - ' . $toTime->format('g:i A'),
			);
			$replacements = array_merge($replacements, $values);
		}
	}

	/**
	 * Runs on content preparation
	 *
	 * @param   string               $context The context for the data
	 * @param   Sellacious\Cart\Item $item    The cart item
	 * @param   array                $prices  Array of rendered pricing options
	 *
	 * @return  void
	 *
	 * @throws  \Exception
	 * @since   1.7.0
	 *
	 */
	public function onRenderCartPrices($context, $item, &$prices)
	{
		if ($context == 'com_sellacious.cart')
		{
			$options = $item->getParam('options');

			$config   = ConfigHelper::getInstance('plg_system_sellacioushyperlocal');
			$hlParams = $config->getParams();

			$prices[] = $this->renderLayout('default_cartprices', array('options' => $options, 'params' => $hlParams));
		}
	}

	/**
	 * Method to call when product items are processed
	 *
	 * @param   string       $context  The calling context
	 * @param   \stdClass[]  $items    The product items
	 * @param   array        $options  Additional options/parameters
	 *
	 * @throws  \Exception
	 *
	 * @since   1.7.0
	 */
	public function onProcessProducts($context, $items, $options = array())
	{
		if (strpos($context, 'com_sellacious.product') === false && $context != 'com_sellacious.store')
		{
			return;
		}

		$user          = JFactory::getUser();
		$now           = JFactory::getDate();
		$view          = $this->app->input->get('view', '');
		$hlConfig      = ConfigHelper::getInstance('plg_system_sellacioushyperlocal');
		$hlParams      = $hlConfig->getParams();
		$timeSelection = $hlParams->get('delivery_time_selection', 0);

		$hyperlocal = $this->app->getUserState('mod_sellacious_hyperlocal.user.location', array());
		$timezone   = isset($hyperlocal['timezone']) && !empty($hyperlocal['timezone']) ? $hyperlocal['timezone'] : $this->app->get('offset', 'UTC');

		if ($user->id)
		{
			$timezone = $user->getParam('timezone', $timezone);
		}

		$timezone = new DateTimeZone($timezone);
		$now->setTimezone($timezone);

		$contexts = explode('.', $context);

		if (isset($contexts[2]) && !empty($options))
		{
			$moduleParams = new Registry($options);
			$displaySlots = $moduleParams->get('display_delivery_slots', 1);

			if ($displaySlots == 0)
			{
				return;
			}
		}

		$displaySlots = empty($hlParams->toArray()) ? array('categories','products','product','product_modal','store') : (array) $hlParams->get('display_delivery_slots');

		if ($context == 'com_sellacious.products' && $view == 'products' && !in_array('products', $displaySlots))
		{
			return;
		}

		if ($context == 'com_sellacious.products' && $view == 'categories' && !in_array('categories', $displaySlots))
		{
			return;
		}

		if ($context == 'com_sellacious.store' && $view == 'store' && !in_array('store', $displaySlots))
		{
			return;
		}

		foreach ($items as $item)
		{
			$displayData = array('item' => $item);

			$hlTable = JTable::getInstance('SellerHyperlocal', 'SellaciousTable');
			$hlTable->load(array('seller_uid' => $item->seller_uid));
			$params    = new Registry($hlTable->get('params'));
			$slotLimit = $params->get('slot_limit', 0);

			// Check for enabled delivery week days
			$deliveryTimings  = $this->getTimings($item->seller_uid, 'delivery_hours', 1);
			$deliveryWeekDays = ArrayHelper::getColumn($deliveryTimings, 'week_day');

			if ($slotLimit > 0 && $timeSelection >= 1 && !empty($deliveryTimings))
			{
				$disabledDates = array();

				// Dates to select
				$todaySlots = $this->getAvailableSlots($item->id, $item->seller_uid, $now->format('Y-m-d', true), $now->format('H:i:s', true));

				if (empty($todaySlots))
				{
					$disabledDates[] = $now->format('Y-m-d', true);
				}

				$selectedDate = JFactory::getDate($now)->format('Y-m-d 00:00:00', true);

				$unavailableDates = $this->getUnavailableDates($item->id, $item->seller_uid);
				$disabledDates    = array_unique(array_merge($disabledDates, $unavailableDates));

				foreach ($deliveryWeekDays as &$deliveryWeekDay)
				{
					if ($deliveryWeekDay == 6)
					{
						$deliveryWeekDay = 0;
					}
					else
					{
						$deliveryWeekDay += 1;
					}
				}

				$disabledDays = array_values(array_diff(array(0, 1, 2, 3, 4, 5, 6), $deliveryWeekDays));

				// Max Date
				$maxDate = JFactory::getDate('Dec 31')->modify('+1 year');

				$prefix = '';

				if (isset($contexts[2]))
				{
					$prefix = $contexts[2] . '_';
				}

				$displayData['params']         = $hlParams;
				$displayData['max_date']       = $maxDate;
				$displayData['selected_date']  = $selectedDate;
				$displayData['disabled_dates'] = '["' . implode('", "', $disabledDates) . '"]';
				$displayData['disabled_days']  = '[' . implode(', ', $disabledDays) . ']';
				$displayData['context_prefix'] = $prefix;
				$displayData['date_input_id']  = $prefix . 'delivery_date_' . strtolower($item->code);
				$displayData['picker_id']      = $prefix . 'delivery_date_dtp_' . strtolower($item->code);
				$displayData['slot_picker_id'] = $prefix . 'slot_picker_' . strtolower($item->code);
				$displayData['options']        = $options;

				$item->rendered_attributes[] = $this->renderLayout('default_item_attributes', $displayData);
			}
		}
	}

	/**
	 * Ajax function to Purge cache
	 *
	 * @return  void
	 *
	 * @since   1.6.0
	 *
	 * @throws  \Exception
	 */
	public function onAjaxSellacioushyperlocal()
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
				// Purging is the default method
				$this->distanceCache->purgeCache();

				echo new JResponseJson('', JText::_('PLG_SYSTEM_SELLACIOUSHYPERLOCAL_PURGE_SUCCESS'));
			}
		}
		catch (Exception $e)
		{
			echo new JResponseJson($e);
		}

		JFactory::getApplication()->close();
	}

	/**
	 * Ajax function to get slots for timing
	 *
	 * @return array
	 *
	 * @since   1.7.0
	 *
	 * @throws  \Exception
	 */
	public function onAjaxGetSlots()
	{
		$app           = JFactory::getApplication();
		$slotDate      = $app->input->get('slot_date', '');
		$sellerUid     = $app->input->getInt('seller_uid', 0);
		$productId     = $app->input->getInt('product_id', 0);
		$code          = $app->input->getString('code', '');
		$html          = '';
		$hlConfig      = ConfigHelper::getInstance('plg_system_sellacioushyperlocal');
		$timeSelection = $hlConfig->get('delivery_time_selection', 0);

		if ($timeSelection == 2)
		{
			$slotOptions = $this->getAvailableSlots($productId, $sellerUid, $slotDate);

			if (!empty($slotOptions))
			{
				$fieldName   = !empty($code) ? 'delivery_slot_' . strtolower($code) : 'delivery_slot';
				$slotOptions = array_merge(array('' => JText::_('PLG_SYSTEM_SELLACIOUSHYPERLOCAL_SELECT_DELIVERY_SLOT')), $slotOptions);
				$html        = JHtml::_('select.genericlist', $slotOptions, $fieldName, 'class="small-input delivery_slot"', 'id', 'title', null, $fieldName);
			}
		}

		if ($timeSelection == 3)
		{
			$slotDate = JFactory::getDate(str_replace(array('AM', 'PM'), '', $slotDate));
		}
		else
		{
			$slotDate = JFactory::getDate($slotDate);
		}

		$date = array('weekday' => $slotDate->format('D'), 'day' => $slotDate->format('F d'));
		$data = array('date' => $date, 'formatted_date' => $slotDate->format('Y-m-d'), 'html' => $html);

		return $data;
	}

	/**
	 * Method to check whether a module is a sellacious module
	 *
	 * @param   string  $moduleName  The module name in question
	 *
	 * @return  bool
	 *
	 * @since   1.7.1
	 */
	public function matchModule($moduleName)
	{
		$modules = array(
			'mod_sellacious_latestproducts',
			'mod_sellacious_bestsellingproducts',
			'mod_sellacious_products',
			'mod_sellacious_recentlyviewedproducts',
			'mod_sellacious_relatedproducts',
			'mod_sellacious_sellerproducts',
			'mod_sellacious_specialcatsproducts',
		);

		return in_array($moduleName, $modules);
	}

	/**
	 * Method to get seller distances
	 *
	 * @param    array  $shippable_coordinates  Shippable coordinates
	 *
	 * @return   bool|mixed
	 *
	 * @throws   \Exception
	 *
	 * @since    1.6.0
	 */
	public function getSellerDistances($shippable_coordinates)
	{
		$this->distanceCache->setShippableCoordinates($shippable_coordinates);

		$hash = $this->distanceCache->getHashCode();

		if (!$hash)
		{
			return false;
		}

		$distances = $this->distanceCache->getDistances($hash);

		return $distances;
	}

	/**
	 * Method to add timings information for seller
	 *
	 * @param   string                     $context  The context
	 * @param   \Joomla\Registry\Registry  $seller   The seller object
	 * @param   array                      $info     Seller information
	 *
	 * @throws  \Exception
	 *
	 * @since   1.6.0
	 */
	public function onRenderSellerInfo($context, $seller, &$info)
	{
		if ($context == 'com_sellacious.store')
		{
			$hlTable = JTable::getInstance('SellerHyperlocal', 'SellaciousTable');
			$hlTable->load(array('seller_uid' => $seller->get('user_id')));

			$sellerParams = new Registry($hlTable->get('params'));
			$config       = $this->helper->config->loadColumn(array('context' => 'plg_system_sellacioushyperlocal'), 3);

			$params = new Registry();
			$params->loadString(isset($config[0]) ? $config[0] : '');

			$show_store_availability    = $sellerParams->get('show_store_availability', $params->get('show_store_availability', 1));
			$show_delivery_availability = $sellerParams->get('show_delivery_availability', $params->get('show_delivery_availability', 1));
			$show_pickup_availability   = $sellerParams->get('show_pickup_availability', $params->get('show_pickup_availability', 1));

			$sellerTimings = array(
				'timings'        => $this->getTimings($seller->get('user_id'), 'timings', 1),
				'delivery_hours' => $this->getTimings($seller->get('user_id'), 'delivery_hours', 1),
				'pickup_hours'   => $this->getTimings($seller->get('user_id'), 'pickup_hours', 1),
			);

			$availability = array();

			if ($show_store_availability)
			{
				$availability['timings_availability'] = $this->getStoreAvailability($sellerTimings['timings'], $seller->get('user_id'), 'timings');
			}

			if ($show_delivery_availability)
			{
				$availability['delivery_hours_availability'] = $this->getStoreAvailability($sellerTimings['delivery_hours'], $seller->get('user_id'), 'delivery_hours');
			}

			if ($show_pickup_availability)
			{
				$availability['pickup_hours_availability'] = $this->getStoreAvailability($sellerTimings['pickup_hours'], $seller->get('user_id'), 'pickup_hours');
			}

			$data = array(
				'sellerTimings' => $sellerTimings,
				'availability'  => $availability,
				'params'        => $params,
				'sellerParams'  => $sellerParams,
			);

			JHtml::_('stylesheet', 'plg_system_sellacioushyperlocal/layout.store.css', null, true);

			$info[] = $this->renderLayout('default_store', $data);
		}
	}

	/**
	 * Method to show additional order item info
	 *
	 * @param   string    $context The context for the data
	 * @param   \stdClass $item    The order item
	 * @param   array     $info    Array of rendered order item information
	 *
	 * @return  void
	 *
	 * @throws  \Exception
	 *
	 * @since   1.7.0
	 */
	public function onRenderOrderItem($context, $item, &$info)
	{
		if ($context == 'com_sellacious.order')
		{
			$oslot = JTable::getInstance('OrderDeliverySlot', 'SellaciousTable');
			$oslot->load(array('order_item_id' => $item->id));

			if ($oslot->get('id'))
			{
				$info[] = $this->renderLayout('default_orderitem', $oslot->getProperties(1));
			}
		}
	}

	/**
	 * Method to get store availability for the timing
	 *
	 * @param    array   $timings     The Seller timings data
	 * @param    int     $seller_uid  The Seller user id
	 * @param    string  $type        Type of timing (store, delivery, etc.)
	 *
	 * @return   string
	 *
	 * @throws \Exception
	 * @since    1.6.0
	 */
	public function getStoreAvailability($timings, $seller_uid, $type)
	{
		if (empty($timings))
		{
			return JText::_('PLG_SYSTEM_SELLACIOUSHYPERLOCAL_NOT_AVAILABLE_' . strtoupper($type));
		}

		$date     = JFactory::getDate();
		$user     = JFactory::getUser($seller_uid);
		$timezone = '';

		if ($user->id)
		{
			$timezone = $user->id;
		}

		if ($timezone)
		{
			$date = $this->helper->core->fixDate($date->toSql(true), 'UTC', $timezone);
		}

		foreach ($timings as $timing)
		{
			if ($timing['week_day'] == ($date->format('N') - 1))
			{
				// If Timings is 24 hours, then always available
				if ($timing['full_day'] == 1)
				{
					return JText::_('PLG_SYSTEM_SELLACIOUSHYPERLOCAL_' . strtoupper($type) . '_AVAILABLE');
				}

				$from = new DateTime($timing['from_time']);
				$to   = new DateTime($timing['to_time']);

				$now = new DateTime($date->toSql(true));

				if ($from <= $now && $to >= $now)
				{
					return JText::_('PLG_SYSTEM_SELLACIOUSHYPERLOCAL_' . strtoupper($type) . '_AVAILABLE');
				}
				elseif ($now < $from)
				{
					$diff       = $now->diff($from);
					$diffFormat = $diff->format('%i') . ' Minute(s) ';

					if (!empty($diff->format('%h')))
					{
						$diffFormat = $diff->format('%h') . ' Hour(s) ' . $diffFormat;
					}

					return JText::sprintf('PLG_SYSTEM_SELLACIOUSHYPERLOCAL_' . strtoupper($type) . '_AVAILABLE_IN', $diffFormat);
				}
			}
			elseif ($timing['week_day'] > ($date->format('N') - 1) && $timing['state'])
			{
				$diffDays = $timing['week_day'] - ($date->format('N') - 1);
				$date2    = JFactory::getDate($timing['from_time'])->modify('+' . $diffDays . ' day');
				$diff     = $date->diff($date2);

				$diffFormat = $diff->format('%i') . ' Minute(s) ';

				if (!empty($diff->format('%h')))
				{
					$diffFormat = $diff->format('%h') . ' Hour(s) ' . $diffFormat;
				}

				if (!empty($diff->format('%d')))
				{
					$diffFormat = $diff->format('%d') . ' Day(s) ' . $diffFormat;
				}

				return JText::sprintf('PLG_SYSTEM_SELLACIOUSHYPERLOCAL_' . strtoupper($type) . '_AVAILABLE_IN', $diffFormat);
			}
		}

		return JText::_('PLG_SYSTEM_SELLACIOUSHYPERLOCAL_NOT_AVAILABLE_' . strtoupper($type));
	}

	/**
	 * Method to get location attributes
	 *
	 * @param    int  $id  The location id
	 *
	 * @return   array
	 *
	 * @since    1.6.0
	 */
	public function getLocation($id)
	{
		$geoLocation = $this->helper->location->getItem($id);
		$location    = array();

		switch ($geoLocation->type)
		{
			case 'country':
				$location['country'] = $id;
				break;
			case 'state':
				$location['state']   = $id;
				$location['country'] = $geoLocation->country_id;
				break;
			case 'district':
				$location['district'] = $id;
				$location['state']    = $geoLocation->state_id;
				$location['country']  = $geoLocation->country_id;
				break;
			case 'zip':
				$location['zip']      = $id;
				$location['district'] = $geoLocation->district_id;
				$location['state']    = $geoLocation->state_id;
				$location['country']  = $geoLocation->country_id;
				break;
		}

		return $location;
	}

	/**
	 * Method to get Shippable location subquery
	 *
	 * @param   string  $type  The location type
	 *
	 * @return  \JDatabaseQuery
	 *
	 * @since   1.6.0
	 */
	public function getLocationSubQuery($type)
	{
		$subQuery = $this->db->getQuery(true);
		$subQuery->select('a.gl_id,a.seller_uid');
		$subQuery->from($this->db->qn('#__sellacious_seller_shippable', 'a'));
		$subQuery->join('INNER', $this->db->qn('#__sellacious_locations', 'b') . ' ON b.id = a.gl_id');
		$subQuery->where('b.type = ' . $this->db->quote($type));

		return $subQuery;
	}

	/**
	 * Method to get seller Timings
	 *
	 * @param   int     $sellerUid   The seller user id
	 * @param   string  $type        The type of timing (store, delivery, etc.)
	 * @param   int     $activeOnly  Whether to get all timings or only published
	 *
	 * @return  array
	 *
	 * @since   1.6.0
	 */
	public function getTimings($sellerUid, $type, $activeOnly = 0)
	{
		$query = $this->db->getQuery(true);
		$query->select('a.week_day, a.full_day, a.from_time, a.to_time, a.state');
		$query->from($this->db->qn('#__sellacious_seller_timings', 'a'));
		$query->where('a.seller_uid = ' . (int) $sellerUid);
		$query->where('a.type = ' . $this->db->quote($type));

		if ($activeOnly)
		{
			$query->where('a.state = 1');
		}

		$this->db->setQuery($query);

		$timings = $this->db->loadAssocList();

		return $timings;
	}

	/**
	 * Method to get timing of particular week day
	 *
	 * @param   int     $sellerUid  The seller user id
	 * @param   int     $weekDay    The day number of the week
	 * @param   string  $type       Type of timing
	 *
	 * @return \stdClass
	 *
	 * @since 1.7.0
	 */
	public function getTiming($sellerUid, $weekDay = 0, $type = 'delivery_hours')
	{
		$query = $this->db->getQuery(true);
		$query->select('a.from_time, a.to_time, a.full_day');
		$query->from($this->db->qn('#__sellacious_seller_timings', 'a'));
		$query->where('a.seller_uid = ' . $sellerUid . ' AND a.state = 1');
		$query->where('a.type = ' . $this->db->q($type) . ' AND a.week_day = ' . $weekDay);

		$this->db->setQuery($query);

		$timings = $this->db->loadObject();

		return $timings;
	}

	/**
	 * Method to save timings
	 *
	 * @param   array   $data       The form data
	 * @param   string  $type       Type of timing (store, delivery, etc.)
	 * @param   int     $sellerUid  The seller user id
	 *
	 * @return  void
	 *
	 * @since   1.6.0
	 */
	public function saveTimings($data, $type, $sellerUid)
	{
		foreach ($data as $weekday => $timing)
		{
			$table = JTable::getInstance('SellerTimings', 'SellaciousTable');
			$table->load(array('seller_uid' => $sellerUid, 'type' => $type, 'week_day' => $weekday));

			if ($timing['full_day'] == 1)
			{
				$timing['from_time'] = '00:00';
				$timing['to_time']   = '00:00';
			}

			$timing['type']       = $type;
			$timing['seller_uid'] = $sellerUid;
			$timing['full_day']   = isset($timing['full_day']) ? $timing['full_day'] : 0;
			$timing['from_time']  = JFactory::getDate($timing['from_time'])->format('H:i:s');
			$timing['to_time']    = JFactory::getDate($timing['to_time'])->format('H:i:s');

			if (!isset($timing['week_day']))
			{
				$timing['state']    = 0;
				$timing['week_day'] = $weekday;
			}
			else
			{
				$timing['state'] = 1;
			}

			$table->bind($timing);
			$table->check();
			$table->store();
		}
	}

	/**
	 * Stock handling for the items in the order when a status change is triggered.
	 *
	 * @param   int $order_id The concerned order id
	 * @param       $order_item_id
	 * @param   int $item_uid The uid of the order item (PnVnSnBnDn)
	 *
	 * @return  bool
	 *
	 * @throws \Exception
	 * @since   1.7.0
	 */
	public function handleStock($order_id, $order_item_id, $item_uid)
	{
		$config        = ConfigHelper::getInstance('plg_system_sellacioushyperlocal');
		$hlParams      = $config->getParams();
		$timeSelection = $hlParams->get('delivery_time_selection', 0);
		$history       = $this->helper->order->getStatusLog($order_id, $item_uid);

		if (empty($history))
		{
			return true;
		}

		$recent = array_shift($history);

		// If this status has no effect on stock, exit.
		if ($recent->s_stock == '')
		{
			return true;
		}

		while ($previous = array_shift($history))
		{
			if (in_array($previous->s_stock, array('A', 'R', 'O')))
			{
				break;
			}
		}

		$c_handle = $recent->s_stock;
		$o_handle = $previous ? $previous->s_stock : '';
		$o_handle = $o_handle ? $o_handle : '-';
		$h_key    = $o_handle . $c_handle;

		// Key => [A=Stock, R=Reserved, O=Sold]
		$handle_map = array(
			//  '-A' => array( '0',  '0',  '0'),
			'-R' => array('-1', '+1', '0'),
			'-O' => array('-1', '0', '+1'),
			'AR' => array('-1', '+1', '0'),
			'AO' => array('-1', '0', '+1'),
			'RA' => array('+1', '-1', '0'),
			'RO' => array('0', '-1', '+1'),
			'OA' => array('+1', '0', '-1'),
			'OR' => array('0', '+1', '-1'),
		);

		$change = ArrayHelper::getValue($handle_map, $h_key);

		if (empty($change))
		{
			return true;
		}

		$table = JTable::getInstance('OrderDeliverySlot', 'SellaciousTable');
		$table->load(array('order_item_id' => $order_item_id));

		$from_time = $table->get('slot_from_time');
		$to_time   = $table->get('slot_to_time');
		$full_day  = $table->get('full_day', 0);

		$this->helper->product->parseCode($item_uid, $product_id, $variant_id, $seller_uid);

		if ($timeSelection == 3)
		{
			$slotDate = JFactory::getDate($from_time);
			$weekDay  = $slotDate->format('w', true);
			$weekDay  = $weekDay == 0 ? 6 : ($weekDay - 1);
			$timings  = $this->getTiming($seller_uid, $weekDay);
			$slotData = array(
				'seller_uid'     => $seller_uid,
				'product_id'     => $product_id,
				'full_day'       => 1,
				'slot_from_time' => $slotDate->format('Y-m-d ' . $timings->from_time),
				'slot_to_time'   => $slotDate->format('Y-m-d ' . $timings->to_time)
			);
		}
		else
		{
			$slotData = array(
				'seller_uid'     => $seller_uid,
				'product_id'     => $product_id,
				'full_day'       => $full_day,
				'slot_from_time' => $from_time,
				'slot_to_time'   => $to_time
			);
		}

		$slotLimit = JTable::getInstance('ProductSellerSlotLimit', 'SellaciousTable');
		$slotLimit->load($slotData);

		$c_stock = $slotLimit->get('slot_count', 0);

		if (!$slotLimit->get('id'))
		{
			$slotLimit->bind($slotData);

			$hlTable = JTable::getInstance('SellerHyperlocal', 'SellaciousTable');
			$hlTable->load(array('seller_uid' => $seller_uid));

			$params  = new Registry($hlTable->get('params'));
			$c_stock = $params->get('slot_limit', 0);

			$slotLimit->set('slot_limit', $c_stock);
		}

		// Slot limit will change with only one unit per order, so quantity not being used
		$c_stock += $change[0];

		$slotLimit->set('slot_count', $c_stock);
		$slotLimit->check();
		$slotLimit->store();

		return true;
	}

	/**
	 * Method to get remaining slots
	 *
	 * @param   int     $sellerUid  The seller user id
	 * @param   int     $productId  The product id
	 * @param   string  $fromTime   From time
	 * @param   string  $toTime     To Time
	 * @param   int     $fullDay    Is it a full day slot?
	 *
	 * @return int|mixed
	 *
	 * @since 1.7.0
	 */
	public function getRemainingSlots($sellerUid, $productId, $fromTime = null, $toTime = null, $fullDay = 0)
	{
		$now = JFactory::getDate()->format('Y-m-d 00:00:00');

		$data = array(
			'seller_uid'     => $sellerUid,
			'product_id'     => $productId,
			'full_day'       => $fullDay,
			'slot_from_time' => $fromTime ?: $now,
			'slot_to_time'   => $toTime ?: $now
		);

		$slotLimit = JTable::getInstance('ProductSellerSlotLimit', 'SellaciousTable');
		$slotLimit->load($data);

		if ($slotLimit->get('id'))
		{
			$slotCount = $slotLimit->get('slot_count', 0);
		}
		else
		{
			$hlTable = JTable::getInstance('SellerHyperlocal', 'SellaciousTable');
			$hlTable->load(array('seller_uid' => $sellerUid));

			$params    = new Registry($hlTable->get('params'));
			$slotCount = $params->get('slot_limit', 0);
		}

		return $slotCount;
	}

	/**
	 * Method to get available slots
	 *
	 * @param   int     $productId  The product id
	 * @param   int     $sellerUid  The seller user id
	 * @param   string  $date       The date for which available slots are required
	 * @param   string  $fromTime   The time from which slots are required
	 *
	 * @return  array
	 *
	 * @throws  \Exception
	 *
	 * @since   1.7.0
	 */
	public function getAvailableSlots($productId, $sellerUid, $date = '', $fromTime = '')
	{
		$user          = JFactory::getUser();
		$now           = JFactory::getDate();
		$config        = ConfigHelper::getInstance('plg_system_sellacioushyperlocal');
		$hlParams      = $config->getParams();
		$timeSelection = $hlParams->get('delivery_time_selection', 0);
		$hyperlocal    = $this->app->getUserState('mod_sellacious_hyperlocal.user.location', array());
		$timezone      = isset($hyperlocal['timezone']) && !empty($hyperlocal['timezone']) ? $hyperlocal['timezone'] : $this->app->get('offset', 'UTC');

		if ($user->id)
		{
			$timezone = $user->getParam('timezone', $timezone);
		}

		$timezone = new DateTimeZone($timezone);
		$now->setTimezone($timezone);

		$slotDate = $date != '' ? JFactory::getDate($date) : $now;
		$weekDay  = $slotDate->format('w', true);
		$weekDay  = $weekDay == 0 ? 6 : ($weekDay - 1);

		$hlTable = JTable::getInstance('SellerHyperlocal', 'SellaciousTable');
		$hlTable->load(array('seller_uid' => $sellerUid));

		$params            = new Registry($hlTable->get('params'));
		$slotWindow        = $params->get('slot_window', null);
		$prepTime          = $params->get('delivery_prep_time', null);
		$todayAvailability = JFactory::getDate($now->format('Y-m-d', true) . ' ' . $params->get('today_availability', '12:00 AM'));

		// Get Delivery Timing of selected day
		$timings     = $this->getTiming($sellerUid, $weekDay);
		$slotOptions = array();

		// Check if full day slots are remaining or not
		$fullDayFromTime = isset($timings->from_time) ? $slotDate->format('Y-m-d') . ' ' . $timings->from_time : null;
		$fullDayToTime   = isset($timings->from_time) ? $slotDate->format('Y-m-d') . ' ' . $timings->to_time : null;
		$fullDaySlots    = $this->getRemainingSlots($sellerUid, $productId, $fullDayFromTime, $fullDayToTime, 1);

		if ($timeSelection == 1 && !empty($timings))
		{
			$slotWindow    = new stdClass();
			$daySlot       = JFactory::getDate($timings->to_time)->diff(JFactory::getDate($timings->from_time));
			$slotWindow->m = $daySlot->h;
			$slotWindow->u = 'hours';
		}

		if (!empty($fullDaySlots) && !empty($timings))
		{
			if (isset($slotWindow->m) && $slotWindow->m > 0)
			{
				$fromTime    = $timings->from_time ? $timings->from_time : $fromTime;
				$toTime      = $timings->to_time;
				$fromTime    = JFactory::getDate($slotDate->format('Y-m-d', true) . ' ' . $fromTime);
				$toTime      = JFactory::getDate($slotDate->format('Y-m-d', true) . ' ' . $toTime);
				$slotWin     = $slotWindow->m;
				$slotWinUnit = $slotWindow->u;

				$normalSlots = array();

				for ($i = JFactory::getDate($fromTime); $i < JFactory::getDate($toTime); $i->modify('+' . $slotWin . ' ' . $slotWinUnit))
				{
					$nslot      = JFactory::getDate($i->format('Y-m-d H:i:s', true));
					$normalSlot = array($nslot->format('Y-m-d H:i:s'));

					$nslot->modify('+' . $slotWin . ' ' . $slotWinUnit);

					$normalSlot[]  = $nslot->format('Y-m-d H:i:s', true);
					$normalSlots[] = $normalSlot;
				}

				// If the slot date is current date
				if ($slotDate->format('Y-m-d', true) == $now->format('Y-m-d', true))
				{
					$nextSlotAvailable = JFactory::getDate($now->format('Y-m-d H:i:s', true));

					// If current date slot has exceeded Same day availability
					if ($nextSlotAvailable > $todayAvailability)
					{
						return $slotOptions;
					}

					if ($prepTime && $timeSelection == 2)
					{
						$nextSlotAvailable->modify($prepTime->m . ' ' . $prepTime->u);
					}

					foreach ($normalSlots as $normalSlot)
					{
						if ($nextSlotAvailable >= $normalSlot[0] && $nextSlotAvailable <= $normalSlot[1])
						{
							$fromTime = JFactory::getDate($normalSlot[0]);
						}
					}
				}

				for ($i = $fromTime; $i < $toTime; $i->modify('+' . $slotWin . ' ' . $slotWinUnit))
				{
					$slot         = JFactory::getDate($i->format('Y-m-d H:i'));
					$slotFromVal  = $slot->format('H:i');
					$slotFromText = $slot->format('g:i A');

					$slot->modify('+' . $slotWin . ' ' . $slotWinUnit);

					if (strtotime($slot) > strtotime($toTime))
					{
						$slot = $toTime;
					}

					$slotToVal  = $slot->format('H:i');
					$slotToText = $slot->format('g:i A');

					$slotLimit = $this->getRemainingSlots($sellerUid, $productId, $slotDate->format('Y-m-d') . ' ' . $slotFromVal . ':00', $slotDate->format('Y-m-d') . ' ' . $slotToVal . ':00');

					if ($slotLimit > 0)
					{
						$slotOptions[$slotFromVal . ' - ' . $slotToVal] = $slotFromText . ' - ' . $slotToText;
					}
				}
			}
			elseif ($timeSelection == 3)
			{
				$fromTime                              = JFactory::getDate($slotDate->format('Y-m-d', true) . ' ' . $timings->from_time);
				$slotOptions[$fromTime->format('H:i')] = $fromTime->format('H:i');
			}
		}

		return $slotOptions;
	}

	/**
	 * Method to get slot dates which are unavailable
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
	public function getUnavailableDates($productId, $sellerUid)
	{
		// Get Full day slot dates that are unavailable
		$query = $this->db->getQuery(true);
		$query->select('DATE_FORMAT(a.slot_from_time, \'%Y-%m-%d\') as slot_date');
		$query->from($this->db->qn('#__sellacious_product_seller_slot_limits', 'a'));
		$query->where('a.full_day = 1');
		$query->where('a.slot_count = 0');
		$query->where('a.product_id = ' . (int)$productId . ' AND a.seller_uid = ' . (int)$sellerUid);

		$this->db->setQuery($query);

		$unavailableDates = $this->db->loadColumn();

		// Get dates which are unavailable due to all slots are used
		$query = $this->db->getQuery(true);
		$query->select('DISTINCT DATE_FORMAT(a.slot_from_time, \'%Y-%m-%d\') as slot_date');
		$query->from($this->db->qn('#__sellacious_product_seller_slot_limits', 'a'));
		$query->where('DATE_FORMAT(a.slot_from_time, \'%Y-%m-%d\') <> \'0000-00-00\' AND slot_count = 0');

		$this->db->setQuery($query);

		$slotDates = $this->db->loadColumn();

		foreach ($slotDates as $slotDate)
		{
			$availableSlots = $this->getAvailableSlots($productId, $sellerUid, $slotDate);

			if (empty($availableSlots))
			{
				$unavailableDates[] = $slotDate;
			}
		}

		return array_unique($unavailableDates);
	}
}
