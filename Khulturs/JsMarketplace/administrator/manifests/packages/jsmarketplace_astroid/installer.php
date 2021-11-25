<?php
/**
 * @version     1.7.3
 * @package     sellacious
 *
 * @copyright   Copyright (C) 2012-2019 Bhartiy Web Technologies. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Izhar Aazmi <info@bhartiy.com> - http://www.bhartiy.com
 */
// No direct access
defined('_JEXEC') or die;

/**
 * @package  Js Kart Package Installer
 *
 * @since    1.7.3
 */
class pkg_jsmarketplace_astroidInstallerScript
{
	/**
	 * After install/uninstall/update
	 *
	 * @param   string              $type
	 * @param   \JInstallerAdapter  $installer
	 *
	 *
	 * @since   1.7.3
	 */
	function postflight($type, $installer)
	{
		if ($type === 'install')
		{
			$db     = JFactory::getDbo();
			$plugin = new stdClass;

			$plugin->type    = 'plugin';
			$plugin->folder  = 'system';
			$plugin->element = 'astroid';
			$plugin->enabled = 1;

			$db->updateObject('#__extensions', $plugin, array('type', 'folder', 'element'));
		}
	}
}
