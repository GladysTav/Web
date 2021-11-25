<?php
/**
 * @version     2.0.0
 * @package     sellacious
 *
 * @copyright   Copyright (C) 2012-2020 Bhartiy Web Technologies. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Izhar Aazmi <info@bhartiy.com> - http://www.bhartiy.com
 */
// no direct access
defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Authentication\AuthenticationResponse;
use Sellacious\Config\ConfigHelper;
use Sellacious\Utilities\Otp;

/**
 * Sellacious Authentication Plugin
 *
 * @since  2.0.0
 */
class PlgAuthenticationSellacious extends JPlugin
{
	protected $autoloadLanguage = true;

	/**
	 * This method should handle any authentication and report back to the subject
	 *
	 * @param   array                    $credentials  Array holding the user credentials
	 * @param   array                    $options      Array of extra options
	 * @param   AuthenticationResponse  &$response     Authentication response object
	 *
	 * @return  void
	 *
	 * @throws  Exception
	 *
	 * @since   2.0.0
	 */
	public function onUserAuthenticate($credentials, $options, &$response)
	{
		$this->loadLanguage();

		$app = JFactory::getApplication();

		if (empty($credentials['username']) || empty($credentials['password']))
		{
			return;
		}

		$response->type = 'Sellacious';

		$verified = false;

		// If hashed with OTP, we must check it first and foremost
		$value = Otp::secureUnhash($credentials['username']);

		if ($value === false)
		{
			$response->status        = JAuthentication::STATUS_FAILURE;
			$response->error_message = JText::_('PLG_AUTHENTICATION_SELLACIOUS_VERIFY_OTP_FAILED');

			return;
		}
		elseif ($value)
		{
			$verified = true;

			$credentials['username'] = $value;
		}

		try
		{
			$result = $this->getUser($credentials['username']);
		}
		catch (Exception $e)
		{
			$response->status        = JAuthentication::STATUS_FAILURE;
			$response->error_message = $e->getMessage();

			return;
		}

		// Check Password if not already OTP verified
		if (!$verified)
		{
			$verified = JUserHelper::verifyPassword($credentials['password'], $result->password, $result->id);
		}

		if ($verified !== true)
		{
			// Invalid password
			$response->status        = JAuthentication::STATUS_FAILURE;
			$response->error_message = JText::_('PLG_AUTHENTICATION_SELLACIOUS_USER_NOT_EXIST');

			return;
		}

		// Successful match
		$user = JUser::getInstance($result->id);

		$response->email    = $user->email;
		$response->fullname = $user->name;
		$response->username = $user->username;

		if ($app->isClient('administrator'))
		{
			$response->language = $user->getParam('admin_language');
		}
		else
		{
			$response->language = $user->getParam('language');
		}

		$response->status        = JAuthentication::STATUS_SUCCESS;
		$response->error_message = '';
	}

	/**
	 * Method to identify the user using given credentials
	 *
	 * @param   string  $username
	 *
	 * @return  stdClass
	 *
	 * @throws  Exception
	 *
	 * @since   2.0.0
	 */
	protected function getUser($username)
	{
		$db    = JFactory::getDbo();
		$query = $db->getQuery(true);

		$query->select('a.id, a.username, a.password')->from($db->qn('#__users', 'a'));

		if (filter_var($username, FILTER_VALIDATE_EMAIL))
		{
			$query->where('a.email = ' . $db->q($username));
		}
		else
		{
			$query->join('inner', $db->qn('#__sellacious_profiles', 'b') . ' ON b.user_id = a.id');
			$query->where('b.mobile = ' . $db->q($username));
		}

		$result = $db->setQuery($query)->loadObject();
		$rows   = $db->getAffectedRows();

		if ($rows == 0)
		{
			throw new Exception(JText::_('PLG_AUTHENTICATION_SELLACIOUS_USER_NOT_EXIST'));
		}

		if ($rows > 1)
		{
			throw new Exception(JText::_('PLG_AUTHENTICATION_SELLACIOUS_MULTIPLE_USERS'));
		}

		return $result;
	}
}
