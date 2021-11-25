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
namespace Sellacious\Media;

use Exception;
use JFactory;
use JFile;
use JFolder;
use JLog;
use Joomla\Utilities\ArrayHelper;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use Sellacious\Media\Image\ResizeImage;
use Sellacious\Media\Storage\AbstractMediaStorage;
use Sellacious\Media\Storage\MediaDatabaseTable;
use Sellacious\Media\Storage\MediaFilesystem;

defined('_JEXEC') or die;

/**
 * Sellacious media helper object
 *
 * @since   1.7.0
 */
class MediaCleanup
{
	/**
	 * Run the cleanup batch
	 *
	 * @throws  Exception
	 *
	 * @since   1.7.0
	 */
	public function execute()
	{
		$this->deleteEntriesForDeletedItems();

		$this->trashEntriesForDeleteFiles();

		$this->discoverFromFilesystem();

		$this->resizeImages();
	}

	/**
	 * Trash media entries for which the files are missing
	 *
	 * @return  void
	 *
	 * @throws  Exception
	 *
	 * @since   1.7.0
	 */
	protected function trashEntriesForDeleteFiles()
	{
		$db    = JFactory::getDbo();
		$query = $db->getQuery(true);

		// Only watch for published and unpublished items to avoid conflict with other api like import, sync etc
		$query->select('a.id, a.path, a.record_id')
		      ->from($db->qn('#__sellacious_media', 'a'))
		      ->where('(a.state = 0 OR a.state = 1)');

		$records = (array) $db->setQuery($query)->loadObjectList();
		$pks     = array();

		foreach ($records as $record)
		{
			if (!file_exists(JPATH_SITE . '/' . $record->path))
			{
				$pks[] = (int) $record->id;
			}
		}

		if (count($pks))
		{
			$query = $db->getQuery(true);

			$query->update('#__sellacious_media')->set('state = -2')->where('id IN (' . implode(', ', $pks) . ')');

			$db->setQuery($query)->execute();
		}
	}

	/**
	 * Method to sync media information in database with filesystem
	 *
	 * @return  void
	 *
	 * @throws  Exception
	 *
	 * @since   1.7.0
	 */
	protected function discoverFromFilesystem()
	{
		$contexts = $this->getContexts();

		foreach ($contexts as $context => $rTable)
		{
			list($tableName, $column) = explode('.', $context, 2);

			$storageFs = new MediaFilesystem($tableName);
			$storageDb = new MediaDatabaseTable($tableName);

			$records = $storageFs->getRecords();

			foreach ($records as $recordId)
			{
				$filesX = $storageDb->getFiles($column, $recordId);
				$filesN = $storageFs->getFiles($column, $recordId);

				foreach ($filesN as $file)
				{
					if (!in_array($file, $filesX))
					{
						$storageDb->add($column, $recordId, $file);
					}
				}
			}
		}
	}

	/**
	 * Find out the entries for non-existing records and call the relevant storage class to delete the entries
	 *
	 * @return  void
	 *
	 * @since   1.7.0
	 */
	protected function deleteEntriesForDeletedItems()
	{
		$contexts = $this->getContexts();

		foreach ($contexts as $context => $dbTable)
		{
			list($tbl) = explode('.', $context, 2);

			if ($tbl === 'com_sellacious/config')
			{
				continue;
			}

			/** @var  AbstractMediaStorage[]  $storage_s */
			$storage_s = array(
				new MediaFilesystem($tbl),
				new MediaDatabaseTable($tbl),
			);

			foreach ($storage_s as $storage)
			{
				$records = $storage->getRecords();

				$pks = ArrayHelper::toInteger($records);
				$pks = array_unique($pks);

				if ($pks)
				{
					$sets = array_chunk($pks, 1000);

					foreach ($sets as $set)
					{
						$db    = JFactory::getDbo();
						$query = $db->getQuery(true);

						$query->select('id')
						      ->from($db->qn($dbTable))
						      ->where('id = ' . implode(' OR id = ', $set));

						$eks = (array) $db->setQuery($query)->loadColumn();

						$dks = array_diff($set, $eks);

						$storage->delete($dks);
					}
				}
			}

			MediaHelper::removeEmptyDir(JPATH_ROOT . '/images/' . $tbl);
		}
	}

	/**
	 * Create resized copies of all media files
	 *
	 * @return  void
	 *
	 * @since   1.7.0
	 */
	protected function resizeImages()
	{
		$contexts = array(
			'products.primary_image' => '#__sellacious_products',
			'products.images'        => '#__sellacious_products',
			'variants.images'        => '#__sellacious_variants',
			'categories.images'      => '#__sellacious_categories',
			'categories.banners'     => '#__sellacious_categories',
			'splcategories.images'   => '#__sellacious_splcategories',
		);

		foreach ($contexts as $context => $tableName)
		{
			list($tbl, $col) = explode('.', $context);

			$storage = new MediaFilesystem('com_sellacious/' . $tbl);
			$rootDir = $storage->getRootDir();
			$files   = $storage->getFiles($col, null);

			foreach ($files as $file)
			{
				$c = JPATH_ROOT . '/' . $file;

				if (MediaHelper::isImage($c))
				{
					set_time_limit(30);

					$rel = ltrim(substr($file, strlen($rootDir)), '/');

					$o = JPATH_ROOT . '/images/originals/' . $rel;
					$t = JPATH_ROOT . '/tmp/resize-stage/' . $rel;

					if (!file_exists($o))
					{
						try
						{
							$image = new ResizeImage($c);

							list($w, $h) = $image->getSize();

							JFolder::create(dirname($t));
							JFolder::create(dirname($o));

							$image->resizeTo(min(800, $w), min(600, $h));
							$image->saveImage($t, 40);

							JFile::move($c, $o);
							JFile::move($t, $c);
						}
						catch (Exception $e)
						{
							JLog::add('Failed to resize image: ' . $e->getMessage());
						}
					}
				}
			}
		}

		/** @var   RecursiveDirectoryIterator  $dii */
		$di  = new RecursiveDirectoryIterator(JPATH_ROOT . '/images/originals');
		$dii = new RecursiveIteratorIterator($di);

		foreach ($dii as $file)
		{
			if ($file->isFile())
			{
				$pathname = $dii->getSubPathname();

				if (!file_exists(JPATH_ROOT . '/images/' . $pathname))
				{
					JFile::delete(JPATH_ROOT . '/images/originals/' . $pathname);
				}
			}
		}

		MediaHelper::removeEmptyDir(JPATH_ROOT . '/images/originals', false);
	}

	/**
	 * Get the media contexts
	 *
	 * @return  array
	 *
	 * @since   1.7.0
	 */
	protected function getContexts()
	{
		// Todo: Allow dynamic bindings of external contexts as well
		$contexts = array(
			'com_sellacious/categories.images'               => '#__sellacious_categories',
			'com_sellacious/categories.banners'              => '#__sellacious_categories',
			'com_sellacious/clients.org_certificate'         => '#__sellacious_clients',
			'com_sellacious/config.backoffice_logo'          => '#__sellacious_config',
			'com_sellacious/config.eproduct_image_watermark' => '#__sellacious_config',
			'com_sellacious/config.purchase_exchange_icon'   => '#__sellacious_config',
			'com_sellacious/config.purchase_return_icon'     => '#__sellacious_config',
			'com_sellacious/config.shop_logo'                => '#__sellacious_config',
			'com_sellacious/eproduct_media.media'            => '#__sellacious_eproduct_media',
			'com_sellacious/eproduct_media.sample'           => '#__sellacious_eproduct_media',
			'com_sellacious/license.logo'                    => '#__sellacious_licenses',
			'com_sellacious/manufacturers.logo'              => '#__sellacious_manufacturers',
			'com_sellacious/paymentmethod.logo'              => '#__sellacious_paymentmethods',
			'com_sellacious/product_sellers.attachments'     => '#__sellacious_product_sellers',
			'com_sellacious/products.attachments'            => '#__sellacious_products',
			'com_sellacious/products.primary_image'          => '#__sellacious_products',
			'com_sellacious/products.images'                 => '#__sellacious_products',
			'com_sellacious/sellers.logo'                    => '#__sellacious_sellers',
			'com_sellacious/splcategories.badge'             => '#__sellacious_splcategories',
			'com_sellacious/splcategories.images'            => '#__sellacious_splcategories',
			'com_sellacious/variants.images'                 => '#__sellacious_variants',
		);

		return $contexts;
	}
}
