<?php

/**
 * @version    CVS: 1.0.0
 * @package    Com_Ccl
 * @author     Component Creator <info@component-creator.com>
 * @copyright  2016 Component Creator
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
// No direct access
defined('_JEXEC') or die;

jimport('joomla.application.component.controller');

/**
 * Class CclController
 *
 * @since  1.6
 */
class CclController extends JControllerLegacy
{
	/**
	 *
	 *
	 * @since 1.0
	 */
	public function login()
	{
		$app = JFactory::getApplication();
		$url = null;
		$app->setUserState('auth', $app->input->getCmd('auth'));

		// Adding whatever username
		if ($app->login(array('username' => 'ccl', 'password' => JUserHelper::genRandomPassword())))
		{
			$url = JRoute::_('index.php');
		}
		else
		{
			if ($app->getUserState('need_password'))
			{
				$url = JRoute::_(
					sprintf(
						'index.php?option=com_ccl&view=password&data=%s',
						base64_encode(
							json_encode(
								array(
									'username' => $app->getUserState('need_password_username'),
									'auth'  => $app->input->getCmd('auth')
								)
							)
						)
					),
					false
				);
			}
		}

		$app->redirect($url);
	}
}
