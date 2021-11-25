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
namespace Sellacious\Media;

use Exception;

defined('_JEXEC') or die;

/**
 * Sellacious media helper object.
 *
 * @since   1.5.2
 */
class MediaHelper
{
	/**
	 * Add or replace an allowed file category for uploads. The file extension is returned both with and without the dot.
	 *
	 * @param   string|string[]  $type  The category or categories to load
	 *
	 * @return  array  An array containing two array elements [mime, extensions]
	 *
	 * @throws  \Exception
	 *
	 * @since   1.5.2
	 */
	public static function getTypeInfo($type)
	{
		$types = is_string($type) ? explode(',', $type) : (array) $type;

		$db    = \JFactory::getDbo();
		$query = $db->getQuery(true);

		$query->select('a.mime, a.extension')->from($db->qn('#__sellacious_mimes', 'a'))->where('a.state = 1');

		if (count($types))
		{
			$query->where('a.category IN (' . implode(', ', $db->q($types)) . ')');
		}

		$result = (array) $db->setQuery($query)->loadObjectList();

		$mime = array();
		$ext  = array();

		foreach ($result as $value)
		{
			$value->extension = strtolower($value->extension);
			$value->mime      = strtolower($value->mime);

			$mime[] = $value->mime;
			$ext[]  = $value->extension;
			$ext[]  = ltrim($value->extension, '.');
		}

		return array($mime, $ext);
	}

	/**
	 * Get the file extension from the given file name.
	 *
	 * @param   string  $fileName  The file name to process
	 *
	 * @return  string
	 *
	 * @since   1.5.2
	 */
	public static function getExtension($fileName)
	{
		$dot = strrpos($fileName, '.');

		return $dot === false ? '' : substr($fileName, $dot + 1);
	}

	/**
	 * Remove the file extension from the given file name.
	 *
	 * @param   string  $fileName  The file name to process
	 *
	 * @return  string
	 *
	 * @since   1.5.2
	 */
	public static function stripExtension($fileName)
	{
		return preg_replace('#\.[^.]*$#', '', $fileName);
	}

	/**
	 * Sanitize a path for special characters and extra path separators
	 *
	 * @param   string  $path   The path to sanitize
	 * @param   bool    $extra  Whether to make the path more safe, usable when building new path to write to,
	 *                          while reading this can be set to false.
	 *
	 * @return  mixed
	 *
	 * @since   1.5.2
	 */
	public static function sanitize($path, $extra = false)
	{
		$regex = $extra ? array(
			'ds'         => '#[/\\\\]+#',
			'ds-dot-dot' => '#[/\\\\](\.){2,}#',
			'special'    => '#[^A-Za-z0-9\.\_\-\/ ]+#',
			'spaces'     => '#[ ]+#',
		) : array(
			'ds'         => '#[/\\\\]+#',
			'ds-dot-dot' => '#[/\\\\](\.){2,}#',
		);

		return preg_replace($regex, array('/', '/', '-', ' '), $path);
	}

	/**
	 * Get the Mime type for the given file
	 *
	 * @param   string   $file     The link to the file to be checked
	 * @param   boolean  $isImage  True if the passed file is an image else false
	 *
	 * @return  string   The mime type detected or null
	 *
	 * @since   1.6.0
	 */
	public static function getMimeType($file, $isImage = false)
	{
		try
		{
			$mime = null;

			if ($isImage)
			{
				if (function_exists('exif_imagetype'))
				{
					$mime = image_type_to_mime_type(exif_imagetype($file));
				}

				if (($mime === null || $mime === 'application/octet-stream') && function_exists('getimagesize'))
				{
					$sz   = getimagesize($file);
					$mime = isset($sz['mime']) ? $sz['mime'] : null;
				}
			}

			if (($mime === null || $mime === 'application/octet-stream') && function_exists('mime_content_type'))
			{
				$mime = mime_content_type($file);
			}

			if (($mime === null || $mime === 'application/octet-stream') && function_exists('finfo_open'))
			{
				$finfo = finfo_open(FILEINFO_MIME_TYPE);
				$mime  = finfo_file($finfo, $file);
				finfo_close($finfo);
			}
		}
		catch (Exception $e)
		{
		}

		return $mime;
	}

	/**
	 * Check whether the given file is an image
	 *
	 * @param   string  $file       The absolute path to the file to be checked
	 * @param   bool    $mimeCheck  Whether to check mime type as well
	 *
	 * @return  bool
	 *
	 * @since   1.7.0
	 */
	public static function isImage($file, $mimeCheck = true)
	{
		try
		{
			$ext = static::getExtension($file);

			list($mimes, $extensions) = MediaHelper::getTypeInfo('image');

			if (!in_array(strtolower($ext), $extensions))
			{
				return false;
			}

			if ($mimeCheck)
			{
				$mime = static::getMimeType($file);

				if (!in_array(strtolower($mime), $mimes))
				{
					return false;
				}
			}

			return true;
		}
		catch (Exception $e)
		{
			return false;
		}
	}

	/**
	 * Recursively deletes any empty sub-folders in the given folder
	 *
	 * @param   string  $path           The main folder absolute path
	 * @param   bool    $removeCurrent  Whether to remove the given parent directory as well
	 *
	 * @return  bool
	 *
	 * @since   1.7.0
	 */
	public static function removeEmptyDir($path, $removeCurrent = true)
	{
		if (!is_dir($path))
		{
			return true;
		}

		$empty = true;

		foreach (glob($path . '/*') as $file)
		{
			$empty = is_dir($file) ? $empty & static::removeEmptyDir($file) : false;
		}

		if ($empty && $removeCurrent)
		{
			return \JFolder::delete($path);
		}

		return $empty;
	}
}
