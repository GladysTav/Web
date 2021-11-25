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
defined('_JEXEC') or die;

use Joomla\Registry\Registry;

/**
 * Sellacious model.
 *
 * @since   1.2.0
 */
class SellaciousModelEmailTemplate extends SellaciousModelAdmin
{
	/**
	 * Method to auto-populate the model state.
	 *
	 * @return  void
	 *
	 * @since   2.0.0
	 */
	protected function populateState()
	{
		parent::populateState();

		$this->state->set('template.context', $this->app->input->get('context'));
	}

	/**
	 * Method to test whether a record can be deleted.
	 *
	 * @param   object  $record  A record object.
	 *
	 * @return  boolean  True if allowed to delete the record. Defaults to the permission for the component.
	 *
	 * @since   12.2
	 */
	protected function canDelete($record)
	{
		return $this->helper->access->check('emailtemplate.delete');
	}

	/**
	 * Method to test whether a record can be deleted.
	 *
	 * @param   object  $record  A record object.
	 *
	 * @return  boolean  True if allowed to change the state of the record. Defaults to the permission for the component.
	 *
	 * @since   12.2
	 */
	protected function canEditState($record)
	{
		return $this->helper->access->check('emailtemplate.edit.state');
	}

	/**
	 * Method to get the data that should be injected in the form.
	 *
	 * @return  mixed  The data for the form.
	 *
	 * @throws  Exception
	 *
	 * @since   1.6
	 */
	protected function loadFormData()
	{
		// Check the session for previously entered form data.
		$data = $this->app->getUserStateFromRequest($this->option . '.edit.' . $this->name . '.data', 'jform', array(), 'array');

		if (empty($data))
		{
			$data = $this->getItem();
		}

		$this->helper->core->loadPlugins();

		$this->preprocessData('com_sellacious.' . $this->name, $data);

		return $data;
	}

	/**
	 * Method to preprocess the form.
	 *
	 * @param   JForm   $form   A JForm object.
	 * @param   mixed   $data   The data expected for the form.
	 * @param   string  $group  The name of the plugin group to import (defaults to "content").
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
		if (is_object($data) ? !empty($data->id) : !empty($data['id']))
		{
			$form->setFieldAttribute('context', 'type', 'hidden');
		}

		$form->loadFile('communication/email');

		parent::preprocessForm($form, $data, $group);
	}

	/**
	 * Method to validate the form data.
	 *
	 * @param   JForm  $form  The form to validate against.
	 * @param   array  $data  The data to validate.
	 * @param   string $group The name of the field group to validate.
	 *
	 * @return  mixed  Array of filtered data if valid, false otherwise.
	 *
	 * @see     JFormRule
	 * @see     JFilterInput
	 *
	 * @since   12.2
	 */
	public function validate($form, $data, $group = null)
	{
		try
		{
			$registry = new Registry($data);
			$actual   = $registry->get('handlers.email.send_actual_recipient');

			if (!$actual)
			{
				$r   = $registry->get('handlers.email.recipients');
				$cc  = $registry->get('handlers.email.cc');
				$bcc = $registry->get('handlers.email.bcc');

				if (trim($r) === '' && trim($cc) === '' && trim($bcc) === '')
				{
					throw new Exception(JText::_('COM_SELLACIOUS_EMAILTEMPLATE_ACTUAL_RECIPIENTS_OR_ALTERNATE_REQUIRED_WARNING'));
				}
			}

			$dispatcher = JEventDispatcher::getInstance();
			$dispatcher->trigger('onFormDataValidation', array($form, &$data));
		}
		catch (Exception $e)
		{
			$this->setError($e->getMessage());
		}

		return parent::validate($form, $data, $group);
	}

	/**
	 * Method to save the form data.
	 *
	 * @param   array  $data  The form data.
	 *
	 * @return  boolean  True on success.
	 *
	 * @throws  Exception
	 *
	 * @since   1.2.0
	 */
	public function save($data)
	{
		// Initialise variables;
		$dispatcher = JEventDispatcher::getInstance();

		$table = $this->getTable();
		$pk    = (!empty($data['id'])) ? $data['id'] : (int)$this->getState($this->getName() . '.id');
		$isNew = true;

		// Include the content plugins for the on save events.
		JPluginHelper::importPlugin('sellacious');

		// Load the row if saving an existing category.
		if ($pk > 0)
		{
			$table->load($pk);
			$isNew = false;
		}

		// Alter the title for save as copy
		if ($this->app->input->get('task') == 'save2copy')
		{
			list($title, $alias) = $this->generateNewTitle(null, $data['alias'], $data['title']);
			$data['title'] = $title;
			$data['alias'] = $alias;
		}

		// Bind the data.
		if (!$table->bind($data))
		{
			$this->setError($table->getError());

			return false;
		}

		// Check the data.
		if (!$table->check())
		{
			$this->setError($table->getError());

			return false;
		}

		// Trigger the onBeforeSave event.
		$result = $dispatcher->trigger($this->event_before_save, array($this->option . '.' . $this->name, &$table, $isNew));
		if (in_array(false, $result, true))
		{
			$this->setError($table->getError());

			return false;
		}

		// Store the data.
		if (!$table->store())
		{
			$this->setError($table->getError());

			return false;
		}

		// Trigger the onAfterSave event.
		$dispatcher->trigger($this->event_after_save, array($this->option . '.' . $this->name, &$table, $isNew));

		$this->setState($this->getName() . '.id', $table->get('id'));

		return true;
	}


}
