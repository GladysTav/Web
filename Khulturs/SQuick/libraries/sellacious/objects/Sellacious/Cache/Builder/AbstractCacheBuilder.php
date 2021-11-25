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
use JFile;
use JFolder;
use Joomla\Registry\Registry;
use PDO;
use PDOStatement;
use Sellacious\Cache\CacheHelper;

/**
 * Class AbstractCacheBuilder
 *
 * @package  Sellacious\Cache
 *
 * @since   2.0.0
 */
abstract class AbstractCacheBuilder
{
	/**
	 * Cache object name
	 *
	 * @var   string
	 *
	 * @since   2.0.0
	 */
	protected $name;

	/**
	 * PDO connection object to the storage database
	 *
	 * @var   PDO
	 *
	 * @since   2.0.0
	 */
	protected $storage;

	/**
	 * Names of the columns names to be used in the cache table
	 *
	 * @var   string[]
	 *
	 * @since   2.0.0
	 */
	protected $columns = array();

	/**
	 * PDO prepared statement for record insertion
	 *
	 * @var   PDOStatement
	 *
	 * @since   2.0.0
	 */
	protected $statement;

	/**
	 * Record index to track how many records are currently appended. Not using <var>count($records)</var> intentionally
	 *
	 * @var   int
	 *
	 * @since   2.0.0
	 */
	protected $cursor = 0;

	/**
	 * Records queued to be added to the cache table
	 *
	 * @var   Registry[]
	 *
	 * @since   2.0.0
	 */
	protected $records = array();

	/**
	 * Number of records to hold before writing out data to the cache table. Zero value means no auto writing.
	 *
	 * @var   int
	 *
	 * @since   2.0.0
	 */
	protected $implicitFlush = 0;

	/**
	 * CacheBuilder constructor
	 *
	 * @param   string  $name  Cache object name
	 *
	 * @throws  Exception
	 *
	 * @since   2.0.0
	 */
	public function __construct($name)
	{
		$this->name = $name;
	}

	/**
	 * Load the cache storage database if it exists
	 *
	 * @param   bool  $create  Whether to create the storage file if does not exist
	 *
	 * @return  void
	 *
	 * @throws  Exception
	 *
	 * @since   2.0.0
	 */
	public function loadStorage($create = true)
	{
		$filename = CacheHelper::getFilename($this->name);

		if (!file_exists($filename) && !$create)
		{
			throw new Exception('Cache storage not found for ' . $this->name);
		}

		if (!JFolder::exists(dirname($filename)))
		{
			JFolder::create(dirname($filename));
		}

		$storage = new PDO('sqlite:' . $filename);

		$storage->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
		$storage->setAttribute(PDO::ATTR_CASE, PDO::CASE_LOWER);

		$this->storage = $storage;
	}

	/**
	 * Load the cache storage database if it exists
	 *
	 * @return  void
	 *
	 * @throws  Exception
	 *
	 * @since   2.0.0
	 */
	public function createTemporaryStorage()
	{
		$filename = CacheHelper::getFilename($this->name);
		$filename = $filename . '.tmp';

		if (file_exists($filename))
		{
			JFile::delete($filename);
		}

		if (!JFolder::exists(dirname($filename)))
		{
			JFolder::create(dirname($filename));
		}

		$storage = new PDO('sqlite:' . $filename);

		$storage->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
		$storage->setAttribute(PDO::ATTR_CASE, PDO::CASE_LOWER);

		$this->storage = $storage;
	}

	/**
	 * Build the complete cache
	 *
	 * @return  void
	 *
	 * @since   2.0.0
	 */
	abstract public function build();

	/**
	 * Delete specific row(s) from the storage database
	 *
	 * @param   array  $keys  The filter for the records to be deleted
	 *
	 * @return  void
	 *
	 * @since   2.0.0
	 */
	abstract public function delete($keys);

	/**
	 * Get a list of columns relevant to the given table
	 *
	 * @param   string  $tableName  The storage table name to insert into, defaults to current db name
	 *
	 * @return  array
	 *
	 * @since   2.0.0
	 */
	abstract protected function getColumns($tableName);

	/**
	 * Drop the storage table
	 *
	 * @param   string  $tableName  The storage table name to insert into, defaults to current db name
	 *
	 * @return  void
	 *
	 * @throws  Exception
	 *
	 * @since   2.0.0
	 */
	public function dropTable($tableName = null)
	{
		$this->storage->exec('DROP TABLE IF EXISTS ' . $tableName);

		$this->storage->exec('VACUUM');
	}

	/**
	 * Create the storage table
	 *
	 * @param   string  $tableName  The storage table name to insert into, defaults to current db name
	 *
	 * @return  void
	 *
	 * @throws  Exception
	 *
	 * @since   2.0.0
	 */
	public function setupTable($tableName = null)
	{
		$tableName = $tableName ?: $this->name;
		$columns   = $this->getColumns($tableName);
		$cols      = array('id INTEGER NOT NULL PRIMARY KEY AUTOINCREMENT');

		foreach ($columns as $col)
		{
			$cols[] = $col . ' VARCHAR';
		}

		$query = sprintf('CREATE TABLE IF NOT EXISTS %s (%s)', $tableName, implode(', ', $cols));

		$this->storage->query($query);
	}

	/**
	 * Enable implicit flush to the output database
	 *
	 * @param   int  $value  Flush automatically after {$value} records are appended. Set zero value to disable.
	 *
	 * @return  void
	 *
	 * @since   2.0.0
	 */
	public function implicitFlush($value = 0)
	{
		$this->implicitFlush = (int) $value;
	}

	/**
	 * Select the storage table for inserts
	 *
	 * @param   string  $tableName  The storage table name to insert into, defaults to current db name
	 *
	 * @return  void
	 *
	 * @throws  Exception
	 *
	 * @since   2.0.0
	 */
	public function beginInsert($tableName = null)
	{
		$this->reset();

		$this->statement = $this->createStatement($tableName ?: $this->name);
	}

	/**
	 * Release the held storage table for inserts
	 *
	 * @return  void
	 *
	 * @since   2.0.0
	 */
	public function endInsert()
	{
		$this->reset();

		$this->statement = null;
	}

	/**
	 * Append a new record to the cache,
	 * It will be written to the storage automatically if implicit flush is enabled
	 * Otherwise you need to call <var>flush()</var> when needed
	 *
	 * @param   Registry  $record  The record to insert
	 *
	 * @return  void
	 *
	 * @throws  Exception
	 *
	 * @since   2.0.0
	 */
	public function append(Registry $record)
	{
		$this->records[] = $record;

		$this->cursor++;

		if ($this->implicitFlush > 0 && $this->cursor >= $this->implicitFlush)
		{
			$this->flush();
		}
	}

	/**
	 * Write the records stored in write queue to the storage
	 *
	 * @return  void
	 *
	 * @throws  Exception
	 *
	 * @since   2.0.0
	 */
	public function flush()
	{
		try
		{
			if (!isset($this->statement))
			{
				throw new Exception('The cache build insert batch was not initialised.');
			}

			$this->storage->beginTransaction();

			foreach ($this->records as $record)
			{
				$this->statement->execute($record->toArray());
			}

			$this->storage->commit();

			$this->reset();
		}
		catch (Exception $e)
		{
			$this->storage->rollback();

			throw $e;
		}
	}

	/**
	 * Reset the write queue
	 * This should be called after writing the cache or when a write is not desired
	 *
	 * @return  void
	 *
	 * @since   2.0.0
	 */
	public function reset()
	{
		$this->records = array();
		$this->cursor  = 0;
	}

	/**
	 * Create a prepared statement from PDO instance for record inserts
	 *
	 * @param   string  $tableName  The storage table name to insert into, defaults to current db name
	 *
	 * @return  PDOStatement
	 *
	 * @since   2.0.0
	 */
	protected function createStatement($tableName)
	{
		$columns = $this->getTableColumns($tableName);
		$values  = array_map(function ($s) { return ':' . $s; }, $columns);

		$query = sprintf("INSERT INTO %s (%s) VALUES (%s);", $tableName, implode(', ', $columns), implode(', ', $values));

		return $this->storage->prepare($query);
	}

	/**
	 * Retrieves field list for a given table
	 *
	 * @param   string  $tableName  The name of the database table
	 *
	 * @return  array  An array of fields for the database table
	 *
	 * @since   2.0.0
	 */
	protected function getTableColumns($tableName)
	{
		$columns = array();
		$result  = $this->storage->query('pragma table_info(' . $tableName . ')');

		while ($field = $result->fetchObject())
		{
			$columns[] = $field->name;
		}

		return $columns;
	}


	/**
	 * Replace the actual cache database with the newly created temporary cache storage
	 *
	 * @return  void
	 *
	 * @since   2.0.0
	 */
	protected function setLive()
	{
		$name = CacheHelper::getFilename($this->name);

		JFile::delete($name);

		if (IS_WIN)
		{
			// File lock is not released at this point, so just copy! Fixes for later.
			JFile::copy($name . '.tmp', $name);
		}
		else
		{
			JFile::move($name . '.tmp', $name);
		}
	}
}
