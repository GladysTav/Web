<?php
/**
 * @version     1.7.4
 * @package     sellacious
 *
 * @copyright   Copyright (C) 2012-2019 Bhartiy Web Technologies. All rights reserved.
 * @license     SPL Sellacious Private License; see http://www.sellacious.com/spl.html
 * @author      Izhar Aazmi <info@bhartiy.com> - http://www.bhartiy.com
 */
namespace Sellacious\Media\Image;

// no direct access
defined('_JEXEC') or die;

use InvalidArgumentException;
use JText;
use RuntimeException;
use Sellacious\Media\MediaHelper;

/**
 * Resize image class will allow you to resize an image
 *
 * - Resize to exact size
 * - Max width size while keep aspect ratio
 * - Max height size while keep aspect ratio
 * - Automatic while keep aspect ratio
 *
 * @since   1.7.0
 */
class ResizeImage
{
	const RESIZE_EXACT        = 0;
	const RESIZE_EXACT_WIDTH  = 1;
	const RESIZE_EXACT_HEIGHT = 2;
	const RESIZE_BOUND        = 3;
	const RESIZE_FIT          = 4;
	const RESIZE_FILL         = 5;

	protected $filename;

	protected $srcImage;

	protected $mime;

	protected $srcW;

	protected $srcH;

	protected $dstImage;

	protected $dstMime;

	protected $dstW;

	protected $dstH;

	protected $srcX = 0;

	protected $srcY = 0;

	protected $dstX = 0;

	protected $dstY = 0;

	protected $interlace;

	/**
	 * Class constructor
	 *
	 * @param   string  $filename  Path to the image you want to resize
	 *
	 * @throws  InvalidArgumentException
	 *
	 * @since   1.7.0
	 */
	public function __construct($filename)
	{
		$this->load($filename);
	}

	/**
	 * Class destructor
	 *
	 * @since   1.7.0
	 */
	public function __destruct()
	{
		if ($this->srcImage)
		{
			imagedestroy($this->srcImage);
		}

		if ($this->dstImage)
		{
			imagedestroy($this->dstImage);
		}
	}

	/**
	 * get the dimension of the source image
	 *
	 * @return  array  An array containing width and height
	 *
	 * @since   1.7.0
	 */
	public function getSize()
	{
		return array($this->srcW, $this->srcH);
	}

	/**
	 * Resize the image to the set dimension constraints
	 *
	 * @param   int  $width   Max width of the image
	 * @param   int  $height  Max height of the image
	 * @param   int  $option  Scale option for the image, see constants self::RESIZE_*
	 *
	 * @return  static
	 *
	 * @throws  InvalidArgumentException
	 *
	 * @since   1.7.0
	 */
	public function resizeTo($width, $height, $option = self::RESIZE_BOUND)
	{
		if ($this->srcW < 1 || $this->srcH < 1)
		{
			throw new InvalidArgumentException(JText::sprintf('LIB_SELLACIOUS_MEDIA_IMAGE_RESIZE_INVALID_SOURCE_DIMENSION', $this->srcW, $this->srcH));
		}

		$this->clear();

		$this->setDimension($width, $height, $option);

		if ($this->dstW < 1 || $this->dstH < 1)
		{
			throw new InvalidArgumentException(JText::sprintf('LIB_SELLACIOUS_MEDIA_IMAGE_RESIZE_INVALID_OUTPUT_DIMENSION', $this->dstW, $this->dstH));
		}

		$dstImage = imagecreatetruecolor($this->dstW, $this->dstH);

		$dstW = $this->dstW - $this->dstX * 2;
		$dstH = $this->dstH - $this->dstY * 2;
		$srcW = $this->srcW - $this->srcX * 2;
		$srcH = $this->srcH - $this->srcY * 2;

		imagesavealpha($dstImage, true);
		imagefill($dstImage, 0, 0, imagecolorallocatealpha($dstImage, 255, 255, 255, 127));

		imagecopyresampled($dstImage, $this->srcImage, $this->dstX, $this->dstY, $this->srcX, $this->srcY, $dstW, $dstH, $srcW, $srcH);

		$this->dstImage = $dstImage;

		return $this;
	}

	/**
	 * Save the image as the image type the original image was
	 *
	 * @param   string  $filename  The path to store the new image
	 * @param   int     $quality   The quality level of image to create in percentage (1-100)
	 *
	 * @return  void
	 *
	 * @throws  RuntimeException
	 *
	 * @since   1.7.0
	 */
	public function saveImage($filename, $quality = 100)
	{
		$format = MediaHelper::getExtension($filename);

		$this->setFormat($format);

		$this->render($filename, $quality);
	}

	/**
	 * Download the image as the image type the original image was
	 *
	 * @param   int     $quality  The quality level of image to create in percentage (1-100)
	 * @param   string  $format   The output file format, currently JPG, GIF, PNG are supported. Defaults to original.
	 *
	 * @return  void
	 *
	 * @throws  RuntimeException
	 *
	 * @since   1.7.0
	 */
	public function download($quality = 100, $format = null)
	{
		ob_start();

		$this->setFormat($format);

		$this->render('php://output', $quality);

		$raw = ob_get_clean();

		if (strlen($raw))
		{
			header('Content-Type: ' . $this->dstMime);
			header('Content-Disposition: attachment; filename="' . basename($this->filename) . '"');
			header('Content-Transfer-Encoding: binary');
			header('Cache-Control: private');
			header('Pragma: private');
			header('Expires: Sat, 08 Mar 1986 00:03:00 GMT');

			echo $raw;

			jexit();
		}
	}

	/**
	 * Output the image resource to the given file, stdout can be used as well
	 *
	 * @param   string  $filename  The path to store the new image
	 * @param   int     $quality   The quality level of image to create in percentage (1-100)
	 *
	 * @return  void
	 *
	 * @throws  RuntimeException
	 *
	 * @since   1.7.0
	 */
	protected function render($filename, $quality)
	{
		$image = $this->dstImage ?: $this->srcImage;

		if (($this->dstMime == 'image/jpg' || $this->dstMime == 'image/jpeg') && imagetypes() & IMG_JPG)
		{
			if (is_bool($this->interlace))
			{
				imageinterlace($image, $this->interlace);
			}

			$done = imagejpeg($image, $filename, $quality);
		}
		elseif ($this->dstMime == 'image/gif' && imagetypes() & IMG_GIF)
		{
			$done = imagegif($image, $filename);
		}
		elseif ($this->dstMime == 'image/png' && imagetypes() & IMG_PNG)
		{
			$pngQ = 9 - round(($quality / 100) * 9);
			$done = imagepng($image, $filename, $pngQ);
		}
		elseif ($this->dstMime == 'image/webp' && imagetypes() & IMG_WEBP)
		{
			$done = imagewebp($image, $filename, $quality);
		}
		else
		{
			throw new RuntimeException(JText::sprintf('LIB_SELLACIOUS_MEDIA_IMAGE_RESIZE_INVALID_OUTPUT_FORMAT', $this->dstMime));
		}

		if (!$done)
		{
			throw new RuntimeException(JText::_('LIB_SELLACIOUS_MEDIA_IMAGE_RESIZE_OUTPUT_WRITE_ERROR'));
		}
	}

	/**
	 * Set the image variable by using image create
	 *
	 * @param   string  $filename  The image filename
	 *
	 * @return  void
	 *
	 * @throws  InvalidArgumentException
	 *
	 * @since   1.7.0
	 */
	protected function load($filename)
	{
		if (!file_exists($filename))
		{
			throw new InvalidArgumentException(JText::_('LIB_SELLACIOUS_MEDIA_IMAGE_RESIZE_SOURCE_FILE_NOT_FOUND'));
		}

		$mime = MediaHelper::getMimeType($filename, true);

		if (($mime == 'image/jpg' || $mime == 'image/jpeg') && imagetypes() & IMG_JPG)
		{
			$image = imagecreatefromjpeg($filename);
		}
		elseif ($mime == 'image/gif' && imagetypes() & IMG_GIF)
		{
			$image = @imagecreatefromgif($filename);
		}
		elseif ($mime == 'image/png' && imagetypes() & IMG_PNG)
		{
			$image = @imagecreatefrompng($filename);
		}
		elseif ($mime == 'image/webp' && imagetypes() & IMG_WEBP)
		{
			$image = @imagecreatefromwebp($filename);
		}
		else
		{
			throw new InvalidArgumentException(JText::sprintf('LIB_SELLACIOUS_MEDIA_IMAGE_RESIZE_INVALID_SOURCE_FILE', $mime, $filename));
		}

		$this->filename = $filename;
		$this->srcImage = $image;
		$this->mime     = $mime;
		$this->srcW     = imagesx($image);
		$this->srcH     = imagesy($image);
	}

	/**
	 * Set the output image format from file extension
	 *
	 * @param   string  $format  The output file format, currently supported: JPG, GIF, PNG, WEBP. Defaults to source format.
	 *
	 * @return  void
	 *
	 * @since   1.7.0
	 */
	protected function setFormat($format = null)
	{
		$format = strtolower($format);

		if (in_array($format, array('jpg', 'jpeg', 'png', 'gif', 'webp')))
		{
			$this->dstMime = sprintf('image/%s', $format);
		}
		else
		{
			$this->dstMime = $this->mime;
		}
	}

	/**
	 * Calculate and set the output dimension based on the resize parameters
	 *
	 * @param   int  $width   Max width of the image
	 * @param   int  $height  Max height of the image
	 * @param   int  $option  Scale option for the image, see constants self::RESIZE_*
	 *
	 * @return  void
	 *
	 * @since   1.7.0
	 */
	protected function setDimension($width, $height, $option = self::RESIZE_BOUND)
	{
		if ($option == self::RESIZE_EXACT)
		{
			$this->setSize($width, $height);
		}
		elseif ($option == self::RESIZE_EXACT_WIDTH)
		{
			$this->setWidth($width);
		}
		elseif ($option == self::RESIZE_EXACT_HEIGHT)
		{
			$this->setHeight($height);
		}
		elseif ($option == self::RESIZE_BOUND)
		{
			$this->setBound($width, $height);
		}
		elseif ($option == self::RESIZE_FIT)
		{
			$this->setFit($width, $height);
		}
		elseif ($option == self::RESIZE_FILL)
		{
			$this->setFill($width, $height);
		}
	}

	/**
	 * Set the given width and height and ignore aspect ratio for the output image
	 *
	 * @param   int  $width
	 * @param   int  $height
	 *
	 * @return  void
	 *
	 * @since   1.7.0
	 */
	protected function setSize($width, $height)
	{
		$this->dstW = $width;
		$this->dstH = $height;
	}

	/**
	 * Set the given width and auto calculate height to preserve aspect ratio for the output image
	 *
	 * @param   int  $width
	 *
	 * @return  void
	 *
	 * @since   1.7.0
	 */
	protected function setWidth($width)
	{
		$this->dstW = $width;
		$this->dstH = $this->scaleH($width);
	}

	/**
	 * Set the given height and auto calculate width to preserve aspect ratio for the output image
	 *
	 * @param   int  $height
	 *
	 * @return  void
	 *
	 * @since   1.7.0
	 */
	protected function setHeight($height)
	{
		$this->dstW = $this->scaleW($height);
		$this->dstH = $height;
	}

	/**
	 * Auto calculate width and height within given maximum limit to preserve aspect ratio for the output image
	 *
	 * @param   int  $maxWidth
	 * @param   int  $maxHeight
	 *
	 * @return  void
	 *
	 * @since   1.7.0
	 */
	protected function setBound($maxWidth, $maxHeight)
	{
		$h = $this->scaleH($maxWidth);

		if ($h <= $maxHeight)
		{
			$this->dstW = $maxWidth;
			$this->dstH = $h;
		}
		else
		{
			$w = $this->scaleW($maxHeight);

			$this->dstW = $w;
			$this->dstH = $maxHeight;
		}
	}

	/**
	 * Set given width and height and fill the the output image while preserving aspect ratio
	 *
	 * @param   int  $width
	 * @param   int  $height
	 *
	 * @return  void
	 *
	 * @since   1.7.0
	 */
	protected function setFill($width, $height)
	{
		$this->dstW = $width;
		$this->dstH = $height;

		$h = $this->scaleH($width);

		if ($h >= $height)
		{
			$srcX = 0;
			$srcY = ((1 - $height / $h) * $this->srcH) / 2;
		}
		else
		{
			$w = $this->scaleW($height);

			$srcX = ((1 - $width / $w) * $this->srcW) / 2;
			$srcY = 0;
		}

		$this->srcX = (int) $srcX;
		$this->srcY = (int) $srcY;
	}

	/**
	 * Set given width and height and fit the the output image while preserving aspect ratio
	 *
	 * @param   int  $width
	 * @param   int  $height
	 *
	 * @return  void
	 *
	 * @since   1.7.0
	 */
	protected function setFit($width, $height)
	{
		$this->dstW = $width;
		$this->dstH = $height;

		$h = $this->scaleH($width);

		if ($h <= $height)
		{
			$dstX = 0;
			$dstY = ((1 - $h / $height) * $this->dstH) / 2;
		}
		else
		{
			$w = $this->scaleW($height);

			$dstX = ((1 - $w / $width) * $this->dstW) / 2;
			$dstY = 0;
		}

		$this->dstX = (int) $dstX;
		$this->dstY = (int) $dstY;
	}

	/**
	 * Scale the width according to given height
	 *
	 * @param   int  $height
	 *
	 * @return  int
	 *
	 * @since   1.7.0
	 */
	protected function scaleW($height)
	{
		return (int) floor($height * ($this->srcW / $this->srcH));
	}

	/**
	 * Scale the height according to given width
	 *
	 * @param   int  $width
	 *
	 * @return  int
	 *
	 * @since   1.7.0
	 */
	protected function scaleH($width)
	{
		return (int) floor($width * ($this->srcH / $this->srcW));
	}

	/**
	 * Clear the output related options
	 *
	 * @return  void
	 *
	 * @since   1.7.0
	 */
	protected function clear()
	{
		if ($this->dstImage)
		{
			imagedestroy($this->dstImage);
		}

		$this->dstImage = null;
		$this->dstMime  = null;
		$this->dstW     = 0;
		$this->dstH     = 0;
		$this->srcX     = 0;
		$this->srcY     = 0;
		$this->dstX     = 0;
		$this->dstY     = 0;
	}

	/**
	 * Set the flag whether the interlace bit will be enabled or not
	 *
	 * @param   bool  $value  Use boolean true/false to force enable/disable. NULL to leave as is.
	 *
	 * @return  static
	 *
	 * @since   1.7.0
	 */
	public function setInterlace($value)
	{
		$this->interlace = $value;

		return $this;
	}
}
