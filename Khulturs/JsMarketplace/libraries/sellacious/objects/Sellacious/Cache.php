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
namespace Sellacious;

defined('_JEXEC') or die;

/**
 * Sellacious Cache Object.
 *
 * @since  1.5.0
 */
abstract class Cache
{
	/**
	 * Sellacious application helper object.
	 *
	 * @var    \SellaciousHelper
	 *
	 * @since  1.5.0
	 */
	protected $helper;

	/**
	 * The database driver object.
	 *
	 * @var    \JDatabaseDriver
	 *
	 * @since  1.5.0
	 */
	protected $db;

	/**
	 * The main cache table
	 *
	 * @var    string
	 *
	 * @since  1.7.0
	 */
	protected $cacheTable;

	/**
	 * The temporary working cache table
	 *
	 * @var    string
	 *
	 * @since  1.7.0
	 */
	protected $tmpCacheTable;

	/**
	 * Constructor
	 *
	 * @throws  \Exception
	 *
	 * @since   1.5.0
	 */
	public function __construct()
	{
		$this->helper = \SellaciousHelper::getInstance();
		$this->db     = \JFactory::getDbo();
	}

	/**
	 * Build the cache
	 *
	 * @return  void
	 *
	 * @throws  \Exception
	 *
	 * @since   1.5.0
	 */
	abstract public function build();

	/**
	 * Method to create and populate the temporary working cache table
	 *
	 * @return  void
	 *
	 * @since   1.7.0
	 */
	protected function setupTemporaryTable()
	{
		$this->db->dropTable($this->tmpCacheTable, true);

		$query = 'CREATE ' . 'TABLE ' . $this->db->qn($this->tmpCacheTable) . ' LIKE ' . $this->db->qn($this->cacheTable);

		$this->db->setQuery($query)->execute();
	}

	/**
	 * Method to write the final cache data from working temporary table to the main cache table
	 *
	 * @return  void
	 *
	 * @since   1.7.0
	 */
	protected function writeCacheTable()
	{
		$this->db->truncateTable($this->cacheTable);

		$query = 'INSERT ' . 'INTO ' . $this->db->qn($this->cacheTable) . ' SELECT * ' . ' FROM ' . $this->db->qn($this->tmpCacheTable);

		$this->db->setQuery($query)->execute();

		$this->db->dropTable($this->tmpCacheTable, true);
	}
}
