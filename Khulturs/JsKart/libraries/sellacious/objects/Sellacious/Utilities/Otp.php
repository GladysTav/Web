<?php
/**
 * @version     2.0.0
 * @package     sellacious
 *
 * @copyright   Copyright (C) 2012-2020 Bhartiy Web Technologies. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Izhar Aazmi <info@bhartiy.com> - http://www.bhartiy.com
 */
namespace Sellacious\Utilities;

// no direct access
defined('_JEXEC') or die;

use FOFEncryptBase32;
use FOFEncryptTotp;
use JFactory;

/**
 * The utility to generate and validate OTP
 *
 * @since   2.0.0
 */
class Otp
{
	/**
	 * The time for otp validity in seconds
	 *
	 * @var    int
	 *
	 * @since   2.0.0
	 */
	protected $validity;

	/**
	 * The length of generated code
	 *
	 * @var    int
	 *
	 * @since   2.0.0
	 */
	protected $length;

	/**
	 * Constructor
	 *
	 * @param   int  $validity  The time for otp validity in seconds
	 * @param   int  $length    The length of generated code
	 *
	 * @since   2.0.0
	 */
	public function __construct($validity = 30, $length = 6)
	{
		$this->validity = $validity;
		$this->length   = $length;
	}

	/**
	 * Get the OTP code
	 *
	 * @param   string  $secret  The secret key to generate OTP
	 *
	 * @return  string  The OTP code
	 *
	 * @since   2.0.0
	 */
	public function getOtp($secret)
	{
		$tOtp = new FOFEncryptTotp($this->validity, $this->length, strlen($secret));

		return $tOtp->getCode($secret);
	}

	/**
	 * Check the OTP code
	 *
	 * @param   string  $secret  The secret key that was used to generate OTP
	 * @param   string  $otp     The OTP
	 *
	 * @return  bool
	 *
	 * @since   2.0.0
	 */
	public function checkOtp($secret, $otp)
	{
		$tOtp = new FOFEncryptTotp($this->validity, $this->length, strlen($secret));

		return $tOtp->checkCode($secret, $otp);
	}

	/**
	 * Method to generate a different secret based on
	 * various parameters and preserves it for 10 minutes since last used
	 *
	 * @param   string  $name  The name for the secret key
	 * @param   string  $_     Variable number of arguments
	 *
	 * @return  string
	 *
	 * @since   2.0.0
	 */
	public function genSecret($name, $_ = null)
	{
		static $secret;

		if (!$secret)
		{
			$key       = md5(serialize(func_get_args()));
			$keyKey    = "sellacious.otp.secret.{$name}.{$key}";
			$expireKey = "sellacious.otp.secret_expire.{$name}.{$key}";

			$app     = JFactory::getApplication();
			$secret  = $app->getUserState($keyKey);
			$expire  = $app->getUserState($expireKey);
			$time    = time();

			if ($secret && $expire > $time)
			{
				// Extend life further if used
				$app->setUserState($expireKey, $time + 600);
			}
			else
			{
				// Purge all of this name
				$app->setUserState('sellacious.otp.secret.' . $name, null);
				$app->setUserState('sellacious.otp.secret_expire.' . $name, null);

				// Generate new secret once expired or not generated
				$prefix = JFactory::getConfig()->get('secret');
				$secret = sha1($prefix . $key . $time);

				// Create new key for given keySet
				$app->setUserState($keyKey, $secret);
				$app->setUserState($expireKey, $time + 600);
			}
		}

		$b32 = new FOFEncryptBase32;

		return $b32->encode($secret);
	}

	/**
	 * Method to generate a securely reversible hash for a given value
	 *
	 * @param   string  $value  The value
	 *
	 * @return  string
	 *
	 * @since   2.0.0
	 */
	public static function secureHash($value)
	{
		$salt   = time();
		$prefix = JFactory::getConfig()->get('secret');
		$hash   = md5($salt . $prefix . $value);

		return sprintf('%s:%s:%s', $hash, $salt, $value);
	}

	/**
	 * Method to reverse the hash generated using secureHash method
	 *
	 * @param   string  $hash  The hash to decode
	 *
	 * @return  string|bool
	 *
	 * @since   2.0.0
	 */
	public static function secureUnhash($hash)
	{
		$parts = explode(':', $hash);

		if (count($parts) === 3)
		{
			list($hash, $salt, $value) = $parts;

			$prefix = JFactory::getConfig()->get('secret');
			$reHash = md5($salt . $prefix . $value);

			return $hash === $reHash && (time() < $salt + 300) ? $value : false;
		}

		return null;
	}
}
