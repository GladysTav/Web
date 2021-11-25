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

use JEventDispatcher;
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
	 * @since   2.0.0
	 */
	protected $id;

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
	 * @param   int     $id
	 *
	 * @since   1.7.0
	 */
	public function __construct($path, $id = null)
	{
		$this->path = $path;
		$this->id   = $id;
	}

	/**
	 * Get local filesystem path
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
	 * Get the local URL to file
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
	 * Get the direct download URL to file.
	 * This can be intercepted by cloud plugins to provide alternate links. Local links will not be returned.
	 *
	 * @return  string
	 *
	 * @since   2.0.0
	 */
	public function getCloudUrl()
	{
		$url        = null;
		$dispatcher = JEventDispatcher::getInstance();

		if ($this->id)
		{
			$dispatcher->trigger('onRequestMediaDownloadUrl', array('sellacious.media', $this->id, &$url));
		}
		else
		{
			$dispatcher->trigger('onRequestFileDownloadUrl', array('sellacious.media', $this->path, &$url));
		}

		return $url;
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
