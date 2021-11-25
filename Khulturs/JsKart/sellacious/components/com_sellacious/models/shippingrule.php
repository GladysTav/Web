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
use Joomla\String\StringHelper;
use Joomla\Utilities\ArrayHelper;

/**
 * Sellacious Shop rule model
 *
 * @since   1.2.0
 */
class SellaciousModelShippingRule extends SellaciousModelAdmin
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
		$me      = JFactory::getUser();
		$allowed = $this->helper->access->check('shippingrule.delete') ||
			($me->id == $record->owned_by && $this->helper->access->check('shippingrule.delete.own'));

		return $allowed;
	}

	/**
	 * Method to test whether a record can be deleted.
	 *
	 * @param   object  $record  A record object.
	 *
	 * @return  boolean  True if allowed to change the state of the record. Defaults to the permission for the component.
	 *
	 * @since  12.2
	 */
	protected function canEditState($record)
	{
		return $this->helper->access->check('shippingrule.edit.state');
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
	 * @since   3.1
	 */
	protected function preprocessData($context, &$data, $group = 'content')
	{
		if (is_object($data))
		{
			$method = $data->get('method_name');

			if ($method == 'slabs.weight')
			{
				$key = 'weight_slabs';
			}
			elseif ($method == 'slabs.quantity')
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

			if ($key)
			{
				try
				{
					$data->slabs[$key] = $this->helper->shippingRule->getSlabs($data->id);
				}
				catch (Exception $e)
				{
					$data->slabs[$key] = array();
				}
			}

			// Load Translations to form
			if ($data->id)
			{
				$data->translations = $this->helper->translation->getTranslations($data->id, 'sellacious_shippingrule');

				$this->helper->translation->translateRecord($data, 'sellacious_shippingrule');
			}

			$itemisedShip  = $this->helper->config->get('itemised_shipping', SellaciousHelperShipping::SHIPPING_SELECTION_PRODUCT);
			$selectProduct = $this->helper->config->get('product_select_shipping');

			if ($itemisedShip == SellaciousHelperShipping::SHIPPING_SELECTION_SELLER && !$selectProduct)
			{
				$data->apply_on_all_products = 1;
			}

			$params       = new Registry($data->params);
			$globalFields = $this->helper->field->getGlobalFields('shippingmethod');
			$params->set('global_form_fields', $globalFields);

			$data->params = $params->toArray();
		}

		parent::preprocessData($context, $data, $group);
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
		$me         = JFactory::getUser();
		$dispatcher = JEventDispatcher::getInstance();
		$import     = $this->helper->config->get('use_shippingrule_import');

		/** @var SellaciousTableShippingRule $table */
		$table = $this->getTable();
		$pk    = !empty($data['id']) ? $data['id'] : (int) $this->getState($this->getName() . '.id');
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

			$existing_translations = $this->helper->translation->getTranslations($pk, 'sellacious_shippingrule');

			// Main table should always have English translations
			if (isset($existing_translations['en-GB']) && JLanguageMultilang::isEnabled() && $lang != 'en-GB')
			{
				$data = array_merge($data, $existing_translations['en-GB']);
			}
		}

		// Alter the title for save as copy
		if ($this->app->input->get('task') == 'save2copy')
		{
			list($title)   = $this->generateNewTitle(null, null, $data['title']);
			$data['title'] = $title;
		}

		$editOnlyOwn = !$this->helper->access->check('shippingrule.edit') && $this->helper->access->check('shippingrule.edit.own');

		if ($editOnlyOwn)
		{
			$data['owned_by'] = JFactory::getUser()->id;
		}

		if (!empty($data['owned_by']))
		{
			$data['created_by'] = $data['owned_by'];
		}

		$table->bind($data);
		$table->check();

		// Trigger the onBeforeSave event.
		$context = $this->option . '.' . $this->name;
		$dispatcher->trigger($this->event_before_save, array($context, &$table, $isNew));

		// Store the data.
		$table->store();

		$ruleId = $table->get('id');
		$method = $table->get('method_name');

		$this->setState($this->getName() . '.id', $ruleId);

		if ($method == 'slabs.weight')
		{
			$slabs = ArrayHelper::getValue($slabs, 'weight_slabs', '[]');
		}
		elseif ($method == 'slabs.quantity')
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
			$this->helper->shippingRule->clearSlabs($ruleId);

			foreach ($slabs as $slab)
			{
				$this->helper->shippingRule->addSlab($ruleId, (object) $slab);
			}
		}

		// Save Translations if any
		$this->helper->translation->saveTranslations($translations, $table->get('id'), 'sellacious_shippingrule');

		// Trigger the onAfterSave event.
		$dispatcher->trigger($this->event_after_save, array($context, &$table, $isNew, $data));

		$this->setState($this->getName() . '.id', $ruleId);

		// If import is allowed, then delete the temporary JSON slabs file
		if ($saveSlabs && $import)
		{
			$jsonFile = 'shippingrule_slabs_r' . $ruleId . 'u' . $me->id . '.txt';
			$this->helper->filestorage->deleteFile($jsonFile);
		}

		return true;
	}

	/**
	 * Method to change the title & alias.
	 *
	 * @param   integer  $parent_id  The id of the category or parent.
	 * @param   string   $alias      The alias.
	 * @param   string   $title      The title.
	 *
	 * @return  array  Contains the modified title and alias.
	 *
	 * @since   12.2
	 */
	protected function generateNewTitle($parent_id, $alias, $title)
	{
		$table = $this->getTable();

		$keys = array('title' => $title);

		while ($table->load($keys))
		{
			$title = StringHelper::increment($title);

			$keys['title'] = $title;
		}

		return array($title, $alias);
	}

	/**
	 * Override preprocessForm to load the sellacious plugin group instead of content.
	 *
	 * @param   JForm   $form   A form object.
	 * @param   mixed   $data   The data expected for the form.
	 * @param   string  $group  Plugin group to load
	 *
	 * @throws  Exception
	 *
	 * @since   1.6
	 */
	protected function preprocessForm(JForm $form, $data, $group = 'sellacious')
	{
		$obj = is_array($data) ? ArrayHelper::toObject($data) : $data;

		$methods = (array) $this->helper->config->get('seller_shippable_methods', array());

		$form->setFieldAttribute('method_name', 'choices', implode('|', $methods));

		$methodName = empty($obj->method_name) ? '*' : $obj->method_name;

		switch ($methodName)
		{
			case '*':
				$form->setFieldAttribute('packaging_weight', 'type', 'hidden', 'params');
				$form->setFieldAttribute('weight_unit', 'type', 'hidden', 'params');
				$form->setFieldAttribute('weight_slabs', 'type', 'hidden', 'slabs');
				$form->setFieldAttribute('quantity_slabs', 'type', 'hidden', 'slabs');
				$form->setFieldAttribute('price_slabs', 'type', 'hidden', 'slabs');
				break;

			case 'slabs.weight':
				$form->setFieldAttribute('amount', 'type', 'hidden');
				$form->setFieldAttribute('amount_additional', 'type', 'hidden');
				$form->setFieldAttribute('quantity_slabs', 'type', 'hidden', 'slabs');
				$form->setFieldAttribute('price_slabs', 'type', 'hidden', 'slabs');
				break;

			case 'slabs.quantity':
				$form->setFieldAttribute('amount', 'type', 'hidden');
				$form->setFieldAttribute('amount_additional', 'type', 'hidden');
				$form->setFieldAttribute('packaging_weight', 'type', 'hidden', 'params');
				$form->setFieldAttribute('weight_unit', 'type', 'hidden', 'params');
				$form->setFieldAttribute('weight_slabs', 'type', 'hidden', 'slabs');
				$form->setFieldAttribute('price_slabs', 'type', 'hidden', 'slabs');
				break;

			case 'slabs.price':
				$form->setFieldAttribute('amount', 'type', 'hidden');
				$form->setFieldAttribute('amount_additional', 'type', 'hidden');
				$form->setFieldAttribute('packaging_weight', 'type', 'hidden', 'params');
				$form->setFieldAttribute('weight_unit', 'type', 'hidden', 'params');
				$form->setFieldAttribute('weight_slabs', 'type', 'hidden', 'slabs');
				$form->setFieldAttribute('quantity_slabs', 'type', 'hidden', 'slabs');
				break;

			default:
				$form->setFieldAttribute('amount', 'type', 'hidden');
				$form->setFieldAttribute('amount_additional', 'type', 'hidden');
				$form->setFieldAttribute('weight_unit', 'type', 'hidden', 'params');
				$form->setFieldAttribute('weight_slabs', 'type', 'hidden', 'slabs');
				$form->setFieldAttribute('quantity_slabs', 'type', 'hidden', 'slabs');
				$form->setFieldAttribute('price_slabs', 'type', 'hidden', 'slabs');
				break;
		}

		$import = $this->helper->config->get('use_shippingrule_import');
		$form->setFieldAttribute('weight_slabs', 'useTable', $import, 'slabs');
		$form->setFieldAttribute('quantity_slabs', 'useTable', $import, 'slabs');
		$form->setFieldAttribute('price_slabs', 'useTable', $import, 'slabs');

		if (!$this->helper->access->check('shippingrule.edit') && $this->helper->access->check('shippingrule.edit.own'))
		{
			$form->removeField('owned_by');
		}

		if (!$this->helper->config->get('itemised_shipping'))
		{
			$form->removeField('apply_on_all_products');
		}

		$creditToField = $this->helper->config->get('shipping_use_global_credit_to');

		if ($creditToField)
		{
			$form->setFieldAttribute('credit_to', 'type', 'hidden');
			$form->setFieldAttribute('credit_to', 'readonly', 'true');
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
						label="COM_SELLACIOUS_SHIPPINGRULE_FIELD_TITLE_LABEL"
						description="COM_SELLACIOUS_SHIPPINGRULE_FIELD_TITLE_DESC"
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
