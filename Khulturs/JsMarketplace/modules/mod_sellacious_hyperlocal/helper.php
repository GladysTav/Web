<?php
/**
 * @version     1.7.4
 * @package     Sellacious Hyperlocal Module
 *
 * @copyright   Copyright (C) 2012-2019 Bhartiy Web Technologies. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Aditya Chakraborty <info@bhartiy.com> - http://www.bhartiy.com
 */

// no direct access
use Joomla\Registry\Registry;
use Sellacious\Cache\Distances;
use Sellacious\Config\ConfigHelper;

defined('_JEXEC') or die('Restricted access');

class ModSellaciousHyperlocalHelper
{
	/**
	 * Ajax Method to get Address from Geolocation
	 *
	 * @throws   Exception
	 *
	 * @since    1.6.0
	 */
	public static function getAddressAjax()
	{
		$app        = JFactory::getApplication();
		$helper     = SellaciousHelper::getInstance();
		$components = $app->input->get('address_components', array(), 'Array');
		$autofill   = $app->input->getString('autofill', 'locality,city,district,state,country,zip');
		$lat        = $app->input->getFloat('lat');
		$lng        = $app->input->getFloat('lng');
		$mapping    = array(
			'zip'      => 'postal_code',
			'locality' => 'sublocality',
			'city'     => 'locality',
			'district' => 'administrative_area_level_2',
			'state'    => 'administrative_area_level_1',
			'country'  => 'country',
		);

		try
		{
			if (empty($components))
			{
				throw new Exception(JText::_('MOD_SELLACIOUS_HYPERLOCAL_ADDRESS_NOT_FOUND'));
			}

			$autofill   = explode(',', $autofill);
			$address    = array();
			$addressIds = array();

			foreach ($autofill as $component)
			{
				$addressComponent = array_values(array_filter($components, function ($item) use ($component, $mapping, $helper, &$addressIds) {
					$found = false;
					$types = $item['types'];

					if (in_array($mapping[$component], $types))
					{
						$location = $helper->location->loadObject(array(
							'type'  => $component == 'locality' ? 'area' : $component,
							'title' => $item['long_name'],
						));

						if (!empty($location))
						{
							$found                  = true;
							$addressIds[$component] = $location->id;
						}
					}

					return $found;
				}));

				if (!empty($addressComponent))
				{
					$address[$component] = $addressComponent[0]['long_name'];
				}
			}

			if (empty($address))
			{
				throw new Exception(JText::_('MOD_SELLACIOUS_HYPERLOCAL_ADDRESS_NOT_FOUND'));
			}

			// Set address to session/state
			$data = array(
				'id'      => reset($addressIds),
				'address' => implode(', ', $address),
				'lat'     => $lat,
				'long'    => $lng,
			);

			$app->setUserState('mod_sellacious_hyperlocal.user.location', $data);
			$app->setUserState('filter.store_location_custom', $data['id']);
			$app->setUserState('filter.store_location_custom_text', $data['address']);
			$app->setUserState('filter.shippable_coordinates', array('lat' => $lat, 'long' => $lng));

			if (self::isShippableEnabled())
			{
				$app->setUserState('filter.shippable', $data['id']);
				$app->setUserState('filter.shippable_text', $data['address']);
			}

			echo new JResponseJson($data, '');
		}
		catch (Exception $e)
		{
			echo new JResponseJson($e);
		}

		$app->close();
	}

	/**
	 * Ajax Method to set Address from Detected Geolocation
	 *
	 * @throws  Exception
	 *
	 * @since   1.6.1
	 */
	public static function setGeoLocationAjax()
	{
		$app                = JFactory::getApplication();
		$address            = $app->input->getString('address', '');
		$lat                = $app->input->getFloat('lat');
		$lng                = $app->input->getFloat('long');
		$address_components = $app->input->get('address_components', array(), 'Array');
		$timezone           = $app->input->getString('timezone', '');
		$params             = new Registry($app->input->get('params', array(), 'Array'));

		try
		{
			// Set address to session/state
			$data = array(
				'id'      => 0,
				'address' => $address,
				'lat'     => $lat,
				'long'    => $lng,
			);

			if (!empty($address_components))
			{
				$data = array_merge($data, $address_components);
			}

			$data['address_matching']      = $params->get('address_matching', 1);
			$data['address_matching_area'] = $params->get('address_matching_area', '');

			if ($timezone)
			{
				$data['timezone'] = $timezone;
			}

			$app->setUserState('mod_sellacious_hyperlocal.user.location', $data);
			$app->setUserState('filter.store_location_custom', $data['id']);
			$app->setUserState('filter.store_location_custom_text', $data['address']);
			$app->setUserState('filter.shippable_coordinates', array('lat' => $lat, 'long' => $lng));

			if (self::isShippableEnabled())
			{
				$app->setUserState('filter.shippable', $data['id']);
				$app->setUserState('filter.shippable_text', $data['address']);
			}

			echo new JResponseJson($data, '');
		}
		catch (Exception $e)
		{
			echo new JResponseJson($e);
		}

		$app->close();
	}

	/**
	 * Ajax Method to set Address
	 *
	 * @throws   Exception
	 *
	 * @since    1.6.0
	 */
	public static function setAddressAjax()
	{
		$app     = JFactory::getApplication();
		$id      = $app->input->getInt('id', 0);
		$address = $app->input->getString('address', '');
		$params  = $app->input->get('params', array(), 'Array');

		try
		{
			$data = array(
				'id'      => $id,
				'address' => $address,
			);

			// Set address to session/state
			self::setLatLong($data, $params);

			echo new JResponseJson($data, '');
		}
		catch (Exception $e)
		{
			echo new JResponseJson($e);
		}

		$app->close();
	}

	/**
	 * Ajax Method to set Address if Lat, Long are provided
	 *
	 * @throws   Exception
	 *
	 * @since    1.7.0
	 */
	public static function setCustomAddressAjax()
	{
		$app       = JFactory::getApplication();
		$address   = $app->input->getString('address', '');
		$lat       = $app->input->getString('lat', '');
		$lng       = $app->input->getString('lng', '');
		$params    = $app->input->get('params', array(), 'Array');
		$modParams = new Registry($params);
		$data      = array();

		try
		{
			$data['id']      = 0;
			$data['address'] = $address;

			if (empty($lat) || empty($lng))
			{
				$include_components = false;

				if ($modParams->get('address_matching', 1) == 2)
				{
					$include_components            = true;
					$data['address_matching']      = $modParams->get('address_matching', 1);
					$data['address_matching_area'] = $modParams->get('address_matching_area', '');
				}

				// Set address to session/state
				self::setLatLong($data, $params, $include_components);
			}
			else
			{
				$data['lat']  = $lat;
				$data['long'] = $lng;

				if ($modParams->get('address_matching', 1) == 2)
				{
					$data['address_matching']      = $modParams->get('address_matching', 1);
					$data['address_matching_area'] = $modParams->get('address_matching_area', '');

					$address = self::getAddress($lat, $lng, $params, true);
					$data    = array_merge($data, $address['address_components']);
				}

				$app->setUserState('mod_sellacious_hyperlocal.user.location', $data);
			}

			echo new JResponseJson($data, '');
		}
		catch (Exception $e)
		{
			echo new JResponseJson($e);
		}

		$app->close();
	}

	/**
	 * Ajax Method to set shippable Filter
	 *
	 * @throws   Exception
	 *
	 * @since    1.6.0
	 */
	public static function setShippableFilterAjax()
	{
		$app     = JFactory::getApplication();
		$address = $app->input->getString('address', '');
		$params  = $app->input->get('params', array(), 'Array');

		try
		{
			// Get lat long
			if ($address)
			{
				JLoader::registerNamespace('Sellacious', JPATH_PLUGINS . '/system/sellacioushyperlocal/libraries');
				$distanceCache = new Distances;

				$latlong = self::getLatLong($address, $params);
				$app->setUserState('filter.shippable_coordinates', $latlong);

				$distanceCache->deleteCache();
			}

			if (self::isShippableEnabled())
			{
				$app->setUserState('filter.shippable', null);
				$app->setUserState('filter.shippable_text', null);
			}

			echo new JResponseJson($latlong, '');
		}
		catch (Exception $e)
		{
			echo new JResponseJson($e);
		}

		$app->close();
	}

	/**
	 * Ajax Method to set Location Filter
	 *
	 * @throws   Exception
	 *
	 * @since    1.6.0
	 */
	public static function setLocationFilterAjax()
	{
		$app     = JFactory::getApplication();
		$address = $app->input->getString('address', '');
		$params  = $app->input->get('params', array(), 'Array');

		try
		{
			$app->setUserState('filter.store_location_custom', null);
			$app->setUserState('filter.store_location_custom_text', null);

			echo new JResponseJson('', '');
		}
		catch (Exception $e)
		{
			echo new JResponseJson($e);
		}

		$app->close();
	}

	/**
	 * Ajax Method to set Bounds
	 *
	 * @throws   Exception
	 *
	 * @since    1.6.0
	 */
	public static function setBoundsAjax()
	{
		$app              = JFactory::getApplication();
		$hyperlocal       = $app->getUserState('mod_sellacious_hyperlocal.user.location', array());
		$productBounds    = $app->input->get('product_bounds', array(), 'Array');
		$productBoundsMin = $app->input->get('product_bounds_min', array(), 'Array');
		$storeBounds      = $app->input->get('store_bounds', array(), 'Array');
		$storeBoundsMin   = $app->input->get('store_bounds_min', array(), 'Array');
		$minRadius        = $app->input->get('min_radius', 0);
		$maxRadius        = $app->input->get('max_radius', 0);
		$timezone         = $app->input->getString('timezone', '');
		$productBounds    = array_filter($productBounds, 'is_numeric');
		$productBoundsMin = array_filter($productBoundsMin, 'is_numeric');
		$storeBounds      = array_filter($storeBounds, 'is_numeric');
		$storeBoundsMin   = array_filter($storeBoundsMin, 'is_numeric');

		try
		{
			if (count($productBounds) < 4 || count($productBoundsMin) < 4 || count($storeBounds) < 4 || count($storeBoundsMin) < 4)
			{
				throw new Exception(JText::_('MOD_SELLACIOUS_HYPERLOCAL_GET_ADDRESS_FAILED'));
			}

			$hyperlocal['product_bounds']     = $productBounds;
			$hyperlocal['product_bounds_min'] = $productBoundsMin;
			$hyperlocal['store_bounds']       = $storeBounds;
			$hyperlocal['store_bounds_min']   = $storeBoundsMin;
			$hyperlocal['min_radius']         = $minRadius;
			$hyperlocal['max_radius']         = $maxRadius;
			$hyperlocal['timezone']           = $timezone;
			$app->setUserState('mod_sellacious_hyperlocal.user.location', $hyperlocal);

			echo new JResponseJson($hyperlocal, '');
		}
		catch (Exception $e)
		{
			echo new JResponseJson($e);
		}

		$app->close();
	}

	/**
	 * Ajax Method to set Radius Range
	 *
	 * @throws   Exception
	 *
	 * @since    1.6.1
	 */
	public static function setRadiusRangeAjax()
	{
		$app              = JFactory::getApplication();
		$hyperlocal       = $app->getUserState('mod_sellacious_hyperlocal.user.location', array());
		$productBounds    = $app->input->get('product_bounds', array(), 'Array');
		$productBoundsMin = $app->input->get('product_bounds_min', array(), 'Array');
		$storeBounds      = $app->input->get('store_bounds', array(), 'Array');
		$storeBoundsMin   = $app->input->get('store_bounds_min', array(), 'Array');
		$minRadius        = $app->input->get('min_radius', 0);
		$maxRadius        = $app->input->get('max_radius', 0);

		$productBounds    = array_filter($productBounds, 'is_numeric');
		$productBoundsMin = array_filter($productBoundsMin, 'is_numeric');
		$storeBounds      = array_filter($storeBounds, 'is_numeric');
		$storeBoundsMin   = array_filter($storeBoundsMin, 'is_numeric');

		try
		{
			if (count($productBounds) < 4 || count($productBoundsMin) < 4 || count($storeBounds) < 4 || count($storeBoundsMin) < 4)
			{
				throw new Exception(JText::_('MOD_SELLACIOUS_HYPERLOCAL_GET_ADDRESS_FAILED'));
			}

			$hyperlocal['product_bounds']     = $productBounds;
			$hyperlocal['product_bounds_min'] = $productBoundsMin;
			$hyperlocal['store_bounds']       = $storeBounds;
			$hyperlocal['store_bounds_min']   = $storeBoundsMin;
			$hyperlocal['min_radius']         = $minRadius;
			$hyperlocal['max_radius']         = $maxRadius;
			$app->setUserState('mod_sellacious_hyperlocal.user.location', $hyperlocal);

			echo new JResponseJson($hyperlocal, '');
		}
		catch (Exception $e)
		{
			echo new JResponseJson($e);
		}

		$app->close();
	}

	/**
	 * Get auto complete list of locations by ajax.
	 *
	 * @throws   Exception
	 *
	 * @since    1.6.0
	 */
	public static function getAutoCompleteSearchAjax()
	{
		$helper = SellaciousHelper::getInstance();
		$app    = JFactory::getApplication();
		$db     = JFactory::getDbo();

		$term  = $app->input->getString('term');
		$types = $app->input->get('types', array(), 'array');
		$start = $app->input->getInt('list_start', 0);
		$limit = $app->input->getInt('list_limit', 5);

		$locality = array_search('locality', $types);

		if ($locality !== '')
		{
			$types[] = 'area';
			unset($types[$locality]);
		}

		$filters = array(
			'list.select' => 'CONCAT(a.title, IFNULL(CONCAT(\', \', a.area_title), \'\'), IFNULL(CONCAT(\', \', a.district_title), \'\'), IFNULL(CONCAT(\', \', a.state_title), \'\'), IFNULL(CONCAT(\', \', a.country_title), \'\')) AS value, a.id',
			'list.where'  => array('a.state = 1', 'a.parent_id >= 1', 'a.type IN (' . implode(',', $db->q($types)) . ')'),
			'list.order'  => 'a.title',
			'list.start'  => $start,
			'list.limit'  => $limit,
		);

		if (!empty($term))
		{
			$text                    = $db->Quote($db->escape($term, true) . '%', false);
			$filters['list.where'][] = 'a.title LIKE ' . $text;
		}

		$items = $helper->location->loadObjectList($filters);

		echo json_encode($items);
		jexit();
	}

	/**
	 * Method to set lat long into user session/state
	 *
	 * @param    array  $data                The address data (int id, string address)
	 * @param    array  $params              The module params
	 * @param    bool   $include_components  Whether to include address components
	 *
	 * @return   void
	 *
	 * @throws   \Exception
	 *
	 * @since    1.6.0
	 */
	public static function setLatLong(&$data, $params, $include_components = false)
	{
		if (!isset($data['address']) || empty($data['address']))
		{
			return;
		}

		$app     = JFactory::getApplication();
		$latlong = self::getLatLong($data['address'], $params, $include_components);

		if (isset($latlong['lat']))
		{
			$data['lat']  = $latlong['lat'];
			$data['long'] = $latlong['long'];

			if ($include_components && !empty($latlong['address_components']))
			{
				$data = array_merge($data, $latlong['address_components']);
			}

			$app->setUserState('mod_sellacious_hyperlocal.user.location', $data);
			$app->setUserState('filter.store_location_custom', $data['id']);
			$app->setUserState('filter.store_location_custom_text', $data['address']);
			$app->setUserState('filter.shippable_coordinates', $latlong);

			if (self::isShippableEnabled())
			{
				$app->setUserState('filter.shippable', $data['id']);
				$app->setUserState('filter.shippable_text', $data['address']);
			}
		}

	}

	/**
	 * Method to get lat long from address using google api
	 *
	 * @param    string  $address             The address string
	 * @param    array   $params              The module params
	 * @param    bool    $include_components  Whether to include address components
	 *
	 * @return   array
	 *
	 * @since    1.6.0
	 */
	public static function getLatLong($address, $params, $include_components = false)
	{
		$address = urlencode($address);

		// Store the latlong of the displayed address
		$json = file_get_contents("https://maps.google.com/maps/api/geocode/json?key=" . $params['google_api_key'] . "&address=$address&sensor=false");
		$json = json_decode($json);

		$data = array();

		if (!empty($json) & !empty($json->results))
		{
			$data['lat']  = round($json->results[0]->geometry->location->lat, 4);
			$data['long'] = round($json->results[0]->geometry->location->lng, 4);

			if ($include_components)
			{
				$address_components = array(
					'locality'    => '',
					'sublocality' => '',
					'city'        => '',
					'district'    => '',
					'state'       => '',
					'country'     => '',
					'zip'         => '',
				);

				foreach ($json->results[0]->address_components as $component)
				{
					if (in_array('sublocality_level_1', $component->types))
					{
						$address_components['locality'] = $component->long_name;
					}
					elseif (in_array('sublocality_level_2', $component->types))
					{
						$address_components['sublocality'] = $component->long_name;
					}
					elseif (in_array('locality', $component->types))
					{
						$address_components['city'] = $component->long_name;
					}
					elseif (in_array('administrative_area_level_2', $component->types))
					{
						$address_components['district'] = $component->long_name;
					}
					elseif (in_array('administrative_area_level_1', $component->types))
					{
						$address_components['state'] = $component->long_name;
					}
					elseif (in_array('country', $component->types))
					{
						$address_components['country'] = $component->long_name;
					}
					elseif (in_array('postal_code', $component->types))
					{
						$address_components['zip'] = $component->long_name;
					}
				}

				$data['address_components'] = $address_components;
			}
		}

		return $data;
	}

	/**
	 * Method to get address from lat long from address using google api
	 *
	 * @param    float  $lat                 The latitude
	 * @param    float  $lng                 The longitude
	 * @param    array  $params              The module params
	 * @param    bool   $include_components  Whether to include address components
	 *
	 * @return   array
	 *
	 * @since    1.7.0
	 */
	public static function getAddress($lat, $lng, $params, $include_components = false)
	{
		// Store the latlong of the displayed address
		$latlng = $lat . ',' . $lng;
		$json   = file_get_contents("https://maps.google.com/maps/api/geocode/json?key=" . $params['google_api_key'] . "&latlng=$latlng&sensor=false");
		$json   = json_decode($json);

		$data = array();

		if (!empty($json) & !empty($json->results))
		{
			$data['address'] = $json->results[0]->formatted_address;

			if ($include_components)
			{
				$address_components = array();

				foreach ($json->results[0]->address_components as $component)
				{
					if (in_array('sublocality_level_1', $component->types))
					{
						$address_components['locality'] = $component->long_name;
					}
					elseif (in_array('sublocality_level_2', $component->types))
					{
						$address_components['sublocality'] = $component->long_name;
					}
					elseif (in_array('locality', $component->types))
					{
						$address_components['city'] = $component->long_name;
					}
					elseif (in_array('administrative_area_level_2', $component->types))
					{
						$address_components['district'] = $component->long_name;
					}
					elseif (in_array('administrative_area_level_1', $component->types))
					{
						$address_components['state'] = $component->long_name;
					}
					elseif (in_array('country', $component->types))
					{
						$address_components['country'] = $component->long_name;
					}
					elseif (in_array('postal_code', $component->types))
					{
						$address_components['zip'] = $component->long_name;
					}
				}

				$data['address_components'] = $address_components;
			}
		}

		return $data;
	}

	/**
	 * Ajax Method to reset address
	 *
	 * @throws   Exception
	 *
	 * @since    1.6.0
	 */
	public static function resetAddressAjax()
	{
		$app = JFactory::getApplication();

		try
		{
			$app->setUserState('mod_sellacious_hyperlocal.user.location', null);
			$app->setUserState('filter.store_location_custom', null);
			$app->setUserState('filter.store_location_custom_text', null);
			$app->setUserState('filter.shippable_coordinates', null);

			if (self::isShippableEnabled())
			{
				$app->setUserState('filter.shippable', null);
				$app->setUserState('filter.shippable_text', null);
			}

			echo new JResponseJson('', '');
		}
		catch (Exception $e)
		{
			echo new JResponseJson($e);
		}

		$app->close();
	}

	/**
	 * Method to check whether Shippable location is enabled
	 *
	 * @return   bool
	 *
	 * @throws   \Exception
	 *
	 * @since    1.6.1
	 */
	protected static function isShippableEnabled()
	{
		$helper                = SellaciousHelper::getInstance();
		$hlConfig              = ConfigHelper::getInstance('plg_system_sellacioushyperlocal');
		$hlParams              = $hlConfig->getParams();
		$hlType                = $hlParams->get('hyperlocal_type');
		$hide_shippable_filter = $helper->config->get('hide_shippable_filter', 0);
		$moduleEnabled         = JModuleHelper::isEnabled('mod_sellacious_hyperlocal');
		$module                = JModuleHelper::getModule('mod_sellacious_filters');
		$enabled               = false;

		if ($hlType == 2 && $hide_shippable_filter == 0 && $moduleEnabled && $module->id)
		{
			$enabled = true;
		}

		return $enabled;
	}
}

