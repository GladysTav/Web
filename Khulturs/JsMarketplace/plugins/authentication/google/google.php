<?php

/**
 * @plugin    Awo Email Login
 * @copyright Copyright (C) 2010 Seyi Awofadeju - All rights reserved.
 * @Website   : http://dev.awofadeju.com
 * @license   - http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 * */
defined('_JEXEC') or die;

class plgAuthenticationGoogle extends JPlugin
{
	/**
	 * plgAuthenticationGoogle constructor.
	 *
	 * @param object $subject
	 * @param array  $config
	 */
	public function __construct($subject, array $config = array())
	{
		parent::__construct($subject, $config);

		require_once dirname(__FILE__) . '/lib/apiclient/src/Google/autoload.php';
	}

	/**
	 * Authenticate user using Google login
	 *
	 * @param                         $credentials
	 * @param                         $options
	 * @param JAuthenticationResponse $response
	 *
	 *
	 * @since 1.0
	 */
	public function onUserAuthenticate($credentials, $options, &$response)
	{
		$app = JFactory::getApplication();

		if ($app->isSite() && $app->input->getCmd('auth') === 'google')
		{
			/* @var $googleObject Google_Client */

			/* @var $authObject Google_Service_Oauth2 */
			list($googleObject, $authObject) = $this->getGoogleObject();
			$googleObject->authenticate($app->input->getString('code'));
			$accessToken = $googleObject->getAccessToken();

			if (!empty($accessToken))
			{
				$userData = $authObject->userinfo->get();
				CclHelpersCcl::logInUser('google', $userData->getId(), $userData->getName(), $userData->getEmail(), $response);
			}
			else
			{
				$response->status = JAuthentication::STATUS_CANCEL;
			}
		}
	}


	public function onAuthenticationMethodRender()
	{
		/* @var $google Google_Client */
		list($google,) = $this->getGoogleObject();

		return
			(object) array(
				'url'    => $google->createAuthUrl(),
				'plugin' => 'google'
			);
	}

	/**
	 *
	 * @return array
	 *
	 * @since version
	 */
	protected function getGoogleObject()
	{
		$gClient = new Google_Client();
		$gClient->setClientId($this->params->get('client_id'));
		$gClient->setClientSecret($this->params->get('client_secret'));
		$gClient->setRedirectUri(CclHelpersCcl::getLoginUrl('google'));
		$gClient->setScopes(
			array(
				'https://www.googleapis.com/auth/plus.me',
				'https://www.googleapis.com/auth/userinfo.email',
				'https://www.googleapis.com/auth/userinfo.profile',
			)
		);

		//Send Client Request
		$objOAuthService = new Google_Service_Oauth2($gClient);

		return array($gClient, $objOAuthService);
	}

}