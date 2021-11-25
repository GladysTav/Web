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
class SellaciousModelCoupon extends SellaciousModelAdmin
{
	/**
	 * Method to test whether a record can be deleted.
	 *
	 * @param   object $record A record object.
	 *
	 * @return  boolean  True if allowed to delete the record. Defaults to the permission for the component.
	 *
	 * @since   12.2
	 */
	protected function canDelete($record)
	{
		return $this->helper->access->check('coupon.delete') ||
			($this->helper->access->check('coupon.delete.own') && $record->seller_uid == JFactory::getUser()->id);
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
		return $this->helper->access->check('coupon.edit.state');
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
	 * @since   1.6
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

			$existing_translations = $this->helper->translation->getTranslations($pk, 'sellacious_coupon');

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
			$table->bind($data);
			$table->check();

			// Trigger the onBeforeSave event.
			$dispatcher->trigger($this->event_before_save, array($this->option . '.' . $this->name, &$table, $isNew));

			// Store the data.
			$table->store();

			// Save Translations if any
			$this->helper->translation->saveTranslations($translations, $table->get('id'), 'sellacious_coupon');

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
	 * Method to get the data that should be injected in the form.
	 *
	 * @param   string  $context  The context identifier.
	 * @param   mixed   &$data    The data to be processed. It gets altered directly.
	 * @param   string  $group    The name of the plugin group to import (defaults to "content").
	 *
	 * @return  void
	 *
	 * @since   1.6
	 */
	protected function preprocessData($context, &$data, $group = 'content')
	{
		$data     = is_array($data) ? ArrayHelper::toObject($data, 'stdClass', false) : $data;
		$me       = JFactory::getUser();
		$isSeller = $this->helper->seller->is();

		// No default selection for Admin, but a must for sellers
		if ($this->helper->access->check('coupon.edit'))
		{
			if (!isset($data->seller_uid))
			{
				$data->seller_uid = $this->app->getUserState('com_sellacious.edit.coupon.seller_uid', null);
			}
		}
		else
		{
			$data->seller_uid = $isSeller ? $me->id : 0;
		}

		if ($data->id)
		{
			$data->translations = $this->helper->translation->getTranslations($data->id, 'sellacious_coupon');
			$this->helper->translation->translateRecord($data, 'sellacious_coupon');
		}

		$this->app->setUserState('com_sellacious.edit.coupon.seller_uid', $data->seller_uid);

		parent::preprocessData($context, $data, $group);
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
	 * @see     JFormField
	 * @since   12.2
	 * @throws  Exception  If there is an error in the form event.
	 */
	protected function preprocessForm(JForm $form, $data, $group = 'sellacious')
	{
		if (!$this->helper->config->get('multi_seller', 0))
		{
			$form->setFieldAttribute('seller_uid', 'type', 'hidden');
			$form->setFieldAttribute('seller_uid', 'hidden', 'true');
			$form->setFieldAttribute('seller_uid', 'readonly', 'true');
		}

		// If allowed to change all then only provide sellers list.
		if (!$this->helper->access->check('coupon.edit'))
		{
			$form->setFieldAttribute('seller_uid', 'type', 'hidden');
			$form->setFieldAttribute('seller_uid', 'hidden', 'true');
			$form->setFieldAttribute('seller_uid', 'readonly', 'true');

			$form->setFieldAttribute('state', 'type', 'hidden');
			$form->setFieldAttribute('state', 'hidden', 'true');
			$form->setFieldAttribute('state', 'readonly', 'true');
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
						label="COM_SELLACIOUS_COUPON_FIELD_TITLE_LABEL"
						description="COM_SELLACIOUS_COUPON_FIELD_TITLE_DESC"
						class="inputbox"
						size="40"
					/>
					<field
						name="description"
						type="editor"
						label="COM_SELLACIOUS_COUPON_FIELD_DESCRIPTION_LABEL"
						description="COM_SELLACIOUS_COUPON_FIELD_DESCRIPTION_DESC"
						class="textarea w75p"
						filter="raw"
						height="250"
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
