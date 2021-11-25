<?php
/**
 * @version     2.0.0
 * @package     Sellacious Login Module
 *
 * @copyright   Copyright (C) 2012-2020 Bhartiy Web Technologies. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Izhar Aazmi <info@bhartiy.com> - http://www.bhartiy.com
 */
defined('_JEXEC') or die;

use Joomla\Registry\Registry;
use Sellacious\Communication\CommunicationHelper;
use Sellacious\Config\ConfigHelper;
use Sellacious\Utilities\Otp;

/**
 * Helper for login module
 *
 * @since   2.0.0
 */
class ModSellaciousLoginHelper
{
	/**
	 * Retrieve the URL where the user should be returned after logging in
	 *
	 * @param   Registry  $params  module parameters
	 *
	 * @return  string
	 *
	 * @since   2.0.0
	 */
	public static function getReturnUrl($params)
	{
		$guest = JFactory::getUser()->guest;
		$url   = JUri::getInstance()->toString();

		try
		{
			$app  = JFactory::getApplication();
			$item = $app->getMenu()->getItem($params->get($guest ? 'login' : 'logout'));

			if ($item)
			{
				$lang = '';

				if ($item->language !== '*' && JLanguageMultilang::isEnabled())
				{
					$lang = '&lang=' . $item->language;
				}

				$url = 'index.php?Itemid=' . $item->id . $lang;
			}
		}
		catch (Exception $e)
		{
		}

		return base64_encode($url);
	}

	/**
	 * Method to get login options
	 *
	 * @return  Registry
	 *
	 * @since   2.0.0
	 */
	public static function getLoginOptions()
	{
		static $registry;

		if ($registry)
		{
			return $registry;
		}

		try
		{
			$config   = ConfigHelper::getInstance('com_sellacious');
			$methods  = $config->get('login_methods');
		}
		catch (Exception $e)
		{
			$methods = array();
		}

		$registry = new Registry;

		if ($methods)
		{
			if (in_array('email-pw', $methods))
			{
				$registry->set('email.pw', true);
				$registry->set('pw.email', true);
			}

			if (in_array('mobile-pw', $methods))
			{
				$registry->set('mobile.pw', true);
				$registry->set('pw.mobile', true);
			}

			if (in_array('email-otp', $methods))
			{
				$registry->set('email.otp', true);
				$registry->set('otp.email', true);
			}

			if (in_array('mobile-otp', $methods))
			{
				$registry->set('mobile.otp', true);
				$registry->set('otp.mobile', true);
			}
		}
		else
		{
			$registry->set('email.pw', true);
			$registry->set('pw.email', true);
		}

		if (JPluginHelper::isEnabled('authentication', 'joomla'))
		{
			$registry->set('username.pw', true);
			$registry->set('pw.username', true);
		}

		return $registry;
	}

	/**
	 * Check user identity (username, email, mobile) to match registered user exists
	 *
	 * @return  void
	 *
	 * @since   2.0.0
	 */
	public static function checkIdentityAjax()
	{
		try
		{
			if (!JSession::checkToken())
			{
				throw new Exception(JText::_('JINVALID_TOKEN'));
			}

			$app      = JFactory::getApplication();
			$identity = $app->input->getString('identity');
			$type     = static::getIdentityType($identity);
			$user     = static::checkUser($identity, $type);

			echo new JResponseJson($user);
		}
		catch (Exception $e)
		{
			echo new JResponseJson($e);
		}

		jexit();
	}

	/**
	 * Generate and send an OTP to the selected email/phone number
	 *
	 * @return  void
	 *
	 * @since   2.0.0
	 */
	public static function requestOtpAjax()
	{
		try
		{
			if (!JSession::checkToken())
			{
				throw new Exception(JText::_('JINVALID_TOKEN'));
			}

			$app      = JFactory::getApplication();
			$identity = $app->input->getString('identity');
			$type     = static::getIdentityType($identity);
			$user     = static::checkUser($identity, $type);
			$config   = ConfigHelper::getInstance('com_sellacious');

			$otpLength   = $config->get('otp_length', 6);
			$otpValidity = $config->get('otp_validity', 30);

			$otp    = new Otp($otpValidity, $otpLength);
			$secret = $otp->genSecret('login', $identity);
			$code   = $otp->getOtp($secret);

			$sent = static::sendOtp($user, $identity, $code);

			echo new JResponseJson($sent, JText::_('MOD_SELLACIOUS_LOGIN_SENT_OTP_SUCCESS'));
		}
		catch (Exception $e)
		{
			echo new JResponseJson($e);
		}

		jexit();
	}

	/**
	 * Login to the site using given credentials
	 *
	 * @return  void
	 *
	 * @since   2.0.0
	 */
	public static function loginAjax()
	{
		try
		{
			if (!JSession::checkToken())
			{
				throw new Exception(JText::_('JINVALID_TOKEN'));
			}

			$me = JFactory::getUser();

			// If the current user is not guest, we must not logout.
			if (!$me->guest)
			{
				$data = array(
					'id'       => $me->id,
					'name'     => $me->name,
					'username' => $me->username,
					'email'    => $me->email,
					'token'    => JSession::getFormToken(),
				);

				echo new JResponseJson($data);

				jexit();
			}

			$app      = JFactory::getApplication();
			$identity = $app->input->getString('identity');
			$password = $app->input->post->getString('passkey');
			$type     = static::getIdentityType($identity);
			$user     = static::checkUser($identity, $type);

			// Check OTP first (if allowed), but leave login logic to authentication plugin
			if (static::getLoginOptions()->get('otp'))
			{
				$config = ConfigHelper::getInstance('com_sellacious');

				$otpLength   = $config->get('otp_length', 6);
				$otpValidity = $config->get('otp_validity', 30);

				$otp    = new Otp($otpValidity, $otpLength);
				$secret = $otp->genSecret('login', $identity);
				$valid  = $otp->checkOtp($secret, $password);

				if ($valid)
				{
					$identity = Otp::secureHash($user->email);
				}
			}

			$credentials = array('username' => $identity, 'password' => $password);
			$login       = $app->login($credentials, array('silent' => true));
			$user        = JFactory::getUser();

			if ($login === true && $user->id > 0)
			{
				$data = array(
					'id'       => $user->id,
					'name'     => $user->name,
					'username' => $user->username,
					'email'    => $user->email,
					'token'    => JSession::getFormToken(),
				);

				echo new JResponseJson($data);
			}
			else
			{
				throw new Exception(JText::sprintf('MOD_SELLACIOUS_LOGIN_LOGIN_FAILED'));
			}
		}
		catch (Exception $e)
		{
			echo new JResponseJson($e);
		}

		jexit();
	}

	/**
	 * Method to send the OTP to the given email/phone number
	 *
	 * @param   stdClass  $user       The destination type, valid values are: email, mobile
	 * @param   string    $recipient  The recipient email or mobile number
	 * @param   string    $code       The OTP code to be sent
	 *
	 * @return  mixed
	 *
	 * @throws  Exception
	 *
	 * @since   2.0.0
	 */
	protected static function sendOtp($user, $recipient, $code)
	{
		if ($user->identity == 'username')
		{
			$recipient = $user->email;
			$message   = CommunicationHelper::getHandler('email');

			$message->setSubject(JText::_('COM_SELLACIOUS_PREVERIFY_OTP_EMAIL_SUBJECT'));
			$message->setBody(JText::sprintf('COM_SELLACIOUS_PREVERIFY_OTP_EMAIL_BODY', $code));
			$message->addRecipient($recipient);
			$message->send();
		}
		elseif ($user->identity == 'email')
		{
			$message = CommunicationHelper::getHandler('email');

			$message->setSubject(JText::_('COM_SELLACIOUS_PREVERIFY_OTP_EMAIL_SUBJECT'));
			$message->setBody(JText::sprintf('COM_SELLACIOUS_PREVERIFY_OTP_EMAIL_BODY', $code));
			$message->addRecipient($recipient);
			$message->send();
		}
		elseif ($user->identity == 'mobile')
		{
			$message = CommunicationHelper::getHandler('text');

			$message->setBody(JText::sprintf('COM_SELLACIOUS_PREVERIFY_OTP_TEXT_BODY', $code));
			$message->addRecipient($recipient);
			$message->send();
		}
		else
		{
			throw new Exception(JText::_('MOD_SELLACIOUS_LOGIN_SEND_OTP_INVALID_FIELD'));
		}

		return true;
	}

	/**
	 * Check whether the given user is valid or not
	 *
	 * @param   string  $identity
	 * @param   string  $type
	 *
	 * @return  stdClass
	 *
	 * @throws  Exception
	 *
	 * @since   2.0.0
	 */
	protected static function checkUser($identity, $type)
	{
		if (!static::getLoginOptions()->get($type))
		{
			throw new Exception(JText::_('MOD_SELLACIOUS_LOGIN_INVALID_INPUT'));
		}

		switch ($type)
		{
			case 'email':
				$user = static::getByEmail($identity);
				break;
			case 'mobile':
				$user = static::getByMobile($identity);
				break;
			case 'username':
				$user = static::getByUsername($identity);
				break;
			default:
				throw new Exception(JText::_('MOD_SELLACIOUS_LOGIN_INVALID_INPUT'));
		}

		if (empty($user))
		{
			throw new Exception(JText::_('MOD_SELLACIOUS_LOGIN_USER_NO_MATCH'));
		}

		$user->identity = $type;

		if ($user->block)
		{
			if ($user->activation)
			{
				$uParams    = JComponentHelper::getParams('com_users');
				$activation = $uParams->get('useractivation');

				throw new Exception(JText::_('COM_SELLACIOUS_USER_USER_NOT_ACTIVATED_' . ($activation == 1 ? 'SELF' : 'ADMIN')));
			}

			throw new Exception(JText::_('COM_SELLACIOUS_USER_USER_BLOCKED'));
		}

		return $user;
	}

	protected static function getIdentityType($identity)
	{
		if (!$identity)
		{
			return null;
		}

		$regexE = chr(1) . '^[a-zA-Z0-9.!#$%&’*+/=?^_`{|}~-]+@[a-zA-Z0-9-]+(?:\.[a-zA-Z0-9-]+)*$' . chr(1);

		if (preg_match($regexE, $identity))
		{
			return 'email';
		}

		try
		{
			$config = ConfigHelper::getInstance('com_sellacious');
			$regexM = $config->get('address_mobile_regex', '^\+?[\d]{8,12}$');
		}
		catch (Exception $e)
		{
			$regexM = '^\+?[\d]{8,12}$';
		}

		if (preg_match(chr(1) . $regexM . chr(1), $identity))
		{
			return 'mobile';
		}

		return 'username';
	}

	protected static function getByEmail($identity)
	{
		$db    = JFactory::getDbo();
		$query = $db->getQuery(true);

		$query->select('id, username, name, email, block, activation')->from('#__users')->where('email = ' . $db->q($identity));

		$user = $db->setQuery($query)->loadObject();

		return $user;
	}

	protected static function getByMobile($identity)
	{
		$db    = JFactory::getDbo();
		$query = $db->getQuery(true);

		$query->select('a.mobile')->from($db->qn('#__sellacious_profiles', 'a'))->where('mobile = ' . $db->q($identity));
		$query->select('u.id, u.username, u.name, u.email, u.block, u.activation')->join('inner', '#__users AS u ON u.id = a.user_id');

		$user = $db->setQuery($query)->loadObject();

		return $user;
	}

	protected static function getByUsername($identity)
	{
		$db    = JFactory::getDbo();
		$query = $db->getQuery(true);

		$query->select('id, username, name, email, block, activation')->from('#__users')->where('username = ' . $db->q($identity));

		$user = $db->setQuery($query)->loadObject();

		return $user;
	}
}
