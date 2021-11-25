<?php
/**
 * @version     2.0.0
 * @package     com_sellaciousreporting
 *
 * @copyright   Copyright (C) 2012-2020 Bhartiy Web Technologies. All rights reserved.
 * @license     SPL Sellacious Private License; see http://www.sellacious.com/spl.html
 * @author      Aditya Chakraborty <info@bhartiy.com> - http://www.bhartiy.com
 */

use Sellacious\User\User;

defined('_JEXEC') or die;

/**
 * Reporting component helper.
 *
 * @since  1.6.0
 */
class ReportingHelper
{
	/**
	 * Method to get Current User categories
	 *
	 * @throws \Exception
	 *
	 * @return  array   user categories
	 *
	 * @since   1.6.0
	 */
	public static function getUserCategories()
	{
		$user   = JFactory::getUser();
		$helper = SellaciousHelper::getInstance();

		$userCategories = array();
		$userCategories[] = $helper->seller->getCategory($user->id);
		$userCategories[] = $helper->client->getCategory($user->id);
		$userCategories[] = $helper->staff->getCategory($user->id);
		$userCategories[] = $helper->manufacturer->getCategory($user->id);

		$userCategories = array_filter($userCategories);
		sort($userCategories);

		return $userCategories;
	}

	/**
	 * Method to check report permissions
	 *
	 * @param   $reportId  int  The report ID
	 * @param   $type      int  The report permission type
	 *
	 * @return  stdClass[]
	 *
	 * @throws  Exception
	 *
	 * @since   1.6.0
	 */
	public static function getReportPermission($reportId, $type = null)
	{
		$db    = JFactory::getDbo();
		$query = $db->getQuery(true);

		$query->select('*')->from('#__sellacious_reports_permissions')->where('report_id = ' .  (int) $reportId);

		if ($type)
		{
			$query->where('permission_type = ' . $db->q($type));
		}

		return (array) $db->setQuery($query)->loadObjectList();
	}

	/**
	 * Method to get report data
	 *
	 * @param   $reportId   int  The report ID
	 *
	 * @return  stdClass
	 *
	 * @throws  Exception
	 *
	 * @since   1.6.0
	 */
	public static function getReportData($reportId)
	{
		$db    = JFactory::getDbo();
		$query = $db->getQuery(true);

		$query->select('title')->from('#__sellacious_reports')->where('id = ' . (int) $reportId);

		return $db->setQuery($query)->loadObject();
	}

	/**
	 * Method to check whether user can edit the report
	 *
	 * @param   int   $id       The Report id
	 * @param   bool  $default  Whether the report can be edited
	 *
	 * @return  bool
	 *
	 * @throws  Exception
	 *
	 * @since   1.6.0
	 */
	public static function canEditReport($id, $default)
	{
		$user = User::getInstance();

		if ($user->authorise('app.admin'))
		{
			return true;
		}

		$permissions = static::getReportPermission($id, 'edit');

		if ($permissions)
		{
			$groups = $user->getAuthorisedGroups();

			foreach ($permissions as $permission)
			{
				if (in_array($permission->user_cat_id, $groups))
				{
					return true;
				}
			}
		}

		return $default;
	}
}
