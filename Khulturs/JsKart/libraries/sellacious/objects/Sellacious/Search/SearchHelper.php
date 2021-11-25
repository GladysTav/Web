<?php
/**
 * @version     2.0.0
 * @package     sellacious
 *
 * @copyright   Copyright (C) 2012-2020 Bhartiy Web Technologies. All rights reserved.
 * @license     SPL Sellacious Private License; see http://www.sellacious.com/spl.html
 * @author      Izhar Aazmi <info@bhartiy.com> - http://www.bhartiy.com
 */
// no direct access.
namespace Sellacious\Search;

defined('_JEXEC') or die;

use Joomla\CMS\Filesystem\Folder;
use Joomla\Utilities\ArrayHelper;
use Sellacious\Search\Adapter\AbstractSearchAdapter;
use Sellacious\Search\Adapter\CategoriesSearchAdapter;
use Sellacious\Search\Adapter\ProductsSearchAdapter;
use Sellacious\Search\Adapter\SellersSearchAdapter;
use Sellacious\Search\Adapter\VariantsSearchAdapter;
use TeamTNT\TNTSearch\Stemmer\PorterStemmer;
use TeamTNT\TNTSearch\TNTSearch;

/**
 * Abstract class for search indexing and lookup
 * Uses TNT search for indexing and keyword matching which requires PHP7
 *
 * @since   1.7.0
 */
class SearchHelper
{
	const SEARCH_TNT_DEFAULT = 0;

	const SEARCH_TNT_AS_YOU_TYPE = 1;

	const SEARCH_TNT_FUZZY = 2;

	const SEARCH_TNT_BOOLEAN = 3;

	protected static $config;

	protected static $adapters = array();

	/**
	 * Method to load adapters for search records
	 *
	 * @return  AbstractSearchAdapter[]
	 *
	 * @since   1.7.0
	 */
	public static function getAdapters()
	{
		if (!static::$adapters)
		{
			static::$adapters[] = new ProductsSearchAdapter;
			static::$adapters[] = new VariantsSearchAdapter;
			static::$adapters[] = new CategoriesSearchAdapter;
			static::$adapters[] = new SellersSearchAdapter;

			$dispatcher = \JEventDispatcher::getInstance();

			$dispatcher->trigger('onLoadAdapters', array('com_sellacious.search', &static::$adapters));
		}

		return static::$adapters;
	}

	/**
	 * Get the configuration params for TNT search API
	 *
	 * @return  array
	 *
	 * @throws  \Exception
	 *
	 * @since   1.7.0
	 */
	protected static function getTntConfig()
	{
		if (!static::$config)
		{
			$config  = \JFactory::getConfig();
			$storage = JPATH_ROOT . '/search-indexes';

			// Create storage dir if not exists
			if (!Folder::exists($storage))
			{
				if (Folder::create($storage))
				{
					file_put_contents($storage . '/index.html', '<html lang="en"></html>');
				}
				else
				{
					throw new \Exception(\JText::_('COM_SELLACIOUS_SEARCH_INDEX_DIR_NOT_WRITABLE'));
				}
			}

			// Only mysql is supported
			static::$config = array(
				'driver'   => 'mysql',
				'host'     => $config->get('host', 'localhost'),
				'database' => $config->get('db'),
				'username' => $config->get('user', 'root'),
				'password' => $config->get('password'),
				'storage'  => $storage,
				'stemmer'  => PorterStemmer::class,
				'secret'   => $config->get('secret'),
			);
		}

		return static::$config;
	}

	/**
	 * Create a TNT search index
	 *
	 * @param   string  $name        The name of the
	 * @param   string  $query       The database query for the data to be indexed for search keyword matching
	 * @param   string  $primaryKey  The primary key for the result-set
	 * @param   bool    $includeKey  Whether to include the primary key when matching keyword
	 *
	 * @return  void
	 *
	 * @throws  \Exception
	 *
	 * @since   1.7.0
	 */
	public static function createIndex($name, $query, $primaryKey = 'id', $includeKey = false)
	{
		$dbo    = \JFactory::getDbo();
		$config = static::getTntConfig();
		$tnt    = new TNTSearch;

		$tnt->loadConfig($config);

		$hash    = md5($name . $config['secret']);
		$indexer = $tnt->createIndex($name . '-' . $hash . '.ssi');

		$indexer->query($dbo->replacePrefix($query));
		$indexer->setPrimaryKey($primaryKey);

		if ($includeKey)
		{
			$indexer->includePrimaryKey();
		}

		// Indexer would output text to stdout, we must discard it
		ob_start();
		$indexer->run();
		ob_end_clean();
	}

	/**
	 * Check whether the given TNT search index exists
	 *
	 * @param   string  $name  The name of the
	 *
	 * @return  TNTSearch
	 *
	 * @throws  \Exception
	 *
	 * @since   1.7.0
	 */
	public static function selectIndex($name)
	{
		$config = static::getTntConfig();
		$tnt    = new TNTSearch;

		$hash = md5($name . $config['secret']);

		$tnt->loadConfig($config);
		$tnt->selectIndex($name . '-' . $hash . '.ssi');

		return $tnt;
	}

	/**
	 * Method to retrieve the data from the database using TNT Search API
	 *
	 * @param   string  $keyword  The search query
	 * @param   int     $start    List offset for paginated results
	 * @param   int     $limit    List limit for paginated results
	 * @param   array   $filters  The additional search filters
	 *
	 * @return  array  The suggested words, the result categories
	 *
	 * @since   1.7.0
	 */
	public static function search($keyword, $start = 0, $limit = 20, $filters = array())
	{
		$searches = array();
		$adapters = SearchHelper::getAdapters();
		$mode     = ArrayHelper::getValue($filters, ':mode', self::SEARCH_TNT_AS_YOU_TYPE, 'int');

		$total  = $start + $limit;
		$offset = $start;
		$range  = $limit;
		$found  = 0;

		foreach ($adapters as $adapter)
		{
			try
			{
				$tnt = static::selectIndex($adapter->getName());

				switch ($mode)
				{
					case self::SEARCH_TNT_BOOLEAN:
						$results = $tnt->searchBoolean($keyword, null);
						break;

					case self::SEARCH_TNT_FUZZY:
						// Fuzzy search is little complex
						// as it searches for several variations of given keyword
						$results = array();
						break;

					case self::SEARCH_TNT_AS_YOU_TYPE:
						$tnt->asYouType = true;

						$results = $tnt->search($keyword, null);
						break;

					default:
						$results = $tnt->search($keyword, null);
						break;
				}

				$pks = ArrayHelper::getValue($results, 'ids', array(), 'array');

				$adapter->setKeys($pks)->setFilters($filters)->setRange($offset, $range);

				$matches = $adapter->load();
				$hit     = $adapter->getTotal();

				$searches[] = $matches;

				$count = count($matches);

				$found += $count;

				if ($offset > 0)
				{
					$offset = max(0, $offset - $hit);
				}

				if ($range > 0)
				{
					$range = max(0, $range - $count);
				}

				if ($found >= $total)
				{
					break;
				}
			}
			catch (\Exception $e)
			{
				// Ignore for now
				$matches = array();

				\JLog::add($e->getMessage(), \JLog::WARNING, 'debug');
			}
		}

		$searches = array_reduce($searches, 'array_merge', array());

		return $searches;
	}

	/**
	 * Check whether the given TNT search index exists
	 *
	 * @param   string  $indexName  The name of the index store
	 * @param   string  $keyword    The search keyword
	 * @param   int     $limit      Number of search results to return
	 * @param   int     $mode       The search mode to use: default = 0, as_you_type = 1, fuzzy = 2, boolean = 3
	 *
	 * @return  array
	 *
	 * @throws  \Exception
	 *
	 * @since   1.7.0
	 */
	public static function searchByKeyword($indexName, $keyword, $limit = 20, $mode = self::SEARCH_TNT_DEFAULT)
	{
		$tnt = static::selectIndex($indexName);

		if ($mode == self::SEARCH_TNT_BOOLEAN)
		{
			$results = $tnt->searchBoolean($keyword, $limit);
		}
		elseif ($mode == self::SEARCH_TNT_FUZZY)
		{
			$results = $tnt->fuzzySearch($keyword);
		}
		elseif ($mode == self::SEARCH_TNT_AS_YOU_TYPE)
		{
			$tnt->asYouType = true;

			$results = $tnt->search($keyword, $limit);
		}
		else
		{
			$results = $tnt->search($keyword, $limit);
		}

		return $results;
	}
}
