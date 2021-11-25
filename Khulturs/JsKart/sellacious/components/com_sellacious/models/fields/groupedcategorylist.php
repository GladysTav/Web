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

JFormHelper::loadFieldClass('GroupedList');

/**
 * Form field class for the categories list
 *
 * @since  1.6
 */
class JFormFieldGroupedCategoryList extends JFormFieldGroupedList
{
	/**
	 * The field type.
	 *
	 * @var   string
	 */
	protected $type = 'groupedCategoryList';

	/**
	 * Method to get the field options.
	 *
	 * @return   array  The field option objects.
	 * @since    1.6
	 */
	protected function getGroups()
	{
		try
		{
			$helper = SellaciousHelper::getInstance();
		}
		catch (Exception $e)
		{
			return array();
		}

		$db     = JFactory::getDbo();
		$groups = array();

		$allTypes = $helper->category->getTypes(true);

		if ($types = (string) $this->element['types'])
		{
			$types = explode('|', $types);
		}
		else
		{
			$types = array_keys($allTypes);
		}

		$d = $this->element['exclude'];

		foreach ($types as $type)
		{
			if (isset($allTypes[$type]))
			{
				$typeName = $allTypes[$type];

				$filter = array(
					'list.select' => 'a.id, a.title, a.type',
					'list.where'  => array('a.state = 1', 'a.level > 0', 'a.type = ' . $db->q($type)),
				);

				$options = array();

				$items = $helper->category->loadObjectList($filter);

				foreach ($items as $item)
				{
					$level     = ($item->level > 1) ? ('|' . str_repeat('&mdash;', $item->level - 1) . ' ') : '';
					$options[] = JHtml::_('select.option', $item->id, $level . $item->title, 'value', 'text', $item->id == $d);
				}

				$groups[$typeName] = $options;
			}
		}

		if ($d == 1)
		{
			$el = $this->element->xpath('option[@value="1"]');

			$el[0]['disabled'] = 'true';
		}

		return array_merge(parent::getGroups(), $groups);
	}
}
