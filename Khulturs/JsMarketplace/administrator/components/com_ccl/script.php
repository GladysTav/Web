<?php

/**
 * @package    Com_Ccl
 * @author     Component Creator <info@component-creator.com>
 * @copyright  2016 Component Creator
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * Updates the database structure of the component
 *
 * @version  Release: 0.2b
 * @author   Component Creator <support@component-creator.com>
 * @since    0.1b
 */
class com_cclInstallerScript
{
	/**
	 * Method to install the component
	 *
	 * @param   mixed $parent Object who called this method.
	 *
	 * @return void
	 *
	 * @since 1.0
	 */
	public function install($parent)
	{
		$this->copyLayoutFiles($parent);
		$this->copyComposerFile($parent);
	}

	/**
	 * @param $parent
	 *
	 *
	 * @since 1.0
	 */
	private function copyLayoutFiles($parent)
	{
		$installationFolder = $parent->getParent()->getPath('source');
		$layoutsPath        = $installationFolder . '/layouts';

		$this->copyRecursive($layoutsPath, JPATH_ROOT . '/layouts');
	}

	/**
	 * @param $parent
	 *
	 *
	 * @since 1.0
	 */
	private function copyComposerFile($parent)
	{
		if (file_exists(JPATH_ROOT . '/composer.json'))
		{
			JFactory::getApplication()->enqueueMessage('Composer.json detected. Please check our <a href="#">documentation</a>', 'warning');
		}
		else
		{
			$installationFolder = $parent->getParent()->getPath('source');
			$composerPath       = $installationFolder . '/site/composer.json';

			copy($composerPath, JPATH_ROOT);
		}
	}

	/**
	 * Method to update the component
	 *
	 * @param   mixed $parent Object who called this method.
	 *
	 * @return void
	 *
	 * @since 1.0
	 */
	public function update($parent)
	{
		$this->copyLayoutFiles($parent);
		$this->copyComposerFile($parent);
	}

	/**
	 * Recursive copy
	 *
	 * @param string $source
	 * @param string $destination
	 *
	 *
	 * @since 1.0
	 */
	protected function copyRecursive($source, $destination)
	{
		$dir = opendir($source);
		@mkdir($destination);
		while (false !== ($file = readdir($dir)))
		{
			if (($file != '.') && ($file != '..'))
			{
				if (is_dir($source . '/' . $file))
				{
					$this->copyRecursive($source . '/' . $file, $destination . '/' . $file);
				}
				else
				{
					copy($source . '/' . $file, $destination . '/' . $file);
				}
			}
		}
		closedir($dir);
	}
}
