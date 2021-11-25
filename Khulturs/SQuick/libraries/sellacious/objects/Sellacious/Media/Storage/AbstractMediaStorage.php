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
namespace Sellacious\Media\Storage;

defined('_JEXEC') or die;

/**
 * Sellacious media storage helper object.
 *
 * @since   1.7.0
 */
abstract class AbstractMediaStorage
{
	/**
	 * Component name for the media context, e.g. - com_sellacious
	 *
	 * @var   string
	 *
	 * @since   1.7.0
	 */
	protected $component;

	/**
	 * Table identifier for the media context, e.g. - products
	 *
	 * @var   string
	 *
	 * @since   1.7.0
	 */
	protected $tableName;

	/**
	 * Column identifier for the media context, e.g. - images, attachments
	 *
	 * @var   string
	 *
	 * @since   1.7.0
	 */
	protected $column;

	/**
	 * AbstractMediaStorage constructor.
	 *
	 * @param   string  $tableName  Fully qualified context identifier, e.g. - com_sellacious/products
	 * @param   string  $column     Field name for the media context
	 *
	 * @since   1.7.0
	 */
	public function __construct($tableName, $column = null)
	{
		$parts = explode('/', $tableName, 2);

		if (count($parts) < 2)
		{
			$this->component = 'com_sellacious';
			$this->tableName = $tableName;
		}
		else
		{
			$this->component = $parts[0];
			$this->tableName = $parts[1];
		}

		$this->setColumn($column);
	}

	/**
	 * Set the column reference
	 *
	 * @param   string  $column  The column (field) name for the media ref
	 *
	 * @return  static
	 *
	 * @since   1.7.0
	 */
	public function setColumn($column)
	{
		$this->column = $column;

		return $this;
	}

	/**
	 * Get a list of defined column references for current table instance
	 *
	 * @return  string[]
	 *
	 * @since   1.7.0
	 */
	abstract public function getColumns();

	/**
	 * Get a list of available record ids for current table instance
	 *
	 * @param   string  $column  The column (field) name for the media, all columns will be used if not provided one
	 *
	 * @return  int[]
	 *
	 * @since   1.7.0
	 */
	abstract public function getRecords($column = null);

	/**
	 * Get a list of available record ids for current table instance
	 *
	 * @param   string  $column    The column (field) name for the media, all columns will be used if not provided one
	 * @param   string  $recordId  The record id for the media, all records will be used if not provided one
	 *
	 * @return  string[]
	 *
	 * @since   1.7.0
	 */
	abstract public function getFiles($column = null, $recordId = null);

	/**
	 * Delete the entries for non existing referenced records
	 *
	 * @param   int[]  $pks  The record_id for the media table reference
	 *
	 * @return  void
	 *
	 * @since   1.7.0
	 */
	abstract public function delete(array $pks);
}
