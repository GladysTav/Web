<?php
/**
 * @version     1.7.4
 * @package     sellacious
 *
 * @copyright   Copyright (C) 2012-2019 Bhartiy Web Technologies. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Izhar Aazmi <info@bhartiy.com> - http://www.bhartiy.com
 */
// no direct access

use Joomla\Registry\Registry;

defined('JPATH_PLATFORM') or die;

/**
 * This is a workaround against the php time limit which causes installation to stop abruptly.
 * This is so far observed only on windows platform, but can occur anywhere.
 *
 * We should fix the issue soon as why the simple install sql is taking so much time.
 */
set_time_limit(600);

/**
 * @package   Sellacious
 *
 * @since     1.4.4
 */
class com_sellaciousInstallerScript
{
	/**
	 * @var  string
	 *
	 * @since   1.5.0
	 */
	protected $version;

	/**
	 * Method to run before process start
	 *
	 * @param   string                    $route
	 * @param   JInstallerAdapterPackage  $installer
	 *
	 * @throws  Exception
	 *
	 * @since   1.4.4
	 */
	public function preflight($route, $installer)
	{
		if ($route == 'update')
		{
			/** @var  JTableExtension $extension */
			$extension = JTable::getInstance('Extension');

			$extension->load(array('element' => 'com_sellacious', 'type' => 'component', 'client_id' => 1));

			if ($extension->get('extension_id'))
			{
				$this->version = $this->fixVersion($extension);
			}
		}
	}

	/**
	 * Method to run after process finish
	 *
	 * @param   string                    $route
	 * @param   JInstallerAdapterPackage  $installer
	 *
	 * @throws  Exception
	 *
	 * @since   1.5.0
	 */
	public function postflight($route, $installer)
	{
		if ($this->version && version_compare($this->version, '1.5.0', '<'))
		{
			$this->fixFields();
		}
		elseif ($route == 'update' && $this->version && version_compare($this->version, '1.7.0', '<'))
		{
			$this->updateConfig();
		}
	}

	/**
	 * Method to update Config settings
	 *
	 * @throws  \Exception
	 *
	 * @since   1.7.0
	 */
	protected function updateConfig()
	{
		// Update Product Edit Fields Setting
		$helper     = SellaciousHelper::getInstance();
		$editFields = $helper->config->get('product_fields');
		$editFields = new Registry($editFields);

		$newSetting = new Registry('{"product_type":{"row_checked":1},"physical":{"product_type":1,"product_category":1,"product_sku":1,"primary_image":1,"primary_video_url":1,"other_images":1,"short_description":1,"description":1,"product_attachments":1,"location":1},"electronic":{"product_type":1,"product_category":1,"product_sku":1,"primary_image":1,"primary_video_url":1,"other_images":1,"short_description":1,"description":1,"product_attachments":1,"location":1},"package":{"product_type":1,"product_category":1,"product_sku":1,"primary_image":1,"primary_video_url":1,"other_images":1,"short_description":1,"description":1,"product_attachments":1,"location":1},"product_category":{"row_checked":1},"product_sku":{"row_checked":1},"primary_image":{"row_checked":1},"primary_video_url":{"row_checked":1},"other_images":{"row_checked":1},"short_description":{"row_checked":1},"description":{"row_checked":1},"product_attachments":{"row_checked":1},"location":{"row_checked":1}}');
		$types      = array('physical', 'electronic', 'package');
		$fields     = array('parent_product', 'manufacturer', 'manufacturer_sku', 'min_quantity', 'max_quantity', 'over_stock', 'whats_in_box', 'product_features', 'short_description', 'product_attachments', 'seller_attachments', 'eproduct_delivery');

		foreach ($types as $type)
		{
			foreach ($fields as $field)
			{
				if ($field_value = $editFields->get($type . '.' . $field))
				{
					$newSetting->set($type . '.' . $field, $field_value);
				}
				else
				{
					$newSetting->offsetUnset($type . '.' . $field);
				}
			}
		}

		$helper->config->set('product_fields', $newSetting->toString());
	}

	/**
	 * Update the fields table to use Json only for non string values. This must run after update as we need the new database structure.
	 *
	 * @return  void
	 *
	 * @since   1.5.0
	 */
	protected function fixFields()
	{
		$db    = JFactory::getDbo();
		$query = $db->getQuery(true);

		$query->select('a.id, a.is_json, a.field_value, a.field_html')
			->from($db->qn('#__sellacious_field_values', 'a'))
			->order('a.id');

		$iterator = $db->setQuery($query)->getIterator();

		foreach ($iterator as $obj)
		{
			if (!empty($obj->field_value))
			{
				$value = json_decode($obj->field_value);

				// We concern only a valid JSON, others would be assumed to be non-json values.
				if (json_last_error() == JSON_ERROR_NONE)
				{
					if (is_scalar($value))
					{
						$obj->is_json = 0;
						$obj->field_value = $value;
					}
					else
					{
						$obj->is_json = 1;
					}

					$db->updateObject('#__sellacious_field_values', $obj, array('id'));
				}
			}
		}
	}

	/**
	 * Insert or fix the missing schema version number in sellacious 1.4.1 before we attempt to update it.
	 *
	 * @param   JTableExtension  $extension  The sellacious component extension record
	 *
	 * @return  string  The current or modified version number as the case may be.
	 *
	 * @since   1.5.0
	 */
	protected function fixVersion($extension)
	{
		$db     = JFactory::getDbo();
		$query  = $db->getQuery(true);
		$extId  = $extension->get('extension_id');
		$mCache = $extension->get('manifest_cache');

		$mCache   = new Registry($mCache);
		$mVersion = $mCache->get('version');

		$query->clear()->select('version_id')->from('#__schemas')->where('extension_id = ' . (int) $extId);

		$vid = $db->setQuery($query)->loadResult();

		if (!$vid)
		{
			$ver = '1.4.1';
		}
		elseif (version_compare($vid, '1.7.1-2018-06-03', 'lt') &&
				in_array($db->replacePrefix('#__sellacious_shoprule_class'), $db->getTableList()))
		{
			$ver = '1.7.1-2018-06-03';
		}

		if (isset($ver))
		{
			$query->clear()->delete('#__schemas')->where('extension_id = ' . (int) $extId);

			$db->setQuery($query)->execute();

			$query->clear()->insert('#__schemas')->columns('extension_id, version_id')->values(sprintf("%d, '%s'", $extId, $ver));

			$db->setQuery($query)->execute();
		}

		return $mVersion;
	}
}
