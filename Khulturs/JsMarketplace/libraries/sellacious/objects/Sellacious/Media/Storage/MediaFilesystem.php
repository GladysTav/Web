<?php
/**
 * @version     1.7.4
 * @package     sellacious
 *
 * @copyright   Copyright (C) 2012-2019 Bhartiy Web Technologies. All rights reserved.
 * @license     SPL Sellacious Private License; see http://www.sellacious.com/spl.html
 * @author      Izhar Aazmi <info@bhartiy.com> - http://www.bhartiy.com
 */
// no direct access.
namespace Sellacious\Media\Storage;

use DirectoryIterator;
use JFolder;
use JTable;
use Sellacious\Media\MediaHelper;
use SellaciousTable;

defined('_JEXEC') or die;

/**
 * Sellacious media storage helper object.
 *
 * @since   1.7.0
 */
class MediaFilesystem extends AbstractMediaStorage
{
	/**
	 * The absolute path to root folder where the files will be stored
	 *
	 * @var   string
	 *
	 * @since   1.7.0
	 */
	protected $rootDir;

	/**
	 * MediaFilesystem constructor.
	 *
	 * @param   string  $tableName  Fully qualified context identifier, e.g. - com_sellacious/products
	 * @param   string  $column     Field name for the media context
	 *
	 * @since   1.7.0
	 */
	public function __construct($tableName, $column = null)
	{
		parent::__construct($tableName, $column);

		$this->setRootDir('images');
	}

	/**
	 * Set the root directory location for the media storage, context specific folders are sub-folders of it
	 *
	 * @param   string  $path
	 *
	 * @return  static
	 *
	 * @since   1.7.0
	 */
	public function setRootDir($path)
	{
		$this->rootDir = $path;

		return $this;
	}

	/**
	 * Get the root directory location for the media storage, context specific folders are sub-folders of it
	 *
	 * @return  string
	 *
	 * @since   1.7.0
	 */
	public function getRootDir()
	{
		return $this->rootDir;
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
		$columns = array();
		$path    = JPATH_ROOT . "/{$this->rootDir}/{$this->component}/{$this->tableName}";

		if (is_dir($path))
		{
			$iterator = new DirectoryIterator($path);

			foreach ($iterator as $record)
			{
				if ($record->isDir() && !$record->isDot())
				{
					$columns[] = $record->getBasename();
				}
			}
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
		$records = array();
		$column  = $column ?: $this->column;

		if (!$column)
		{
			$columns = $this->getColumns();
			$rec     = array();

			foreach ($columns as $col)
			{
				$rec[] = $this->getRecords($col);
			}

			$records = array_reduce($rec, 'array_merge', array());
		}
		else
		{
			$colPath = JPATH_ROOT . "/{$this->rootDir}/{$this->component}/{$this->tableName}/{$column}";

			if (is_dir($colPath))
			{
				$iterator = new DirectoryIterator($colPath);

				foreach ($iterator as $record)
				{
					if ($record->isDir() && !$record->isDot())
					{
						$records[] = $record->getBasename();
					}
				}
			}
		}

		return $records;
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

		if (!$column)
		{
			$f       = array();
			$columns = $this->getColumns();

			foreach ($columns as $col)
			{
				$f[] = $this->getFiles($col, $recordId);
			}

			return array_reduce($f, 'array_merge', array());
		}

		if (!$recordId)
		{
			$f       = array();
			$records = $this->getRecords($column);

			foreach ($records as $rec)
			{
				$f[] = $this->getFiles($column, $rec);
			}

			return array_reduce($f, 'array_merge', array());
		}

		$files      = array();
		$recordPath = "{$this->rootDir}/{$this->component}/{$this->tableName}/{$column}/{$recordId}";

		if (is_dir(JPATH_ROOT . '/' . $recordPath))
		{
			$iterator = new DirectoryIterator(JPATH_ROOT . '/' . $recordPath);

			foreach ($iterator as $file)
			{
				if ($file->isFile() && !$file->isDot() && !$file->isDot())
				{
					$fileName = $file->getFilename();
					$char     = substr($fileName, 0, 1);

					if ($char !== '.' && $char !== '_' && $char !== '~')
					{
						$files[] = $recordPath . '/' . $file->getBasename();
					}
				}
			}
		}

		return $files;
	}

	/**
	 * Delete existing files for given record id
	 *
	 * @param   int[]  $pks  The record ids to process
	 *
	 * @return  void
	 *
	 * @since   1.7.0
	 */
	public function delete(array $pks)
	{
		$cols = $this->getColumns();

		foreach ($pks as $dk)
		{
			foreach ($cols as $col)
			{
				$path = JPATH_ROOT . "/{$this->rootDir}/{$this->component}/{$this->tableName}/{$col}/{$dk}";

				if(is_dir($path))
				{
					JFolder::delete($path);
				}
			}
		}
	}
}
