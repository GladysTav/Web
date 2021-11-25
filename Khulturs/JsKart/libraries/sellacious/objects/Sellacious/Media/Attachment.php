<?php
/**
 * @version     2.0.0
 * @package     sellacious
 *
 * @copyright   Copyright (C) 2012-2020 Bhartiy Web Technologies. All rights reserved.
 * @license     SPL Sellacious Private License; see http://www.sellacious.com/spl.html
 * @author      Izhar Aazmi <info@bhartiy.com> - http://www.bhartiy.com
 */
namespace Sellacious\Media;

defined('_JEXEC') or die;

use JFile;

/**
 * Attachment object class
 * This class structure is compliant to the PHPMailer attachment function
 *
 * @since   2.0.0
 */
class Attachment
{
	/**
	 * The attachment file name to set
	 *
	 * @var   string
	 *
	 * @since   2.0.0
	 */
	protected $name;

	/**
	 * The path to the file this object points to
	 *
	 * @var   string
	 *
	 * @since   2.0.0
	 */
	protected $path;

	/**
	 * The file encoding
	 *
	 * @var   string
	 *
	 * @since   2.0.0
	 */
	protected $encoding = 'base64';

	/**
	 * The type of file
	 *
	 * @var   string
	 *
	 * @since   2.0.0
	 */
	protected $mime = 'application/octet-stream';

	/**
	 * The content disposition, viz. 'inline' or 'attachment'
	 *
	 * @var   string
	 *
	 * @since   2.0.0
	 */
	protected $disposition = 'attachment';

	/**
	 * Flag to set if the file should be deleted after sending
	 *
	 * @var   bool
	 *
	 * @since   2.0.0
	 */
	public $delete_on_sent = false;

	/**
	 * Constructor
	 *
	 * @param   string  $path  The relative path to the target file
	 * @param   string  $name  The target name, used base file name if omitted
	 *
	 * @since   2.0.0
	 */
	public function __construct($path, $name = null)
	{
		$this->path = $path;
		$this->name = $name ?: basename($path);
		$this->mime = MediaHelper::getMimeType($this->getPath(true));
	}

	/**
	 * Get local filesystem path
	 *
	 * @param   bool  $absolute
	 *
	 * @return  string
	 *
	 * @since   2.0.0
	 */
	public function getPath($absolute = true)
	{
		return $absolute ? JPATH_ROOT . '/' . $this->path : $this->path;
	}

	/**
	 * Get the target name for the attachment
	 *
	 * @return  string
	 *
	 * @since   2.0.0
	 */
	public function getName()
	{
		return $this->name;
	}

	/**
	 * Get the content disposition type
	 *
	 * @return  string
	 *
	 * @since   2.0.0
	 */
	public function getDisposition()
	{
		return $this->disposition;
	}

	/**
	 * Get the file encoding
	 *
	 * @return  string
	 *
	 * @since   2.0.0
	 */
	public function getEncoding()
	{
		return $this->encoding;
	}

	/**
	 * Get the file mime
	 *
	 * @return  string
	 *
	 * @since   2.0.0
	 */
	public function getMime()
	{
		return $this->mime;
	}

	/**
	 * Set the content disposition
	 *
	 * @param   string  $disposition
	 *
	 * @return  Attachment
	 *
	 * @since   2.0.0
	 */
	public function setDisposition($disposition)
	{
		$this->disposition = $disposition;

		return $this;
	}

	/**
	 * Set the file encoding
	 *
	 * @param   string  $encoding
	 *
	 * @return  Attachment
	 *
	 * @since   2.0.0
	 */
	public function setEncoding($encoding)
	{
		$this->encoding = $encoding;

		return $this;
	}

	/**
	 * Set the file mime
	 *
	 * @param   string  $mime
	 *
	 * @return  Attachment
	 *
	 * @since   2.0.0
	 */
	public function setMime($mime)
	{
		$this->mime = $mime;

		return $this;
	}

	/**
	 * Method to delete the file
	 *
	 * @since   2.0.0
	 */
	public function delete()
	{
		JFile::delete($this->getPath(true));
	}
}
