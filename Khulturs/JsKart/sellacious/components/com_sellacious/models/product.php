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
use Sellacious\Cache\Builder\ProductsCacheBuilder;
use Sellacious\Cache\Reader\ProductsCacheReader;
use Sellacious\Media\MediaCleanup;
use Sellacious\Price\PriceHelper;

/**
 * Sellacious model.
 *
 * @since   1.0.0
 */
class SellaciousModelProduct extends SellaciousModelAdmin
{
	/**
	 * Method to test whether a record can be deleted.
	 *
	 * @param   object  $record  A record object.
	 *
	 * @return  bool  True if allowed to delete the record. Defaults to the permission for the component.
	 *
	 * @since   12.2
	 */
	protected function canDelete($record)
	{
		if ($this->helper->access->check('product.delete'))
		{
			return true;
		}

		$me       = JFactory::getUser();
		$owned_by = ArrayHelper::getValue((array) $record, 'owned_by');

		return $this->helper->access->check('product.delete.own') && $owned_by == $me->get('id');
	}

	/**
	 * Method to test whether a record can be deleted.
	 *
	 * @param   object  $record  A record object.
	 *
	 * @return  boolean  True if allowed to change the state of the record. Defaults to the permission for the component.
	 *
	 * @since   1.0.0
	 */
	protected function canEditState($record)
	{
		if ($record->state == 0 || $record->state == 1 || $record->state == 2 || $record->state == -2)
		{
			return $this->helper->access->check('product.edit.state');
		}
		else
		{
			return $this->helper->access->check('product.approve');
		}
	}

	/**
	 * Return a product item
	 *
	 * @param   int  $pk  The id of the primary key.
	 *
	 * @return  stdClass
	 *
	 * @since   1.0.0
	 */
	public function getItem($pk = null)
	{
		static $cache;

		$pk = (!empty($pk)) ? $pk : (int) $this->getState('product.id');

		if (empty($cache[$pk]))
		{
			$item = new stdClass;

			$item->id         = $pk;
			$item->basic      = $this->helper->product->getItem($pk);
			$item->categories = $this->helper->product->getCategories($pk);
			$item->language   = $item->basic->language;

			$cache[$pk] = $item;
		}

		return $cache[$pk];
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
	 * @since   1.0.0
	 */
	protected function preprocessData($context, &$data, $group = 'content')
	{
		if ($wasArray = is_array($data))
		{
			$data = ArrayHelper::toObject($data);
		}

		$sellerSelected = true;

		$me  = JFactory::getUser();

		// Enforce type if configured so
		$product_type         = $this->helper->config->get('allowed_product_type', 'both');
		$allow_package        = $this->helper->config->get('allowed_product_package', 1);
		$default_product_type = $this->helper->config->get('default_product_type', 'physical');

		if (!isset($data->basic->type))
		{
			if ($product_type == 'both')
			{
				$data->basic->type = $default_product_type;
			}
			else
			{
				$data->basic->type = $product_type;
			}
		}

		if ($data->basic->type == 'package' && $allow_package)
		{
			// That's fine if package is allowed and that is selected
		}
		elseif (($product_type == 'physical' || $product_type == 'electronic'))
		{
			// If only a one specific type is allowed enforce it
			$data->basic->type = $product_type;
		}
		elseif (!($data->basic->type == 'physical' || $data->basic->type == 'electronic'))
		{
			// If not a package, and not from of the allowed ones then clear selected
			$data->basic->type = null;
		}

		// Get the attributes pertinent to the specific product type attribution
		if (($data->basic->type == 'physical' && empty($data->physical))
			|| ($data->basic->type == 'electronic' && empty($data->electronic))
			|| ($data->basic->type == 'package' && empty($data->package))
		)
		{
			$data->{$data->basic->type} = $this->helper->product->getAttributesByType($data->id, $data->basic->type);
		}

		// Get the product specifications
		if (!empty($data->id) && empty($data->specifications))
		{
			$data->specifications = $this->helper->field->getValue('products', $data->id);
		}

		// No default selection for Admin, but a must for sellers
		$multi_seller   = $this->helper->config->get('multi_seller', 0);
		$default_seller = $this->helper->config->get('default_seller', 0);
		$isSeller       = $this->helper->seller->is();

		if ($this->helper->access->checkAny(array('seller', 'shipping'), 'product.edit.'))
		{
			if (!$multi_seller)
			{
				$data->seller_uid = $default_seller;
			}
			elseif (!isset($data->seller_uid))
			{
				$data->seller_uid = $this->app->getUserState('com_sellacious.edit.product.seller_uid', $isSeller ? $me->id : $default_seller);
			}
		}
		elseif ($this->helper->access->checkAny(array('seller.own', 'shipping.own'), 'product.edit.'))
		{
			$data->seller_uid = ($multi_seller || $default_seller == $me->id) && $isSeller ? $me->id : 0;
		}
		else
		{
			$data->seller_uid = 0;
		}

		$this->app->setUserState('com_sellacious.edit.product.seller_uid', $data->seller_uid);

		if ($data->seller_uid)
		{
			// Seller was just selected or changed?
			if (empty($data->seller))
			{
				$sellerSelected = true;

				// Reset price if seller switched and load the seller specific attributes
				$data->prices        = null;
				$data->seller        = $this->helper->product->getSellerAttributesByType($data->id, $data->seller_uid, $data->basic->type);
				$data->seller->phone = $data->seller->mobile;
			}

			$categories = isset($data->categories) ? (array)$data->categories : array();
			$priceTypes = PriceHelper::getAllowedForCategory($categories);
			$handlers   = PriceHelper::getSelectedHandlers($priceTypes, true);

			// If there is only one available price type, then select it as default
			if (count($handlers) == 1)
			{
				$priceType = array_key_first($handlers);

				if ($data->seller->pricing_type != $priceType)
				{
					$data->seller->pricing_type = $priceType;
				}
			}

			if (isset($data->seller->pricing_type))
			{
				$priceHandler = PriceHelper::getHandler($data->seller->pricing_type);

				$priceHandler->processFormData($data, isset($data->id) ? $data->id : 0, $data->seller_uid);
			}
		}

		if ($data->id > 0)
		{
			$language = empty($data->language) ? JFactory::getLanguage()->getTag() : $data->language;
			$variants = $this->helper->product->getVariants($data->id, true, false, $language);

			$data->variants['items'] = $variants;

			if (isset($data->prices->variants))
			{
				$data->variants['prices'] = $data->prices->variants;
			}
		}

		$categoryIds    = isset($data->categories) ? (array) $data->categories : array();
		$uniqueSetting  = $this->helper->product->getUniqueFieldSetting($categoryIds);
		$uniqueField    = $uniqueSetting->get('product_unique_field', '');
		$uniqueFieldVal = $this->helper->product->getUniqueFieldValue($uniqueField, $data->id, $data->seller_uid);

		$this->setState('product.unique_field', $uniqueField);
		$this->setState('product.unique_field_title', $uniqueSetting->get('product_unique_field_title'));
		$this->setState('product.unique_field_value', $uniqueFieldVal);

		if ($wasArray)
		{
			if ($sellerSelected)
			{
				$data = ArrayHelper::fromObject($data);
				$data = ArrayHelper::toObject($data, 'stdClass', false);
			}
			else
			{
				// Temporary workaround to reset data type to original
				$data = ArrayHelper::fromObject($data);
			}
		}

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
	 * @throws  Exception if there is an error in the form event.
	 * @since   1.0.0
	 */
	protected function preprocessForm(JForm $form, $data, $group = 'sellacious')
	{
		$me       = JFactory::getUser();
		$registry = new Registry($data);
		$isNew    = $registry->get('id', 0) == 0;

		if ($isNew)
		{
			$form->setFieldAttribute('id', 'type', 'hidden');
		}

		$language      = $registry->get('language', JFactory::getLanguage()->getTag());
		$product_type  = $registry->get('basic.type', 'physical');
		$type_allowed  = $this->helper->config->get('allowed_product_type', 'both');
		$allow_package = $this->helper->config->get('allowed_product_package', 1);

		// If only a specific type is allowed, disallow change BUT if package is allowed then we still show list
		if (!$allow_package && ($type_allowed == 'physical' || $type_allowed == 'electronic'))
		{
			$form->setFieldAttribute('type', 'type', 'hidden', 'basic');
		}

		// If allowed then extend with edit form
		$allowCreate = $this->helper->access->check('product.create');
		$owned_by    = $registry->get('basic.owned_by');
		$isOwner     = $owned_by > 0 && $owned_by == $me->get('id');

		$basicEdit = $isNew ? $allowCreate : $this->helper->access->check('product.edit.basic')
			|| ($isOwner && $this->helper->access->check('product.edit.basic.own'));

		$variantEdit = $this->helper->access->check('variant.create')
			|| $this->helper->access->check('variant.edit')
			|| $this->helper->access->check('variant.edit.own');

		$multiVariant = $this->helper->config->get('multi_variant', 0);

		$product_id = $registry->get('id');
		$seller_uid = $registry->get('seller_uid');

		$uniqueField     = $this->getState('product.unique_field');
		$allowDuplicates = $this->helper->config->get('allow_duplicate_products');

		$multiSeller = $this->helper->config->get('multi_seller', 0);

		// If Seller cannot be switched, hide the list
		if (!$this->helper->access->checkAny(array('seller', 'shipping'), 'product.edit.') || !$multiSeller)
		{
			$form->setFieldAttribute('seller_uid', 'type', 'hidden');
			$form->setFieldAttribute('seller_uid', 'readonly', 'true');
		}

		// If creating product and the unique field is not filled (if there is one)
		$uniqueFieldEmpty = (!$uniqueField && !$registry->get('categories')) || $this->isUniqueFieldEmpty($registry, $uniqueField);
		
		if (!$allowDuplicates && $isNew && $uniqueFieldEmpty)
		{
			$form->removeGroup('no_seller');

			if (!$allowCreate)
			{
				$form->removeField('seller_uid');
			}

			if ($basicEdit && $product_type)
			{
				$categoryGrp = ($product_type == 'package') ? 'product/physical;product/electronic' : 'product/' . $product_type;

				$form->setFieldAttribute('language', 'product_id', $product_id);
				$form->setFieldAttribute('categories', 'group', $categoryGrp);

				if ($uniqueField && $this->isUniqueFieldEmpty($registry, $uniqueField))
				{
					// Show Unique Field
					if (is_numeric($uniqueField))
					{
						$uniqueFieldXmls = $this->helper->field->getFieldsXml(array($uniqueField), 'specifications', 'basic', 'COM_SELLACIOUS_PRODUCT_FIELDSET_BASIC', $language);

						foreach ($uniqueFieldXmls as $uniqueFieldXml)
						{
							$form->load($uniqueFieldXml);
						}
					}
					else
					{
						$contexts = explode('.', $uniqueField);
						$context  = (isset($contexts[1]) & $contexts[0] == 'seller') ? 'seller' : 'basic';
						$uField   = isset($contexts[1]) ? $contexts[1] : $uniqueField;

						$basicForm      = JForm::getInstance($context, 'product/' . $context, array('control' => 'jform'));
						$uniqueFieldXml = $basicForm->getFieldXml($uField, $context);

						if ($uniqueFieldXml)
						{
							$element = new SimpleXMLElement('
							<fieldset name="basic" label="COM_SELLACIOUS_PRODUCT_FIELDSET_BASIC">
	                            <fields name="' . $context . '">
	                                ' . $uniqueFieldXml->asXML() . '
								</fields>
                            </fieldset>
						');
							$form->setField($element, null, true, 'basic');
						}
					}
				}
				else
				{
					$form->setFieldAttribute('categories', 'required', true);
				}
			}
		}
		elseif ($product_type)
		{
			// Disable type change at all after save.
			if (!$isNew)
			{
				$form->setFieldAttribute('type', 'type', 'hidden', 'basic');
				$form->setFieldAttribute('type', 'readonly', 'true', 'basic');
			}

			$categories = $this->helper->category->getParents($registry->get('categories'), true);

			$form->setFieldAttribute('language', 'product_id', $product_id);

			if ($basicEdit)
			{
				$form->loadFile('product/basic');

				if ($this->helper->config->get('product_category_required'))
				{
					$form->setFieldAttribute('categories', 'required', 'true');
				}

				$form->setFieldAttribute('attachments', 'recordId', $product_id, 'basic');
				$form->setFieldAttribute('images', 'recordId', $product_id, 'basic');
				$form->setFieldAttribute('primary_image', 'recordId', $product_id, 'basic');

				if ($product_type == 'physical')
				{
					$form->loadFile('product/physical');

					$form->setFieldAttribute('categories', 'group', 'product/physical');
				}
				elseif ($product_type == 'electronic')
				{
					$form->loadFile('product/electronic');

					$form->setFieldAttribute('categories', 'group', 'product/electronic');
				}
				elseif ($product_type == 'package')
				{
					$form->loadFile('product/package');

					// Allow both type of categories for now
					$form->setFieldAttribute('categories', 'group', 'product/physical;product/electronic');
					$form->setFieldAttribute('products', 'product_id', $registry->get('id'), 'package');
				}

				// Core (+ variant @20150903@) fields defined in all the categories in hierarchy defines the specifications
				$field_ids = $this->helper->category->getFields($categories, array('core', 'variant'), true, 'product');

				if ($uniqueField && is_numeric($uniqueField) && ($key = array_search($uniqueField, $field_ids)) !== false)
				{
					unset($field_ids[$key]);

					$uniqueFieldXmls = $this->helper->field->getFieldsXML(array($uniqueField), 'specifications', 'basic', 'COM_SELLACIOUS_PRODUCT_FIELDSET_BASIC', $language);

					foreach ($uniqueFieldXmls as $uniqueFieldXml)
					{
						$form->load($uniqueFieldXml);
					}
				}

				$xmlElements = $this->helper->field->getFieldsXML($field_ids, 'specifications', 'specifications', 'COM_SELLACIOUS_PRODUCT_FIELDSET_SPECIFICATIONS', $language);

				foreach ($xmlElements as $xmlElement)
				{
					$form->load($xmlElement);
				}
			}
			else
			{
				$form->setFieldAttribute('id', 'type', 'hidden');
				$form->removeField('categories');
			}

			if (!$isNew && $variantEdit && $multiVariant)
			{
				// This is now just a dummy form so as show the Variants tab
				$form->loadFile('product/variant');

				$form->setFieldAttribute('variants', 'product_id', $product_id);
				$form->setFieldAttribute('variants', 'seller_uid', $seller_uid);
			}

			$form->setFieldAttribute('seller_uid', 'product_id', $product_id);

			// If I cannot change prices remove note
			if (!$this->helper->access->check('product.edit.seller'))
			{
				$form->removeField('prices_note', 'no_seller');
			}

			// If I cannot change shipping remove note
			if (!$this->helper->access->check('product.edit.shipping'))
			{
				$form->removeField('shipping_note', 'no_seller');
			}

			if ($seller_uid)
			{
				$form->removeGroup('no_seller');

				$sellerEdit = $this->helper->access->check('product.edit.seller') ||
					($this->helper->access->check('product.edit.seller.own') && $seller_uid == $me->id);

				if ($sellerEdit)
				{
					$form->loadFile('product/seller');
					$form->loadFile('product/seller/common');

					$form->setFieldAttribute('attachments', 'recordId', $registry->get('seller.id'), 'seller');

					if ($product_type == 'physical' || $product_type == 'package')
					{
						$form->loadFile('product/seller/' . $product_type);

						$allow_return   = $this->helper->config->get('purchase_return', 0);
						$allow_exchange = $this->helper->config->get('purchase_exchange', 0);

						if ($allow_return == 2 || $allow_exchange == 2)
						{
							$form->loadFile('product/returnexchange');

							if ($allow_return != 2)
							{
								$form->removeField('return_days', 'seller');
								$form->removeField('return_tnc', 'seller');
							}

							if ($allow_exchange != 2)
							{
								$form->removeField('exchange_days', 'seller');
								$form->removeField('exchange_tnc', 'seller');
							}
						}
					}
					elseif ($product_type == 'electronic')
					{
						$form->loadFile('product/seller/electronic');

						$types = $this->helper->config->get('eproduct_file_type', array());
						$types = empty($types) ? array('image', 'document', 'archive', 'audio', 'video') : (array) $types;

						$form->setFieldAttribute('eproduct', 'filetype', implode(',', $types), 'seller');
						$form->setFieldAttribute('eproduct', 'product_id', $product_id, 'seller');
						$form->setFieldAttribute('eproduct', 'variant_id', 0, 'seller');
						$form->setFieldAttribute('eproduct', 'seller_uid', $seller_uid, 'seller');
					}

					if (!$multiSeller)
					{
						$form->setFieldAttribute('state', 'type', 'hidden', 'seller');
						$form->removeField('seller_sku', 'seller');
					}

					// Remove stock fields if not managing at product level else use the set defaults
					list($shS, $st, $sot) = $this->helper->product->getStockHandling($product_id, $seller_uid);

					if (!$shS)
					{
						list($shP) = $this->helper->product->getStockHandling($product_id);

						if (!$shP)
						{
							// Hide this only if disallowed by settings and not the seller
							$form->removeField('disable_stock', 'seller');
						}
					}
					else
					{
						$form->setFieldAttribute('stock', 'default', $st, 'seller');
						$form->setFieldAttribute('over_stock', 'default', $sot, 'seller');
					}
				}

				$priceEdit = $this->helper->access->check('product.edit.seller') ||
					($this->helper->access->check('product.edit.seller.own') && $seller_uid == $me->id);

				if ($priceEdit)
				{
					$categories = $registry->get('categories', array());
					$priceTypes = PriceHelper::getAllowedForCategory($categories);

					$form->loadFile('product/prices/handlers');

					$form->setFieldAttribute('pricing_type', 'limit', implode(',', $priceTypes), 'seller');

					$pricingType = $registry->get('seller.pricing_type', 'hidden');

					if (in_array($pricingType, $priceTypes))
					{
						if (in_array($pricingType, array('flat', 'basic', 'dynamic')))
						{
							$form->loadFile('product/prices/flat-basic-dynamic');
						}
						else
						{
							$form->loadFile('product/prices/' . $pricingType);
						}
					}
				}

				$shippingEdit = $this->helper->access->check('product.edit.shipping') ||
					($this->helper->access->check('product.edit.shipping.own') && $seller_uid == $me->id);

				if ($shippingEdit && $product_type == 'physical')
				{
					$form->loadFile('product/shipping');
					
					$form->setFieldAttribute('shipping_rules', 'owner_id', $seller_uid, 'seller.rules');

					if ($this->helper->config->get('itemised_shipping', SellaciousHelperShipping::SHIPPING_SELECTION_PRODUCT) != SellaciousHelperShipping::SHIPPING_SELECTION_PRODUCT || $this->helper->config->get('shipped_by') != 'seller')
					{
						$form->removeField('flat_shipping', 'seller');
						$form->removeField('shipping_flat_fee', 'seller');
					}

					$shipRulesField = $form->getField('shipping_rules', 'seller.rules');
					$rulesInput     = $shipRulesField->input;
					
					$itemisedShipping  = $this->helper->config->get('itemised_shipping');
					$shippingSelection = $this->helper->config->get('product_select_shipping');
					$selectionDisabled = $itemisedShipping == SellaciousHelperShipping::SHIPPING_SELECTION_CART || ($itemisedShipping == SellaciousHelperShipping::SHIPPING_SELECTION_SELLER && $shippingSelection == 0);
					
					if ($selectionDisabled || ($shipRulesField && empty($rulesInput)) || ($this->helper->config->get('shipped_by') == 'shop' && $this->helper->config->get('flat_shipping', 1)))
					{
						$form->removeField('shipping_rules', 'seller.rules');
					}
				}
			}

			if ($this->helper->access->check('product.edit.seo')
				|| (($isNew || $isOwner) && $this->helper->access->check('product.edit.seo.own')))
			{
				$form->loadFile('product/seo');
			}

			if ($this->helper->access->check('product.edit.related')
				|| (($isNew || $isOwner) && $this->helper->access->check('product.edit.related.own')))
			{
				$form->loadFile('product/related');
				$form->setFieldAttribute('groups', 'product_id', $product_id, 'related');
			}

			$product_category_limit     = $this->helper->config->get('product_category_limit', 0);
			$product_category_limit_val = $this->helper->config->get('product_category_limit_value', 2);

			if ($product_category_limit == 1)
			{
				$form->setFieldAttribute('categories', 'multiple', 'false');
			}
			elseif ($product_category_limit == 2 && $product_category_limit_val >= 2)
			{
				$form->setFieldAttribute('categories', 'selection_limit', $product_category_limit_val);
			}

			$editFields = $this->helper->config->get('product_fields');
			$editFields = new Registry($editFields);
			$editCols   = $editFields->extract($product_type) ?: new Registry;

			if (!$editCols->get('product_type'))
			{
				$form->removeField('type', 'basic');
			}

			if (!$editCols->get('product_category'))
			{
				$form->removeField('categories');
			}

			if (!$editCols->get('parent_product'))
			{
				$form->removeField('parent_id', 'basic');
			}

			if (!$editCols->get('product_sku'))
			{
				$form->removeField('local_sku', 'basic');
			}

			if (!$editCols->get('seller_sku'))
			{
				$form->removeField('seller_sku', 'seller');
			}

			if (!$editCols->get('primary_image'))
			{
				$form->removeField('primary_image', 'basic');
			}

			if (!$editCols->get('primary_video_url'))
			{
				$form->removeField('primary_video_url', 'basic');
			}

			if (!$editCols->get('other_images'))
			{
				$form->removeField('images', 'basic');
			}

			if (!$editCols->get('manufacturer'))
			{
				$form->removeField('manufacturer_id', 'basic');
			}

			if (!$editCols->get('manufacturer_sku'))
			{
				$form->removeField('manufacturer_sku', 'basic');
			}

			if (!$editCols->get('min_quantity'))
			{
				$form->removeField('quantity_min', 'seller');
			}

			if (!$editCols->get('max_quantity'))
			{
				$form->removeField('quantity_max', 'seller');
			}

			if (!$editCols->get('over_stock'))
			{
				$form->removeField('over_stock', 'seller');
			}

			if (!$editCols->get('whats_in_box'))
			{
				$form->removeField('whats_in_box', 'seller');
			}

			if (!$editCols->get('product_features'))
			{
				$form->removeField('features', 'basic');
				$form->removeField('features', 'variant');
			}

			if (!$editCols->get('short_description'))
			{
				$form->removeField('introtext', 'basic');
			}

			if (!$editCols->get('description'))
			{
				$form->removeField('description', 'basic');
			}

			if (!$editCols->get('product_attachments'))
			{
				$form->removeField('attachments', 'basic');
			}

			if (!$editCols->get('seller_attachments'))
			{
				$form->removeField('attachments', 'seller');
			}

			if (!$editCols->get('eproduct_delivery'))
			{
				$form->setFieldAttribute('delivery_mode', 'type', 'hidden', 'seller');
				$form->setFieldAttribute('delivery_mode', 'default', 'download', 'seller');
			}

			if (!$editCols->get('location'))
			{
				$form->removeField('address', 'basic');
				$form->removeField('product_location', 'seller');
				$form->removeField('product_address', 'seller');
				$form->removeField('loc_zip', 'seller');
				$form->removeField('loc_sublocality', 'seller');
				$form->removeField('loc_locality', 'seller');
				$form->removeField('loc_city', 'seller');
				$form->removeField('loc_district', 'seller');
				$form->removeField('loc_state', 'seller');
				$form->removeField('loc_country', 'seller');
			}
		}
		elseif (!$allowCreate)
		{
			$form->removeField('seller_uid');
			$form->removeField('type', 'basic');
			$form->setFieldAttribute('id', 'type', 'hidden');
		}
		else
		{
			$form->removeField('seller_uid');
		}

		if (!$allowDuplicates)
		{
			$this->processUniqueField($form, $uniqueField, $isNew);
		}

		parent::preprocessForm($form, $data, $group);
	}

	/**
	 * Ajax query suggestion list
	 *
	 * @param   string  $context    What item type is searched - product-sku, product or manufacturer
	 * @param   string  $key        Searched key
	 * @param   array   $ids        The preselected Ids
	 * @param   int     $sellerUid  Product Seller User Id
	 * @param   bool    $separate   Whether to include product for each seller separately also
	 * @param   array   $types      Product Types
	 *
	 * @return  stdClass[]
	 *
	 * @throws  Exception
	 *
	 * @since   1.0.0
	 */
	public function suggest($context, $key, $ids = null, $sellerUid = 0, $separate = false, $types = array())
	{
		if ($context === 'manufacturer')
		{
			$filters = array('list.select' => 'id, title', 'state' => 1);

			if ($ids)
			{
				$filters['id'] = $ids;
			}

			if ($key)
			{
				$filters['list.where'] = sprintf('title LIKE %1$s', $this->_db->q('%' . $this->_db->escape($key) . '%', false));
			}

			return $this->helper->manufacturer->loadObjectList($filters);
		}
		elseif (!$separate)
		{
			$filters = array('list.select' => 'id, title', 'state' => 1);

			if ($key)
			{
				$kw = $this->_db->q('%' . $this->_db->escape($key) . '%', false);

				$filters['list.where'] = sprintf($context == 'product' ? 'title LIKE %1$s' : '(title LIKE %1$s OR local_sku LIKE %1$s)', $kw);
			}

			if ($ids)
			{
				$filters['id'] = $ids;
			}

			return $this->helper->product->loadObjectList($filters);
		}
		else
		{
			$loader = new ProductsCacheReader;

			$loader->filterValue('product_active', 1);
			$loader->filterValue('variant_id', 0);
			
			if (!empty($types))
			{
				$loader->filterValue('product_type', $types, 'IN');
			}

			$query = $loader->getQuery();

			if ($key)
			{
				$cond = $context === 'product' ? '(product_title LIKE %1$s)' : '(product_title LIKE %1$s OR product_sku LIKE %1$s OR code LIKE %1$s)';

				$query->where(sprintf($cond, $query->q('%' . $query->escape($key) . '%', false)));
			}

			$wOrId = array();

			if (is_array($ids))
			{
				foreach ((array) $ids as $id)
				{
					if (is_numeric($id))
					{
						$wOrId[] = sprintf('(product_id = ' . (int) $id . ' AND variant_id = 0)');
					}
					elseif ($this->helper->product->parseCode($id))
					{
						$wOrId[] = sprintf('code = ' . $query->q($id));
					}
				}
			}

			if ($wOrId)
			{
				$query->where(sprintf('(%s)', implode(' OR ', $wOrId)));
			}

			if ($sellerUid)
			{
				$query->select('code AS record_id');

				$query->where('seller_uid = ' . (int) $sellerUid);
			}
			else
			{
				$query->select('CASE WHEN seller_count > 1 THEN code ELSE product_id END AS record_id');
			}

			$options = array();
			$items   = $loader->getItems(0, 10);

			foreach ($items as $item)
			{
				if ($item->product_type == 'package')
				{
					$products = $this->helper->package->getProducts($item->product_id, true);
					$types    = array_unique(ArrayHelper::getColumn($products, 'type'));
					
					// Exclude package products which only have electronic products
					if (!in_array('physical', $types))
					{
						continue;
					}
				}
				
				$options[] = array('id' => $item->record_id, 'title' => $item->product_title);
			}

			return $options;
		}
	}

	/**
	 * Method to search all products/variants from the given filters and search query
	 *
	 * @param   array  $filters  The filters to limit the search result
	 * @param   int    $offset   Search result list offset
	 * @param   int    $limit    Max number of result items to return
	 * @param   bool   &$more    Whether more results are available
	 *
	 * @return  stdClass[]
	 *
	 * @throws  Exception
	 *
	 * @since   1.4.1
	 */
	public function search($filters, $offset = 0, $limit = 0, &$more = false)
	{
		$db    = $this->getDbo();
		$query = $db->getQuery(true);

		$query->select('a.id, a.variant_id, a.title, a.variant_title, a.local_sku, a.variant_sku')
			->from('#__sellacious_products_cache a');

		$seller_uid = ArrayHelper::getValue($filters, 'seller_uid', 0, 'int');
		$keyword    = ArrayHelper::getValue($filters, 'keyword', '', 'string');
		$type       = ArrayHelper::getValue($filters, 'type', '', 'string');

		if ($seller_uid)
		{
			$query->join('inner', '#__sellacious_product_sellers ps ON ps.product_id = a.id')
				->where('ps.seller_uid = ' . (int) $seller_uid);
		}

		if ($keyword)
		{
			$match = $db->q('%' . $db->escape($keyword, true) . '%', false);
			$wh    = array(
				"CONCAT(a.title, ' ', a.variant_title) LIKE " . $match,
				"CONCAT(a.local_sku, ' ', a.variant_sku) LIKE " . $match,
			);
			$query->where('(' . implode(' OR ', $wh). ')');
		}

		try
		{
			$items = array();
			$rows  = $db->setQuery($query, $offset, $limit ? $limit + 1 : 0)->loadObjectList();
			$count = count($rows);
			$more  = $count > $limit;

			foreach ($rows as $i => $item)
			{
				$item->code = $this->helper->product->getCode($item->id, $item->variant_id, $seller_uid);
				$items[]    = $item;

				if ($limit && $limit <= $i + 1)
				{
					break;
				}
			}
		}
		catch (Exception $e)
		{
			throw $e;
		}

		return $items;
	}

	/**
	 * Method to search all products/variants from the given filters and search query
	 *
	 * @param   array  $filters  The filters to limit the search result
	 * @param   int    $offset   Search result list offset
	 * @param   int    $limit    Max number of result items to return
	 * @param   bool   &$more    Whether more results are available
	 *
	 * @return  stdClass[]
	 *
	 * @throws  Exception
	 *
	 * @since   1.4.1
	 */
	public function searchSeller($filters, $offset = 0, $limit = 0, &$more = false)
	{
		$db    = $this->getDbo();
		$query = $db->getQuery(true);

		$p_code     = ArrayHelper::getValue($filters, 'product', '', 'string');
		$keyword    = ArrayHelper::getValue($filters, 'keyword', '', 'string');
		$product_id = 0;

		// Skip if product selected is not valid
		if ($p_code && !$this->helper->product->parseCode($p_code, $product_id, $variant_id))
		{
			return array();
		}

		$query->select('s.user_id, s.title company, s.code, u.name')
			->from('#__sellacious_sellers s')
			->join('inner', '#__sellacious_product_sellers ps ON s.user_id = ps.seller_uid')
			->join('inner', '#__users u ON s.user_id = u.id')
			->where('u.block = 0')
			->group('u.id');

		if ($product_id)
		{
			$query->where('ps.product_id = ' . (int) $product_id);
		}

		if ($keyword)
		{
			$match = $db->q('%' . $db->escape($keyword, true) . '%', false);
			$query->where('(s.title LIKE ' . $match . ' OR u.name LIKE ' . $match . ')');
		}

		try
		{
			$items = array();
			$rows  = $db->setQuery($query, $offset, $limit ? $limit + 1 : 0)->loadObjectList();
			$count = count($items);
			$more  = $count > $limit;

			foreach ($rows as $i => $item)
			{
				$item->title = sprintf('%s (%s)', $item->company ?: $item->name, $item->code);

				$items[] = $item;

				if ($limit && $limit <= $i + 1)
				{
					break;
				}
			}
		}
		catch (Exception $e)
		{
			throw $e;
		}

		return $items;
	}

	/**
	 * Method to validate the form data.
	 *
	 * @param   JForm   $form   The form to validate against.
	 * @param   array   $data   The data to validate.
	 * @param   string  $group  The name of the field group to validate.
	 *
	 * @return  mixed  Array of filtered data if valid, false otherwise.
	 *
	 * @see     JFormRule
	 * @see     JFilterInput
	 * @since   12.2
	 */
	public function validate($form, $data, $group = null)
	{
		// Variants are entered separately;
		$tForm = clone $form;
		$tForm->removeGroup('variant');
		$tForm->removeGroup('variant_fields');

		$sellerSku = ArrayHelper::getValue($data, 'seller.seller_sku');
		$sellerUid = ArrayHelper::getValue($data, 'seller_uid');
		$productId = ArrayHelper::getValue($data, 'id');

		if ($sellerSku && !$this->helper->product->isSkuUnique($productId, $sellerUid, $sellerSku))
		{
			$this->setError(JText::_('COM_SELLACIOUS_PRODUCT_SAVE_SELLER_SKU_NOT_UNIQUE'));
			return false;
		}

		return parent::validate($tForm, $data, $group);
	}

	/**
	 * Method to save the form data
	 *
	 * @param   array  $data  The form data
	 *
	 * @return  bool
	 *
	 * @since   1.0.0
	 */
	public function save($data)
	{
		$me    = JFactory::getUser();
		$table = $this->getTable();
		$pk    = !empty($data['id']) ? $data['id'] : (int) $this->getState('product.id');
		$isNew = ($pk == 0);

		$product_type         = $this->helper->config->get('allowed_product_type', 'both');
		$default_product_type = $this->helper->config->get('default_product_type', 'physical');

		if (!isset($data['basic']['type']))
		{
			if ($product_type == 'both')
			{
				$data['basic']['type'] = $default_product_type;
			}
			else
			{
				$data['basic']['type'] = $product_type;
			}
		}

		$dispatcher = $this->helper->core->loadPlugins();

		try
		{
			$registry = new Registry($data);

			// Save the basic info first
			$registry->set('basic.id', $pk);

			if ($isNew)
			{
				$product_id = 0;

				if ($this->helper->access->check('product.edit.state'))
				{
					$registry->set('basic.state', 1);
				}

				if (!$this->helper->access->check('product.edit.basic'))
				{
					// Assign ownership if a new product and the creator cannot add/modify shop (global) owned products
					$registry->set('basic.owned_by', $me->id);

					// Check Required Approval for seller product
					if ($this->helper->config->get('seller_product_approve', 0))
					{
						$registry->set('basic.state', -1);
					}
				}
			}
			else
			{
				$table->load($pk);
				$product_id = $table->get('id');

				$registry->set('basic.owned_by', $table->get('owned_by'));

				// Allow seller to set product state in approval pending on edit
				if (!$this->helper->access->check('product.edit.basic'))
				{
					// Check Required Approval for seller product
					if (($table->get('state') == -3) && ($this->helper->config->get('seller_product_approve', 0)))
					{
						$registry->set('basic.state', -1);
					}

				}
			}

			$owned_by  = $registry->get('basic.owned_by');
			$isOwner   = $owned_by > 0 && $owned_by == $me->get('id');
			$basicEdit = $isNew ? $this->helper->access->check('product.create') : $this->helper->access->check('product.edit.basic')
				|| ($isOwner && $this->helper->access->check('product.edit.basic.own'));

			// Add extended product attributes based on type
			$type          = $registry->get('basic.type');
			$type_allowed  = $this->helper->config->get('allowed_product_type', 'both');
			$allow_package = $this->helper->config->get('allowed_product_package', 1);

			$valid_type  = ($type == 'package' && $allow_package)
						|| ($type == 'physical' && ($type_allowed == 'both' || $type_allowed == 'physical'))
						|| ($type == 'electronic' && ($type_allowed == 'both' || $type_allowed == 'electronic'));

			if (!$valid_type)
			{
				throw new Exception(JText::_('COM_SELLACIOUS_PRODUCT_SAVE_INVALID_TYPE'));
			}

			$this->validateUniqueField($registry, $product_id);

			if ($basicEdit)
			{
				$categories                 = (array) $registry->get('categories');
				$product_category_limit     = $this->helper->config->get('product_category_limit', 0);
				$product_category_limit_val = $this->helper->config->get('product_category_limit_value', 2);
				$product_category_limit_val = $product_category_limit <= 1 ? $product_category_limit : $product_category_limit_val;

				if ($product_category_limit_val > 0 && count($categories) > $product_category_limit_val)
				{
					throw new Exception(JText::sprintf('COM_SELLACIOUS_PRODUCT_SAVE_PRODUCT_CATEGORIES_LIMIT_EXCEEDED', $product_category_limit_val));
				}

				if ($basic = $registry->extract('basic'))
				{
					if ($bFeatures = $basic->get('features'))
					{
						$bFeatures = array_values(array_filter((array) $bFeatures, 'trim'));

						$basic->set('features', $bFeatures);
					}

					if (isset($data['language']))
					{
						$basic->set('language', $data['language']);
					}

					$table->save($basic->toArray());
				}

				// Get updated record id (for new inserts)
				$product_id = $table->get('id');

				// Update state beforehand
				$this->setState('product.id', $product_id);

				// Assign categories to product
				$this->helper->product->setCategories($product_id, $categories);

				if ($attributes = $registry->extract($type))
				{
					$this->helper->product->setAttributesByType($attributes->toArray(), $product_id, $type);
				}

				// Add extended specifications
				if ($specs = $registry->extract('specifications'))
				{
					$this->helper->product->setSpecifications($product_id, $specs->toArray(), true);
				}

				try
				{
					$_control    = 'jform.basic.primary_image';
					$_tableName  = 'products';
					$_context    = 'primary_image';
					$_recordId   = $product_id;
					$_extensions = array('jpg', 'png', 'jpeg', 'gif');
					$_options    = $basic->get('primary_image') ?: array();

					$this->helper->media->handleUploader($_control, $_tableName, $_context, $_recordId, $_extensions, $_options);
				}
				catch (Exception $e)
				{
					$this->app->enqueueMessage($e->getMessage(), 'warning');
				}

				try
				{
					$_control    = 'jform.basic.images';
					$_tableName  = 'products';
					$_context    = 'images';
					$_recordId   = $product_id;
					$_extensions = array('jpg', 'png', 'jpeg', 'gif');
					$_options    = $basic->get('images') ?: array();

					$this->helper->media->handleUploader($_control, $_tableName, $_context, $_recordId, $_extensions, $_options);
				}
				catch (Exception $e)
				{
					$this->app->enqueueMessage($e->getMessage(), 'warning');
				}

				try
				{
					$_control    = 'jform.basic.attachments';
					$_tableName  = 'products';
					$_context    = 'attachments';
					$_recordId   = $product_id;
					$_extensions = array('jpg', 'png', 'jpeg', 'gif', 'doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx', 'pdf', 'txt');
					$_options    = $basic->get('attachments') ?: array();

					$this->helper->media->handleUploader($_control, $_tableName, $_context, $_recordId, $_extensions, $_options);
				}
				catch (Exception $e)
				{
					$this->app->enqueueMessage($e->getMessage(), 'warning');
				}
			}

			// Different db table and different approach for variant fields. They are added via ajax already.
			if ($this->helper->access->check('product.edit.related')
				|| (($isOwner) && $this->helper->access->check('product.edit.related.own')))
			{
				// Add related product groups
				$this->helper->relatedProduct->setProduct($product_id, explode(',', $registry->get('related.groups')));
			}

			// Add seller specific attributes
			if ($seller_uid = $registry->get('seller_uid'))
			{
				/*
				 * If the listing is free in the settings, auto-renew the listing for next 1 year
				 * We've set up a link in product list view to facilitate this. User needs to click manually now.
				 */
				if ($this->helper->config->get('free_listing'))
				{
					$active = $this->helper->listing->getActive($product_id, $seller_uid, 0);

					// Require renewal in the last 15 days of year for now, this is to avoid renewal on every save action.
					if ($active->state == 0 || (strtotime($active->publish_down) - strtotime('now') <= 15 * 24 * 60 * 60))
					{
						$this->helper->listing->extend($product_id, $seller_uid, 0, 365, true);
					}
				}

				/*
				 * Shipping data is in sellers table itself, don't need to worry about other values as they'll be
				 * filtered out in the **validation** process automatically.
				 */
				$sellerEdit = $this->helper->access->check('product.edit.seller')
					|| ($this->helper->access->check('product.edit.seller.own') && $seller_uid == $me->id);

				if ($sellerEdit && ($attributes = $registry->extract('seller')))
				{
					// Stock handling is checked internally, so not needed here
					$psx_id = $this->helper->product->setSellerAttributesByType($attributes->toArray(), $product_id, $seller_uid, $type);

					// Seller specific attachments?
					try
					{
						$_control    = 'jform.seller.attachments';
						$_tableName  = 'product_sellers';
						$_context    = 'attachments';
						$_recordId   = $psx_id;
						$_extensions = array('jpg', 'png', 'jpeg', 'gif', 'doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx', 'pdf', 'txt');
						$_options    = $attributes->get('attachments') ?: array();

						$this->helper->media->handleUploader($_control, $_tableName, $_context, $_recordId, $_extensions, $_options);
					}
					catch (Exception $e)
					{
						$this->app->enqueueMessage($e->getMessage(), 'warning');
					}
				}

				if ($this->helper->access->check('product.edit.seller')
					|| ($this->helper->access->check('product.edit.seller.own') && $seller_uid == $me->id))
				{
					// Prices (Overrides and Fallback)
					$fallback = $registry->get('prices.fallback');
					$prices   = $registry->get('prices.product');
					$prices   = is_object($prices) ? ArrayHelper::fromObject($prices) : ($prices ? $prices : array());

					if ($fallback)
					{
						$fallback = ArrayHelper::fromObject($fallback);

						$fallback['is_fallback'] = 1;

						$prices[] = $fallback;
					}

					if (count($prices))
					{
						$this->setPrices($product_id, $seller_uid, $prices);
					}

					$priceHandler = PriceHelper::getPsxHandler($product_id, $seller_uid);

					$priceHandler->saveProduct($product_id, $seller_uid, $registry);

					$var_seller_specs = (array) $registry->get('prices.variants');

					foreach ($var_seller_specs as $var_seller_spec)
					{
						if ($variant_id = $var_seller_spec->variant_id)
						{
							$this->helper->variant->setSellerAttributes($variant_id, $seller_uid, (array) $var_seller_spec);
						}
					}
				}
			}

			//Add Language Association
			$assocId = $this->app->getUserState('com_sellacious.edit.product.assoc_id');

			if ($isNew && $assocId)
			{
				$this->helper->product->saveAssociation($assocId, $table->get('id'), 'com_sellacious.product', $data['language']);

				$this->app->setUserState('com_sellacious.edit.product.assoc_id', null);
			}

			$dispatcher->trigger('onContentAfterSave', array('com_sellacious.product', $table, $isNew, $data));
		}
		catch (Exception $e)
		{
			$this->setError($e->getMessage());

			return false;
		}

		return true;
	}

	/**
	 * Method to save the basic/fallback price and stock for the given product-seller
	 *
	 * @param   array[]  $items  The form data
	 *
	 * @return  bool
	 * @throws  Exception
	 *
	 * @since   1.0.0
	 */
	public function savePriceAndStock($items)
	{
		$me         = JFactory::getUser();
		$dispatcher = $this->helper->core->loadPlugins();

		foreach ($items as $item)
		{
			$productId = ArrayHelper::getValue($item, 'product_id');
			$sellerUid = ArrayHelper::getValue($item, 'seller_uid');
			$price     = ArrayHelper::getValue($item, 'price');
			$stock     = ArrayHelper::getValue($item, 'stock');

			if ($this->helper->access->check('product.edit.seller') ||
				($this->helper->access->check('product.edit.seller.own') && $sellerUid == $me->id)
			)
			{
				$table = $this->getTable('ProductPrices');
				$table->load(array('product_id' => $productId, 'seller_uid' => $sellerUid, 'is_fallback' => 1));

				// Ids must match even if zero
				if ($table->get('id') == $price['id'])
				{
					$price['product_id']  = $productId;
					$price['seller_uid']  = $sellerUid;
					$price['is_fallback'] = 1;
					$price['margin_type'] = isset($price['margin_type']) ? $price['margin_type'] : 0;
					$price['state']       = 1;

					$table->save($price);

					$dispatcher->trigger('onContentAfterSave', array('com_sellacious.product.price', $table, false));
				}
			}

			if ($this->helper->access->check('product.edit.seller') ||
				($this->helper->access->check('product.edit.seller.own') && $sellerUid == $me->id))
			{
				$table = $this->getTable('ProductSeller');
				$table->load(array('product_id' => $productId, 'seller_uid' => $sellerUid));

				$table->set('seller_uid', $sellerUid);
				$table->set('product_id', $productId);
				$table->set('stock', $stock);

				$table->check();
				$table->store();

				$dispatcher->trigger('onContentAfterSave', array('com_sellacious.product.psx', $table, false));
			}
		}

		return true;
	}

	/**
	 * Method to create a full clone of a product including all variants, images, attachments etc.
	 *
	 * @param   int  $productId  The selected product id
	 *
	 * @return  bool
	 *
	 * @throws  \Exception
	 *
	 * @since   1.5.0
	 */
	public function duplicate($productId)
	{
		$allowDuplicates = $this->helper->config->get('allow_duplicate_products');

		$product = $this->helper->product->getTable();
		$product->load($productId);

		if (!$product)
		{
			throw new Exception(JText::_('COM_SELLACIOUS_PRODUCT_NOT_FOUND'));
		}

		$me = JFactory::getUser();

		$product->set('id', 0);

		if (!$this->helper->access->check('product.edit.basic'))
		{
			// Assign ownership if a new product and the creator cannot add/modify shop (global) owned products
			$product->set('owned_by', $me->id);

			// Check Required Approval for seller product
			if ($this->helper->config->get('seller_product_approve', 0))
			{
				$product->set('state', -1);
			}
		}

		$filter      = array('list.from' => '#__sellacious_product_categories', 'product_id' => $productId);
		$categories  = $this->helper->product->loadObjectList($filter);
		$categoryIds = ArrayHelper::getColumn($categories, 'category_id');

		// Unique Field
		$uniqueField    = null;
		$uniqueFieldGrp = null;

		if (!$allowDuplicates)
		{
			$uniqueSetting = $this->helper->product->getUniqueFieldSetting($categoryIds);
			$uniqueField   = $uniqueSetting->get('product_unique_field', '');

			if (!is_numeric($uniqueField))
			{
				$contexts       = explode('.', $uniqueField);
				$uniqueFieldGrp = 'basic';

				if (isset($contexts[1]) & $contexts[0] == 'seller')
				{
					$uniqueFieldGrp = 'seller';
					$uniqueField    = $contexts[1];
				}
			}

			// Cannot allow duplicates for unique fields
			if ($uniqueField && $uniqueFieldGrp == 'basic' && isset($product->$uniqueField) && $product->$uniqueField)
			{
				$product->$uniqueField = $this->generateUniqueValue($uniqueField, $product->$uniqueField, 'Product', array($uniqueField => $product->$uniqueField));
			}
		}

		$product->check();
		$product->store();

		if (!$product->get('id'))
		{
			throw new Exception(JText::_('COM_SELLACIOUS_PRODUCT_CLONE_FAILED'));
		}

		$this->setState('product.id', $product->get('id'));

		$this->copyMedia('products', $productId, $product->get('id'));
		$this->copyFields('products', $productId, $product->get('id'), $uniqueField);

		// Copy references
		foreach ($categories as $category)
		{
			$category->product_id = $product->get('id');
			$this->_db->insertObject('#__sellacious_product_categories', $category, null);
		}

		$filter = array('list.from' => '#__sellacious_product_physical', 'product_id' => $productId);
		$items  = $this->helper->product->loadObjectList($filter);

		foreach ($items as $item)
		{
			$item->id         = null;
			$item->product_id = $product->get('id');
			$this->_db->insertObject('#__sellacious_product_physical', $item, 'id');
		}

		$filter = array('list.from' => '#__sellacious_eproduct_media', 'product_id' => $productId);
		$items  = $this->helper->product->loadObjectList($filter);

		foreach ($items as $item)
		{
			$emId = $item->id;

			$item->id         = null;
			$item->product_id = $product->get('id');
			$this->_db->insertObject('#__sellacious_eproduct_media', $item, 'id');

			$this->copyMedia('eproduct_media', $emId, $item->id);
		}

		$filter = array('list.from' => '#__sellacious_variants', 'product_id' => $productId);
		$items  = $this->helper->product->loadObjectList($filter);

		foreach ($items as $item)
		{
			$vId = $item->id;

			$item->id         = null;
			$item->product_id = $product->get('id');
			$this->_db->insertObject('#__sellacious_variants', $item, 'id');

			$this->copyMedia('variants', $vId, $item->id);
			$this->copyFields('variants', $vId, $item->id);
		}

		$filter = array('list.from' => '#__sellacious_relatedproducts', 'product_id' => $productId);
		$items  = $this->helper->product->loadObjectList($filter);

		foreach ($items as $item)
		{
			$item->id         = null;
			$item->product_id = $product->get('id');
			$this->_db->insertObject('#__sellacious_relatedproducts', $item, 'id');
		}

		$filter = array('list.from' => '#__sellacious_package_items', 'package_id' => $productId);
		$items  = $this->helper->product->loadObjectList($filter);

		foreach ($items as $item)
		{
			$item->id         = null;
			$item->package_id = $product->get('id');
			$this->_db->insertObject('#__sellacious_package_items', $item, 'id');
		}

		// Copy seller inventory/listings
		$filter = array('list.from' => '#__sellacious_product_sellers', 'product_id' => $productId);

		if (!$this->helper->access->check('product.edit.seller'))
		{
			$filter['seller_uid'] = $this->helper->access->check('product.edit.seller.own') ? $me->id : 0;
		}

		$items  = $this->helper->product->loadObjectList($filter);

		foreach ($items as $psx)
		{
			$psxId = $psx->id;

			$psx->id         = null;
			$psx->product_id = $product->get('id');
			$psx->state      = $psx->seller_uid == $me->id ? 1 : 0;

			if (!$allowDuplicates && $uniqueField && $uniqueFieldGrp == 'seller' && isset($psx->$uniqueField) && $psx->$uniqueField)
			{
				$psx->$uniqueField = $this->generateUniqueValue($uniqueField, $psx->$uniqueField , 'ProductSeller', array($uniqueField => $psx->$uniqueField));
			}

			$this->_db->insertObject('#__sellacious_product_sellers', $psx, 'id');

			$psxTables = array(
				'#__sellacious_physical_sellers',
				'#__sellacious_eproduct_sellers',
				'#__sellacious_package_sellers',
			);

			foreach ($psxTables as $psxTable)
			{
				$filter  = array('list.from' => $psxTable, 'psx_id' => $psxId);
				$subRows = $this->helper->product->loadObjectList($filter);

				foreach ($subRows as $subRow)
				{
					$subRow->id     = null;
					$subRow->psx_id = $psx->id;
					$this->_db->insertObject($psxTable, $subRow, 'id');
				}
			}
		}

		// Copy seller prices
		$filter = array('list.from' => '#__sellacious_product_prices', 'product_id' => $productId);

		if (!$this->helper->access->check('product.edit.seller'))
		{
			$filter['seller_uid'] = $this->helper->access->check('product.edit.seller.own') ? $me->id : 0;
		}

		$prices = $this->helper->product->loadObjectList($filter);

		foreach ($prices as $price)
		{
			$priceId = $price->id;

			$price->id         = null;
			$price->product_id = $product->get('id');
			$this->_db->insertObject('#__sellacious_product_prices', $price, 'id');

			$filter  = array('list.from' => '#__sellacious_productprices_clientcategory_xref', 'product_price_id' => $priceId);
			$subRows = $this->helper->product->loadObjectList($filter);

			foreach ($subRows as $subRow)
			{
				$subRow->id               = null;
				$subRow->product_price_id = $price->id;
				$this->_db->insertObject('#__sellacious_productprices_clientcategory_xref', $subRow, 'id');
			}
		}

		// Copy listing records (ignore old expired ones)
		if ($this->helper->config->get('free_listing'))
		{
			$filter = array('list.from' => '#__sellacious_seller_listing', 'product_id' => $productId, 'state' => 1);

			if (!$this->helper->access->check('product.edit.seller'))
			{
				$filter['seller_uid'] = $this->helper->access->check('product.edit.seller.own') ? $me->id : 0;
			}

			$items  = $this->helper->product->loadObjectList($filter);

			foreach ($items as $item)
			{
				$item->id         = null;
				$item->product_id = $product->get('id');
				$this->_db->insertObject('#__sellacious_seller_listing', $item, 'id');
			}
		}

		$dispatcher = $this->helper->core->loadPlugins();
		$dispatcher->trigger('onContentAfterSave', array('com_sellacious.product', $product, true));

		return $product->get('id');
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
	 * @since   1.5.0
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
	 * Method to copy field values from one record to another
	 *
	 * @param   string  $tableName
	 * @param   int     $recordId
	 * @param   int     $newId
	 * @param   int     $uniqueField
	 *
	 * @return  void
	 *
	 * @since   1.5.0
	 */
	protected function copyFields($tableName, $recordId, $newId, $uniqueField = null)
	{
		$allowDuplicates = $this->helper->config->get('allow_duplicate_products');

		$filter = array(
			'list.from'  => '#__sellacious_field_values',
			'table_name' => $tableName,
			'record_id'  => $recordId,
		);
		$items  = $this->helper->field->loadObjectList($filter);

		foreach ($items as $item)
		{
			$item->id        = null;
			$item->record_id = $newId;

			// Cannot allow duplicates for unique fields
			$value = $item->field_value;

			if (!$allowDuplicates && is_numeric($uniqueField) && $item->field_id == $uniqueField && $value && is_string($value))
			{
				$filters           = array('field_id' => $item->field_id, 'field_value' => $value, 'table_name' => $tableName);
				$item->field_value = $this->generateUniqueValue('field_value', $value, 'FieldValues', $filters);
			}

			$this->_db->insertObject('#__sellacious_field_values', $item, 'id');
		}
	}

	/**
	 * Method to delete one or more records.
	 *
	 * @param   array  &$pks  An array of record primary keys.
	 *
	 * @return  bool  True if successful, false if an error occurs.
	 *
	 * @throws  Exception
	 *
	 * @since   12.2
	 */
	public function delete(&$pks)
	{
		array_walk($pks, 'intval');

		$this->helper->core->loadPlugins();

		$deleted = parent::delete($pks);

		if ($deleted && count($pks))
		{
			// Direct references to products
			$db      = $this->_db;
			$query   = $db->getQuery(true);
			$queries = array();

			$products = $db->getQuery(true)->select('id')->from('#__sellacious_products');

			$queries[] = (string) $query->clear()->delete('#__sellacious_cart')->where('product_id NOT IN (' . $products . ')');
			$queries[] = (string) $query->clear()->delete('#__sellacious_eproduct_media')->where('product_id NOT IN (' . $products . ')');
			$queries[] = (string) $query->clear()->delete('#__sellacious_package_items')->where('product_id NOT IN (' . $products . ')');
			$queries[] = (string) $query->clear()->delete('#__sellacious_package_items')->where('package_id NOT IN (' . $products . ')');
			$queries[] = (string) $query->clear()->delete('#__sellacious_product_categories')->where('product_id NOT IN (' . $products . ')');
			$queries[] = (string) $query->clear()->delete('#__sellacious_product_physical')->where('product_id NOT IN (' . $products . ')');
			$queries[] = (string) $query->clear()->delete('#__sellacious_product_prices')->where('product_id NOT IN (' . $products . ')');
			$queries[] = (string) $query->clear()->delete('#__sellacious_product_queries')->where('product_id NOT IN (' . $products . ')');
			$queries[] = (string) $query->clear()->delete('#__sellacious_product_sellers')->where('product_id NOT IN (' . $products . ')');
			$queries[] = (string) $query->clear()->delete('#__sellacious_ratings')->where('product_id NOT IN (' . $products . ')');
			$queries[] = (string) $query->clear()->delete('#__sellacious_relatedproducts')->where('product_id NOT IN (' . $products . ')');
			$queries[] = (string) $query->clear()->delete('#__sellacious_seller_listing')->where('product_id NOT IN (' . $products . ')');
			$queries[] = (string) $query->clear()->delete('#__sellacious_variants')->where('product_id NOT IN (' . $products . ')');
			$queries[] = (string) $query->clear()->delete('#__sellacious_wishlist')->where('product_id NOT IN (' . $products . ')');

			// Seller references for products
			$psx = $db->getQuery(true)->select('id')->from('#__sellacious_product_sellers');

			$queries[] = (string) $query->clear()->delete('#__sellacious_physical_sellers')->where('psx_id NOT IN (' . $psx . ')');
			$queries[] = (string) $query->clear()->delete('#__sellacious_eproduct_sellers')->where('psx_id NOT IN (' . $psx . ')');
			$queries[] = (string) $query->clear()->delete('#__sellacious_package_sellers')->where('psx_id NOT IN (' . $psx . ')');

			// Variant references
			$variants = $db->getQuery(true)->select('id')->from('#__sellacious_variants');

			$queries[] = (string) $query->clear()->delete('#__sellacious_variant_sellers')->where('variant_id NOT IN (' . $variants . ')');

			// TODO: Images or other media from db as well as filesystem, BUT SHOULD WE?

			// Execute all queries
			foreach ($queries as $query)
			{
				try
				{
					$db->setQuery($query)->execute();
				}
				catch (Exception $e)
				{
				}
			}

			// Multilanguage: if associated, delete the item in the _associations table
			if (JLanguageAssociations::isEnabled())
			{
				foreach ($pks as $pk)
				{
					$db = $this->getDbo();
					$query = $db->getQuery(true)
						->select('COUNT(*) as count, ' . $db->quoteName('as1.assoc_key'))
						->from($db->quoteName('#__sellacious_associations') . ' AS as1')
						->join('LEFT', $db->quoteName('#__sellacious_associations') . ' AS as2 ON ' . $db->quoteName('as1.assoc_key') . ' =  ' . $db->quoteName('as2.assoc_key'))
						->where($db->quoteName('as1.context') . ' = ' . $db->quote('com_sellacious.product'))
						->where($db->quoteName('as1.id') . ' = ' . (int) $pk)
						->group($db->quoteName('as1.assoc_key'));

					$db->setQuery($query);
					$row = $db->loadAssoc();

					if (!empty($row['count']))
					{
						$query = $db->getQuery(true)
							->delete($db->quoteName('#__sellacious_associations'))
							->where($db->quoteName('context') . ' = ' . $db->quote('com_sellacious.product'))
							->where($db->quoteName('assoc_key') . ' = ' . $db->quote($row['assoc_key']));

						if ($row['count'] > 2)
						{
							$query->where($db->quoteName('id') . ' = ' . (int) $pk);
						}

						$db->setQuery($query);
						$db->execute();
					}
				}
			}

			$cleanup = new MediaCleanup;

			$cleanup->execute();
		}

		return $deleted;
	}

	/**
	 * Method to delete one or more products listing
	 *
	 * @param   array  $records  An array of record primary keys
	 *
	 * @return  array  Array of successfully deleted records, false if an error occurs
	 *
	 * @since   2.0.0
	 */
	public function deleteListing($records)
	{
		$db      = $this->getDbo();
		$query   = $db->getQuery(true);
		$deletes = array();

		foreach ($records as $ps)
		{
			try
			{
				$queries = array();

				list($pid, $sid) = $ps;

				$condPS = sprintf('product_id = %d AND seller_uid = %d', $pid, $sid);
				$condVS = sprintf('variant_id IN (SELECT id FROM #__sellacious_variants WHERE product_id = %d) AND seller_uid = %d', $pid, $sid);

				$queries[] = (string) $query->clear()->delete('#__sellacious_variant_sellers')->where($condVS);
				$queries[] = (string) $query->clear()->delete('#__sellacious_product_sellers')->where($condPS);
				$queries[] = (string) $query->clear()->delete('#__sellacious_product_prices')->where($condPS);
				$queries[] = (string) $query->clear()->delete('#__sellacious_seller_listing')->where($condPS);
				$queries[] = (string) $query->clear()->delete('#__sellacious_eproduct_media')->where($condPS);
				$queries[] = (string) $query->clear()->delete('#__sellacious_product_queries')->where($condPS);
				$queries[] = (string) $query->clear()->delete('#__sellacious_ratings')->where($condPS);
				$queries[] = (string) $query->clear()->delete('#__sellacious_wishlist')->where($condPS);
				$queries[] = (string) $query->clear()->delete('#__sellacious_cart')->where($condPS);

				foreach ($queries as $sql)
				{
					$db->setQuery($sql)->execute();
				}

				$deletes[] = $ps;
			}
			catch (Exception $e)
			{
				$this->app->enqueueMessage($e->getMessage(), 'warning');
			}
		}

		try
		{
			$queries = array();
			$condPSX = 'psx_id NOT IN (SELECT id FROM #__sellacious_product_sellers)';

			$queries[] = (string) $query->clear()->delete('#__sellacious_physical_sellers')->where($condPSX);
			$queries[] = (string) $query->clear()->delete('#__sellacious_eproduct_sellers')->where($condPSX);
			$queries[] = (string) $query->clear()->delete('#__sellacious_package_sellers')->where($condPSX);

			foreach ($queries as $sql)
			{
				$db->setQuery($sql)->execute();
			}

			$cleanup = new MediaCleanup;

			$cleanup->execute();
		}
		catch (Exception $e)
		{
			$this->app->enqueueMessage($e->getMessage(), 'warning');
		}

		return $deletes;
	}

	/**
	 * Get a product item including all prices details from given keys
	 *
	 * @param   int  $product_id
	 * @param   int  $variant_id
	 * @param   int  $seller_uid
	 *
	 * @return  object
	 *
	 * @since   1.0.0
	 */
	public function getProduct($product_id, $variant_id, $seller_uid)
	{
		return (object) compact(get_defined_vars());
	}

	/**
	 * Add price slots for a given product and a seller
	 *
	 * @param   int    $product_id
	 * @param   int    $seller_uid
	 * @param   mixed  $prices
	 *
	 * @return  array  The prices array with updated record ids
	 *
	 * @throws  Exception
	 *
	 * @since   1.0.0
	 */
	public function setPrices($product_id, $seller_uid, $prices)
	{
		$return = array();

		$pks   = array();
		$db    = JFactory::getDbo();
		$query = $db->getQuery(true);

		$cat_map_remove = array();

		foreach ($prices as $price)
		{
			// Save price info
			$price['product_id']  = $product_id;
			$price['seller_uid']  = $seller_uid;
			$price['margin_type'] = isset($price['margin_type']) ? $price['margin_type'] : 0;
			$price['state']       = 1;

			$table = $this->getTable('ProductPrices');

			$table->save($price);

			$pp_id = $table->get('id');
			$pks[] = $pp_id;

			// Save client category map
			$client_cats = empty($price['cat_id']) ? array() : (array) $price['cat_id'];

			// Set remaining client category mappings for removal
			if (count($client_cats))
			{
				foreach ($client_cats as $cat_id)
				{
					$xref = array('product_price_id' => $pp_id, 'cat_id' => $cat_id);

					$catTable = $this->getTable('ProductPricesClientCategoryXref');
					$catTable->load($xref);
					$catTable->save($xref);
				}

				$cond = sprintf('(product_price_id = %d AND cat_id NOT IN (%s))', $pp_id, implode(', ', $db->q($client_cats)));
			}
			else
			{
				$cond = 'product_price_id = ' . (int) $pp_id;
			}

			$cat_map_remove[] = $cond;

			$price['id'] = $pp_id;

			$return[] = $price;
		}

		// Remove remaining price records, they are marked deleted by the user.
		$query->clear()
			->delete('#__sellacious_product_prices')
			->where('product_id = ' . $db->q($product_id))
			->where('seller_uid = ' . $db->q($seller_uid));

		if (count($pks))
		{
			$query->where('id NOT IN (' . implode(',', $db->q($pks)) . ')');
		}

		$db->setQuery($query);
		$db->execute();

		// Remove expired client category mappings
		$cat_map_remove[] = 'product_price_id NOT IN (SELECT id FROM #__sellacious_product_prices)';

		$catTable = $this->getTable('ProductPricesClientCategoryXref');
		$query->clear()->delete($db->qn($catTable->getTableName()))->where($cat_map_remove, 'OR');

		$db->setQuery($query);
		$db->execute();

		return $return;
	}

	/**
	 * Set selling state for product - seller
	 *
	 * @param   int[]  $productIds
	 * @param   int[]  $sellerUids
	 * @param   int    $value
	 *
	 * @return  int[]
	 *
	 * @since   1.0.0
	 */
	public function setSelling($productIds, $sellerUids, $value)
	{
		$pks = array();

		foreach ($productIds as $i => $productId)
		{
			$sellerUid = $sellerUids[$i];

			try
			{
				if (!$this->helper->product->count(array('id' => $productId)))
				{
					throw new Exception(JText::_('COM_SELLACIOUS_PRODUCT_NOT_FOUND'));
				}

				$table = $this->getTable('ProductSeller');
				$data  = array('product_id' => $productId, 'seller_uid' => $sellerUid);

				$table->load($data);
				$table->bind($data);
				$table->set('state', $value);

				// If the listing is free extend it automatically for a year
				$this->helper->listing->extend($productId, $sellerUid, 0, 365, true);

				$table->store();

				$pks[] = $table->get('id');
			}
			catch (Exception $e)
			{
				JLog::add($e->getMessage(), JLog::WARNING, 'jerror');
			}
		}

		$dispatcher = $this->helper->core->loadPlugins();
		$dispatcher->trigger($this->event_change_state, array('com_sellacious.product.selling', $pks, $value));

		return $pks;
	}

	/**
	 * Method to save the form data
	 *
	 * @param   array  $data  The form data
	 *
	 * @return  mixed
	 *
	 * @since   1.6.0
	 *
	 * @throws  Exception
	 */
	public function setLanguage($data)
	{
		$productId  = $data['id'];
		$lang       = $data['language'];
		$redirectTo = 0;

		if ($productId)
		{
			$redirectTo = $productId;

			$associations = $this->helper->product->getAssociations(
				'com_sellacious',
				'#__sellacious_products',
				'com_sellacious.product',
				$productId,
				'id',
				'alias',
				true
			);

			if (!empty($associations))
			{
				$association = array_values(array_filter($associations, function ($item) use ($lang) {
					return ($item->language == $lang);
				}));

				if (!empty($association))
				{
					$id = explode(':', $association[0]->id);
					$redirectTo = $id[0];
					return $redirectTo;
				}
			}

			$table = $this->getTable('Product');
			$table->load($productId);

			$oldLang = $table->language;

			if (empty($oldLang) || $oldLang == '*')
			{
				$table->bind(array('language' => $lang));

				$table->check();
				$table->store();

				$cache = new ProductsCacheBuilder;

				$cache->rebuild($productId);
			}
			else
			{
				$redirectTo = 0;
			}
		}

		return $redirectTo;
	}

	/**
	 * Remove the selected e-product media record and its referenced media files. With (optionally) extra check for the product_id.
	 *
	 * @param   int  $id
	 *
	 * @return  bool
	 *
	 * @throws  Exception
	 *
	 * @since   1.0.0
	 */
	public function removeEProductMedia($id)
	{
		$table = $this->getTable('EProductMedia');

		// Since deletion is only allowed when editing a certain product for a seller so session match is optimum for security.
		$product_id = $this->app->getUserState('com_sellacious.edit.product.id', null);
		$seller_uid = $this->app->getUserState('com_sellacious.edit.product.seller_uid', null);

		$table->load($id);

		if (!$table->get('id'))
		{
			throw new Exception(JText::_('COM_SELLACIOUS_PRODUCT_EPRODUCT_MEDIA_NOT_FOUND'));
		}

		if ($table->get('product_id') != $product_id || $table->get('seller_uid') != $seller_uid)
		{
			throw new Exception(JText::_('COM_SELLACIOUS_PRODUCT_EPRODUCT_MEDIA_NOT_AUTHORISED'));
		}

		$filter = array(
			'list.select' => 'a.id',
			'table_name'  => 'eproduct_media',
			'context'     => array('media', 'sample'),
			'record_id'   => $table->get('id'),
		);

		$files = $this->helper->media->loadColumn($filter);

		// Attempt to remove linked files
		$this->helper->media->remove($files);

		return $table->delete();
	}

	/**
	 * Method to validate if there is any existing product for the unique field (if there is any)
	 *
	 * @param   Registry  $registry   Object containing product data
	 * @param   int       $productId  Product Id
	 *
	 * @throws  Exception
	 *
	 * @since   2.0.0
	 */
	protected function validateUniqueField(Registry $registry, $productId)
	{
		$basic          = $registry->extract('basic');
		$seller         = $registry->extract('seller');
		$categories     = (array)$registry->get('categories');
		$specifications = $registry->extract('specifications');
		$specifications = $specifications ? $specifications->toArray() : null;

		$uniqueSetting = $this->helper->product->getUniqueFieldSetting($categories);
		$uniqueField   = $uniqueSetting->get('product_unique_field', '');

		if (is_numeric($uniqueField) && $specifications)
		{
			foreach ($specifications as $fieldId => $value)
			{
				if ($fieldId == $uniqueField && $value)
				{
					$this->helper->product->validateUniqueField($categories, $value, $productId);
					break;
				}
			}
		}
		elseif ($uniqueField)
		{
			$contexts = explode('.', $uniqueField);

			if (isset($contexts[1]) & $contexts[0] == 'seller')
			{
				$value = $seller->get($uniqueField);
			}
			else
			{
				$value = $basic->get($uniqueField);
			}

			$this->helper->product->validateUniqueField($categories, $value, $productId);
		}
	}

	/**
	 * Method to check if the unique field is empty (if there is one)
	 *
	 * @param   Registry  $registry     Product data
	 * @param   mixed     $uniqueField  The field which will have a unique value for a product
	 *
	 * @return  bool
	 *
	 * @since   2.0.0
	 */
	protected function isUniqueFieldEmpty(Registry $registry, $uniqueField)
	{
		$me         = JFactory::getUser();
		$seller_uid = $registry->get('seller_uid');

		if (is_numeric($uniqueField))
		{
			$value = $registry->get('specifications.' . $uniqueField);

			if (!$value)
			{
				return true;
			}
		}
		elseif ($uniqueField)
		{
			$contexts = explode('.', $uniqueField);

			if (isset($contexts[1]) & $contexts[0] == 'seller')
			{
				$sellerEdit = $this->helper->access->check('product.edit.seller') ||
				              ($this->helper->access->check('product.edit.seller.own') && $seller_uid == $me->id);

				if (!$sellerEdit)
				{
					// if seller information cannot be edited, we return false by default
					return false;
				}

				$value = $registry->get('seller.' . $contexts[1]);

				if (!$value)
				{
					return true;
				}
			}
			else
			{
				$value = $registry->get('basic.' . $uniqueField);

				if (!$value)
				{
					return true;
				}
			}
		}
		
		return false;
	}

	/**
	 * Method to process unique field
	 *
	 * @param  JForm  $form         The product form
	 * @param  mixed  $uniqueField  The field which will have a unique value for a product
	 * @param  bool   $isNew        Whether the product is new
	 *
	 * @since  2.0.0
	 */
	protected function processUniqueField(JForm $form, $uniqueField, $isNew = false)
	{
		$group = null;

		if (is_numeric($uniqueField))
		{
			$group = 'specifications';
		}
		elseif ($uniqueField)
		{
			$contexts    = explode('.', $uniqueField);
			$group       = (isset($contexts[1]) & $contexts[0] == 'seller') ? 'seller' : 'basic';
			$uniqueField = isset($contexts[1]) ? $contexts[1] : $uniqueField;
		}

		if ($uniqueField && $group)
		{
			$form->setFieldAttribute($uniqueField, 'required', true, $group);

			// Add class to field
			$classes   = explode(' ', $form->getFieldAttribute($uniqueField, 'class', null, $group));
			$classes[] = 'unique_field';

			if (!$isNew)
			{
				$classes[] = 'validate-unique';
			}

			$form->setFieldAttribute($uniqueField, 'class', implode(' ', $classes), $group);
			$form->setFieldAttribute($uniqueField, 'data-field', $uniqueField, $group);
		}
	}

	/**
	 * Method to generate a unique value for a field in table
	 *
	 * @param   string  $field      The field for which the unique value has to be generated
	 * @param   string  $value      The value to check so thats its not duplicated
	 * @param   string  $tableName  Name of the table for which the field has to be checked
	 * @param   array   $filters    Keys to load in table
	 *
	 * @return  string  The unique value
	 *
	 * @throws  Exception
	 *
	 * @since   2.0.0
	 */
	protected function generateUniqueValue($field, $value, $tableName, $filters)
	{
		$table = $this->getTable($tableName);

		if ($table && $filters && is_string($value))
		{
			while ($table->load($filters))
			{
				if ($value === $table->get($field))
				{
					$value = StringHelper::increment($value);
				}

				$filters[$field] = $value;
			}
		}

		return $value;
	}
}
