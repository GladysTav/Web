<?php
/**
 * @version     1.7.4
 * @package     sellacious
 *
 * @copyright   Copyright (C) 2012-2019 Bhartiy Web Technologies. All rights reserved.
 * @license     SPL Sellacious Private License; see http://www.sellacious.com/spl.html
 * @author      Aditya Chakraborty <info@bhartiy.com> - http://www.bhartiy.com
 */
// no direct access.
use Joomla\Registry\Registry;

defined('_JEXEC') or die;

/**
 * Sellacious Templates model.
 *
 */
class SellaciousTemplatesModelTemplate extends SellaciousModelAdmin
{
	/**
	 * Method to test whether a record can be deleted.
	 *
	 * @param   object $record A record object.
	 *
	 * @return  boolean  True if allowed to delete the record. Defaults to the permission for the component.
	 *
	 * @since   1.7.0
	 */
	protected function canDelete($record)
	{
		return $this->helper->access->check('template.delete');
	}

	/**
	 * Method to test whether a record can be deleted.
	 *
	 * @param   object $record A record object.
	 *
	 * @return  boolean  True if allowed to change the state of the record. Defaults to the permission for the component.
	 *
	 * @since   1.7.0
	 */
	protected function canEditState($record)
	{
		return $this->helper->access->check('template.edit.state');
	}

	/**
	 * Method to allow derived classes to preprocess the form.
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
	 * @since   1.7.0
	 */
	protected function preprocessForm(JForm $form, $data, $group = 'sellacious')
	{
		parent::preprocessForm($form, $data, $group);

		$registry = new Registry($data);
		$app      = JFactory::getApplication();

		if ($registry->get('id', 0))
		{
			$preview = $app->getUserState($this->option . '.preview');
			$context = $app->getUserState($this->option . '.preview_context');

			if (empty($preview))
			{
				$app->setUserState($this->option . '.preview', $this->getPreview($data->context, $data->body));
			}

			if (empty($context))
			{
				$app->setUserState($this->option . '.preview_context', $data->context);
			}
		}
	}

	/**
	 * Method to get template preview with short codes replaced
	 *
	 * @param   string  $templateContext  The template context
	 * @param   string  $html             The template html
	 *
	 * @return  string
	 *
	 * @since   1.7.0
	 */
	public function getPreview($templateContext, $html)
	{
		$dispatcher = $this->helper->core->loadPlugins();
		$dispatcher->trigger('onTemplatePreview', array('com_sellacious.viewtemplate', $templateContext, &$html));

		return $html;
	}

	/**
	 * @param   string  $templateContext  The template context
	 *
	 * @return  string
	 *
	 * @since   1.7.0
	 */
	public function getTemplateDefaultPreview($templateContext)
	{
		$table = SellaciousTable::getInstance('TemplateDefault');
		$table->load(array('context' => $templateContext));

		return $table->get('body');
	}

	/**
	 * Method to save the form data.
	 *
	 * @param  array  $data  The form data.
	 *
	 * @return  boolean  True on success.
	 *
	 * @throws  Exception
	 *
	 * @since   1.7.0
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
