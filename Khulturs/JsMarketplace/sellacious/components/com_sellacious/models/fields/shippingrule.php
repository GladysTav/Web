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
defined('_JEXEC') or die;

JFormHelper::loadFieldClass('List');

/**
 * Shipping rules list field class
 *
 * @since   1.7.0
 */
class JFormFieldShippingRule extends JFormFieldList
{
	/**
	 * The field type.
	 *
	 * @var   string
	 *
	 * @since   1.7.0
	 */
	protected $type = 'ShippingRule';

	/**
	 * Method to get the field input markup for a generic list.
	 * Use the multiple attribute to enable multiselect.
	 *
	 * @return  string  The field input markup.
	 *
	 * @since   1.7.0
	 */
	protected function getInput()
	{
		// Get the field options.
		$options = (array) $this->getOptions();

		if (empty($options))
		{
			return null;
		}

		return parent::getInput();
	}

	/**
	 * Method to get a list of options for a list input.
	 *
	 * @return  array  An array of JHtml options.
	 *
	 * @since   1.7.0
	 */
	protected function getOptions()
	{
		$helper  = SellaciousHelper::getInstance();
		$me      = JFactory::getUser();
		$options = array();

		if (!$helper->seller->is() && !JFactory::getUser()->authorise('core.admin'))
		{
			$rules = array();
		}
		else
		{

			$filters = array(
				'state'                 => 1,
				'apply_on_all_products' => 0,
			);

			if (!$me->authorise('core.admin'))
			{
				$filters['owned_by'] = $me->id;
			}

			$rules = $helper->shippingRule->loadObjectList($filters);
		}

		foreach ($rules as $rule)
		{
			$options[] = JHtml::_('select.option', $rule->id, JText::_($rule->title));
		}

		return $options;
	}
}
