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

/**
 * Helper to deal with user groups.
 *
 * @since  2.0.0
 */
class UserGroupHelper
{
	/**
	 * Available user groups
	 *
	 * @var    array
	 *
	 * @since  2.0.0
	 */
	protected static $groups = array();

	/**
	 * Get a user group by its id.
	 *
	 * @param   int  $id  Group identifier
	 *
	 * @return  \stdClass  The usergroup if exists, null otherwise
	 *
	 * @since   2.0.0
	 */
	public static function get($id)
	{
		if (!static::has($id))
		{
			static::load($id);
		}

		if (static::has($id))
		{
			return static::$groups[$id];
		}

		return null;
	}

	/**
	 * Get the list of existing user groups.
	 *
	 * @return  array
	 *
	 * @since   2.0.0
	 */
	public static function getAll()
	{
		// If we already have all items loaded, skip loading
		if (static::getTotal() !== count(static::$groups))
		{
			static::loadAll();
		}

		return static::$groups;
	}

	/**
	 * Clear the list of loaded user groups. Helpful to force reload
	 *
	 * @return  void
	 *
	 * @since   2.0.0
	 */
	public static function clearAll()
	{
		static::$groups = array();
	}

	/**
	 * Get total available user groups in database.
	 *
	 * @return  int
	 *
	 * @since   2.0.0
	 */
	public static function getTotal()
	{
		static $total = null;

		if ($total === null)
		{
			$types = UserHelper::getRoles();

			$db    = \JFactory::getDbo();
			$query = $db->getQuery(true)
				->select('COUNT(a.id)')
				->from($db->qn('#__sellacious_categories', 'a'))
				->where('(a.type IN (' . implode(', ', $db->q(array_keys($types))) . ') OR a.id = 1)');

			$db->setQuery($query);

			$total = (int) $db->loadResult();
		}

		return $total;
	}

	/**
	 * Check if a group is in the list.
	 *
	 * @param   int  $id  Group identifier
	 *
	 * @return  boolean
	 *
	 * @since   2.0.0
	 */
	protected static function has($id)
	{
		return array_key_exists($id, static::$groups);
	}

	/**
	 * Load a user group from the database.
	 *
	 * @param   int  $id  Group id to load
	 *
	 * @return  void
	 *
	 * @since   2.0.0
	 */
	protected static function load($id)
	{
		$types = UserHelper::getRoles();

		$db    = \JFactory::getDbo();
		$query = $db->getQuery(true)
			->select('a.id, a.title, a.alias, a.parent_id, a.type, a.is_default')
			->from($db->qn('#__sellacious_categories', 'a'))
			->where('(a.type IN (' . implode(', ', $db->q(array_keys($types))) . ') OR a.id = 1)')
			->where('a.id = ' . (int) $id)
			->order('a.lft ASC');

		$db->setQuery($query);

		$group = $db->loadObject();

		if ($group)
		{
			static::$groups[$id] = &$group;

			static::populateGroupData($group);
		}
	}

	/**
	 * Load all user groups from the database.
	 *
	 * @return  void
	 *
	 * @since   2.0.0
	 */
	protected static function loadAll()
	{
		static::$groups = array();

		$types = UserHelper::getRoles();

		$db    = \JFactory::getDbo();
		$query = $db->getQuery(true)
			->select('a.id, a.title, a.alias, a.parent_id, a.type, a.is_default')
			->from($db->qn('#__sellacious_categories', 'a'))
			->where('(a.type IN (' . implode(', ', $db->q(array_keys($types))) . ') OR a.id = 1)')
			->order('a.lft ASC');

		$db->setQuery($query);

		$groups = $db->loadObjectList('id');

		static::$groups = $groups ?: array();

		foreach (static::$groups as $group)
		{
			static::populateGroupData($group);
		}
	}

	/**
	 * Populate data for a specific user group.
	 *
	 * @param   \stdClass  $group  Group
	 *
	 * @return  void
	 *
	 * @since   2.0.0
	 */
	protected static function populateGroupData($group)
	{
		if ($group && !property_exists($group, 'hierarchy'))
		{
			$parentId = (int) $group->parent_id;

			if ($parentId === 0)
			{
				$group->hierarchy = array($group->id);
				$group->level     = 0;
			}
			elseif ($parentGroup = static::get($parentId))
			{
				if (!property_exists($parentGroup, 'hierarchy'))
				{
					static::populateGroupData($parentGroup);
				}

				$group->hierarchy = array_merge($parentGroup->hierarchy, array($group->id));
				$group->level     = count($group->hierarchy) - 1;
			}
			else
			{
				// Would that ever be in a sane db state?
				$group->hierarchy = $group->id == 1 ? array(1) : array($group->id, 1);
				$group->level     = $group->id == 1 ? 0 : 1;
			}
		}
	}
}
