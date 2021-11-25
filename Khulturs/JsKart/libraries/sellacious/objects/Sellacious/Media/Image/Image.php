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

use JFolder;
use Sellacious\Media\File\MediaFile;

/**
 * Image object class
 *
 * @property   string  $path
 *
 * @since   1.7.0
 */
class Image extends MediaFile
{
	/**
	 * The Image object instance containing the reference to the source file if this object points to a copy
	 *
	 * @var   static
	 *
	 * @since   1.7.0
	 */
	protected $source;

	/**
	 * Method to get a custom resized copy of the same image
	 *
	 * @param   int  $width    Max width of the image
	 * @param   int  $height   Max height of the image
	 * @param   int  $quality  The output image quality in percentage [1-100]
	 * @param   int  $mode     Scale option for the image, see constants self::RESIZE_*
	 *
	 * @return  self
	 *
	 * @since   1.7.0
	 */
	public function getResized($width, $height, $quality = 60, $mode = ResizeImage::RESIZE_BOUND)
	{
		if ($this->source)
		{
			return $this->source->getResized($width, $height, $quality, $mode);
		}

		$filename = sprintf('cache/images/%dx%d@%d-%s/%s', $width, $height, $quality, $mode, $this->path);
		$filePath = JPATH_ROOT . '/' . $filename;

		if (!file_exists($filePath))
		{
			$path   = $this->getPath(true);
			$resize = new ResizeImage($path);

			JFolder::create(dirname($filePath));

			$resize->setInterlace(true);
			$resize->resizeTo($width, $height, $mode);
			$resize->saveImage($filePath, $quality);
		}

		if (file_exists($filePath))
		{
			$image = new Image($filename);

			$image->setSource($this);

			return $image;
		}

		return null;
	}

	/**
	 * Set the source original image reference
	 *
	 * @param   Image  $source
	 *
	 * @return  void
	 *
	 * @since   1.7.0
	 */
	public function setSource(Image $source)
	{
		$this->source = $source;
	}
}
