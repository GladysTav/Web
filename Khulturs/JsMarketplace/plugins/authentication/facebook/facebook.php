<?php

/**
 * @plugin    Awo Email Login
 * @copyright Copyright (C) 2010 Seyi Awofadeju - All rights reserved.
 * @Website   : http://dev.awofadeju.com
 * @license   - http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 * */
defined('_JEXEC') or die;

class plgAuthenticationFacebook extends JPlugin
{
	/**
	 * plgAuthenticationFacebook constructor.
	 *
	 * @param object $subject
	 * @param array  $config
	 */
	public function __construct($subject, array $config = array())
	{
		parent::__construct($subject, $config);

		JLoader::registerNamespace('Facebook', dirname(__FILE__) . '/lib/graph-sdk/');
	}


	/**
	 * Authenticate user using Facebook login
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

		if ($app->isSite() && $app->input->getCmd('auth') === 'facebook')
		{
			$facebook    = $this->getFacebookObject();
			$helperLogin = $facebook->getRedirectLoginHelper();

			try
			{
				$accessToken = $helperLogin->getAccessToken();

				if (!empty($accessToken))
				{
					$facebookResponse = $facebook->get('/me?fields=id,name,email', $accessToken);
					$facebookUser     = $facebookResponse->getGraphUser();
					CclHelpersCcl::logInUser('facebook', $facebookUser->getId(), $facebookUser->getName(), $facebookUser->getEmail(), $response);
				}
				else
				{
					$response->status = JAuthentication::STATUS_FAILURE;
				}
			}
			catch (\Facebook\Exceptions\FacebookResponseException $e)
			{
				$response->status = JAuthentication::STATUS_FAILURE;
			}
		}
	}

	/**
	 * Get button rendering information
	 *
	 * @return string
	 *
	 * @since 1.0
	 */
	public function onAuthenticationMethodRender()
	{
		$facebook    = $this->getFacebookObject();
		$helperLogin = $facebook->getRedirectLoginHelper();

		return
			(object) array(
				'url'    => $helperLogin->getLoginUrl(CclHelpersCcl::getLoginUrl('facebook'), array('email')),
				'plugin' => 'facebook'
			);
	}

	/**
	 *
	 *
	 * @return \Facebook\Facebook
	 *
	 * @since version
	 */
	protected function getFacebookObject()
	{
		$facebook = new \Facebook\Facebook(
			array(
				'app_id'                  => $this->params->get('app_id'),
				'app_secret'              => $this->params->get('app_secret'),
				'default_graph_version'   => 'v2.8',
				'persistent_data_handler' => 'session',
				'default_access_token'    => $this->params->get('app_id') . '|' . $this->params->get('app_secret')
			)
		);

		return $facebook;
	}

}