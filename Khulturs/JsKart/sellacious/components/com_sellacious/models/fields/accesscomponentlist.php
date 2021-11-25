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

JFormHelper::loadFieldClass('List');

/**
 * Form Field class.
 *
 * @since   2.0.0
 */
class JFormFieldAccessComponentList extends JFormFieldList
{
	/**
	 * The field type.
	 *
	 * @var   string
	 *
	 * @since   2.0.0
	 */
	protected $type = 'AccessComponentList';

	/**
	 * Method to get the field options.
	 *
	 * @return  array  The field option objects.
	 *
	 * @since   2.0.0
	 */
	protected function getOptions()
	{
		$options    = array();
		$components = JComponentHelper::getComponents();

		foreach ($components as $component)
		{
			if (is_file(JPATH_BASE . '/components/' . $component->option . '/access.xml'))
			{
				JFactory::getLanguage()->load($component->option . '.sys', JPATH_BASE . '/components/' . $component->option);
				JFactory::getLanguage()->load($component->option . '.sys', JPATH_BASE);

				$options[] = JHtml::_('select.option', $component->option, JText::_($component->option));
			}
		}

		return array_merge(parent::getOptions(), $options);
	}
}
