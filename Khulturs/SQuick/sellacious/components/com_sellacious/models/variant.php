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
 * @since   2.0.0
 */
class SellaciousModelVariant extends SellaciousModelAdmin
{
	/**
	 * Seller user id
	 *
	 * @var    int
	 * @since  2.0.0
	 */
	protected $seller_uid;

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

		$id        = $this->app->getUserStateFromRequest('com_sellacious.edit.variant.id', 'id', 0, 'int');
		$productId = $this->app->getUserStateFromRequest('com_sellacious.edit.variant.product_id', 'product_id', 0, 'int');
		$sellerUid = $this->app->getUserStateFromRequest('com_sellacious.edit.variant.seller_uid', 'seller_uid', 0, 'int');

		$this->state->set('variant.id', $id);
		$this->state->set('variant.productId', $productId);
		$this->state->set('variant.sellerUid', $sellerUid);
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
		$me       = JFactory::getUser();
		$owned_by = ArrayHelper::getValue((array)$record, 'owned_by');

		return $this->helper->access->check('variant.delete')
			|| ($owned_by == $me->get('id') && $this->helper->access->check('variant.delete.own'));
	}

	/**
	 * Method to test whether a record can be deleted.
	 *
	 * @param   object $record A record object.
	 *
	 * @return  boolean  True if allowed to change the state of the record. Defaults to the permission for the component.
	 *
	 * @since   12.2
	 */
	protected function canEditState($record)
	{
		return $this->helper->access->check('variant.edit.state');
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
		parent::preprocessData($context, $data, $group);

		$data             = is_array($data) ? ArrayHelper::toObject($data) : $data;
		$data->product_id = isset($data->product_id) ? $data->product_id : $this->state->get('variant.productId');
		$data->variant_id = $data->id;
		
		$seller = $this->helper->variant->getSellerAttributes($data->variant_id, $this->state->get('variant.sellerUid'));
		
		$data->seller = $seller;
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
	 * @since   2.0.0
	 */
	protected function preprocessForm(JForm $form, $data, $group = 'sellacious')
	{
		// Todo: Variant owned by, and variant x-ref edits e.g. - primary variant
		if (!$this->helper->access->checkAny(array('state', 'state.own'), 'variant.edit.'))
		{
			$form->removeField('state', 'variant');
		}
		
		$me           = JFactory::getUser();
		$variant      = new Registry($data);
		$variantId    = $variant->get('id', 0);
		$productId    = $variant->get('product_id', 0);
		$productId    = $productId ?: $this->state->get('variant.productId');
		$sellerUid    = $this->state->get('variant.sellerUid', null);
		$sellerUid    = !is_null($sellerUid) ? $sellerUid : $this->app->getUserStateFromRequest('com_sellacious.edit.variant.seller_uid', 'seller_uid', 0, 'int');
		$categories   = $this->helper->product->getCategories($productId);
		$product      = $this->helper->product->getItem($productId);
		$product_type = $product->type;

		$sellerEdit = $this->helper->access->check('product.edit.seller') ||
		              ($this->helper->access->check('product.edit.seller.own') && $sellerUid == $me->id);
		
		$stockHandling = $this->helper->product->getStockHandling($productId, $sellerUid);
		list($allowStock) = $stockHandling;

		$language = $product->language;
		$language = $language ?: JFactory::getLanguage()->getTag();

		if ($product_type == 'electronic' && $sellerUid)
		{
			$types = $this->helper->config->get('eproduct_file_type', array());
			$types = empty($types) ? array('image', 'document', 'archive', 'audio', 'video') : (array) $types;

			$form->setFieldAttribute('eproduct', 'filetype', implode(',', $types));
			$form->setFieldAttribute('eproduct', 'product_id', $productId);
			$form->setFieldAttribute('eproduct', 'variant_id', $variantId);
			$form->setFieldAttribute('eproduct', 'seller_uid', $sellerUid);
		}
		else
		{
			$form->removeField('eproduct');
		}
		
		if ($sellerEdit && $allowStock)
		{
			$form->loadFile('variant/seller');
		}

		$field_ids   = $this->helper->category->getFields($categories, array('variant'), true, 'product');
		$xmlElements = $this->helper->field->getFieldsXML($field_ids, 'variant_fields', 'variants', 'COM_SELLACIOUS_PRODUCT_FIELDSET_VARIANTS', $language);

		foreach ($xmlElements as $xmlElement)
		{
			$form->load($xmlElement);
		}

		$form->setFieldAttribute('images', 'record_id', $variant->get('id'));
		$form->setFieldAttribute('eproduct', 'variant_id', $variant->get('id'));
		
		$editFields = $this->helper->config->get('product_fields');
		$editFields = new Registry($editFields);
		$editCols   = $editFields->extract($product_type) ?: new Registry;
		
		if (!$editCols->get('product_features'))
		{
			$form->removeField('features');
			$form->removeField('features');
		}

		parent::preprocessForm($form, $data, $group);
	}

	/**
	 * Method to save the form data
	 *
	 * @param   array  $data  The form data
	 *
	 * @return  bool
	 * @throws  Exception
	 */
	public function save($data)
	{
		$me        = JFactory::getUser();
		$pk        = ArrayHelper::getValue($data, 'id', $this->getState('variant.id'), 'int');
		$variantId = ArrayHelper::getValue($data, 'variant_id', 0);
		$fields    = ArrayHelper::getValue($data, 'variant_fields', array(), 'array');
		$eProducts = ArrayHelper::getValue($data, 'eproduct', array(), 'array');
		$seller    = ArrayHelper::getValue($data, 'seller', array(), 'array');

		$this->helper->core->loadPlugins('sellacious');

		unset($data['fields']);

		$isNew = $pk == 0;
		$table = $this->getTable();

		if (!$isNew)
		{
			$table->load($pk);
		}

		/**
		 * If the product is electronic also save the e-products
		 * Product Id etc is already bound to each of these as the rows are created beforehand. Maybe we should unset those to prevent changes?
		 *
		 * Todo: Add validation check whether its permitted and applicable for this variant
		 */
		$eProducts = array_filter($eProducts);

		foreach ($eProducts as $eproduct)
		{
			$tableM = $this->getTable('EProductMedia');

			$eproduct['is_latest'] = isset($eproduct['is_latest']) ? $eproduct['is_latest'] : 0;
			$eproduct['state']     = isset($eproduct['state']) ? $eproduct['state'] : 0;

			$tableM->load($eproduct['id']);
			$tableM->bind($eproduct);
			$tableM->check();
			$tableM->store();
		}

		$table->bind($data);

		// Assign ownership if a new product and the creator cannot add/modify shop (global) owned products
		if ($isNew && !$this->helper->access->check('variant.edit'))
		{
			$table->set('owned_by', $me->id);
		}

		$table->check();
		$table->store();

		// Update state beforehand
		$this->state->set($this->getName() . '.id', $table->get('id'));
		
		// Seller attributes
		if (!empty($seller))
		{
			$this->helper->variant->setSellerAttributes($table->get('id'), $this->state->get('variant.sellerUid'), $seller);
		}

		$vFields = $this->helper->product->getFields($table->get('product_id'), array('variant'));
		$pFid    = ArrayHelper::getColumn($vFields, 'id');
		$values  = array();

		foreach ($pFid as $fid)
		{
			$values[$fid] = ArrayHelper::getValue($fields, $fid);
		}

		$this->helper->variant->setSpecifications($table->get('id'), $values);
		
		if ($this->app->input->get('task') == 'save2copy')
		{
			$this->copyMedia('variants', $variantId, $table->get('id'));
		}

		$dispatcher = $this->helper->core->loadPlugins();
		$dispatcher->trigger('onContentAfterSave', array('com_sellacious.variant', $table, $isNew));

		return true;
	}
	
	/**
	 * Method to copy media from one record to another
	 *
	 * @param   string  $tableName
	 * @param   int     $recordId
	 * @param   int     $newId
	 *
	 * @return  void
	 *
	 * @since   2.0.0
	 */
	protected function copyMedia($tableName, $recordId, $newId)
	{
		$filter = array('list.select' => 'a.id', 'table_name' => $tableName, 'record_id' => $recordId);
		$pks    = $this->helper->media->loadColumn($filter);
		
		foreach ($pks as $mediaId)
		{
			$this->helper->media->copy($mediaId, $newId, null, null);
		}
	}

	/**
	 * Method to return a single record. Joomla model doesn't use caching, we use.
	 *
	 * @param   int  $pk  (optional) The record id of desired item.
	 *
	 * @return  JObject
	 *
	 * @throws  Exception
	 *
	 * @since   2.0.0
	 */
	public function getItem($pk = null)
	{
		$item = parent::getItem($pk);

		$specifications = $this->helper->variant->getSpecifications($item->get('id', 0), null, true);
		$variant_fields = array();

		foreach ($specifications as $specification)
		{
			$variant_fields[$specification->field_id] = $specification->field_value;
		}

		$item->set('variant_fields', $variant_fields);
		$item->set('eproducts', $this->helper->product->getEProductMedia($item->get('product_id', 0), $item->get('id', 0), $item->get('seller_uid')));

		return $item;
	}
}
