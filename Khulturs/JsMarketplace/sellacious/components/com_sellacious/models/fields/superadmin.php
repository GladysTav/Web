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
use Joomla\CMS\Helper\UserGroupsHelper;
use Joomla\Utilities\ArrayHelper;

defined('_JEXEC') or die;

JFormHelper::loadFieldClass('list');

/**
 * Form Field class for a list of super administrator
 *
 * @since  1.7.0
 */
class SellaciousFormFieldSuperAdmin extends JFormFieldList
{
	/**
	 * The field type.
	 *
	 * @var  string
	 *
	 * @since  1.7.0
	 */
	protected $type = 'SuperAdmin';

	/**
	 * Method to get the field options.
	 *
	 * @return  array  The field option objects.
	 *
	 * @since   1.6
	 *
	 * @since  1.7.0
	 */
	protected function getOptions()
	{
		$db    = JFactory::getDbo();
		$query = $db->getQuery(true);

		$users = $this->getSuperUsers();

		if ($users)
		{
			$query->select('u.id value, u.name text')
				->from('#__users u')
				->where('u.id = ' . implode(' OR u.id = ', $users));

			$users = $db->setQuery($query)->loadObjectList();
		}

		return $users;
	}

	/**
	 * Method to return a list of user groups assigned with super access.
	 *
	 * @return  array  List of user group ids
	 *
	 * @since   1.7.0
	 */
	public function getSuperGroups()
	{
		static $suGroups = array();

		if (!$suGroups)
		{
			$uh     = UserGroupsHelper::getInstance();
			$groups = $uh->loadAll()->getAll();

			foreach ($groups as $groupId => $group)
			{
				if (JAccess::checkGroup($groupId, 'core.admin'))
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
	 * @since   1.7.0
	 */
	public function getSuperUsers()
	{
		$users    = array();
		$suGroups = $this->getSuperGroups();

		foreach ($suGroups as $groupId => $group)
		{
			$users[$groupId] = JAccess::getUsersByGroup($groupId, true);
		}

		$users = array_unique(array_reduce($users, 'array_merge', array()));

		return ArrayHelper::toInteger($users);
	}

}
