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

JFormHelper::loadFieldClass('GroupedList');

/**
 * @class  JFormFieldTemplateContext
 *
 * @since  1.7.0
 */
class JFormFieldTemplateContext extends JFormFieldGroupedList
{
	/**
	 * Method to get the custom field options.
	 * Use the query attribute to supply a query to generate the list.
	 *
	 * @return  array  The field option objects.
	 *
	 * @since   1.7.0
	 */
	protected function getGroups()
	{
		$db     = JFactory::getDbo();
		$select = $db->getQuery(true);

		$select->select('DISTINCT context')
			->from('#__sellacious_viewtemplates')
			->order('context ASC');
		$contexts = $db->setQuery($select)->loadColumn();

		JPluginHelper::importPlugin('sellacious');

		$assoc      = array();
		$dispatcher = JEventDispatcher::getInstance();
		$dispatcher->trigger('onFetchTemplateContext', array('com_sellacious.viewtemplate', &$assoc));

		$groups      = array();
		$lblActive   = JText::_('COM_SELLACIOUSTEMPLATES_TEMPLATE_ACTIVE_LABEL');
		$lblInactive = JText::_('COM_SELLACIOUSTEMPLATES_TEMPLATE_INACTIVE_LABEL');

		foreach ($assoc as $key => $text)
		{
			$groups[$lblActive][] = JHtml::_('select.option', $key, $text);
		}

		$active = array_keys($assoc);

		foreach ($contexts as $context)
		{
			if (!in_array($context, $active))
			{
				$label = ucwords(str_replace(array('_', '.'), array(' ', ' - '), $context));

				$groups[$lblInactive][] = JHtml::_('select.option', $context, $label);
			}
		}

		$groupsE = parent::getGroups();

		$groups = array_merge($groupsE, $groups);

		return $groups;
	}
}
