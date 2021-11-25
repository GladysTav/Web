<?php
/**
 * @version     2.0.0
 * @package     sellacious
 *
 * @copyright   Copyright (C) 2012-2020 Bhartiy Web Technologies. All rights reserved.
 * @license     SPL Sellacious Private License; see http://www.sellacious.com/spl.html
 * @author      Izhar Aazmi <info@bhartiy.com> - http://www.bhartiy.com
 */
// no direct access.
use Joomla\Utilities\ArrayHelper;
use Sellacious\User\UserGroupHelper;

defined('_JEXEC') or die;

/**
 * Sellacious model.
 *
 * @since   1.2.0
 */
class SellaciousModelPermissions extends SellaciousModelAdmin
{
	/**
	 * Method to auto-populate the model state.
	 *
	 * @note   Calling getState in this method will result in recursion.
	 *
	 * @param   string  $ordering
	 * @param   string  $direction
	 *
	 * @throws  Exception
	 *
	 * @since   1.2.0
	 */
	protected function populateState($ordering = null, $direction = null)
	{
		$this->app->getUserStateFromRequest('com_sellacious.permissions.return', 'return', '', 'cmd');
		$this->app->getUserStateFromRequest('com_sellacious.edit.permissions.data.user_group', 'catid', '1', 'int');
	}

	/**
	 * Method to save the form data
	 *
	 * @param   array  $data  The form data
	 *
	 * @return  bool
	 *
	 * @throws  Exception
	 *
	 * @since   1.2.0
	 */
	public function save($data)
	{
		return true;
	}

	/**
	 * Method to allow derived classes to preprocess the data.
	 *
	 * @param   string  $context  The context identifier.
	 * @param   mixed   &$data    The data to be processed. It gets altered directly.
	 * @param   string  $group    The name of the plugin group to import (defaults to "content").
	 *
	 * @return  void
	 *
	 * @since   2.0.0
	 */
	protected function preprocessData($context, &$data, $group = 'content')
	{
		if (is_array($data))
		{
			$data['set'] = null;
			$data['src'] = null;
		}
		else
		{
			$data->set = null;
			$data->src = null;
		}

		parent::preprocessData($context, $data, $group);
	}

	/**
	 * Method to allow derived classes to preprocess the form.
	 *
	 * @param   JForm  $form  A JForm object.
	 * @param   mixed  $data  The data expected for the form.
	 * @param   string $group The name of the plugin group to import (defaults to "content").
	 *
	 * @return  void
	 *
	 * @throws  Exception if there is an error in the form event.
	 *
	 * @see     JFormField
	 *
	 * @since   2.0.0
	 */
	protected function preprocessForm(JForm $form, $data, $group = 'sellacious')
	{
		$subject = is_array($data) ? ArrayHelper::toObject($data) : $data;

		if ($catid = $this->getState('permissions.catid'))
		{
			$subject->user_group = $catid;
		}

		$form->setFieldAttribute('rules', 'section', isset($subject->component) ? 'component' : '');
		$form->setFieldAttribute('rules', 'component', isset($subject->component) ? $subject->component : '');
		$form->setFieldAttribute('rules', 'group_id', isset($subject->user_group) ? $subject->user_group : 1);
		$form->setFieldAttribute('src', 'exclude', isset($subject->user_group) ? $subject->user_group : 1);

		parent::preprocessForm($form, $data, $group);
	}

	/**
	 * Method to save the form data.
	 *
	 * @param   int  $pk  The id of the primary key.
	 *
	 * @return  \stdClass
	 *
	 * @since   1.2.0
	 */
	public function getItem($pk = null)
	{
		return new stdClass;
	}
}
