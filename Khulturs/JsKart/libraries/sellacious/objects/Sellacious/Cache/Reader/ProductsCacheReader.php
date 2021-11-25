<?php
/**
 * @version     2.0.0
 * @package     sellacious
 *
 * @copyright   Copyright (C) 2012-2020 Bhartiy Web Technologies. All rights reserved.
 * @license     SPL Sellacious Private License; see http://www.sellacious.com/spl.html
 * @author      Izhar Aazmi <info@bhartiy.com> - http://www.bhartiy.com
 */
namespace Sellacious\Cache\Reader;

// no direct access.
defined('_JEXEC') or die;

/**
 * Class ProductsCacheReader
 *
 * @package  Sellacious\Cache
 *
 * @since   2.0.0
 */
class ProductsCacheReader extends AbstractCacheReader
{
	/**
	 * Cache object name
	 *
	 * @var   string
	 *
	 * @since   2.0.0
	 */
	protected $name = 'products';

	/**
	 * Add a condition to check if the given latitude longitude falls within the given bounds represented by [N-E-W-S] limits
	 *
	 * @param   float  $lat
	 * @param   float  $lng
	 * @param   float  $n
	 * @param   float  $e
	 * @param   float  $w
	 * @param   float  $s
	 *
	 * @return  void
	 *
	 * @since   2.0.0
	 */
	public function filterInMapBoundary($lat, $lng, $n, $e, $w, $s)
	{
		$query = $this->getQuery();

		$query->where(sprintf('in_map_boundary(%s, %s, %s, %s, %s, %s)', $query->qn($lat), $query->qn($lng), $query->q($n), $query->q($e), $query->q($w), $query->q($s)));
	}

	/**
	 * Add a condition to check if the given latitude longitude falls outside the given bounds represented by [N-E-W-S] limits
	 *
	 * @param   float  $lat
	 * @param   float  $lng
	 * @param   float  $n
	 * @param   float  $e
	 * @param   float  $w
	 * @param   float  $s
	 *
	 * @return  void
	 *
	 * @since   2.0.0
	 */
	public function filterNotInMapBoundary($lat, $lng, $n, $e, $w, $s)
	{
		$query = $this->getQuery();

		$query->where(sprintf('NOT in_map_boundary(%s, %s, %s, %s, %s, %s)', $query->qn($lat), $query->qn($lng), $query->q($n), $query->q($e), $query->q($w), $query->q($s)));
	}
}
