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

use Exception;
use JDatabaseIterator;
use JDatabaseQuerySqlite;
use PDO;
use PDOStatement;
use Sellacious\Cache\CacheHelper;
use Sellacious\Cache\Exception\CacheMissingException;
use Sellacious\Database\SQLite\SQLiteDatabaseDriver;
use Sellacious\Database\SQLite\SQLiteFunctions;
use stdClass;

/**
 * Class AbstractCacheReader
 *
 * @package  Sellacious\Cache
 *
 * @since   2.0.0
 */
abstract class AbstractCacheReader
{
	protected static $instances = array();

	/**
	 * Cache object name
	 *
	 * @var   string
	 *
	 * @since   2.0.0
	 */
	protected $name;

	/**
	 * Database connection object to the storage database
	 *
	 * @var   SQLiteDatabaseDriver
	 *
	 * @since   2.0.0
	 */
	protected $db;

	/**
	 * PDO prepared statement for record insertion
	 *
	 * @var   PDOStatement
	 *
	 * @since   2.0.0
	 */
	protected $statement;

	/**
	 * Record index to track current record
	 *
	 * @var   int
	 *
	 * @since   2.0.0
	 */
	protected $cursor = 0;

	/**
	 * Flag to indicate whether some filter is known to cause zero results.
	 * This is to improve performance by skipping query.
	 *
	 * @var  bool
	 *
	 * @since   2.0.0
	 */
	protected $fallacy;

	/**
	 * Filters for the query loader
	 *
	 * @var  array
	 *
	 * @since   2.0.0
	 */
	protected $filters = array();

	/**
	 * The sql statement for reading cache database
	 *
	 * @var  JDatabaseQuerySqlite
	 *
	 * @since   2.0.0
	 */
	protected $query;

	/**
	 * AbstractCacheReader constructor
	 *
	 * @param   string  $name  Cache object name
	 *
	 * @throws  CacheMissingException
	 *
	 * @since   2.0.0
	 */
	public function __construct($name = null)
	{
		if ($name)
		{
			$this->name = $name;
		}

		$this->loadStorage();
	}

	/**
	 * Load the cache storage database if it exists
	 *
	 * @return  void
	 *
	 * @throws  CacheMissingException
	 *
	 * @since   2.0.0
	 */
	public function loadStorage()
	{
		$filename = CacheHelper::getFilename($this->name);

		if (!file_exists($filename))
		{
			throw new CacheMissingException('Cache storage not found for ' . $this->name);
		}

		$db = SQLiteDatabaseDriver::getInstance(array('database' => $filename));

		$db->setOption(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
		$db->setOption(PDO::ATTR_CASE, PDO::CASE_LOWER);

		$db->addFunction('json_val', array(SQLiteFunctions::class, 'jsonField'));
		$db->addFunction('json_in_array', array(SQLiteFunctions::class, 'jsonInArray'));
		$db->addFunction('json_in_key', array(SQLiteFunctions::class, 'jsonInKey'));
		$db->addFunction('json_intersect_array', array(SQLiteFunctions::class, 'jsonIntersectArray'));
		$db->addFunction('json_intersect_key', array(SQLiteFunctions::class, 'jsonIntersectKey'));
		$db->addFunction('in_map_boundary', array(SQLiteFunctions::class, 'inMapBoundary'));

		$this->db = $db;
	}

	/**
	 * Get the sql query bound to this object, new query if requested
	 *
	 * @param   bool  $new
	 *
	 * @return  JDatabaseQuerySqlite
	 *
	 * @since   2.0.0
	 */
	public function getQuery($new = false)
	{
		if ($new || !$this->query)
		{
			$this->query = $this->db->getQuery(true);

			$this->query->select('*')->from($this->db->qn($this->name));
		}

		return $this->query;
	}

	/**
	 * Method to load a single record found with matching filters
	 *
	 * @return  stdClass
	 *
	 * @throws  Exception
	 *
	 * @since   2.0.0
	 */
	public function getItem()
	{
		if ($this->fallacy)
		{
			return null;
		}

		$query = clone $this->getQuery();

		$this->db->setQuery($query, 0, 1);

		return $this->db->loadObject();
	}

	/**
	 * Method to load a list of records found with matching filters
	 *
	 * @param   int  $offset  The called method
	 * @param   int  $limit   The array of arguments passed to the method
	 *
	 * @return  stdClass[]
	 *
	 * @throws  Exception
	 *
	 * @since   2.0.0
	 */
	public function getItems($offset = 0, $limit = 0)
	{
		if ($this->fallacy)
		{
			return array();
		}

		$query = clone $this->getQuery();

		$this->db->setQuery($query, $offset, $limit);

		return $this->db->loadObjectList();
	}

	/**
	 * Method to load an iterator for records found with matching filters
	 *
	 * @return  JDatabaseIterator
	 *
	 * @throws  Exception
	 *
	 * @since   2.0.0
	 */
	public function getIterator()
	{
		if ($this->fallacy)
		{
			return $this->db->setQuery('SELECT 1 WHERE 1 = 0')->getIterator();
		}

		$query = $this->getQuery();

		$this->db->setQuery($query);

		return $this->db->getIterator();
	}

	/**
	 * Method to find number of matching records
	 *
	 * @return  int
	 *
	 * @since   2.0.0
	 */
	public function getTotal()
	{
		if ($this->fallacy)
		{
			return 0;
		}

		$query = clone $this->getQuery();

		$query->clear('select')->select('SUM(1)');

		return $this->db->setQuery($query)->loadResult();
	}

	/**
	 * Set fallacy flag ON, this disabled all filters and forces any query to have no match
	 *
	 * @return  void
	 *
	 * @since   2.0.0
	 */
	public function fallacy()
	{
		$this->fallacy = true;
	}

	/**
	 * Filter on given value using said operator for comparison
	 *
	 * @param   string        $col    Database column name
	 * @param   string|mixed  $value  Value to match
	 * @param   string        $op     Comparison operator
	 *
	 * @return  void
	 *
	 * @since   2.0.0
	 */
	public function filterValue($col, $value, $op = '=')
	{
		$query = $this->getQuery();

		if ($op === 'IN' || is_array($value))
		{
			$op = 'IN';
			$v  = '(' . implode(', ', (array) $query->q($value)) . ')';
		}
		elseif ($op === 'LIKE')
		{
			$v = $query->q('%' . $query->e($value, true) . '%', false);
		}
		else
		{
			$v = $query->q($value);
		}

		$query->where(sprintf('%s %s %s', $query->qn($col), $op, $v));
	}

	/**
	 * Filter on given value in a json column using said operator for comparison
	 *
	 * @param   string  $col    Database column name
	 * @param   string  $key    JSON field to lookup for
	 * @param   string  $value  Value to match
	 * @param   string  $op     Comparison operator
	 *
	 * @return  void
	 *
	 * @since   2.0.0
	 */
	public function filterJsonValue($col, $key, $value, $op = '=')
	{
		$query = $this->getQuery();

		if ($op === 'IN' || is_array($value))
		{
			$op = 'IN';
			$v  = '(' . implode(', ', (array) $query->q($value)) . ')';
		}
		elseif ($op === 'LIKE')
		{
			$v = $query->q('%' . $query->e($value, true) . '%', false);
		}
		else
		{
			$v = $query->q($value);
		}

		$query->where(sprintf('json_val(%s, %s) %s %s', $query->qn($col), $query->q($key), $op, $v));
	}

	/**
	 * Filter matching the given value is a value or a key (as indicated with {$matchKey}) in a json array
	 *
	 * @param   string  $col    Database column name
	 * @param   string  $key    JSON field to lookup for
	 * @param   string  $value  Value to match
	 *
	 * @return  void
	 *
	 * @since   2.0.0
	 */
	public function filterInJsonArray($col, $key, $value)
	{
		$query = $this->getQuery();

		$query->where(sprintf('json_in_array(%s, %s, %s)', $query->qn($col), $query->q($key), $query->q($value)));
	}

	/**
	 * Filter matching the given values is a value or a key (as indicated with {$matchKey}) in a json array
	 *
	 * @param   string    $col     Database column name
	 * @param   string    $key     JSON field to lookup for
	 * @param   string[]  $values  Value to match
	 *
	 * @return  void
	 *
	 * @since   2.0.0
	 */
	public function filterIntersectJsonArray($col, $key, $values)
	{
		$query = $this->getQuery();

		if (count($values) === 1)
		{
			$this->filterInJsonArray($col, $key, reset($values));
		}
		else
		{
			$query->where(sprintf('json_intersect_array(%s, %s, %s)', $query->qn($col), $query->q($key), $query->q(json_encode($values))));
		}
	}

	/**
	 * Filter matching the given values is a value or a key (as indicated with {$matchKey}) in a json array
	 *
	 * @param   string    $col     Database column name
	 * @param   string    $key     JSON field to lookup for
	 * @param   string[]  $values  Value to match
	 *
	 * @return  void
	 *
	 * @since   2.0.0
	 */
	public function filterIntersectJsonKey($col, $key, $values)
	{
		$query = $this->getQuery();

		if (count($values) === 1)
		{
			$this->filterInJsonKey($col, $key, reset($values));
		}
		else
		{
			$query->where(sprintf('json_intersect_key(%s, %s, %s)', $query->qn($col), $query->q($key), $query->q(json_encode($values))));
		}
	}

	/**
	 * Filter matching the given value is a value or a key (as indicated with {$matchKey}) in a json array
	 *
	 * @param   string  $col    Database column name
	 * @param   string  $key    JSON field to lookup for
	 * @param   string  $value  Value to match
	 *
	 * @return  void
	 *
	 * @since   2.0.0
	 */
	public function filterInJsonKey($col, $key, $value)
	{
		$query = $this->getQuery();

		$query->where(sprintf('json_in_key(%s, %s, %s)', $col, $query->q($key), $query->q($value)));
	}
}
