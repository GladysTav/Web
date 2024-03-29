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

JFormHelper::loadFieldClass('list');

/**
 * Form Field class for the Joomla Framework.
 *
 * @package     Joomla.Administrator
 * @subpackage  com_sellacious
 *
 * @since       1.6
 */
class JFormFieldUnitGroup extends JFormFieldList
{
	/**
	 * The field type.
	 *
	 * @var  string
	 */
	protected $type = 'unitGroup';

	/**
	 * Method to get the field options.
	 *
	 * @return  array  The field option objects.
	 * @since   1.6
	 */
	protected function getOptions()
	{
		$helper  = SellaciousHelper::getInstance();
		$groups  = $helper->unit->getGroups();
		$options = array();

		if (is_array($groups))
		{
			$options = array_combine($groups, array_map('ucwords', $groups));
		}

		return array_merge(parent::getOptions(), $options);
	}
}
