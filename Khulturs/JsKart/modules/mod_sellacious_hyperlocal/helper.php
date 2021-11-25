<?php
/**
 * @version     2.0.0
 * @package     Sellacious Hyperlocal Module
 *
 * @copyright   Copyright (C) 2012-2020 Bhartiy Web Technologies. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Aditya Chakraborty <info@bhartiy.com> - http://www.bhartiy.com
 */
// no direct access
defined('_JEXEC') or die('Restricted access');

use Sellacious\Hyperlocal\Settings;

JLoader::registerNamespace('Sellacious', __DIR__ . '/libraries', false, false, 'psr4');
JLoader::register('SellaciousHyperlocal', __DIR__ . '/libraries/module.php');
JTable::addIncludePath(__DIR__ . '/libraries/table');

/**
 * @package  Sellacious Hyperlocal
 *
 * @since    2.0.0
 */
class ModSellaciousHyperlocalHelper
{
	/**
	 * Get autocomplete list of locations by ajax.
	 *
	 * @throws  Exception
	 *
	 * @since   1.6.0
	 */
	public static function getAutoCompleteSearchAjax()
	{
		$helper   = SellaciousHelper::getInstance();
		$app      = JFactory::getApplication();
		$db       = JFactory::getDbo();
		$settings = Settings::getInstance();

		$term  = $app->input->getString('term');
		$start = $app->input->getInt('list_start', 0);
		$limit = $app->input->getInt('list_limit', 5);

		$types    = $settings->getAutofillComponents();
		$locality = array_search('locality', $types);

		if ($locality !== false)
		{
			$types[$locality] = 'area';
		}

		$filters = array(
			'list.select' => "CONCAT(a.title, IFNULL(CONCAT(', ', a.area_title), ''), IFNULL(CONCAT(', ', a.district_title), ''), IFNULL(CONCAT(', ', a.state_title), ''), IFNULL(CONCAT(', ', a.country_title), '')) AS value, a.id",
			'list.where'  => array('a.state = 1', 'a.parent_id >= 1', 'a.type IN (' . implode(',', $db->q($types)) . ')'),
			'list.order'  => 'a.title',
			'list.start'  => $start,
			'list.limit'  => $limit,
		);

		if ($term)
		{
			$filters['list.where'][] = 'a.title LIKE ' . $db->q($db->escape($term, true) . '%', false);
		}

		$items = $helper->location->loadObjectList($filters);

		echo json_encode($items);

		jexit();
	}

	/**
	 * Ajax Method to set Address
	 *
	 * @throws  Exception
	 *
	 * @since   1.6.0
	 */
	public static function setAddressAjax()
	{
		$app      = JFactory::getApplication();
		$id       = $app->input->getInt('id', 0);
		$address  = $app->input->getString('address');
		$settings = Settings::getInstance();

		try
		{
			$data = array(
				'id'      => $id,
				'address' => $address,
			);

			$b = $settings->get('hyperlocal_type') == SellaciousHyperlocal::BY_REGION;

			self::setLatLong($data, $b);

			echo new JResponseJson($data);
		}
		catch (Exception $e)
		{
			echo new JResponseJson($e);
		}

		$app->close();
	}

	/**
	 * Ajax Method to get Address from Geolocation
	 *
	 * @since   1.6.0
	 */
	public static function getAddressAjax()
	{
		try
		{
			$app        = JFactory::getApplication();
			$lat        = $app->input->getFloat('lat');
			$lng        = $app->input->getFloat('lng');
			$components = $app->input->get('components', array(), 'array');

			if (!$components)
			{
				throw new Exception(JText::_('MOD_SELLACIOUS_HYPERLOCAL_ADDRESS_NOT_FOUND'));
			}

			$address    = array();
			$addressIds = array();
			$helper     = SellaciousHelper::getInstance();
			$settings   = Settings::getInstance();
			$autofill   = $settings->getAutofillComponents();
			$mapping    = array(
				'locality'    => 'sublocality_level_1',
				'sublocality' => 'sublocality_level_2',
				'city'        => 'locality',
				'district'    => 'administrative_area_level_2',
				'state'       => 'administrative_area_level_1',
				'country'     => 'country',
				'zip'         => 'postal_code',
			);

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

						if ($location)
						{
							$found = true;

							$addressIds[$component] = $location->id;
						}
					}

					return $found;
				}));

				if ($addressComponent)
				{
					$address[$component] = $addressComponent[0]['long_name'];
				}
			}

			if (!$address)
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

			static::setFilter($data['id'], $data['address'], $data['lat'], $data['long']);

			echo new JResponseJson($data);
		}
		catch (Exception $e)
		{
			echo new JResponseJson($e);
		}

		jexit();
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
		$app        = JFactory::getApplication();
		$lat        = $app->input->getFloat('lat');
		$lng        = $app->input->getFloat('long');
		$timezone   = $app->input->getString('timezone');
		$address    = $app->input->getString('address');
		$components = $app->input->get('components', array(), 'array');

		try
		{
			// Set address to session/state
			$data = array('id' => 0, 'address' => $address, 'lat' => $lat, 'long' => $lng);

			if ($components)
			{
				$data = array_merge($data, $components);
			}

			if ($timezone)
			{
				$data['timezone'] = $timezone;
			}

			$app->setUserState('mod_sellacious_hyperlocal.user.location', $data);

			static::setFilter($data['id'], $data['address'], $data['lat'], $data['long']);

			echo new JResponseJson($data);
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
	 * @throws  Exception
	 *
	 * @since   1.7.0
	 *
	 * @deprecated  See if usable, else remove
	 */
	public static function setCustomAddressAjax()
	{
		$app      = JFactory::getApplication();
		$address  = $app->input->getString('address');
		$lat      = $app->input->getString('lat');
		$lng      = $app->input->getString('lng');
		$settings = Settings::getInstance();
		$data     = array();

		try
		{
			$data['id']      = 0;
			$data['address'] = $address;

			if (!$lat || !$lng)
			{
				$b = $settings->get('hyperlocal_type') == SellaciousHyperlocal::BY_REGION;

				self::setLatLong($data, $b);
			}
			else
			{
				$data['lat']  = $lat;
				$data['long'] = $lng;

				if ($settings->get('hyperlocal_type') == SellaciousHyperlocal::BY_REGION)
				{
					$address = self::getAddress($lat, $lng, true);
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
	 * @throws  Exception
	 *
	 * @since   1.6.0
	 *
	 * @deprecated  See if usable, else remove
	 */
	public static function setShippableFilterAjax()
	{
		$app     = JFactory::getApplication();
		$address = $app->input->getString('address');
		$latLong = null;

		try
		{
			if ($address)
			{
				$latLong = self::getLatLong($address);

				$app->setUserState('com_sellacious.products.filter.shippable_coordinates', $latLong);
			}

			echo new JResponseJson($latLong);
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
	 * @since   1.6.0
	 *
	 * @deprecated  See if usable, else remove
	 */
	public static function setLocationFilterAjax()
	{
		try
		{
			static::setFilter(null, null, null, null);

			echo new JResponseJson;
		}
		catch (Exception $e)
		{
			echo new JResponseJson($e);
		}

		jexit();
	}

	/**
	 * Ajax Method to set Bounds
	 *
	 * @since   1.6.0
	 */
	public static function setBoundsAjax()
	{
		try
		{
			$app = JFactory::getApplication();

			$data      = $app->getUserState('mod_sellacious_hyperlocal.user.location', array());
			$boundsMin = $app->input->get('bounds_min', array(), 'array');
			$boundsMax = $app->input->get('bounds_max', array(), 'array');
			$minRadius = $app->input->get('min_radius', 0);
			$maxRadius = $app->input->get('max_radius', 0);
			$timezone  = $app->input->getString('timezone');
			$boundsMin = array_filter($boundsMin, 'is_numeric');
			$boundsMax = array_filter($boundsMax, 'is_numeric');

			if (count($boundsMax) < 4 || count($boundsMin) < 4 || count($boundsMax) < 4)
			{
				throw new Exception(JText::_('MOD_SELLACIOUS_HYPERLOCAL_GET_ADDRESS_FAILED'));
			}

			$data['bounds_max'] = $boundsMax;
			$data['bounds_min'] = $boundsMin;
			$data['min_radius'] = $minRadius;
			$data['max_radius'] = $maxRadius;
			$data['timezone']   = $timezone;

			$app->setUserState('mod_sellacious_hyperlocal.user.location', $data);

			echo new JResponseJson($data);
		}
		catch (Exception $e)
		{
			echo new JResponseJson($e);
		}

		jexit();
	}

	/**
	 * Ajax Method to reset address
	 *
	 * @since   1.6.0
	 */
	public static function resetAddressAjax()
	{
		try
		{
			$app = JFactory::getApplication();

			$app->setUserState('mod_sellacious_hyperlocal.user.location', null);
			$app->setUserState('mod_sellacious_hyperlocal.user.no_detect', true);

			static::setFilter(null, null, null, null);

			echo new JResponseJson;
		}
		catch (Exception $e)
		{
			echo new JResponseJson($e);
		}

		jexit();
	}

	/**
	 * Method to set lat long into user session/state
	 *
	 * @param    array  $data                The address data (int id, string address)
	 * @param    bool   $include_components  Whether to include address components
	 *
	 * @return   void
	 *
	 * @throws   Exception
	 *
	 * @since    1.6.0
	 */
	protected static function setLatLong(&$data, $include_components = false)
	{
		if (empty($data['address']))
		{
			return;
		}

		$app     = JFactory::getApplication();
		$latLong = self::getLatLong($data['address'], $include_components);

		if (isset($latLong['lat']))
		{
			$data['lat']  = $latLong['lat'];
			$data['long'] = $latLong['long'];

			if ($include_components && !empty($latLong['address_components']))
			{
				$data = array_merge($data, $latLong['address_components']);
			}

			$app->setUserState('mod_sellacious_hyperlocal.user.location', $data);

			static::setFilter($data['id'], $data['address'], $data['lat'], $data['long']);
		}
	}

	/**
	 * Method to get lat long from address using google api
	 *
	 * @param   string  $address             The address string
	 * @param   bool    $include_components  Whether to include address components
	 *
	 * @return  array
	 *
	 * @throws  Exception
	 *
	 * @since   1.6.0
	 */
	protected static function getLatLong($address, $include_components = false)
	{
		$settings = Settings::getInstance();

		// Store the lat-lng of the displayed address
		$apiKey = $settings->getApiKey();
		$json   = file_get_contents(sprintf('https://maps.google.com/maps/api/geocode/json?key=%s&address=%s&sensor=false', $apiKey, urlencode($address)));
		$obj    = json_decode($json);
		$data   = array();

		if (is_object($obj) & !empty($obj->results))
		{
			$data['lat']  = round(@$obj->results[0]->geometry->location->lat, 4);
			$data['long'] = round(@$obj->results[0]->geometry->location->lng, 4);

			if ($include_components)
			{
				$data['address_components'] = static::parseComponents($obj);
			}
		}

		return $data;
	}

	/**
	 * Method to get address from lat long from address using google api
	 *
	 * @param   float  $lat                 The latitude
	 * @param   float  $lng                 The longitude
	 * @param   bool   $include_components  Whether to include address components
	 *
	 * @return  array
	 *
	 * @throws  Exception
	 *
	 * @since   1.7.0
	 */
	protected static function getAddress($lat, $lng, $include_components = false)
	{
		$settings = Settings::getInstance();

		// Store the lat-lng of the displayed address
		$apiKey = $settings->getApiKey();
		$json   = file_get_contents(sprintf('https://maps.google.com/maps/api/geocode/json?key=%s&latlng=%s,%s&sensor=false', $apiKey, $lat, $lng));
		$obj    = json_decode($json);
		$data   = array();

		if (is_object($obj) & !empty($obj->results))
		{
			$data['address'] = $obj->results[0]->formatted_address;

			if ($include_components)
			{
				$data['address_components'] = static::parseComponents($obj);
			}
		}

		return $data;
	}

	/**
	 * Set hyperlocal filter in session
	 *
	 * @param   int     $id
	 * @param   string  $address
	 * @param   float   $lat
	 * @param   float   $lng
	 *
	 * @throws  Exception
	 *
	 * @since   2.0.0
	 */
	protected static function setFilter($id, $address, $lat, $lng)
	{
		$app = JFactory::getApplication();

		$app->setUserState('com_sellacious.products.filter.store_location_custom', $id);
		$app->setUserState('com_sellacious.products.filter.store_location_custom_text', $address);
		$app->setUserState('com_sellacious.products.filter.shippable_coordinates', array('lat' => $lat, 'long' => $lng));
	}

	/**
	 * Parse response from Google Maps API to find address components of given address
	 *
	 * @param   $obj
	 *
	 * @return  array
	 *
	 * @since   2.0.0
	 */
	protected static function parseComponents($obj)
	{
		$map = array(
			'locality'    => 'sublocality_level_1',
			'sublocality' => 'sublocality_level_2',
			'city'        => 'locality',
			'district'    => 'administrative_area_level_2',
			'state'       => 'administrative_area_level_1',
			'country'     => 'country',
			'zip'         => 'postal_code',
		);

		$components = array_fill_keys(array_keys($map), '');

		foreach ($obj->results[0]->address_components as $component)
		{
			foreach ($map as $key => $fld)
			{
				if (in_array($fld, $component->types))
				{
					$components[$key] = $component->long_name;

					break;
				}
			}
		}

		return $components;
	}
}
