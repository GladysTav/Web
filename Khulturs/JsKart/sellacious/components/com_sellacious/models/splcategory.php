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
use Joomla\Utilities\ArrayHelper;

/**
 * Sellacious model.
 *
 * @since   1.1.0
 */
class SellaciousModelSplCategory extends SellaciousModelAdmin
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
		return $this->helper->access->check('splcategory.delete');
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
		return $this->helper->access->check('splcategory.edit.state');
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
	 * @since   1.6
	 */
	public function save($data)
	{
		// Initialise variables
		$dispatcher = JEventDispatcher::getInstance();

		/** @var SellaciousTableSplCategory $table */
		$table = $this->getTable();
		$pk    = (!empty($data['id'])) ? $data['id'] : (int)$this->getState($this->getName() . '.id');
		$isNew = true;

		// Include the content plugins for the on save events.
		JPluginHelper::importPlugin('sellacious');

		$translations = ArrayHelper::getValue($data, 'translations', array());

		// Include current language translation, if not already included
		$lang = JFactory::getLanguage()->getTag();

		if (!isset($translations[$lang]))
		{
			$translations[$lang] = array('title' => $data['title'], 'description' => $data['description']);
		}

		// Load the row if saving an existing splcategory.
		if ($pk > 0)
		{
			$table->load($pk);
			$isNew = false;

			$existing_translations = $this->helper->translation->getTranslations($pk, 'sellacious_splcategory');

			// Main table should always have English translations
			if (isset($existing_translations['en-GB']) && JLanguageMultilang::isEnabled() && $lang != 'en-GB')
			{
				$data = array_merge($data, $existing_translations['en-GB']);
			}
		}

		// Set the new parent id if parent id not matched OR while New/Save as Copy .
		if ($table->parent_id != $data['parent_id'] || $data['id'] == 0)
		{
			$table->setLocation($data['parent_id'], 'last-child');
		}

		// Alter the title for save as copy
		if ($this->app->input->get('task') == 'save2copy')
		{
			list($title, $alias) = $this->generateNewTitle($data['parent_id'], $data['alias'], $data['title']);
			$data['title'] = $title;
			$data['alias'] = $alias;

			// User might attempt to clone core items
			$data['is_core'] = 0;
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

		// Process images to remove and add
		try
		{
			$_control    = 'jform.images';
			$_tableName  = 'splcategories';
			$_context    = 'images';
			$_recordId   = $table->id;
			$_extensions = array('jpg', 'png', 'jpeg', 'gif');
			$_options    = ArrayHelper::getValue($data, 'images', array(), 'array');

			$this->helper->media->handleUploader($_control, $_tableName, $_context, $_recordId, $_extensions, $_options);
		}
		catch (Exception $e)
		{
			$this->app->enqueueMessage($e->getMessage(), 'warning');
		}

		try
		{
			$registry    = new Registry($data);
			$_control    = 'jform.params.badge.icon';
			$_tableName  = 'splcategories';
			$_context    = 'badge';
			$_recordId   = $table->id;
			$_extensions = array('jpg', 'png', 'jpeg', 'gif');
			$_options    = $registry->get('params.badge.icon', array());

			$this->helper->media->handleUploader($_control, $_tableName, $_context, $_recordId, $_extensions, $_options);
		}
		catch (Exception $e)
		{
			$this->app->enqueueMessage($e->getMessage(), 'warning');
		}

		// Save Translations if any
		$this->helper->translation->saveTranslations($translations, $table->get('id'), 'sellacious_splcategory');

		// Trigger the onAfterSave event.
		$dispatcher->trigger($this->event_after_save, array($this->option . '.' . $this->name, &$table, $isNew));

		// Rebuild the path for the splcategory:
		if (!$table->rebuildPath($table->get('id')))
		{
			$this->setError($table->getError());

			return false;
		}

		// Rebuild the paths of the splcategory's children:
		if (!$table->rebuild($table->get('id'), $table->lft, $table->level, $table->get('path')))
		{
			$this->setError($table->getError());

			return false;
		}

		$this->setState($this->getName() . '.id', $table->get('id'));

		return true;
	}

	/**
	 * Method to allow derived classes to preprocess the data.
	 *
	 * @param   string  $context The context identifier.
	 * @param   mixed   &$data   The data to be processed. It gets altered directly.
	 * @param   string  $group   The name of the plugin group to import (defaults to "content").
	 *
	 * @return  void
	 *
	 * @since   3.1
	 */
	protected function preprocessData($context, &$data, $group = 'content')
	{
		if (is_object($data))
		{
			if (!empty($data->params) && !isset($data->params['styles']))
			{
				$data->params = array('styles' => $data->params);
			}

			// Load Translations to form
			if ($data->id)
			{
				$data->translations = $this->helper->translation->getTranslations($data->id, 'sellacious_splcategory');

				$this->helper->translation->translateRecord($data, 'sellacious_splcategory');
			}
		}

		parent::preprocessData($context, $data, $group);
	}

	/**
	 * Override preprocessForm to load the sellacious plugin group instead of content.
	 *
	 * @param   JForm   $form   A form object.
	 * @param   mixed   $data   The data expected for the form.
	 * @param   string  $group  Load plugin group
	 *
	 * @return  void
	 *
	 * @throws  Exception if there is an error in the form event.
	 *
	 * @since   1.6
	 */
	protected function preprocessForm(JForm $form, $data, $group = 'sellacious')
	{
		$obj = is_array($data) ? ArrayHelper::toObject($data) : $data;

		// prevent root item's parent change
		if (isset($obj->parent_id) && $obj->parent_id == 0 && $obj->id > 0)
		{
			$form->setFieldAttribute('parent_id', 'type', 'hidden');
			$form->setFieldAttribute('parent_id', 'hidden', 'true');
		}

		if (!empty($obj->id))
		{
			$form->setFieldAttribute('images', 'recordId', $obj->id);
			$form->setFieldAttribute('icon', 'recordId', $obj->id, 'params.badge');
		}

		// Show Translation fields
		$this->setTranslationFields($form);

		parent::preprocessForm($form, $data, $group);
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

		$languages = array_filter($languages, function ($item) use ($tag) {
			return ($item->lang_code != $tag);
		});

		if (!empty($languages))
		{
			// Language Tabs
			$spacer = htmlentities('<div class="container">');

			foreach ($languages as $language)
			{
				$spacer .= htmlentities('<a class="btn btn-primary margin-right-5" href="#jform_translations_' . str_replace('-', '_', $language->lang_code) . '_language_title-lbl">' . '<img src="' . JUri::root() . 'media/mod_languages/images/' . $language->image . '.gif" alt="' . $language->image . '"> ') . $language->title . htmlentities('</a>');
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
						label="COM_SELLACIOUS_SPLCATEGORY_FIELD_TITLE_LABEL"
						description="COM_SELLACIOUS_SPLCATEGORY_FIELD_TITLE_DESC"
						class="inputbox"
						size="40"
					/>
					<field
						name="description"
						type="editor"
						label="COM_SELLACIOUS_SPLCATEGORY_FIELD_DESCRIPTION_LABEL"
						description="COM_SELLACIOUS_SPLCATEGORY_FIELD_DESCRIPTION_DESC"
						width="580"
						height="200"
						filter="raw"
						class="inputbox"
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
