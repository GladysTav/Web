<?php
/**
 * @version     2.0.0
 * @package     sellacious
 *
 * @copyright   Copyright (C) 2012-2020 Bhartiy Web Technologies. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Izhar Aazmi <info@bhartiy.com> - http://www.bhartiy.com
 */
// No direct access
defined('_JEXEC') or die;

/**
 * @package  Js-Kart v2 Package Installer
 *
 * @since    2.5.3
 */
class pkg_jskartv2_helixultimateInstallerScript
{
	/**
	 * After install/uninstall/update
	 *
	 * @param   string              $type
	 * @param   \JInstallerAdapter  $parent
	 *
	 *
	 * @since   2.5.3
	 */
	function postflight($type, $parent)
	{
		if ($type == 'install')
		{
			/** @var   SimpleXMLElement  $manifest */
			$manifest = $parent->getParent()->manifest;
			$db       = JFactory::getDbo();

			foreach ($manifest->files->file as $file)
			{
				$query = $db->getQuery(true);

				$query->update('#__extensions')
					->set('enabled = 1')
					->where('type = ' . $db->q('plugin'))
					->where('folder = ' . $db->q($file['group']))
					->where('element = ' . $db->q($file['id']));

				$db->setQuery($query)->execute();
			}
		}

		if ($type == 'update')
		{
			$this->cleanupOldFiles();
		}

		$this->chmodFiles(JPATH_ROOT);
	}

	public function chmodFiles($source)
	{
		if (is_dir($source))
		{
			@chmod(@$source, 0755);

			$files = scandir($source);

			foreach ($files as $file)
			{
				if ($file != '.' && $file != '..')
				{
					$this->chmodFiles("$source/$file");
				}
			}
		}
		else if (is_file($source))
		{
			@chmod($source, 0644);
		}

		return true;
	}

	protected function cleanupOldFiles()
	{
		$files = array(
			'__DELETED_FILES__',
		);

		foreach ($files as $file)
		{
			JFile::delete(JPATH_ROOT . '/' . $file);
		}
	}
}
