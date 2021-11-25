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
 * Class CclFrontendHelper
 *
 * @since  1.6
 */
class CclHelpersCcl
{
	/**
	 * Create user
	 *
	 * @param string $name
	 * @param string $email
	 * @param string $socialProvider
	 * @param string $socialIdentifier
	 *
	 * @return bool|JUser
	 *
	 * @since 1.0
	 */
	public static function createUser($name, $email, $socialProvider, $socialIdentifier)
	{
		$params                 = JComponentHelper::getParams('com_ccl');
		$allowUsersRegistration = $params->get('allow_registration', true);

		if (!$allowUsersRegistration)
		{
			return false;
		}

		$generator = new \Facebook\PseudoRandomString\OpenSslPseudoRandomStringGenerator();
		$user      = JUser::getInstance();

		$userData             = array();
		$userData['name']     = $name;
		$userData['username'] = $email;
		$userData['email']    = $email;
		$password             = $generator->getPseudoRandomString(16) . sha1('JCC Rocks');
		$userData['password'] = $password;
		if ($user->bind($userData))
		{
			$user->groups[] = 2;

			if ($user->save())
			{
				static::createSocialRecord($user->id, $socialProvider, $socialIdentifier);

				return $user;
			}
		}

		return false;
	}

	/**
	 * Get login URL
	 *
	 * @param string $socialLogin Social login plugin
	 *
	 * @return string|array
	 *
	 * @since 1.0
	 */
	public static function getLoginUrl($socialLogin)
	{
		$uriBase      = JUri::base();
		$router       = JApplicationSite::getRouter();
		$url          = preg_replace('/\/administrator/', '', substr($uriBase, 0, strlen($uriBase) - 1) . $router->build("index.php?option=com_ccl&task=login&auth=$socialLogin"));
		$languagesSef = static::getLanguagesSef();

		foreach ($languagesSef as $languageCode => $languageSef)
		{
			$languageSef = $languageSef['sef'];
			$url         = preg_replace("/$languageSef\\/component\\//", "/component/", $url);
		}

		return preg_replace('/([a-zA-Z0-9])(\/\/)/', '$1/', $url);
	}

	/**
	 * Get languages SEF
	 *
	 * @return array
	 *
	 * @since 1.0
	 */
	protected static function getLanguagesSef()
	{
		$db    = JFactory::getDbo();
		$query = $db->getQuery(true);

		$query
			->select(
				array(
					'sef',
					'lang_code',
				)
			)
			->from('#__languages')
			->where('published = 1');

		$db->setQuery($query);
		$sef = $db->loadAssocList('lang_code');

		return $sef;
	}

	/**
	 * Check if the language code is enabled
	 *
	 * @return bool
	 *
	 * @since 1.0
	 */
	protected static function isLanguageFilterPluginEnabled()
	{
		return static::isPluginEnabled('system', 'languagefilter');
	}

	/**
	 * Render social login buttons
	 *
	 * @return string
	 *
	 * @since 1.0
	 */
	public static function renderSocialLoginButtons()
	{
		if (static::canRenderButtons())
		{
			// Import authentication plugins
			JPluginHelper::importPlugin('authentication');

			// Import language file
			JFactory::getLanguage()->load('com_ccl');

			$app         = JFactory::getApplication();
			$pluginsData = $app->triggerEvent('onAuthenticationMethodRender');
			$buttonsHtml = array();

			foreach ($pluginsData as $pluginData)
			{
				$buttonsHtml[] = JLayoutHelper::render('components.com_ccl.authentication.login-button', $pluginData);
			}

			return implode(JLayoutHelper::render('components.com_ccl.authentication.button-separator'), $buttonsHtml);
		}

		return '';
	}

	/**
	 * Check if the login buttons can be rendered
	 *
	 * @return bool
	 *
	 * @since 1.0
	 */
	protected static function canRenderButtons()
	{
		return static::isPluginEnabled('user', 'cclogin') && static::isPluginEnabled('system', 'cclogin');
	}

	/**
	 * Check if a certain plugin is enabled
	 *
	 * @param string $folder
	 * @param string $element
	 *
	 * @return bool
	 *
	 * @since 1.0
	 */
	protected static function isPluginEnabled($folder, $element)
	{
		$db    = JFactory::getDbo();
		$query = $db->getQuery(true);

		$query
			->select('enabled')
			->from('#__extensions')
			->where(
				array(
					'type LIKE ' . $db->quote('plugin'),
					'element LIKE ' . $db->quote($element),
					'folder LIKE ' . $db->quote($folder)
				)
			);

		$db->setQuery($query);

		return 1 == $db->loadResult();
	}

	/**
	 * Get user identifier from social provider id
	 *
	 * @param string $socialProvider
	 * @param string $identifier
	 *
	 * @return bool|string
	 *
	 * @since 1.0
	 */
	public static function getUserIdFromSocialIdentifier($socialProvider, $identifier)
	{
		$db    = JFactory::getDbo();
		$query = $db->getQuery(true);

		$query
			->select('user_id')
			->from('#__ccl_user_details')
			->where(
				array(
					'social_plugin = ' . $db->quote($socialProvider),
					'social_identifier = ' . $db->quote($identifier)
				)
			);
		$db->setQuery($query);
		$userId = $db->loadResult();

		return $userId !== null ? $userId : false;
	}

	/**
	 * Get user id from email address
	 *
	 * @param string $email
	 *
	 * @return bool|mixed
	 *
	 * @since version
	 */
	public static function getUserIdFromEmail($email)
	{
		$db    = JFactory::getDbo();
		$query = $db->getQuery(true);

		$query
			->clear()
			->select('id')
			->from('#__users')
			->where('email LIKE ' . $db->quote($email));

		$db->setQuery($query);
		$userId = $db->loadResult();

		return $userId !== null ? $userId : false;
	}

	/**
	 * Log in user
	 *
	 * @param string                  $socialProvider
	 * @param string                  $socialIdentifier
	 * @param string                  $name
	 * @param string                  $email
	 * @param JAuthenticationResponse $response
	 *
	 *
	 * @since 1.0
	 */
	public static function logInUser($socialProvider, $socialIdentifier, $name, $email, &$response)
	{
		$app    = JFactory::getApplication();
		$userId = CclHelpersCcl::getUserIdFromSocialIdentifier($socialProvider, $socialIdentifier);

		$app->setUserState('identifier', $socialIdentifier);

		if (empty($userId))
		{
			// Check if the email exists in the system
			$userId = static::getUserIdFromEmail($email);

			if (empty($userId))
			{
				$user = static::createUser($name, $email, $socialProvider, $socialIdentifier);

				if ($user !== false)
				{
					static::generateSuccessResponse($user, $response, true);
				}
				else
				{
					$response->status = JAuthentication::STATUS_FAILURE;
				}
			}
			else
			{
				$user = JFactory::getUser($userId);
				$app->setUserState('need_password', true);
				$app->setUserState('need_password_username', $user->username);
				$app->setUserState('identifier', $socialIdentifier);
				$response->error_message = '';
				$response->status        = JAuthentication::STATUS_FAILURE;
			}
		}
		else
		{
			$user = JFactory::getUser($userId);

			// This user does not exist
			if (empty($user->id))
			{
				$user = static::createUser($name, $email, $socialProvider, $socialIdentifier);

				if (!empty($user->id))
				{
					static::generateSuccessResponse($user, $response, true);
				}
				else
				{
					$response->status = JAuthentication::STATUS_FAILURE;
				}
			}
			else
			{
				static::generateSuccessResponse($user, $response);
			}
		}
	}

	/**
	 * Generate success response
	 *
	 * @param JUser                   $user
	 * @param JAuthenticationResponse $response
	 * @param bool                    $isNew
	 *
	 * @return void
	 *
	 * @since 1.0
	 */
	protected static function generateSuccessResponse(JUser $user, JAuthenticationResponse &$response, $isNew = false)
	{
		$response->username = $user->username;
		$response->email    = $user->email;
		$response->fullname = $user->name;
		$response->type     = 'cclogin';
		$response->language = JFactory::getApplication()->isAdmin() ? $user->getParam('admin_language') : $user->getParam('language');
		$response->status   = JAuthentication::STATUS_SUCCESS;

		if ($isNew)
		{
			JFactory::getApplication()->setUserState('new_user', true);
		}
	}

	/**
	 * Create social record
	 *
	 * @param $userId
	 * @param $socialProvider
	 * @param $socialIdentifier
	 *
	 * @return bool
	 *
	 * @since 1.0
	 */
	protected static function createSocialRecord($userId, $socialProvider, $socialIdentifier)
	{
		// Create record in table
		$socialRecord = (object) array(
			'user_id'           => $userId,
			'social_plugin'     => $socialProvider,
			'social_identifier' => $socialIdentifier
		);

		return JFactory::getDbo()->insertObject('#__ccl_user_details', $socialRecord, 'id');
	}
}
