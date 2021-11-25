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

use Joomla\Utilities\ArrayHelper;
use JTable;
use Sellacious\Media\MediaHelper;

defined('_JEXEC') or die;

/**
 * Sellacious media storage helper object.
 *
 * @since   1.7.0
 */
class MediaDatabaseTable extends AbstractMediaStorage
{
	/**
	 * The database driver instance
	 *
	 * @var   \JDatabaseDriver
	 *
	 * @since   1.7.0
	 */
	protected $db;

	/**
	 * MediaDatabaseTable constructor.
	 *
	 * @param   string  $tableName  Fully qualified context identifier, e.g. - com_sellacious/products
	 * @param   string  $column     Field name for the media context
	 *
	 * @since   1.7.0
	 */
	public function __construct($tableName, $column = null)
	{
		parent::__construct($tableName, $column);

		$this->db = \JFactory::getDbo();
	}

	/**
	 * Get a list of defined column references for current table instance
	 *
	 * @return  string[]
	 *
	 * @since   1.7.0
	 */
	public function getColumns()
	{
		$query = $this->db->getQuery(true);

		$query->select('DISTINCT a.context')
			->from($this->db->qn('#__sellacious_media', 'a'));

		$wh[] = 'a.table_name = ' . $this->db->q($this->component . '/' . $this->tableName);

		if ($this->component === 'com_sellacious')
		{
			$wh[] = 'a.table_name = ' . $this->db->q($this->tableName);
		}

		$query->where('(' . implode(' OR ', $wh) . ')');

		try
		{
			$columns = (array) $this->db->setQuery($query)->loadColumn();
		}
		catch (\Exception $e)
		{
			$columns = array();
		}

		return $columns;
	}

	/**
	 * Get a list of available record ids for current table instance
	 *
	 * @param   string  $column  The column (field) name for the media, all columns will be used if not provided one
	 *
	 * @return  int[]
	 *
	 * @since   1.7.0
	 */
	public function getRecords($column = null)
	{
		$column = $column ?: $this->column;
		$query  = $this->db->getQuery(true);

		$query->select('DISTINCT a.record_id')
		      ->from($this->db->qn('#__sellacious_media', 'a'));

		$wh[] = 'a.table_name = ' . $this->db->q($this->component . '/' . $this->tableName);

		if ($this->component === 'com_sellacious')
		{
			$wh[] = 'a.table_name = ' . $this->db->q($this->tableName);
		}

		$query->where('(' . implode(' OR ', $wh) . ')');

		if ($column)
		{
			$query->where('a.context = ' . $this->db->q($column));
		}

		try
		{
			$pks = (array) $this->db->setQuery($query)->loadColumn();
		}
		catch (\Exception $e)
		{
			$pks = array();
		}

		return $pks;
	}

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
	public function getFiles($column = null, $recordId = null)
	{
		$column = $column ?: $this->column;
		$query  = $this->db->getQuery(true);

		$query->select('DISTINCT a.path')
		      ->from($this->db->qn('#__sellacious_media', 'a'));

		$wh[] = 'a.table_name = ' . $this->db->q($this->component . '/' . $this->tableName);

		if ($this->component === 'com_sellacious')
		{
			$wh[] = 'a.table_name = ' . $this->db->q($this->tableName);
		}

		$query->where('(' . implode(' OR ', $wh) . ')');

		if ($column)
		{
			$query->where('a.context = ' . $this->db->q($column));
		}

		if ($recordId)
		{
			$query->where('a.record_id = ' . $this->db->q($recordId));
		}

		try
		{
			$paths = (array) $this->db->setQuery($query)->loadColumn();
		}
		catch (\Exception $e)
		{
			$paths = array();
		}

		return $paths;
	}

	/**
	 * Get a list of available record ids for current table instance
	 *
	 * @param   string  $column    The column (field) name for the media, all columns will be used if not provided one
	 * @param   string  $recordId  The record id for the media, all records will be used if not provided one
	 *
	 * @return  \stdClass[]
	 *
	 * @throws  \Exception
	 *
	 * @since   1.7.0
	 */
	public function getList($column = null, $recordId = null)
	{
		$column = $column ?: $this->column;
		$query  = $this->db->getQuery(true);

		$query->select('a.*')
		      ->from($this->db->qn('#__sellacious_media', 'a'))
			  ->where('a.state = 1');

		$wh[] = 'a.table_name = ' . $this->db->q($this->component . '/' . $this->tableName);

		if ($this->component === 'com_sellacious')
		{
			$wh[] = 'a.table_name = ' . $this->db->q($this->tableName);
		}

		$query->where('(' . implode(' OR ', $wh) . ')');

		if ($column)
		{
			$query->where('a.context = ' . $this->db->q($column));
		}

		if ($recordId)
		{
			$query->where('a.record_id = ' . $this->db->q($recordId));
		}

		$items = (array) $this->db->setQuery($query)->loadObjectList();

		return $items;
	}

	/**
	 * Add a new entry in the media table
	 *
	 * @param   string  $column    The column name
	 * @param   int     $recordId  The record id
	 * @param   string  $file      Site root relative path to the file
	 *
	 * @return  void
	 *
	 * @since   1.7.0
	 */
	public function add($column, $recordId, $file)
	{
		$tableName = $this->component === 'com_sellacious' ? $this->tableName : $this->component . '/' . $this->tableName;

		$props = array(
			'table_name'    => $tableName,
			'record_id'     => $recordId,
			'context'       => $column,
			'path'          => $file,
			'original_name' => basename($file),
			'type'          => MediaHelper::getMimeType(JPATH_SITE . '/' . $file),
			'size'          => filesize(JPATH_ROOT . '/' . $file),
			'state'         => 1,
		);

		$query = $this->db->getQuery(true);

		$query->insert('#__sellacious_media')
			->columns(array_keys($props))
			->values(implode(',', $this->db->q(array_values($props))));

		$this->db->setQuery($query)->execute();
	}

	/**
	 * Delete the entries for non existing referenced records
	 *
	 * @param   int[]  $pks  The record_id for the media table reference
	 *
	 * @return  void
	 *
	 * @since   1.7.0
	 */
	public function delete(array $pks)
	{
		if ($pks)
		{
			$query = $this->db->getQuery(true);

			$query->select('*')
			      ->from('#__sellacious_media')
			      ->where('record_id IN ('. implode(', ', (array) $pks) . ')');

			$wh[] = 'table_name = ' . $this->db->q($this->component . '/' . $this->tableName);

			if ($this->component === 'com_sellacious')
			{
				$wh[] = 'table_name = ' . $this->db->q($this->tableName);
			}

			$query->where('(' . implode(' OR ', $wh) . ')');

			$items = $this->db->setQuery($query)->loadObjectList();

			if ($items)
			{
				$query = $this->db->getQuery(true);

				$query->delete('#__sellacious_media')
				      ->where('id IN ('. implode(', ', ArrayHelper::getColumn($items, 'id')) . ')');

				$this->db->setQuery($query)->execute();

				// Trigger plugin
				$dispatcher = \JEventDispatcher::getInstance();
				$dispatcher->trigger('onAfterDeleteUploadedFiles', array('sellacious.media', $items));
			}
		}
	}
}
