<?php

/**
 * @version    CVS: 1.0.0
 * @package    Com_Ccl
 * @author     Component Creator <info@component-creator.com>
 * @copyright  2016 Component Creator
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die;

/**
 * Methods supporting a list of Ccl records.
 *
 * @since  1.6
 */
class CclModelDashboard extends JModelList
{
	/**
	 * Get plugin list
	 *
	 * @return array
	 *
	 * @since 1.0
	 */
	public function getPlugins()
	{
		$db    = JFactory::getDbo();
		$query = $db->getQuery(true);

		$query
			->select('*')
			->from('#__extensions AS e')
			->where(
				array(
					'type LIKE ' . $db->quote('plugin'),
					'folder LIKE' . $db->quote('authentication'),
					'element IN (' . implode(',', $db->quote(array('google', 'facebook', 'github'))) . ')'
				)
			);

		$db->setQuery($query);

		return $db->loadObjectList();
	}

	/**
	 * Get template name
	 *
	 * @return string
	 *
	 * @since 1.1
	 */
	public function getTemplate()
	{
		$db    = JFactory::getDbo();
		$query = $db->getQuery(true);

		$query
			->select('template')
			->from('#__template_styles')
			->where(
				array(
					'client_id = 0',
					'home = 1'
				)
			);

		$db->setQuery($query);

		return $db->loadResult();
	}

	/**
	 * Get module identifier
	 *
	 * @return int
	 *
	 * @since 1.1
	 */
	public function getModuleIdentifier()
	{
		$db    = JFactory::getDbo();
		$query = $db->getQuery(true);

		$query
			->select('id')
			->from('#__modules')
			->where('module = ' . $db->quote('mod_ccl'));

		$db->setQuery($query);

		return (int) $db->loadResult();
	}

	/**
	 * Check if the user plugin is enabled
	 *
	 * @return bool
	 *
	 * @since 1.0
	 */
	public function getUserPluginEnabled()
	{
		return $this->isEnabled('plugin', 'cclogin', 'user');
	}

	/**
	 * Check if system plugin is enabled
	 *
	 * @return bool
	 *
	 * @since 1.0
	 */
	public function getSystemPluginEnabled()
	{
		return $this->isEnabled('plugin', 'cclogin', 'system');
	}

	/**
	 * Check if extension is enabled
	 *
	 * @param string $type
	 * @param string $element
	 * @param string $folder
	 *
	 * @return bool
	 *
	 * @since 1.0
	 */
	protected function isEnabled($type, $element, $folder)
	{
		$db    = JFactory::getDbo();
		$query = $db->getQuery(true);

		$query
			->select('enabled')
			->from('#__extensions')
			->where(
				array(
					'type LIKE ' . $db->quote($type),
					'element LIKE ' . $db->quote($element),
					'folder LIKE ' . $db->quote($folder)
				)
			);

		$db->setQuery($query);

		return 1 == $db->loadResult();
	}
}
