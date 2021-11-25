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
namespace Sellacious\Cache;

use Exception;
use JFactory;
use JLog;
use JPath;
use Sellacious\Cache\Builder\ProductsCacheBuilder;
use Sellacious\Media\MediaCleanup;

defined('_JEXEC') or die;

/**
 * Sellacious Cache helper class.
 *
 * @since  1.6.1
 */
class CacheHelper
{
	/**
	 * Method to queue the cli based cache builder to run in the background
	 *
	 * @param   string  $logfile  Path to the log file where the generated output will be saved
	 * @param   int     $userId   The masked joomla/sellacious user as which the process will be run
	 *
	 * @return  int
	 *
	 * @since   1.6.1
	 */
	public static function executeCli($logfile, $userId)
	{
		$config     = JFactory::getConfig();
		$executable = $config->get('php_executable', 'php');
		$script     = escapeshellarg(JPATH_SELLACIOUS . '/cli/sellacious_cache.php');
		$logfileE   = escapeshellarg($logfile);

		// Truncate/initialize log file
		file_put_contents($logfile, '');

		if (IS_WIN)
		{
			$CMD = "{$executable} {$script} --user={$userId} --log={$logfileE} > {$logfileE}";

			return shell_exec($CMD);
		}
		else
		{
			$CMD = "{$executable} {$script} --user={$userId} --log={$logfileE} > {$logfileE} 2> {$logfileE} & echo \$!";

			return exec($CMD);
		}
	}

	/**
	 * Build the cache for all cache handlers.
	 * We'd soon load the handlers dynamically instead of hard-coding here
	 *
	 * @return  void
	 *
	 * @since   1.6.1
	 */
	public static function buildCache()
	{
		try
		{
			$builder = new ProductsCacheBuilder;

			$builder->build();
		}
		catch (Exception $e)
		{
			JLog::add($e->getMessage(), JLog::WARNING);
		}

		try
		{
			$cleanup = new MediaCleanup;

			$cleanup->execute();
		}
		catch (Exception $e)
		{
			JLog::add($e->getMessage(), JLog::WARNING);
		}
	}

	/**
	 * Running status check when running by Cli,
	 * if running in a web session this will return false anyway
	 *
	 * @return  bool
	 *
	 * @since   1.6.1
	 */
	public static function isRunning()
	{
		$tmp = JFactory::getConfig()->get('tmp_path');

		return file_exists($tmp . '/.s-cache-lock');
	}

	/**
	 * Method to get the file name for the cache database for given object type
	 *
	 * @param   string  $element  The item type to be cached
	 * @param   string  $secret   The secret key to encode filename, uses JConfig->secret if omitted
	 *
	 * @return  string
	 *
	 * @since   2.0.0
	 */
	public static function getFilename($element, $secret = null)
	{
		$secret   = $secret ?: JFactory::getConfig()->get('secret');
		$filename = JPath::clean($element . '-' . md5($element . $secret));

		return JPATH_SELLACIOUS . '/cache/sellacious/' . $filename . '.scdb';
	}
}
