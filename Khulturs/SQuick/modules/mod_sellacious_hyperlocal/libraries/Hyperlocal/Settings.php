<?php
/**
 * @version     2.0.0
 * @package     Sellacious Hyperlocal
 *
 * @copyright   Copyright (C) 2012-2020 Bhartiy Web Technologies. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Izhar Aazmi <info@bhartiy.com> - http://www.bhartiy.com
 */
namespace Sellacious\Hyperlocal;

// no direct access
use Exception;
use Joomla\Registry\Registry;
use JText;
use Sellacious\Config\Config;
use Sellacious\Config\ConfigHelper;

defined('_JEXEC') or die('Restricted access');

/**
 * @package   Sellacious Hyperlocal
 *
 * @since     2.0.0
 */
class Settings
{
	/**
	 * Module settings object instance
	 *
	 * @var   Settings
	 *
	 * @since   2.0.0
	 */
	protected static $instance;

	/**
	 * Module configuration
	 *
	 * @var   Config
	 *
	 * @since   2.0.0
	 */
	protected $config;

	/**
	 * Settings constructor
	 *
	 * @throws  Exception
	 *
	 * @since   2.0.0
	 */
	private function __construct()
	{
		$this->config = ConfigHelper::getInstance('mod_sellacious_hyperlocal');
	}

	/**
	 * Get the configuration instance
	 *
	 * @throws  Exception
	 *
	 * @since   2.0.0
	 */
	public static function getInstance()
	{
		if (!self::$instance)
		{
			self::$instance = new self;
		}

		return self::$instance;
	}

	/**
	 * Get the google maps api key
	 *
	 * @return  string
	 *
	 * @since   2.0.0
	 */
	public function getApiKey()
	{
		return $this->get('google_api_key');
	}

	/**
	 * Check if the hyperlocal enabled
	 *
	 * @since   2.0.0
	 */
	public function isEnabled()
	{
		return (bool) $this->get('enabled');
	}

	/**
	 * Get the address components to be used by hyperlocal
	 *
	 * @return  string[]
	 *
	 * @since   2.0.0
	 */
	public function getAutofillComponents()
	{
		// WARNING: Do not change the order of the address components in array below unless absolutely sure!
		$comps = array('sublocality', 'locality', 'city', 'district', 'state', 'country', 'zip');

		if ($this->get('hyperlocal_type') == \SellaciousHyperlocal::BY_RADIUS)
		{
			return $comps;
		}

		$autofill = $this->get('address_components', $comps);

		return array_values(array_intersect($comps, $autofill));
	}

	/**
	 * Get the hyperlocal configuration value
	 *
	 * @param   string  $prop     The property name
	 * @param   mixed   $default  The default value if empty
	 *
	 * @return  mixed
	 *
	 * @since   2.0.0
	 */
	public function get($prop, $default = null)
	{
		return $this->config->get($prop, $default);
	}

	/**
	 * Get the hyperlocal configuration value
	 *
	 * @return  Registry
	 *
	 * @since   2.0.0
	 */
	public function getParams()
	{
		return $this->config->getParams();
	}
}
