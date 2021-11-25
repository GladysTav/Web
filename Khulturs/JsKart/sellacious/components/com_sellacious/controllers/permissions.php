<?php
/**
 * @version     2.0.0
 * @package     sellacious
 *
 * @copyright   Copyright (C) 2012-2020 Bhartiy Web Technologies. All rights reserved.
 * @license     SPL Sellacious Private License; see http://www.sellacious.com/spl.html
 * @author      Izhar Aazmi <info@bhartiy.com> - http://www.bhartiy.com
 */
// no direct access
use Sellacious\Access\Access;
use Sellacious\Access\AccessHelper;

defined('_JEXEC') or die;

/**
 * Permissions controller class.
 *
 * @since   1.2.0
 */
class SellaciousControllerPermissions extends SellaciousControllerForm
{
	/**
	 * @var   string  The name of the list view related to this
	 *
	 * @since  1.6
	 */
	protected $view_list = 'permissions';

	/**
	 * @var   string  The prefix to use with controller messages
	 *
	 * @since  1.6
	 */
	protected $text_prefix = 'COM_SELLACIOUS_PERMISSIONS';

	public function __construct($config = array())
	{
		parent::__construct($config);

		$this->registerTask('clearAll', 'save');
		$this->registerTask('allowAll', 'save');
		$this->registerTask('denyAll', 'save');
		$this->registerTask('reset', 'save');
	}

	/**
	 * Common function to simply update the form data and update session for it.
	 *
	 * @return  bool
	 *
	 * @since   1.0.0
	 */
	public function setGroup()
	{
		// Check for request forgeries.
		JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));

		$post = $this->input->get('jform', array(), 'array');

		unset($post['rules']);

		$this->app->setUserState('com_sellacious.edit.permissions.data', $post);
		$this->setRedirect(JRoute::_('index.php?option=com_sellacious&view=permissions', false));

		return true;
	}

	/**
	 * Method to check if you can save a new or existing record.
	 *
	 * Extended classes can override this if necessary.
	 *
	 * @param   array  $data An array of input data.
	 * @param   string $key  The name of the key for the primary key.
	 *
	 * @return  boolean
	 *
	 * @since   12.2
	 */
	protected function allowSave($data, $key = 'id')
	{
		return AccessHelper::allow('permissions.edit');
	}

	/**
	 * Method to save a record.
	 *
	 * @param   string  $key     The name of the primary key of the URL variable.
	 * @param   string  $urlVar  The name of the URL variable if different from the primary key (sometimes required to avoid router collisions).
	 *
	 * @return  bool  True if successful, false otherwise.
	 *
	 * @since   12.2
	 */
	public function save($key = null, $urlVar = null)
	{
		JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));

		/** @var   SellaciousModelPermissions  $model */
		$model = $this->getModel();
		$data  = $this->input->post->get('jform', array(), 'array');

		$this->setRedirect(JRoute::_('index.php?option=com_sellacious&view=permissions', false));

		if (!$this->allowSave($data, $key))
		{
			$this->setMessage(JText::_('JLIB_APPLICATION_ERROR_SAVE_NOT_PERMITTED'), 'error');

			return false;
		}

		// Validate the posted data.
		// Sometimes the form needs some posted data, such as for plugins and modules.
		$form = $model->getForm($data, false);

		if (!$form)
		{
			$this->app->enqueueMessage($model->getError(), 'error');

			return false;
		}

		// Test whether the data is valid.
		$validData = $model->validate($form, $data);

		// Check for validation errors.
		if ($validData === false)
		{
			// Get the validation messages.
			$errors = $model->getErrors();

			// Push up to three validation messages out to the user.
			foreach ($errors as $i => $error)
			{
				$this->app->enqueueMessage($error instanceof Exception ? $error->getMessage() : $error, 'warning');

				if ($i >= 3)
				{
					break;
				}
			}

			return false;
		}

		try
		{
			$task      = strtolower($this->getTask());
			$component = $data['component'];
			$groupId   = $data['user_group'];

			switch ($task)
			{
				case 'clearall':
					AccessHelper::setAccess($groupId, null, array($component));
					break;

				case 'allowall':
					AccessHelper::setAccess($groupId, 1, array($component));
					break;

				case 'denyall':
					AccessHelper::setAccess($groupId, 0, array($component));
					break;

				case 'reset':
					AccessHelper::setDefaultAccess($groupId, array($component));
					break;

				default:
					if ($data['set'] == 2)
					{
						AccessHelper::copyAccess($groupId, $data['src'], array($component));
					}
					else
					{
						$rules = Access::getAssetOwnRules($component);
						$key   = $component ?: 'root';

						$rules->replace($data['rules'][$key]);

						AccessHelper::saveRules($rules, $component);
					}
			}

			$this->setMessage(JText::_($this->text_prefix . '_SAVE_SUCCESS'));

			return true;
		}
		catch (Exception $e)
		{
			$this->setMessage(JText::sprintf('JLIB_APPLICATION_ERROR_SAVE_FAILED', $e->getMessage()), 'error');

			return false;
		}
	}
}
