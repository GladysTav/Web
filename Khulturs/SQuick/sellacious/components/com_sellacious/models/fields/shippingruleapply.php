<?php
/**
 * @version     2.0.0
 * @package     sellacious
 *
 * @copyright   Copyright (C) 2012-2020 Bhartiy Web Technologies. All rights reserved.
 * @license     SPL Sellacious Private License; see http://www.sellacious.com/spl.html
 * @author      Aditya Chakraborty <info@bhartiy.com> - http://www.bhartiy.com
 */
// no direct access.
use Joomla\CMS\Form\Form;

defined('_JEXEC') or die;

JFormHelper::loadFieldClass('Radio');

/**
 * Apply shipping rule radio field
 *
 * @since    2.0.0
 */
class JFormFieldShippingRuleApply extends JFormFieldRadio
{
	/**
	 * The field type.
	 *
	 * @var   string
	 *
	 * @since  2.0.0
	 */
	protected $type = 'ShippingRuleApply';

	/**
	 * @var  SellaciousHelper
	 *
	 * @since  2.0.0
	 */
	protected $helper;

	/**
	 * Method to instantiate the form field object.
	 *
	 * @param   Form  $form  The form to attach to the form field object.
	 *
	 * @since   2.0.0
	 */
	public function __construct($form = null)
	{
		parent::__construct($form);

		$this->helper = SellaciousHelper::getInstance();
	}

	/**
	 * Method to attach a JForm object to the field.
	 *
	 * @param   \SimpleXMLElement  $element  The SimpleXMLElement object representing the `<field>` tag for the form field object.
	 * @param   mixed              $value    The form field value to validate.
	 * @param   string             $group    The field name group control value. This acts as as an array container for the field.
	 *                                       For example if the field has name="foo" and the group value is set to "bar" then the
	 *                                       full field name would end up being "bar[foo]".
	 *
	 * @return  boolean  True on success.
	 *
	 * @since   2.0.0
	 */
	public function setup(\SimpleXMLElement $element, $value, $group = null)
	{
		if (parent::setup($element, $value, $group))
		{
			$this->value   = !isset($element['value']) && $this->value == '' ? 0 : $this->value;
			$itemisedShip  = $this->helper->config->get('itemised_shipping', SellaciousHelperShipping::SHIPPING_SELECTION_PRODUCT);
			$selectProduct = $this->helper->config->get('product_select_shipping');

			if ($itemisedShip == SellaciousHelperShipping::SHIPPING_SELECTION_SELLER && !$selectProduct)
			{
				$this->value = 1;
			}
		}

		return true;
	}

	/**
	 * Method to get a list of options for a list input.
	 *
	 * @return  array  An array of JHtml options.
	 *
	 * @since   2.0.0
	 */
	protected function getOptions()
	{
		$options   = array();
		$options[] = JHtml::_('select.option', 1, JText::_('COM_SELLACIOUS_SHIPPINGRULE_FIELD_APPLY_ON_ALL_PRODUCTS'));

		$itemisedShip  = $this->helper->config->get('itemised_shipping', SellaciousHelperShipping::SHIPPING_SELECTION_PRODUCT);
		$selectProduct = $this->helper->config->get('product_select_shipping');

		if (!($itemisedShip == SellaciousHelperShipping::SHIPPING_SELECTION_SELLER && !$selectProduct))
		{
			$options[] = JHtml::_('select.option', 0, JText::_('COM_SELLACIOUS_SHIPPINGRULE_FIELD_APPLY_ON_SELECTED_PRODUCTS'));
		}

		return array_merge(parent::getOptions(), $options);
	}
}
