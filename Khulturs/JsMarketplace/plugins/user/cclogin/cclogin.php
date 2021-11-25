<?php
/**
 * @package     Joomla.Plugin
 * @subpackage  User.joomla
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\Registry\Registry;

/**
 * Joomla User plugin
 *
 * @since  1.5
 */
class PlgUserCcLogin extends JPlugin
{
	/**
	 * Application object
	 *
	 * @var    JApplicationCms
	 * @since  3.2
	 */
	protected $app;

	/**
	 * Database object
	 *
	 * @var    JDatabaseDriver
	 * @since  3.2
	 */
	protected $db;

	/**
	 * Remove all sessions for the user name
	 *
	 * Method is called after user data is deleted from the database
	 *
	 * @param   array   $user    Holds the user data
	 * @param   boolean $success True if user was successfully stored in the database
	 * @param   string  $msg     Message
	 *
	 * @return  boolean
	 *
	 * @since   1.0
	 */
	public function onUserAfterDelete($user, $success, $msg)
	{
		if (!$success)
		{
			return false;
		}

		$query = $this
			->db->getQuery(true)
			->delete('#__ccl_user_details')
			->where('user_id' . ' = ' . (int) $user['id']);

		try
		{
			$this->db->setQuery($query)->execute();
		}
		catch (RuntimeException $e)
		{
			return false;
		}

		return true;
	}

	/**
	 * Store new record on afterLogin and redirect to the view
	 *
	 * @since 1.0
	 */
	public function onUserAfterLogin()
	{
		if ($this->app->getUserState('need_password'))
		{
			$user = JFactory::getUser();

			$query = $this
				->db
				->getQuery(true)
				->insert('#__ccl_user_details')
				->columns(
					array(
						'user_id',
						'social_plugin',
						'social_identifier'
					)
				)->values($this->db->quote($user->id) . ',' . $this->db->quote($this->app->getUserState('auth')) . ',' . $this->db->quote($this->app->getUserState('identifier')));
			$this->db->setQuery($query)->execute();
		}

		if ($this->app->isSite())
		{
			$arguments = func_get_args();

			// If the user was logged in using CCLogin or Joomla plugin
			if (in_array($arguments[0]['responseType'], array('joomla', 'cclogin')))
			{
				$params             = JComponentHelper::getParams('com_ccl');
				$afterLoginRoute    = $params->get('after_login_route');
				$afterLoginMenuItem = $params->get('after_login_menu_item');

				if ($afterLoginRoute !== null || $afterLoginMenuItem !== null)
				{
					if ($afterLoginRoute !== null)
					{
						$afterLoginRoute = JRoute::_($afterLoginRoute);
					}
					else
					{
						$afterLoginRoute = JRoute::_('index.php?Itemid=' . $afterLoginMenuItem);
					}
				}
				else
				{
					$afterLoginRoute = JRoute::_('index.php?option=com_users&view=profile');
				}

				$this->app->redirect($afterLoginRoute);
			}
		}
	}
}
