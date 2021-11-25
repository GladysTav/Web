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
	 * Use the multiple attribute to enable multi-select.
	 *
	 * @return  string  The field input markup.
	 *
	 * @throws  Exception
	 *
	 * @since   1.7.0
	 */
	protected function getInput()
	{
		$options = (array) $this->getOptions();

		return $options ? parent::getInput() : '';
	}

	/**
	 * Method to get a list of options for a list input
	 *
	 * @return  array  An array of JHtml options
	 *
	 * @throws  Exception
	 *
	 * @since   1.7.0
	 */
	protected function getOptions()
	{
		$me      = JFactory::getUser();
		$helper  = SellaciousHelper::getInstance();
		$options = array();

		if ($helper->access->check('shippingrule.list'))
		{
			$ownerId = (int) $this->element['owner_id'] ?: (int) $me->id;
		}
		elseif ($helper->access->check('shippingrule.list.own'))
		{
			$ownerId = (int) $me->id;
		}
		else
		{
			return array();
		}

		$filters = array(
			'state'                 => 1,
			'apply_on_all_products' => 0,
			'list.where'            => array('(a.owned_by = ' . $ownerId . ' OR a.owned_by = 0)'),
		);

		$rules = $helper->shippingRule->loadObjectList($filters);

		foreach ($rules as $rule)
		{
			$helper->translation->translateRecord($rule, 'sellacious_shippingrule');

			$options[] = JHtml::_('select.option', $rule->id, JText::_($rule->title));
		}

		return $options;
	}
}
