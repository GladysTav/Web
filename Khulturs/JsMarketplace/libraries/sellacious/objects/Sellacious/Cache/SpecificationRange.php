<?php
/**
 * @version     1.7.4
 * @package     sellacious
 *
 * @copyright   Copyright (C) 2012-2019 Bhartiy Web Technologies. All rights reserved.
 * @license     SPL Sellacious Private License; see http://www.sellacious.com/spl.html
 * @author      Izhar Aazmi <info@bhartiy.com> - http://www.bhartiy.com
 */
// no direct access.
namespace Sellacious\Cache;

defined('_JEXEC') or die;

use Sellacious\Cache;

/**
 * Sellacious Products Specifications values range boundary Cache Object.
 *
 * @since  1.7.0
 */
class SpecificationRange extends Cache
{
	/**
	 * @var    string
	 *
	 * @since  1.7.0
	 */
	protected $cacheTable = '#__sellacious_cache_spec_ranges';

	/**
	 * Build the cache.
	 *
	 * @return  void
	 *
	 * @throws  \Exception
	 *
	 * @since   1.7.0
	 */
	public function build()
	{
		$query = $this->db->getQuery(true);

		$query->select("CASE a.table_name WHEN 'products' THEN a.record_id ELSE v.product_id END AS pid")
			->select('a.field_id, MIN(a.field_value) AS value_min, MAX(a.field_value) AS value_max')
			->from($this->db->qn('#__sellacious_field_values', 'a'))
			->where("(a.table_name = 'products' OR a.table_name = 'variants')");

		$query->join('left', $this->db->qn('#__sellacious_variants', 'v') . " ON v.id = a.record_id AND a.table_name = 'variants'");
		$query->join('inner', $this->db->qn('#__sellacious_fields', 'f') . " ON f.id = a.field_id AND f.type = 'number'");

		$query->group('pid, field_id');

		$dSQL = 'DELETE FROM ' . $this->cacheTable . ' WHERE 1';
		$iSQL = 'INSERT INTO ' . $this->cacheTable . ' (product_id, field_id, value_min, value_max) ' . $query;

		try
		{
			$this->db->setQuery($dSQL)->execute();
			$this->db->setQuery($iSQL)->execute();
		}
		catch (\Exception $e)
		{
			// IGNORE
		}
	}
}
