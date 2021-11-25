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
defined('_JEXEC') or die('Restricted access');

/**
 * @package   Sellacious Hyperlocal
 *
 * @since     2.0.0
 */
class Distance
{
	const UNIT_METER = 'M';

	const UNIT_KILOMETER = 'KM';

	const UNIT_FOOT = 'FT';

	const UNIT_YARD = 'YD';

	const UNIT_MILE = 'MI';

	const UNIT_NAUTICAL_MILE = 'NM';

	const METER_TO_METER = 1;

	const KILOMETER_TO_METER = 1000;

	const FOOT_TO_METER = 0.304785126;

	const YARD_TO_METER = 0.914076782;

	const MILE_TO_METER = 1609.344;

	const NAUTICAL_MILE_TO_METER = 1852;

	/**
	 * Convert given distance from given unit into meters
	 *
	 * @param   float   $value
	 * @param   string  $unit
	 *
	 * @return  float
	 *
	 * @since   2.0.0
	 */
	public static function toMeters($value, $unit)
	{
		switch (strtoupper($unit))
		{
			case 'M':
				return $value * self::METER_TO_METER;
			case 'K':
				return $value * self::KILOMETER_TO_METER;
			case 'F':
				return $value * self::FOOT_TO_METER;
			case 'Y':
				return $value * self::YARD_TO_METER;
			case 'I':
				return $value * self::MILE_TO_METER;
			case 'N':
				return $value * self::NAUTICAL_MILE_TO_METER;
			default:
				return 0.0;
		}
	}

	/**
	 * Convert given distance from meters into given unit
	 *
	 * @param   float   $value
	 * @param   string  $unit
	 *
	 * @return  float
	 *
	 * @since   2.0.0
	 */
	public static function fromMeters($value, $unit)
	{
		switch (strtoupper($unit))
		{
			case 'M':
				return $value / self::METER_TO_METER;
			case 'K':
				return $value / self::KILOMETER_TO_METER;
			case 'F':
				return $value / self::FOOT_TO_METER;
			case 'Y':
				return $value / self::YARD_TO_METER;
			case 'I':
				return $value / self::MILE_TO_METER;
			case 'N':
				return $value / self::NAUTICAL_MILE_TO_METER;
			default:
				return 0.0;
		}
	}

	/**
	 * Get the symbol for given unit
	 *
	 * @param   string  $unit
	 *
	 * @return  float
	 *
	 * @since   2.0.0
	 */
	public static function getSymbol($unit)
	{
		switch (strtoupper($unit))
		{
			case 'K':
				return 'Km';
			case 'F':
				return 'Ft';
			case 'Y':
				return 'Yd';
			case 'I':
				return 'Mi';
			case 'N':
				return 'NMi';
			case 'M':
			default:
				return 'm';
		}
	}
}
