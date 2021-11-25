<?php
/**
 * @version     2.0.0
 * @package     sellacious
 *
 * @copyright   Copyright (C) 2012-2020 Bhartiy Web Technologies. All rights reserved.
 * @license     SPL Sellacious Private License; see http://www.sellacious.com/spl.html
 * @author      Izhar Aazmi <info@bhartiy.com> - http://www.bhartiy.com
 */
namespace Sellacious\Media\File;

use Exception;
use JLog;
use Sellacious\Media\Storage\MediaDatabaseTable;

/**
 * Media file helper utility class
 *
 * @since   1.7.0
 */
class MediaFileHelper
{
	/**
	 * Get all files for the given context
	 *
	 * @param   string  $tableName
	 * @param   int     $recordId
	 * @param   string  $field
	 *
	 * @return  MediaFile[]
	 *
	 * @since   1.7.0
	 */
	public static function getFiles($tableName, $recordId, $field)
	{
		$files = array();

		try
		{
			$storage = new MediaDatabaseTable($tableName);
			$items   = $storage->getList($field, $recordId);

			foreach ($items as $item)
			{
				if (file_exists(JPATH_ROOT . '/' . $item->path))
				{
					$files[] = new MediaFile($item->path);
				}
			}
		}
		catch (Exception $e)
		{
			JLog::add($e->getMessage(), JLog::WARNING);
		}

		return $files;
	}

	/**
	 * Get a single file for the given context
	 *
	 * @param   string  $tableName
	 * @param   int     $recordId
	 * @param   string  $field
	 *
	 * @return  MediaFile
	 *
	 * @since   1.7.0
	 */
	public static function getFile($tableName, $recordId, $field)
	{
		try
		{
			$storage = new MediaDatabaseTable($tableName);
			$items   = $storage->getList($field, $recordId);

			foreach ($items as $item)
			{
				if (file_exists(JPATH_ROOT . '/' . $item->path))
				{
					return new MediaFile($item->path);
				}
			}
		}
		catch (Exception $e)
		{
			JLog::add($e->getMessage(), JLog::WARNING);
		}

		return null;
	}

	/**
	 * Get filesystem paths
	 *
	 * @param   MediaFile[]  $images    The original images
	 * @param   bool         $absolute
	 *
	 * @return  string[]
	 *
	 * @since   1.7.0
	 */
	public static function getPaths(array $images, $absolute = false)
	{
		$paths = array();

		foreach ($images as $image)
		{
			$paths[] = $image->getPath($absolute);
		}

		return $paths;
	}

	/**
	 * Get URL to files
	 *
	 * @param   MediaFile[]  $images    The original images
	 * @param   bool         $absolute
	 *
	 * @return  string[]
	 *
	 * @since   1.7.0
	 */
	public static function getUrls(array $images, $absolute = false)
	{
		$urls = array();

		foreach ($images as $image)
		{
			$urls[] = $image->getUrl($absolute);
		}

		return $urls;
	}

}
