<?php
/**
 * @version     2.0.0
 * @package     sellacious
 *
 * @copyright   Copyright (C) 2012-2020 Bhartiy Web Technologies. All rights reserved.
 * @license     SPL Sellacious Private License; see http://www.sellacious.com/spl.html
 * @author      Izhar Aazmi <info@bhartiy.com> - http://www.bhartiy.com
 */
namespace Sellacious\Database\SQLite;

// no direct access.
defined('_JEXEC') or die;

/**
 * @package  Sellacious\Database\SQLite\SQLiteFunctions
 *
 * @since    2.0.0
 */
class SQLiteFunctions
{
	/**
	 * Get a field value from a JSON string
	 *
	 * @param   string  $value
	 * @param   string  $key
	 *
	 * @return  string
	 *
	 * @since   2.0.0
	 */
	public static function jsonField($value, $key)
	{
		return self::getValue($value, $key);
	}

	/**
	 * Check a value if it exists in an array extracted from a JSON string
	 *
	 * @param   string  $value
	 * @param   string  $key
	 * @param   string  $match
	 *
	 * @return  string
	 *
	 * @since   2.0.0
	 */
	public static function jsonInArray($value, $key, $match)
	{
		$val = self::getValue($value, $key);

		return (is_array($val) && in_array($match, $val)) ? 1 : 0;
	}

	/**
	 * Check a value if it is a key in an array extracted from a JSON string
	 *
	 * @param   string  $value
	 * @param   string  $key
	 * @param   string  $match
	 *
	 * @return  string
	 *
	 * @since   2.0.0
	 */
	public static function jsonInKey($value, $key, $match)
	{
		$val = self::getValue($value, $key);

		return (is_array($val) && array_key_exists($match, $val)) ? 1 : 0;
	}

	/**
	 * Check a value if it exists in an array extracted from a JSON string
	 *
	 * @param   string    $value
	 * @param   string    $key
	 * @param   string[]  $matches
	 *
	 * @return  string
	 *
	 * @since   2.0.0
	 */
	public static function jsonIntersectArray($value, $key, $matches)
	{
		$val     = self::getValue($value, $key);
		$matches = json_decode($matches, true);

		return (is_array($val) && is_array($matches) && array_intersect($matches, $val)) ? 1 : 0;
	}

	/**
	 * Check a value if it is a key in an array extracted from a JSON string
	 *
	 * @param   string  $value
	 * @param   string  $key
	 * @param   string  $matches
	 *
	 * @return  string
	 *
	 * @since   2.0.0
	 */
	public static function jsonIntersectKey($value, $key, $matches)
	{
		$val     = self::getValue($value, $key);
		$matches = json_decode($matches, true);

		return (is_array($val) && is_array($matches) && array_intersect($matches, array_keys($val))) ? 1 : 0;
	}

	/**
	 * Check whether the given latitude longitude falls with given bounds represented by [N-E-W-S] limits
	 *
	 * @param   float  $lat
	 * @param   float  $lng
	 * @param   float  $north
	 * @param   float  $east
	 * @param   float  $west
	 * @param   float  $south
	 *
	 * @return  bool
	 *
	 * @since   2.0.0
	 */
	public static function inMapBoundary($lat, $lng, $north, $east, $west, $south)
	{
		return ($south < $lat && $lat < $north) && ($west < $lng && $lng < $east);
	}

	/**
	 * Get a field value from a JSON string
	 *
	 * @param   string  $value
	 * @param   string  $key
	 *
	 * @return  string
	 *
	 * @since   2.0.0
	 */
	protected static function getValue($value, $key)
	{
		$val = json_decode($value, true);

		if (!$key)
		{
			return $val;
		}

		if (!is_array($val))
		{
			return null;
		}

		foreach (explode('.', $key) as $segment)
		{
			if (is_array($val) && isset($val[$segment]))
			{
				$val = $val[$segment];
			}
			else
			{
				return null;
			}
		}

		return $val;
	}
}
