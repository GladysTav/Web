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

use Joomla\Utilities\ArrayHelper;

/**
 * Sellacious model.
 */
class SellaciousModelStatus extends SellaciousModelAdmin
{
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
		if ($record->is_core)
		{
			$this->setError(JText::_($this->text_prefix . '_CORE_DELETE_DENIED'));

			return false;
		}

		return $this->helper->access->check('status.delete');
	}

	/**
	 * Method to test whether a record can be deleted.
	 *
	 * @param  object  $record  A record object.
	 *
	 * @return  boolean  True if allowed to change the state of the record. Defaults to the permission for the component.
	 *
	 * @since   12.2
	 */
	protected function canEditState($record)
	{
		if ($record->is_core)
		{
			$this->setError(JText::_($this->text_prefix . '_CORE_EDIT_STATE_DENIED'));

			return false;
		}

		return $this->helper->access->check('status.edit.state');
	}

	/**
	 * Method to preprocess the form
	 *
	 * @param   JForm  $form  A form object.
	 * @param   mixed  $data  The data expected for the form.
	 * @param   string $group The name of the plugin group to import (defaults to "content").
	 *
	 * @return  void
	 *
	 * @since   1.6
	 * @throws  Exception if there is an error loading the form.
	 */
	protected function preprocessForm(JForm $form, $data, $group = 'sellacious')
	{
		$obj = is_array($data) ? ArrayHelper::toObject($data) : $data;

		if (!empty($obj->context))
		{
			$form->setFieldAttribute('allow_change_to', 'context', $obj->context);
		}

		if (empty($obj->context) || ($obj->context != 'order.physical' && $obj->context != 'order.electronic'))
		{
			$form->removeField('stock');
		}

		if (!empty($obj->is_core))
		{
			$form->setFieldAttribute('type', 'readonly', 'true');
			$form->setFieldAttribute('state', 'readonly', 'true');
		}

		// Show Translation fields
		$this->setTranslationFields($form);

		parent::preprocessForm($form, $data, $group);
	}

	/**
	 * Method to preprocess the data.
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
		if (is_object($data))
		{
			// Load Translations to form
			if ($data->id)
			{
				$data->translations = $this->helper->translation->getTranslations($data->id, 'sellacious_status');

				$this->helper->translation->translateRecord($data, 'sellacious_status');
			}
		}

		parent::preprocessData($context, $data, $group);
	}

	/**
	 * Set stock handling for the status
	 *
	 * @param  int    $pk    Status Id
	 * @param  string $value New Value for handling - A, R, O, ''
	 *
	 * @return bool
	 * @throws Exception
	 */
	public function setStockHandling($pk, $value)
	{
		$table = $this->getTable();

		$table->load($pk);

		if ($table->get('id'))
		{
			$table->set('stock', $value);

			$table->store();
		}
		else
		{
			throw new Exception($this->text_prefix . '_INVALID_ITEM');
		}

		return true;
	}
	
	/**
	 * Method to save the form data.
	 *
	 * @param   array  The form data.
	 *
	 * @return  boolean  True on success.
	 *
	 * @throws  Exception
	 *
	 * @since   2.0.0
	 */
	public function save($data)
	{
		// Initialise variables
		$dispatcher = JEventDispatcher::getInstance();
		JPluginHelper::importPlugin('sellacious');
		
		$table = $this->getTable();
		$pk    = !empty($data['id']) ? $data['id'] : (int) $this->getState($this->getName() . '.id');
		$isNew = true;

		$translations = ArrayHelper::getValue($data, 'translations', array());

		// Include current language translation, if not already included
		$lang = JFactory::getLanguage()->getTag();

		if (!isset($translations[$lang]))
		{
			$translations[$lang] = array('title' => $data['title']);
		}

		// Load the row if saving an existing category.
		if ($pk > 0)
		{
			$table->load($pk);
			$isNew = false;

			$existing_translations = $this->helper->translation->getTranslations($pk, 'sellacious_status');

			// Main table should always have English translations
			if (isset($existing_translations['en-GB']) && JLanguageMultilang::isEnabled() && $lang != 'en-GB')
			{
				$data = array_merge($data, $existing_translations['en-GB']);
			}
		}
		
		// Alter the title for save as copy
		if ($this->app->input->get('task') == 'save2copy')
		{
			list($title, $alias) = $this->generateNewTitle(null, $data['alias'], $data['title']);
			$data['title'] = $title;
			$data['alias'] = $alias;
		}
		
		try
		{
			if (!isset($data['usergroups']))
			{
				$data['usergroups'] = '';
			}
			
			$table->bind($data);
			$table->check();
			
			// Trigger the onBeforeSave event.
			$dispatcher->trigger($this->event_before_save, array($this->option . '.' . $this->name, &$table, $isNew));
			
			// Store the data.
			$table->store();

			// Save Translations if any
			$this->helper->translation->saveTranslations($translations, $this->getState($this->getName() . '.id'), 'sellacious_status');

			// Trigger the onAfterSave event.
			$dispatcher->trigger($this->event_after_save, array($this->option . '.' . $this->name, &$table, $isNew));
		}
		catch (Exception $e)
		{
			JLog::add($e->getMessage(), JLog::WARNING, 'jerror');
			
			return false;
		}
		
		$this->setState($this->getName() . '.id', $table->get('id'));
		
		return true;
	}
	
	/**
	 * Method to translations fields to form
	 *
	 * @param   \JForm  $form  The form
	 *
	 * @return  void
	 * @throws  Exception
	 *
	 * @since   2.0.0
	 */
	public function setTranslationFields(&$form)
	{
		$defLanguage = JFactory::getLanguage();
		$tag         = $defLanguage->getTag();
		$languages   = JLanguageHelper::getContentLanguages();
		
		$languages = array_filter($languages, function ($item) use ($tag){
			return ($item->lang_code != $tag);
		});
		
		if (!empty($languages))
		{
			// Language Tabs
			$spacer = htmlentities('<div class="container">');
			
			foreach ($languages as $language)
			{
				$spacer .= htmlentities('<a class="btn btn-primary margin-right-5" href="#jform_translations_' . str_replace('-', '_', $language->lang_code) . '_language_title-lbl">' . '<img src="' . JUri::root() . 'media/mod_languages/images/'. $language->image . '.gif" alt="'. $language->image . '"> ') . $language->title . htmlentities('</a>');
			}
			
			$spacer .= htmlentities('</div>');
			
			$spacerElement = new SimpleXMLElement('
				<field type="spacer" name="language_tab" label="' . $spacer . '" />
			');
			
			$form->setField($spacerElement, 'translations', true, 'translations');
			
			// Language Translation fields
			foreach ($languages as $language)
			{
				$spacer = htmlentities('<b>') . $language->title . htmlentities('</b>');
				
				$element = new SimpleXMLElement('
				<fields name="' . $language->lang_code . '">
					<field type="spacer" name="language_title" label="' . $spacer . '" />
					<field
						name="title"
						type="text"
						label="COM_SELLACIOUS_STATUS_FIELD_TITLE_LABEL"
						description="COM_SELLACIOUS_STATUS_FIELD_TITLE_DESC"
						class="inputbox"
						size="40"
					/>
				</fields>');
				
				$form->setField($element, 'translations', true, 'translations');
			}
		}
		else
		{
			$form->removeGroup('translations');
		}
	}
}
