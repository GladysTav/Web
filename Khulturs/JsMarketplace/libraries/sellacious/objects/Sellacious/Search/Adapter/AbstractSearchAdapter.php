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
namespace Sellacious\Search\Adapter;

use Joomla\Registry\Registry;

defined('_JEXEC') or die;

/**
 * Abstract class for search adapters
 *
 * @since   1.7.0
 */
abstract class AbstractSearchAdapter
{
	/**
	 * Name of the adapter
	 *
	 * @var   string
	 *
	 * @since   1.7.0
	 */
	protected $name;

	/**
	 * The database driver instance
	 *
	 * @var   \JDatabaseDriver
	 *
	 * @since   1.7.0
	 */
	protected $db;

	/**
	 * The application instance
	 *
	 * @var   \JApplicationCms
	 *
	 * @since   1.7.0
	 */
	protected $app;

	/**
	 * The database driver instance
	 *
	 * @var   \SellaciousHelper
	 *
	 * @since   1.7.0
	 */
	protected $helper;

	/**
	 * Primary key values for the records to limit the search within
	 *
	 * @var   int[]
	 *
	 * @since   1.7.0
	 */
	protected $keys = array();

	/**
	 * Additional filters for the search
	 *
	 * @var   Registry
	 *
	 * @since   1.7.0
	 */
	protected $filters;

	/**
	 * Total number of matching records found
	 *
	 * @var   int
	 *
	 * @since   1.7.0
	 */
	protected $total;

	/**
	 * Total number of records to skip
	 *
	 * @var   int
	 *
	 * @since   1.7.0
	 */
	protected $start;

	/**
	 * Maximum number of records to return
	 *
	 * @var   int
	 *
	 * @since   1.7.0
	 */
	protected $limit;

	/**
	 * The iterator after applying all the filters
	 *
	 * @var   \JDatabaseIterator
	 *
	 * @since   1.7.0
	 */
	protected $iterator;

	/**
	 * AbstractSearchAdapter constructor
	 *
	 * @throws  \Exception
	 *
	 * @since   1.7.0
	 */
	public function __construct()
	{
		$this->db     = \JFactory::getDbo();
		$this->app    = \JFactory::getApplication();
		$this->helper = \SellaciousHelper::getInstance();
	}

	/**
	 * Method to get the adapter name
	 *
	 * @return  string  The name of the dispatcher
	 *
	 * @since   1.7.0
	 */
	public function getName()
	{
		if (empty($this->name))
		{
			$r = null;

			if (preg_match('/([^\\\]+)SearchAdapter$/i', static::class, $r))
			{
				$this->name = strtolower($r[1]);
			}
			else
			{
				$this->name = md5(static::class);
			}
		}

		return $this->name;
	}

	/**
	 * Generate search index for TNT Search API based searches
	 *
	 * @return  void
	 *
	 * @throws  \Exception
	 *
	 * @since   1.7.0
	 */
	abstract public function buildIndex();

	/**
	 * Perform a search for the relevant entity
	 *
	 * @return  \stdClass[]
	 *
	 * @since   1.7.0
	 */
	abstract public function load();

	/**
	 * Set the id values to limit the search within
	 *
	 * @param   int[]  $pks  The record ids for the matching items based on the keyword
	 *
	 * @return  static
	 *
	 * @since   1.7.0
	 */
	public function setKeys(array $pks)
	{
		$this->keys = $pks;

		return $this;
	}

	/**
	 * Set additional filter values to limit the search
	 *
	 * @param   array  $filters  The filters values to be set
	 *
	 * @return  static
	 *
	 * @since   1.7.0
	 */
	public function setFilters(array $filters)
	{
		$this->filters  = new Registry($filters);
		$this->iterator = null;

		return $this;
	}

	/**
	 * Set list offset and limit to limit the search
	 *
	 * @param   int  $start  List offset for paginated results
	 * @param   int  $limit  List limit for paginated results
	 *
	 * @return  static
	 *
	 * @since   1.7.0
	 */
	public function setRange($start = 0, $limit = 0)
	{
		$this->start = $start;
		$this->limit = $limit;

		return $this;
	}

	/**
	 * Get total number of matching records
	 *
	 * @return  int
	 *
	 * @since   1.7.0
	 */
	public function getTotal()
	{
		if ($this->iterator === null)
		{
			$this->getIterator();
		}

		return (int) $this->iterator->count();
	}

	/**
	 * Get total number of matching records
	 *
	 * @return  \JDatabaseIterator
	 *
	 * @since   1.7.0
	 */
	protected function getIterator()
	{
		if ($this->iterator === null)
		{
			$this->iterator = $this->execute();
		}

		return $this->iterator;
	}

	/**
	 * Execute the search query. Also set number of matching records found
	 *
	 * @return  \JDatabaseIterator
	 *
	 * @since   1.7.0
	 */
	abstract protected function execute();
}
