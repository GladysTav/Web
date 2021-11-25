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
 * Sellacious Shop rule model
 */
class SellaciousModelShoprule extends SellaciousModelAdmin
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
		return $this->helper->access->check('shoprule.delete') ||
			($this->helper->access->check('shoprule.delete.own') && $record->seller_uid == JFactory::getUser()->id);
	}

	/**
	 * Method to test whether a record can be edited.
	 *
	 * @param   object $record A record object.
	 *
	 * @return  boolean  True if allowed to change the state of the record. Defaults to the permission for the component.
	 *
	 * @since   12.2
	 */
	protected function canEditState($record)
	{
		return $this->helper->access->check('shoprule.edit.state');
	}

	/**
	 * Method to save the form data.
	 *
	 * @param   array $data  The form data.
	 *
	 * @return  boolean  True on success.
	 * @since   1.6
	 */
	public function save($data)
	{
		$me         = JFactory::getUser();
		$registry   = new Registry($data);
		$dispatcher = JEventDispatcher::getInstance();
		$import     = $this->helper->config->get('use_shoprule_import');

		/** @var SellaciousTableShopRule $table */
		$table = $this->getTable();
		$pk    = (!empty($data['id'])) ? $data['id'] : (int)$this->getState($this->getName() . '.id');
		$isNew = true;

		$slabs = !empty($data['slabs']) ? $data['slabs'] : array();

		$translations = ArrayHelper::getValue($data, 'translations', array());

		// Include current language translation, if not already included
		$lang = JFactory::getLanguage()->getTag();

		if (!isset($translations[$lang]))
		{
			$translations[$lang] = array('title' => $data['title']);
		}

		// Include the plugins for the on save events.
		JPluginHelper::importPlugin('sellacious');

		// Load the row if saving an existing category.
		if ($pk > 0)
		{
			$table->load($pk);

			$isNew = false;

			$existing_translations = $this->helper->translation->getTranslations($pk, 'sellacious_shoprule');

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
		}

		// Bind the data.
		$table->bind($data);

		// Check the data.
		$table->check();

		// Trigger the onBeforeSave event.
		$context = $this->option . '.' . $this->name;
		$dispatcher->trigger($this->event_before_save, array($context, &$table, $isNew));

		// Store the data.
		$table->store();

		$ruleId   = $table->get('id');
		$method   = $table->get('method_name');
		$owned_by = $table->get('created_by');
		$isOwner  = $owned_by > 0 && $owned_by == $me->get('id');

		if ($this->helper->access->check('shoprule.edit')
			|| (($isOwner) && $this->helper->access->check('shoprule.edit.own')))
		{
			// Save Discount Class Groups
			$discountGroups = $registry->get('discount_class_groups', '');
			$this->helper->shopRuleClass->setShopRule($ruleId, explode(',', $discountGroups), 'discount');

			// Save Tax Class Groups
			$taxGroups = $registry->get('tax_class_groups', '');
			$this->helper->shopRuleClass->setShopRule($ruleId, explode(',', $taxGroups), 'tax');
		}

		if ($method == 'slabs.quantity')
		{
			$slabs = ArrayHelper::getValue($slabs, 'quantity_slabs', '[]');
		}
		elseif ($method == 'slabs.price')
		{
			$slabs = ArrayHelper::getValue($slabs, 'price_slabs', '[]');
		}
		else
		{
			$slabs = '[]';
		}

		$slabs     = json_decode($slabs);
		$saveSlabs = true;

		if ($import)
		{
			// If Import is allowed and JSON slabs file is not available, do not replace Slabs
			if (isset($slabs->data))
			{
				// Get Slabs from JSON file if available
				$slabs = $this->helper->filestorage->extractFromFile($slabs->data);
			}
			else
			{
				$saveSlabs = false;
			}
		}

		if ($saveSlabs)
		{
			$this->helper->shopRule->clearSlabs($ruleId);

			foreach ($slabs as $slab)
			{
				$this->helper->shopRule->addSlab($ruleId, (object) $slab);
			}
		}

		// Save Translations if any
		$this->helper->translation->saveTranslations($translations, $table->get('id'), 'sellacious_shoprule');

		// Trigger the onAfterSave event.
		$dispatcher->trigger($this->event_after_save, array($context, &$table, $isNew, $data));

		// Rebuild the path for the shoprule:
		$table->rebuildPath($table->get('id'));

		// Rebuild the paths of the shop-rule's children
		$table->rebuild($table->get('id'), $table->lft, $table->level, $table->get('path'));

		$this->setState($this->getName() . '.id', $table->get('id'));

		// If import is allowed, then delete the temporary JSON slabs file
		if ($saveSlabs && $import)
		{
			$jsonFile = 'shoprule_slabs_r' . $ruleId . 'u' . $me->id . '.txt';
			$this->helper->filestorage->deleteFile($jsonFile);
		}

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
		if ($wasArray = is_array($data))
		{
			$data = ArrayHelper::toObject($data);
		}

		$me       = JFactory::getUser();
		$isSeller = $this->helper->seller->is();

		// No default selection for Admin, but a must for sellers
		if ($this->helper->access->check('shoprule.edit'))
		{
			if (!isset($data->seller_uid))
			{
				$data->seller_uid = $this->app->getUserState('com_sellacious.edit.shoprule.seller_uid', null);
			}
		}
		else
		{
			$data->seller_uid = $isSeller ? $me->id : 0;
		}

		$method = $data->method_name;

		if ($method == 'slabs.quantity')
		{
			$key = 'quantity_slabs';
		}
		elseif ($method == 'slabs.price')
		{
			$key = 'price_slabs';
		}
		else
		{
			$key = null;
		}

		if ($key && !$wasArray)
		{
			try
			{
				$data->slabs[$key] = $this->helper->shopRule->getSlabs($data->id);
			}
			catch (Exception $e)
			{
				$data->slabs[$key] = array();
			}
		}

		if ($data->id)
		{
			$classes           = $this->helper->shopRuleClass->getShopRuleClasses($data->id, $data->type);
			$classField        = $data->type . '_class_groups';
			$data->$classField = ArrayHelper::getColumn($classes, 'title');

			$data->translations = $this->helper->translation->getTranslations($data->id, 'sellacious_shoprule');
			$this->helper->translation->translateRecord($data, 'sellacious_shoprule');
		}

		$this->app->setUserState('com_sellacious.edit.shoprule.seller_uid', $data->seller_uid);

		if ($wasArray)
		{
			// Temporary workaround to reset data type to original
			$data = ArrayHelper::fromObject($data);
		}

		parent::preprocessData($context, $data, $group);
	}

	/**
	 * Override preprocessForm to load the sellacious plugin group instead of content.
	 *
	 * @param  JForm $form   A form object.
	 * @param  mixed $data   The data expected for the form.
	 * @param string $group  Plugin group to load
	 *
	 * @since   1.6
	 */
	protected function preprocessForm(JForm $form, $data, $group = 'content')
	{
		$this->helper->core->loadPlugins('sellaciousrules');

		$obj = is_array($data) ? ArrayHelper::toObject($data) : $data;

		if ($obj->type != 'discount')
		{
			$form->removeField('filterable');
		}

		if (isset($obj->parent_id) && $obj->parent_id == 0 && $obj->id > 0)
		{
			$form->setFieldAttribute('parent_id', 'type', 'hidden');
			$form->setFieldAttribute('parent_id', 'hidden', 'true');
		}

		if (!empty($obj->id))
		{
			$form->setFieldAttribute('type', 'readonly', 'true');
		}

		if (!$this->helper->config->get('multi_seller', 0))
		{
			$form->setFieldAttribute('seller_uid', 'type', 'hidden');
			$form->setFieldAttribute('seller_uid', 'hidden', 'true');
			$form->setFieldAttribute('seller_uid', 'readonly', 'true');
		}

		// If allowed to change all then only provide sellers list.
		if (!$this->helper->access->check('shoprule.edit'))
		{
			$form->setFieldAttribute('seller_uid', 'type', 'hidden');
			$form->setFieldAttribute('seller_uid', 'hidden', 'true');
			$form->setFieldAttribute('seller_uid', 'readonly', 'true');

			$form->setFieldAttribute('state', 'type', 'hidden');
			$form->setFieldAttribute('state', 'hidden', 'true');
			$form->setFieldAttribute('state', 'readonly', 'true');
		}

		if ($obj->sum_method != 2)
		{
			$form->removeField('apply_rule_on_price_display');
			$form->removeField('apply_rule_on_list_price');
			$form->removeField('apply_on_all_products');
		}

		if ($obj->type && $obj->sum_method == 2)
		{
			if ($obj->type == 'discount')
			{
				$form->removeField('tax_class_groups');
			}
			elseif ($obj->type == 'tax')
			{
				$form->removeField('discount_class_groups');
			}

			if (isset($obj->apply_on_all_products) && $obj->apply_on_all_products == 1)
			{
				$form->removeField('tax_class_groups');
				$form->removeField('discount_class_groups');
			}
		}
		else
		{
			$form->removeField('tax_class_groups');
			$form->removeField('discount_class_groups');
		}

		$methodName = empty($obj->method_name) ? '*' : $obj->method_name;

		switch ($methodName)
		{
			case '*':
				$form->setFieldAttribute('quantity_slabs', 'type', 'hidden', 'slabs');
				$form->setFieldAttribute('price_slabs', 'type', 'hidden', 'slabs');
				break;

			case 'slabs.quantity':
				$form->setFieldAttribute('amount', 'type', 'hidden');
				$form->setFieldAttribute('price_slabs', 'type', 'hidden', 'slabs');
				break;

			case 'slabs.price':
				$form->setFieldAttribute('amount', 'type', 'hidden');
				$form->setFieldAttribute('quantity_slabs', 'type', 'hidden', 'slabs');
				break;

			default:
				$form->setFieldAttribute('amount', 'type', 'hidden');
				$form->setFieldAttribute('quantity_slabs', 'type', 'hidden', 'slabs');
				$form->setFieldAttribute('price_slabs', 'type', 'hidden', 'slabs');
				break;
		}

		$import = $this->helper->config->get('use_shoprule_import');
		$form->setFieldAttribute('quantity_slabs', 'useTable', $import, 'slabs');
		$form->setFieldAttribute('price_slabs', 'useTable', $import, 'slabs');
		
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
						label="COM_SELLACIOUS_SHOPRULE_FIELD_TITLE_LABEL"
						description="COM_SELLACIOUS_SHOPRULE_FIELD_TITLE_DESC"
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
