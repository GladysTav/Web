<?php
/**
 * @version     2.0.0
 * @package     sellacious
 *
 * @copyright   Copyright (C) 2012-2020 Bhartiy Web Technologies. All rights reserved.
 * @license     SPL Sellacious Private License; see http://www.sellacious.com/spl.html
 * @author      Izhar Aazmi <info@bhartiy.com> - http://www.bhartiy.com
 */
namespace Sellacious\Cache\Builder;

// no direct access.
defined('_JEXEC') or die;

use Exception;
use JFactory;
use Sellacious\Cache\Record\ProductCacheRecord;
use Sellacious\Event\EventHelper;

/**
 * Class ProductsCacheBuilder
 *
 * @package  Sellacious\Cache
 *
 * @since   2.0.0
 */
class ProductsCacheBuilder extends AbstractCacheBuilder
{
	/**
	 * CacheBuilder constructor
	 *
	 * @throws  Exception
	 *
	 * @since   2.0.0
	 */
	public function __construct()
	{
		parent::__construct('products');
	}

	/**
	 * Build the complete cache
	 *
	 * @return  void
	 *
	 * @throws  Exception
	 *
	 * @since   2.0.0
	 */
	public function build()
	{
		$db    = JFactory::getDbo();
		$query = $db->getQuery(true);

		$query->select('id')->from('#__sellacious_products');

		$iterator = $db->setQuery($query)->getIterator();

		$this->createTemporaryStorage();

		$this->dropTable($this->name);

		$this->setupTable($this->name);

		$this->beginInsert($this->name);

		$this->implicitFlush(100);

		foreach ($iterator as $o)
		{
			$processor = new ProductCacheRecord($o->id);
			$records   = $processor->getRecords();

			foreach ($records as $record)
			{
				$this->append($record);
			}
		}

		$this->flush();

		$this->endInsert();

		$this->setLive();
	}

	/**
	 * Update the specific products' cache
	 *
	 * @param   int[]  $pks  Product ids of modified products
	 *
	 * @return  void
	 *
	 * @throws  Exception
	 *
	 * @since   2.0.0
	 */
	public function rebuild($pks)
	{
		$this->loadStorage();

		$this->delete($pks);

		$this->beginInsert($this->name);

		$this->implicitFlush(100);

		foreach ($pks as $pk)
		{
			$processor = new ProductCacheRecord($pk);
			$records   = $processor->getRecords();

			foreach ($records as $record)
			{
				$this->append($record);
			}
		}

		$this->flush();

		$this->endInsert();
	}

	/**
	 * Method to update the table with given values where records match given conditions
	 *
	 * @param   array  $values
	 * @param   array  $conditions
	 *
	 * @return  void
	 *
	 * @since   2.0.0
	 * @throws  Exception
	 *
	 */
	public function update($values, $conditions)
	{
		$this->loadStorage();

		$query = JFactory::getDbo()->getQuery(true);

		// This query will be run on sqlite PDO instance, but built using mysqli query builder (fix-it)
		$query->update('products')->set($values)->where($conditions);

		$this->storage->exec((string) $query);
	}

	/**
	 * Delete specific row(s) from the storage database
	 *
	 * @param   array  $keys  The filter for the records to be deleted (numeric ids only for now)
	 *
	 * @return  void
	 *
	 * @since   2.0.0
	 * @throws  Exception
	 *
	 */
	public function delete($keys)
	{
		$this->loadStorage();

		foreach ($keys as $pk)
		{
			$this->storage->exec('DELETE FROM products WHERE product_id = ' . (int) $pk);
		}
	}

	/**
	 * Get a list of columns relevant to the given table
	 *
	 * @param   string  $tableName  The storage table name to insert into, defaults to current db name
	 *
	 * @return  array
	 *
	 * @since   2.0.0
	 */
	protected function getColumns($tableName)
	{
		$columns = ProductCacheRecord::getColumns();

		// More cols to add: order_count, order_units, product_rating, product_ordering
		EventHelper::trigger('onFetchCacheColumns', array('context' => 'com_sellacious.product', 'columns' => &$columns));

		return array_unique($columns);
	}
}
