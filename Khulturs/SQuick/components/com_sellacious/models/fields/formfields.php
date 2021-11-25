<?php
/**
 * @version     2.0.0
 * @package     sellacious
 *
 * @copyright   Copyright (C) 2012-2020 Bhartiy Web Technologies. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Izhar Aazmi <info@bhartiy.com> - http://www.bhartiy.com
 */
// no direct access.
defined('_JEXEC') or die;

use Joomla\Utilities\ArrayHelper;

JFormHelper::loadFieldClass('list');

/**
 * Form Field class for the Joomla Framework.
 *
 * @since   1.1.0
 */
class JFormFieldFormFields extends JFormFieldList
{
	/**
	 * The field type.
	 *
	 * @var  string
	 *
	 * @since   1.1.0
	 */
	protected $type = 'FormFields';

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
		if ($value && is_object($value))
		{
			$value = (array) $value;
		}

		return parent::setup($element, $value, $group);
	}

	/**
	 * Method to get the field options.
	 *
	 * @return  array  The field option objects.
	 *
	 * @since   1.1.0
	 */
	protected function getOptions()
	{
		$db      = JFactory::getDbo();
		$query   = $db->getQuery(true);
		$options = array();

		$tags         = (string) $this->element['tags'] == 'true';
		$showAll      = (string) $this->element['showall'] == 'true';
		$context      = trim((string) $this->element['context'], ' ,');
		$contexts     = $context ? explode(',', $context) : array();
		$includeTypes = trim((string) $this->element['limit_types'], ' ,');
		$includeTypes = $includeTypes ? explode(',', $includeTypes) : array();
		$global       = (string) $this->element['global'] == 'true';

		$query->select($db->qn(array('a.id', 'a.title', 'a.type')))
			->from($db->qn('#__sellacious_fields', 'a'))
			->where('a.level > 0');

		$query->select($db->qn('g.title', 'group_title'))
			->join('left', '#__sellacious_fields g ON g.id = a.parent_id AND a.parent_id > 1')
			->group('a.id');

		if (!$showAll)
		{
			$query->where('a.state = 1');
		}

		if (count($contexts))
		{
			$where = '((';
			
			if (!$showAll)
			{
				$where .= 'g.state = 1 AND ';
			}
			
			$where .= ' g.context IN (' . implode(', ', $db->q($contexts)) . ')) ';
			$where .= 'OR (a.type != ' . $db->q('fieldgroup') . ' AND a.context IN (' . implode(', ', $db->q($contexts)) . ')))';
			
			$query->where($where);
		}

		// Limit field types
		if (count($includeTypes))
		{
			$query->where('a.type IN (' . implode(',', $db->q($includeTypes)) . ')');
		}

		$query->where('a.global = ' . (int) $global);

		$query->order('g.title, a.title');

		$db->setQuery($query);

		try
		{
			$helper  = SellaciousHelper::getInstance();
			$results = $db->loadObjectList();

			foreach ($results as $result)
			{
				$option        = new stdClass;
				$option->value = $result->id;
				
				if ($result->group_title)
				{
					$format       = '%s / %s (%s)';
					$option->text = sprintf($format, $result->group_title, $result->title, $result->type);
				}
				else
				{
					$format       = '%s (%s)';
					$option->text = sprintf($format, $result->title, $result->type);
				}

				if ($tags)
				{
					$tags_o = $helper->field->getTags($result->id);

					if (count($tags_o))
					{
						$titles = ArrayHelper::getColumn($tags_o, 'tag_title');

						$option->text = sprintf('%s : %s', implode(', ', $titles), $option->text);
					}
				}

				$options[] = $option;
			}
		}
		catch (Exception $e)
		{
			JLog::add($e->getMessage(), JLog::WARNING, 'jerror');

			$options = array();
		}

		$options = ArrayHelper::sortObjects($options, 'text');

		// Merge any additional options in the XML definition.
		$options = array_merge(parent::getOptions(), $options);

		return $options;
	}
}
