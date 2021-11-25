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

/**
 * Class CclController
 *
 * @since  1.6
 */
class CclController extends JControllerLegacy
{
	/**
	 * Method to display a view.
	 *
	 * @param   boolean $cachable  If true, the view output will be cached
	 * @param   mixed   $urlparams An array of safe url parameters and their variable types, for valid values see {@link JFilterInput::clean()}.
	 *
	 * @return   JControllerLegacy This object to support chaining.
	 *
	 * @since    1.5
	 */
	public function display($cachable = false, $urlparams = false)
	{
		$view = JFactory::getApplication()->input->getCmd('view', 'dashboard');
		JFactory::getApplication()->input->set('view', $view);

		parent::display($cachable, $urlparams);

		return $this;
	}

	/**
	 * Import users from Offlajn social login extension
	 *
	 *
	 * @since 1.0
	 */
	public function importFromOfflajn()
	{
		$db    = JFactory::getDbo();
		$query = $db->getQuery(true);

		$query
			->select('*')
			->from('#__offlajn_users');

		$db->setQuery($query);
		$users = $db->loadAssocList();

		foreach ($users as $user)
		{
			$userId = $user['user_id'];
			unset($user['user_id']);

			$socialProviders = array_keys($user);

			foreach ($socialProviders as $socialProvider)
			{
				if (!empty($user[$socialProvider]))
				{
					$ccLoginRecord = (object) array(
						'user_id'           => $userId,
						'social_plugin'     => str_replace('_id', '', $socialProvider),
						'social_identifier' => $user[$socialProvider]
					);

					$db->insertObject('#__ccl_user_details', $ccLoginRecord);
				}
			}
		}

		$app = JFactory::getApplication();
		$app->enqueueMessage(JText::_('COM_CCL_DASHBOARD_USERS_IMPORTED'), 'success');

		$app->redirect(JRoute::_('index.php?option=com_ccl'));
	}

	/**
	 * Enable plugin
	 *
	 * @since 1.1
	 */
	public function enablePlugin()
	{
		$input    = $this->input;
		$app      = JFactory::getApplication();
		$pluginId = $input->getInt('plugin');
		$db       = JFactory::getDbo();
		$query    = $db->getQuery(true);

		$query
			->update('#__extensions')
			->set('enabled = 1')
			->where('extension_id = ' . $db->quote($pluginId));

		$db->setQuery($query);
		$result = $db->execute();

		if ($result)
		{
			$app->enqueueMessage(JText::_('COM_CCL_ENABLE_PLUGIN_SUCCESS_MESSAGE'), 'success');
		}
		else
		{
			$app->enqueueMessage(JText::_('COM_CCL_ENABLE_PLUGIN_ERROR_MESSAGE'), 'error');
		}

		$app->redirect(JRoute::_('index.php?option=com_ccl'));
	}
}
