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

namespace Sellacious\User;

defined('_JEXEC') or die;

use Joomla\Utilities\ArrayHelper;
use Sellacious\Access\Access;
use Sellacious\Config\ConfigHelper;

/**
 * Authorisation helper class, provides static methods to perform various tasks relevant
 * to the Sellacious user and authorisation classes
 *
 * @since  2.0.0
 */
abstract class UserHelper
{
	/**
	 * Internal storage for local caching of retrieved users list by group
	 *
	 * @var   int[][]
	 *
	 * @since   2.0.0
	 */
	protected static $usersByGroup;

	/**
	 * Internal storage for local caching of retrieved groups list by user
	 *
	 * @var   int[][]
	 *
	 * @since   2.0.0
	 */
	protected static $groupsByUser;

	/**
	 * Method to return a list of user Ids contained in a Group (sellacious user category)
	 *
	 * @param   integer  $groupId    The group Id
	 * @param   boolean  $recursive  Recursively include all child groups (optional)
	 *
	 * @return  int[]
	 *
	 * @since   2.0.0
	 */
	public static function getUsersByGroup($groupId, $recursive = false)
	{
		// Creates a simple unique string for each parameter combination:
		$storeId = $groupId . ':' . (int) $recursive;

		if (!isset(self::$usersByGroup[$storeId]))
		{
			$db = \JFactory::getDbo();

			static $tables = array(
				'client'       => '#__sellacious_clients',
				'seller'       => '#__sellacious_sellers',
				'manufacturer' => '#__sellacious_manufacturers',
				'staff'        => '#__sellacious_staffs',
			);

			$query = $db->getQuery(true)
				->select('a.type')
				->from($db->qn('#__sellacious_categories', 'a'))
				->where('a.id = ' . (int) $groupId)
				->where('a.state = 1');

			$cType = $db->setQuery($query)->loadResult();

			if (!array_key_exists($cType, $tables))
			{
				return array();
			}

			// First find the users contained in the group
			$tableName = ArrayHelper::getValue($tables, $cType);
			$op        = $recursive ? '>=' : '=';

			$query = $db->getQuery(true)
				->select('DISTINCT(user_id)')
				->from('#__sellacious_categories AS ug1')
				->where('ug1.state = 1')
				->join('INNER', '#__sellacious_categories AS ug2 ON ug2.lft ' . $op . ' ug1.lft AND ug1.rgt ' . $op . ' ug2.rgt AND ug2.state = 1')
				->join('INNER', $tableName . ' AS m ON ug2.id = m.category_id AND m.state = 1')
				->where('ug1.id = ' . (int) $groupId);

			$result = (array) $db->setQuery($query)->loadColumn();

			$result = ArrayHelper::toInteger($result);

			self::$usersByGroup[$storeId] = $result;
		}

		return self::$usersByGroup[$storeId];
	}

	/**
	 * Method to return a list of user Ids having the given role
	 *
	 * @param   int   $role     The desired user role
	 * @param   bool  $enabled  Whether to load only enabled users
	 *
	 * @return  int[]
	 *
	 * @since   2.0.0
	 */
	public static function getUsersByRole($role, $enabled = true)
	{
		static $tables = array(
			'client'       => '#__sellacious_clients',
			'seller'       => '#__sellacious_sellers',
			'manufacturer' => '#__sellacious_manufacturers',
			'staff'        => '#__sellacious_staffs',
		);

		// Creates a simple unique string for each parameter combination
		$storeId = $role;

		if (!isset(self::$usersByGroup[$storeId]))
		{
			$roles = static::getRoles();

			if (!array_key_exists($role, $roles) || !array_key_exists($role, $tables))
			{
				return array();
			}

			$db        = \JFactory::getDbo();
			$tableName = ArrayHelper::getValue($tables, $role);

			$query = $db->getQuery(true)
				->select('DISTINCT(user_id)')
				->from($db->qn('#__sellacious_categories', 'ug1'))
				->where('ug1.state = 1')
				->where('ug1.type = ' . $db->q($role))
				->join('INNER', $db->qn($tableName, 'm') . ' ON ug1.id = m.category_id');

			if ($enabled)
			{
				$query->where('m.state = 1');
			}

			$result = (array) $db->setQuery($query)->loadColumn();

			$result = ArrayHelper::toInteger($result);

			self::$usersByGroup[$storeId] = $result;
		}

		return self::$usersByGroup[$storeId];
	}

	/**
	 * Method to return a list of user groups mapped to a user. The returned list can optionally hold
	 * only the groups explicitly mapped to the user or all groups both explicitly mapped and inherited
	 * by the user.
	 *
	 * @param   integer  $userId     Id of the user for which to get the list of groups.
	 * @param   boolean  $recursive  True to include inherited user groups.
	 *
	 * @return  int[]  List of user group ids to which the user is mapped.
	 *
	 * @since   2.0.0
	 */
	public static function getGroupsByUser($userId, $recursive = true)
	{
		static $tables = array(
			'client'       => '#__sellacious_clients',
			'seller'       => '#__sellacious_sellers',
			'manufacturer' => '#__sellacious_manufacturers',
			'staff'        => '#__sellacious_staffs',
		);

		// Creates a simple unique string for each parameter combination:
		$storeId = $userId . ':' . (int) $recursive;

		if (!isset(self::$groupsByUser[$storeId]))
		{
			if (empty($userId))
			{
				try
				{
					$config         = ConfigHelper::getInstance('com_sellacious');
					$guestUsergroup = $config->get('guest_usergroup', 1);
				}
				catch (\Exception $e)
				{
					$guestUsergroup = 1;
				}

				if (!$recursive)
				{
					$result = array($guestUsergroup);
				}
				else
				{
					$db = \JFactory::getDbo();

					// Build the database query to get the rules for the asset.
					$query = $db->getQuery(true)
						->select('b.id');

					// A guest can only be a client
					$query->from('#__sellacious_categories AS a')
						->where('a.type = ' . $db->q('client'))
						->where('a.id = ' . (int) $guestUsergroup)
						->where('a.state = 1');

					$query->join('LEFT', '#__sellacious_categories AS b ON b.lft <= a.lft AND b.rgt >= a.rgt AND b.state = 1');

					// Execute the query and load the rules from the result.
					$result = (array) $db->setQuery($query)->loadColumn();
				}
			}
			else
			{
				$db     = \JFactory::getDbo();
				$result = array();

				// Gather all user groups from all user types
				foreach ($tables as $type => $tableName)
				{
					$query = $db->getQuery(true)
						->select($recursive ? 'b.id' : 'a.id');

					$query->from($db->qn($tableName, 'map'))
						->where('map.user_id = ' . (int) $userId)
						->where('map.state = 1');

					$query->join('LEFT', '#__sellacious_categories AS a ON a.id = map.category_id AND a.state = 1');

					// If we want the rules cascading up to the global asset node we need a self-join.
					if ($recursive)
					{
						$query->join('LEFT', '#__sellacious_categories AS b ON b.lft <= a.lft AND b.rgt >= a.rgt AND b.state = 1');
					}

					// Execute the query and load the rules from the result.
					$result[] = (array) $db->setQuery($query)->loadColumn();
				}

				$result = array_reduce($result, 'array_merge', array());
			}

			$result = ArrayHelper::toInteger($result);
			$result = empty($result) ? array('1') : array_unique($result);

			self::$groupsByUser[$storeId] = $result;
		}

		return self::$groupsByUser[$storeId];
	}

	/**
	 * Method to return a list of user groups assigned with super access.
	 *
	 * @return  array  List of user group ids
	 *
	 * @since   2.0.0
	 */
	public static function getSuperGroups()
	{
		static $suGroups = array();

		if (!$suGroups)
		{
			$groups = UserGroupHelper::getAll();

			foreach ($groups as $groupId => $group)
			{
				if (Access::checkGroup($groupId, 'app.admin'))
				{
					$suGroups[$groupId] = $group;
				}
			}
		}

		return $suGroups;
	}

	/**
	 * Method to return a list of users assigned with super access.
	 *
	 * @return  int[]  List of user ids
	 *
	 * @since   2.0.0
	 */
	public static function getSuperUsers()
	{
		$users    = array();
		$suGroups = static::getSuperGroups();

		foreach ($suGroups as $groupId => $group)
		{
			$users[$groupId] = static::getUsersByGroup($groupId, true);
		}

		$users = array_unique(array_reduce($users, 'array_merge', array()));

		return $users;
	}

	/**
	 * Get the available roles for the users in sellacious
	 *
	 * @return  string[]
	 *
	 * @since   2.0.0
	 */
	public static function getRoles()
	{
		$roles = array(
			'client'       => \JText::_('LIB_SELLACIOUS_USER_ROLE_CLIENT_LABEL'),
			'seller'       => \JText::_('LIB_SELLACIOUS_USER_ROLE_SELLER_LABEL'),
			'manufacturer' => \JText::_('LIB_SELLACIOUS_USER_ROLE_MANUFACTURER_LABEL'),
			'staff'        => \JText::_('LIB_SELLACIOUS_USER_ROLE_STAFF_LABEL'),
		);

		return $roles;
	}
}
