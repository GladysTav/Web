<?php
/**
 * @version     2.0.0
 * @package     sellacious
 *
 * @copyright   Copyright (C) 2012-2020 Bhartiy Web Technologies. All rights reserved.
 * @license     SPL Sellacious Private License; see http://www.sellacious.com/spl.html
 * @author      Izhar Aazmi <info@bhartiy.com> - http://www.bhartiy.com
 */
namespace Sellacious\Media\Image;

use Exception;
use JFactory;
use JLog;
use Sellacious\Media\File\MediaFileHelper;
use Sellacious\Media\MediaHelper;
use Sellacious\Media\Storage\MediaDatabaseTable;

/**
 * Image helper utility class
 *
 * @since   1.7.0
 */
class ImageHelper extends MediaFileHelper
{
	/**
	 * Get all images for the given context
	 *
	 * @param   string  $tableName
	 * @param   int     $recordId
	 * @param   string  $field
	 *
	 * @return  Image[]
	 *
	 * @since   1.7.0
	 */
	public static function getImages($tableName, $recordId, $field = 'images')
	{
		$images = array();

		try
		{
			$storage = new MediaDatabaseTable($tableName);
			$items   = $storage->getList($field, $recordId);

			list(, $extensions) = MediaHelper::getTypeInfo('image');

			foreach ($items as $item)
			{
				$ext = MediaHelper::getExtension($item->path);

				if (in_array(strtolower($ext), $extensions) && file_exists(JPATH_ROOT . '/' . $item->path))
				{
					$images[] = new Image($item->path);
				}
			}
		}
		catch (Exception $e)
		{
			JLog::add($e->getMessage(), JLog::WARNING);
		}

		return $images;
	}

	/**
	 * Get a single image for the given context
	 *
	 * @param   string  $tableName
	 * @param   int     $recordId
	 * @param   string  $field
	 *
	 * @return  Image
	 *
	 * @since   1.7.0
	 */
	public static function getImage($tableName, $recordId, $field = 'images')
	{
		try
		{
			$storage = new MediaDatabaseTable($tableName);
			$items   = $storage->getList($field, $recordId);

			list(, $extensions) = MediaHelper::getTypeInfo('image');

			foreach ($items as $item)
			{
				$ext = MediaHelper::getExtension($item->path);

				if (in_array(strtolower($ext), $extensions) && file_exists(JPATH_ROOT . '/' . $item->path))
				{
					return new Image($item->path);
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
	 * Get a placeholder image for the given context
	 *
	 * @param   string  $tableName
	 * @param   string  $field
	 *
	 * @return  Image
	 *
	 * @since   1.7.0
	 */
	public static function getBlank($tableName, $field = 'images')
	{
		if (strpos($tableName, '/') === false)
		{
			list($component, $tableName) = array('com_sellacious', $tableName);
		}
		else
		{
			list($component, $tableName) = explode('/', $tableName);
		}

		try
		{
			$template = JFactory::getApplication()->getTemplate();
		}
		catch (Exception $e)
		{
			$template = 'protostar';
		}

		$paths = array();

		$paths[] = "templates/{$template}/images/{$component}/{$tableName}/{$field}/placeholder.jpg";
		$paths[] = "templates/{$template}/images/{$component}/{$tableName}/{$field}/placeholder.png";
		$paths[] = "media/{$component}/images/{$tableName}/{$field}/placeholder.jpg";
		$paths[] = "media/{$component}/images/{$tableName}/{$field}/placeholder.png";

		$paths[] = "templates/{$template}/images/{$component}/{$tableName}/placeholder.jpg";
		$paths[] = "templates/{$template}/images/{$component}/{$tableName}/placeholder.png";
		$paths[] = "media/{$component}/images/{$tableName}/placeholder.jpg";
		$paths[] = "media/{$component}/images/{$tableName}/placeholder.png";

		$paths[] = "templates/{$template}/images/{$component}/placeholder.jpg";
		$paths[] = "templates/{$template}/images/{$component}/placeholder.png";
		$paths[] = "media/{$component}/images/placeholder.jpg";
		$paths[] = "media/{$component}/images/placeholder.png";

		$paths[] = "templates/{$template}/images/sellacious/placeholder.jpg";
		$paths[] = "templates/{$template}/images/sellacious/placeholder.png";
		$paths[] = "media/sellacious/images/placeholder.jpg";
		$paths[] = "media/sellacious/images/placeholder.png";

		foreach ($paths as $path)
		{
			if (file_exists(JPATH_ROOT . '/' . $path))
			{
				return new Image($path);
			}
		}

		return null;
	}

	/**
	 * Get resized versions of given set of images for the given context
	 *
	 * @param   Image[]  $images   The original images
	 * @param   int      $width    Max width of the image
	 * @param   int      $height   Max height of the image
	 * @param   bool     $blank    Whether to use fallback to blank image
	 * @param   int      $quality  The output image quality in percentage [1-100]
	 * @param   int      $mode     Scale option for the image, see constants ResizeImage::RESIZE_*
	 *
	 * @return  Image[]
	 *
	 * @since   1.7.0
	 */
	public static function getResized(array $images, $width, $height, $blank = false, $quality = 60, $mode = ResizeImage::RESIZE_BOUND)
	{
		$thumbs = array();

		foreach ($images as $image)
		{
			if ($image instanceof Image)
			{
				set_time_limit(30);

				try
				{
					if ($r = $image->getResized($width, $height, $quality, $mode))
					{
						$thumbs[] = $r;
					}
				}
				catch (Exception $e)
				{
					$thumbs[] = $image;
				}
			}
		}

		if (!$thumbs && $blank)
		{
			$placeholder = static::getBlank('com_sellacious/products', 'images');
			$thumbs      = static::getResized(array($placeholder), $width, $height, false, $quality, $mode);
		}

		return $thumbs;
	}
}
