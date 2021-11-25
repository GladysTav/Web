<?php
/**
 * @version     2.0.0
 * @package     sellacious
 *
 * @copyright   Copyright (C) 2012-2020 Bhartiy Web Technologies. All rights reserved.
 * @license     SPL Sellacious Private License; see http://www.sellacious.com/spl.html
 * @author      Aditya Chakraborty <info@bhartiy.com> - http://www.bhartiy.com
 */
// no direct access
defined('_JEXEC') or die;

use Joomla\Registry\Registry;

JFormHelper::loadFieldClass('List');

/**
 * Field to map import template columns in template editor
 *
 * @since   2.0.0
 */
class JFormFieldProductUniqueKey extends JFormFieldList
{
	/**
	 * The form field type.
	 *
	 * @var    string
	 *
	 * @since  2.0.0
	 */
	public $type = 'ProductUniqueKey';

	/**
	 * Method to get the field options.
	 *
	 * @return  array  The field option objects.
	 *
	 * @throws  Exception
	 *
	 * @since   2.0.0
	 */
	protected function getOptions()
	{
		$helper         = SellaciousHelper::getInstance();
		$allowDuplicate = $helper->config->get('allow_duplicate_products');

		$options    = array();
		$params     = new Registry($this->form->getValue('params'));
		$scope      = $params->get('product_scope', 'global');
		$category   = $params->get('product_category');
		$categories = $scope == 'global' ? array() : array($category);

		$mapping = array(
			'local_sku'        => 'product_sku',
			'manufacturer_sku' => 'mfg_assigned_sku',
		);

		$uniqueFieldSetting = $helper->product->getUniqueFieldSetting($categories);
		$uniqueField        = $uniqueFieldSetting->get('product_unique_field', '');
		$uniqueField        = is_numeric($uniqueField) ? $uniqueField : (array_key_exists($uniqueField, $mapping) ? $mapping[$uniqueField] : null);

		if ($allowDuplicate || !$uniqueField)
		{
			if ($helper->access->checkAny(array('basic', 'basic.own'), 'product.edit.'))
			{
				$options[] = JHtml::_('select.option', 'product_title', JText::_('PLG_SYSTEM_SELLACIOUSIMPORTER_PRODUCT_PRODUCT_TITLE_OPTION_LABEL'));
				$options[] = JHtml::_('select.option', 'product_unique_alias', JText::_('PLG_SYSTEM_SELLACIOUSIMPORTER_PRODUCT_PRODUCT_UNIQUE_ALIAS_OPTION_LABEL'));
				$options[] = JHtml::_('select.option', 'product_sku', JText::_('PLG_SYSTEM_SELLACIOUSIMPORTER_PRODUCT_PRODUCT_SKU_OPTION_LABEL'));
				$options[] = JHtml::_('select.option', 'mfg_assigned_sku', JText::_('PLG_SYSTEM_SELLACIOUSIMPORTER_PRODUCT_PRODUCT_MFG_SKU_OPTION_LABEL'));
			}

			if ($helper->access->checkAny(array('seller', 'seller.own'),'product.edit.'))
			{
				$options[] = JHtml::_('select.option', 'seller_sku', JText::_('PLG_SYSTEM_SELLACIOUSIMPORTER_PRODUCT_PRODUCT_SELLER_SKU_OPTION_LABEL'));
			}
		}
		else
		{
			if (is_numeric($uniqueField))
			{
				$specsField  = $helper->field->getItem($uniqueField);
				$uniqueField = 'spec_' . $uniqueField . '_' . preg_replace('/[^0-9a-z]+/i', '_', $specsField->title);
			}

			$options[] = JHtml::_('select.option', $uniqueField, strtoupper($uniqueField));
		}

		return array_merge(parent::getOptions(), $options);
	}
}
