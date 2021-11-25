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

// no direct access
defined('_JEXEC') or die;

use JDatabaseDriverSqlite;
use JDatabaseExceptionConnecting;
use RuntimeException;

/**
 * @package  Sellacious\Database\SQLite
 *
 * @since    2.0.0
 */
class SQLiteDatabaseDriver extends JDatabaseDriverSqlite
{
	/**
	 * The registered custom functions
	 *
	 * @var    array
	 *
	 * @since  2.0.0
	 */
	protected $functions = array();

	/**
	 * Constructor
	 *
	 * @param   array  $options  The database to connect
	 *
	 * @since   2.0.0
	 */
	public function __construct($options)
	{
		$options['driver'] = 'sqlite';

		parent::__construct($options);
	}

	/**
	 * Connects to the database if needed and rebinds all custom functions
	 *
	 * @return  void
	 *
	 * @throws  RuntimeException
	 *
	 * @since   2.0.0
	 */
	public function connect()
	{
		if ($this->connection)
		{
			return;
		}

		parent::connect();

		foreach ($this->functions as $name => $callable)
		{
			$this->connection->sqliteCreateFunction($name, $callable);
		}
	}

	/**
	 * Attach a custom function to the database connection
	 *
	 * @param   string    $name      Sqlite function name
	 * @param   callable  $callable  The PHP function to bind, can be a Closure or a class method as well
	 *
	 * @return  void
	 *
	 * @throws  RuntimeException
	 *
	 * @since   2.0.0
	 */
	public function addFunction($name, callable $callable)
	{
		$this->functions[$name] = $callable;

		if ($this->connection)
		{
			// Bind if already connected
			$this->connection->sqliteCreateFunction($name, $callable);
		}
	}

	/**
	 * Method to return a JDatabaseDriver instance
	 *
	 * Instances are unique to the given options and new objects are only created when a unique options array is
	 * passed into the method. This ensures that we don't end up with unnecessary database connection resources
	 *
	 * @param   array  $options  Database filename
	 *
	 * @return  static  A database object
	 *
	 * @throws  RuntimeException
	 *
	 * @since   2.0.0
	 */
	public static function getInstance($options = array())
	{
		$signature = md5(serialize($options));

		if (empty(self::$instances[$signature]))
		{
			try
			{
				self::$instances[$signature] = new static($options);
			}
			catch (RuntimeException $e)
			{
				throw new JDatabaseExceptionConnecting(sprintf('Unable to connect to the internal database: %s', $e->getMessage()), $e->getCode(), $e);
			}
		}

		return self::$instances[$signature];
	}
}
