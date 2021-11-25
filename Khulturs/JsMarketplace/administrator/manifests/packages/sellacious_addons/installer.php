<?php
/**
 * @version     2.2.0
 * @package     SP Page Builder Addons and plugins for Sellacious
 *
 * @copyright   Copyright (C) 2017. Bhartiy Web Technologies. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Bhavika Matariya <info@bhartiy.com> - http://www.bhartiy.com
 */

//no direct accees
defined ('_JEXEC') or die ('resticted aceess');

class pkg_Sellacious_AddonsInstallerScript
{

	/**
	 * Method to run before an install/update/uninstall method.
	 * Used to warn user that core package needs to be installed first before installing this one.
	 *
	 * @param   string                 $type
	 * @param   JInstallerAdapterFile  $installer
	 *
	 * @throws  Exception
	 */
	public function preFlight($type, $installer)
	{
		if ($type == 'install')
		{
			$table = JTable::getInstance('Extension');

			if (!$table->load(array('type' => 'component', 'element' => 'com_sellacious')) || !$table->load(array('type' => 'component', 'element' => 'com_sppagebuilder')))
			{
				$message = '<div align="center">
							<div align="left" style="background-color: #fff4f4;border: 1px solid #f7e0e0;border-radius: 2px;color: #b36a6a;padding: 20px;">
								<h3 style="margin: 0 0 20px; font-size: 24px;">Before you install</h3>
								<p>This plugin requires Sellacious and SP Page builder to work. Please click on the button below to download the additional plugin.</p>
								<div class="btn-actions">
									<a href="http://sellacious.com/download.html" target="_blank" title="Document" class="primary">Download Sellacious</a>
									<a href="https://www.joomshaper.com/page-builder" target="_blank" title="Forum">Get SP Pagebuilder</a>
								</div>
								<p>Please install this plugin again after installing the above required plugins.</p>
								<p style="font-size: 12px;">Copyright 2012 - 2017 <a href="http://sellacious.com" title="Sellacious">sellacious.com</a>.</p>
							</div>
						</div>
						<style>
							.btn-actions {margin: 30px 0;}.btn-actions a {background: #fff; border: 1px solid #ddd; border-radius: 2px; color: #666;
							box-shadow: 0 -2px 0 rgba(0,0,0,0.1) inset; font-size: 16px; letter-spacing: 1px; padding: 8px 20px 9px; margin: 0 5px 0 0;
							text-decoration: none; transition: all 0.35s;}.btn-actions a.primary {background-color: #2176b9;border-color: #1e6faf;color: #fff;}
							.btn-actions a:hover, .btn-actions a:focus {background: #01579b; border-color: #01579b; color: #fff;}
						</style>';

				throw new RuntimeException($message);
			}
		}
	}

	/**
	 * Method to run after an install/update/uninstall method
	 * Used to setup user groups and their default permissions as required by sellacious.
	 *
	 * @param   string                 $type
	 * @param   JInstallerAdapterFile  $installer
	 */
	function postFlight($type, $installer)
	{
		$db = JFactory::getDbo();

		// Enable plg_system_sellaciouscattemplates
		$query      = $db->getQuery(true);
		$fields     = array(
			$db->quoteName('enabled') . ' = 1',
			$db->quoteName('ordering') . ' = 9999'
		);
		$conditions = array(
			$db->quoteName('element') . ' = ' . $db->quote('sellaciouscattemplates'),
			$db->quoteName('type') . ' = ' . $db->quote('plugin')
		);
		$query->update($db->quoteName('#__extensions'))->set($fields)->where($conditions);
		$db->setQuery($query);
		$db->execute();

		// Enable plg_content_sellaciouscatspecs
		$query      = $db->getQuery(true);
		$fields     = array(
			$db->quoteName('enabled') . ' = 1',
			$db->quoteName('ordering') . ' = 9999'
		);
		$conditions = array(
			$db->quoteName('element') . ' = ' . $db->quote('sellaciouscatspecs'),
			$db->quoteName('type') . ' = ' . $db->quote('plugin')
		);
		$query->update($db->quoteName('#__extensions'))->set($fields)->where($conditions);
		$db->setQuery($query);
		$db->execute();

		// Copy sppagebuilder to its folder (administrator/components/com_sppagebuilder/builder/templates)
		$src  = $installer->getParent()->getPath('source');

		try
		{
			$componentFolder = JPATH_ADMINISTRATOR . '/components/com_sppagebuilder/builder/templates';

			jimport('joomla.filesystem.folder');
			jimport('joomla.filesystem.file');

			if (JFolder::exists($src . '/sppagebuilder/templates'))
			{
				if (!JFolder::exists($componentFolder))
				{
					JFolder::create($componentFolder);
				}

				JFolder::copy($src . '/sppagebuilder/templates', $componentFolder, '', true);
			}

			if (JFolder::exists($src . '/sppagebuilder/addons'))
			{
				$addonsFolder = JPATH_ROOT . '/components/com_sppagebuilder/addons';

				if (JFolder::exists($addonsFolder))
				{
					JFolder::copy($src . '/sppagebuilder/addons', $addonsFolder, '', true);
				}
			}

			if (JFolder::exists($src . '/sppagebuilder/assets'))
			{
				$assetsFolder = JPATH_ROOT . '/components/com_sppagebuilder/assets';

				if (JFolder::exists($assetsFolder))
				{
					JFolder::copy($src . '/sppagebuilder/assets', $assetsFolder, '', true);
				}
			}
		}
		catch (Exception $e){}
	}

	/**
	 * method to run before package uninstall
	 *
	 * @param   JInstallerAdapterPackage  $installer
	 *
	 * @throws  Exception
	 */
	public function uninstall($installer)
	{
		try
		{
			$componentFolder = JPATH_ADMINISTRATOR . '/components/com_sppagebuilder/builder/templates';

			jimport('joomla.filesystem.folder');
			jimport('joomla.filesystem.file');

			if (JFolder::exists($componentFolder . '/Sellacious Category'))
			{
				JFolder::delete($componentFolder . '/Sellacious Category');
			}
			if (JFolder::exists($componentFolder . '/Sellacious Product'))
			{
				JFolder::delete($componentFolder . '/Sellacious Product');
			}

			$folders = array('sl_category_desc', 'sl_category_image', 'sl_category_products',
				'sl_category_specs', 'sl_category_subcategories', 'sl_category_title', 'sl_product_attributes',
				'sl_product_box', 'sl_product_cartbuttons', 'sl_product_condition', 'sl_product_custom_specs',
				'sl_product_desc', 'sl_product_features', 'sl_product_gallery', 'sl_product_images', 'sl_product_introtext',
				'sl_product_price', 'sl_product_prices', 'sl_product_quantity', 'sl_product_ratings',
				'sl_product_relatedproducts', 'sl_product_sellers', 'sl_product_shipping', 'sl_product_soldby',
				'sl_product_specs', 'sl_product_title', 'sl_product_toolbar', 'sl_product_variants',
				'sl_view_productratings', 'sl_view_sellerratings');

			$addonsFolder = JPATH_ROOT . '/components/com_sppagebuilder/addons';

			foreach ($folders as $folder)
			{
				if (JFolder::exists($addonsFolder . '/' . $folder))
				{
					JFolder::delete($addonsFolder . '/' . $folder);
				}
			}

			$assets = JPATH_ROOT . '/components/com_sppagebuilder/assets';

			if (JFolder::exists($assets . '/css/sellacious'))
			{
				JFolder::delete($assets . '/css/sellacious');
			}
			if (JFolder::exists($assets . '/js/sellacious'))
			{
				JFolder::delete($assets . '/js/sellacious');
			}
		}
		catch (Exception $e){}
	}
}
