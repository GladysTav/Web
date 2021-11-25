<?php
/**
 * @version     2.0.0
 * @package     sellacious
 *
 * @copyright   Copyright (C) 2012-2020 Bhartiy Web Technologies. All rights reserved.
 * @license     SPL Sellacious Private License; see http://www.sellacious.com/spl.html
 * @author      Aditya Chakraborty <info@bhartiy.com> - http://www.bhartiy.com
 */
// no direct access
defined('_JEXEC') or die;

/**
 * Sellacious file storage helper
 *
 * @since  2.0.0
 */
class SellaciousHelperFileStorage extends SellaciousHelperBase
{
	/**
	 * @var  bool
	 *
	 * @since   2.0.0
	 */
	protected $hasTable = false;

	/**
	 * @var  string
	 *
	 * @since   2.0.0
	 */
	protected $basePath;

	/**
	 * Constructor
	 *
	 * @throws  Exception
	 *
	 * @since   2.0.0
	 */
	public function __construct()
	{
		parent::__construct();

		$this->basePath = JFactory::getConfig()->get('tmp_path');
	}

	/**
	 * Method to Extract Data from a given JSON
	 *
	 * @param   string  $filename   The source JSON filename
	 * @param   bool    $decode     Whether to decode the JSON data
	 *
	 * @return  mixed
	 *
	 * @since   2.0.0
	 */
	public function extractFromFile($filename, $decode = true)
	{
		$filename = $this->basePath . '/' . $filename;
		$records  = file_get_contents($filename);

		if ($decode)
		{
			$decoded = json_decode($records);
			$decoded = $decoded == null ? unserialize($records) : $decoded;

			if ($decoded)
			{
				$records = $decoded;
			}
		}

		return $records;
	}

	/**
	 * Method to store data into a file
	 *
	 * @param   array   $data      Array of data
	 * @param   string  $filename  Name of the file with extension
	 *
	 * @return  bool|int
	 *
	 * @since   2.0.0
	 */
	public function storeToFile($data, $filename)
	{
		$filename = $this->basePath . '/' . $filename;

		return file_put_contents($filename, serialize($data));
	}

	/**
	 * Method to remove file
	 *
	 * @param   string  $filename  The name of the file with extension
	 *
	 * @return  bool
	 *
	 * @since   2.0.0
	 */
	public function deleteFile($filename)
	{
		$filename = $this->basePath . '/' . $filename;

		return JFile::delete($filename);
	}
}
