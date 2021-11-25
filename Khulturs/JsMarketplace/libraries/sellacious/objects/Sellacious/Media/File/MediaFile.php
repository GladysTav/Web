<?php
/**
 * @version     1.7.4
 * @package     sellacious
 *
 * @copyright   Copyright (C) 2012-2019 Bhartiy Web Technologies. All rights reserved.
 * @license     SPL Sellacious Private License; see http://www.sellacious.com/spl.html
 * @author      Izhar Aazmi <info@bhartiy.com> - http://www.bhartiy.com
 */
namespace Sellacious\Media\File;

use Joomla\CMS\Uri\Uri;

/**
 * Image object class
 *
 * @property   string  $path
 *
 * @since   1.7.0
 */
class MediaFile
{
	/**
	 * The path to the file this object points to
	 *
	 * @var   string
	 *
	 * @since   1.7.0
	 */
	protected $path;

	/**
	 * Constructor
	 *
	 * @param   string  $path
	 *
	 * @since   1.7.0
	 */
	public function __construct($path)
	{
		$this->path = $path;
	}

	/**
	 * Get filesystem path
	 *
	 * @param   bool  $absolute
	 *
	 * @return  string
	 *
	 * @since   1.7.0
	 */
	public function getPath($absolute = false)
	{
		return $absolute ? JPATH_ROOT . '/' . $this->path : $this->path;
	}

	/**
	 * Get URL to file
	 *
	 * @param   bool  $absolute
	 *
	 * @return  string
	 *
	 * @since   1.7.0
	 */
	public function getUrl($absolute = false)
	{
		return $absolute ? Uri::root() . $this->path : Uri::root(true) . '/' . $this->path;
	}

	/**
	 * Make this class directly accessible to string operations
	 *
	 * @return  string
	 *
	 * @since   1.7.0
	 */
	public function __toString()
	{
		return $this->path;
	}
}
