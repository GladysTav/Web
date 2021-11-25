<?php
/**
 * @version     2.0.0
 * @package     Sellacious Filters Module
 *
 * @copyright   Copyright (C) 2012-2020 Bhartiy Web Technologies. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Izhar Aazmi <info@bhartiy.com> - http://www.bhartiy.com
 */
namespace Sellacious\ProductsFilter;

// no direct access
defined('_JEXEC') or die;

use Joomla\Utilities\ArrayHelper;
use stdClass;

/**
 * Filter Class
 *
 * @package  Sellacious\ProductsFilter
 *
 * @since    2.0.0
 */
class Attributes extends AbstractFilter
{
	/**
	 * Fields list for filter module
	 *
	 * @var   stdClass[]
	 *
	 * @since   2.0.0
	 */
	protected $fields = array();

	/**
	 * The category id filter value, required to find relevant specification fields
	 *
	 * @var   int
	 *
	 * @since   2.0.0
	 */
	protected $catId;

	/**
	 * Method to compute and assign the values to be cached for the filter to work
	 *
	 * @return  void
	 *
	 * @since   2.0.0
	 */
	public function buildCacheData()
	{

	}

	/**
	 * Method to populate the state with the filter values submitted
	 *
	 * @return  void
	 *
	 * @since   2.0.0
	 */
	public function populateState()
	{
		// Model will handle
	}

	/**
	 * Method to apply the filter to search query using the state with values submitted
	 *
	 * @return  void
	 *
	 * @since   2.0.0
	 */
	public function addFilter()
	{
		$loader = $this->module->getLoader();
		$values = $this->module->getState()->get('filter.fields', array());
		$fields = $this->getFields();

		foreach ($fields as $field)
		{
			$key   = sprintf('f%d', $field->id);
			$value = ArrayHelper::getValue($values, $key, array(), 'array');

			if (count($value))
			{
				$loader->filterJsonValue('specifications', $key, $value);
			}
		}
	}

	/**
	 * Get required data for the filter form, if any
	 *
	 * @return  array
	 *
	 * @since   2.0.0
	 */
	protected function getFilterFormData()
	{
		return array(
			'fields' => $this->getFilters(true),
		);
	}

	/**
	 * Get fully qualified filter list; i.e. all values, available values and selected values included
	 *
	 * @param   bool  $availability  Whether to check availability for each filter value
	 *
	 * @return  stdClass[]
	 *
	 * @since   2.0.0
	 */
	protected function getFilters($availability = false)
	{
		$fields = $this->getFields();
		$values = (array) $this->module->getState()->get('filter.fields');

		// We only have to get disable/enable choice for custom filters
		foreach ($fields as $field)
		{
			$selected = ArrayHelper::getValue($values, sprintf('f%d', $field->id), array(), 'array');

			$field->selected  = (array) $selected;
			$field->available = $availability ? $this->getAvailable($field) : $field->choices;

			foreach ($field->choices as $chi => $ch)
			{
				$choice = new stdClass;

				$choice->value    = $ch;
				$choice->disabled = !in_array($ch, $field->available);
				$choice->selected = !$choice->disabled && in_array($choice->value, $field->selected);

				$field->choices[$chi] = $choice;
			}
		}

		return $fields;
	}

	/**
	 * Get a list of choices having at least one product for given specification field
	 *
	 * @param   stdClass  $field
	 *
	 * @return  string[]
	 *
	 * @since   2.0.0
	 */
	protected function getAvailable($field)
	{
		// Todo: Calculate

		return $field->choices;
	}

	/**
	 * Get the list of relevant specification fields
	 *
	 * @return  stdClass[]
	 *
	 * @since   2.0.0
	 */
	protected function getFields()
	{
		$catId = (int) $this->module->getState()->get('filter.category_id', 1);

		if ($this->catId !== $catId)
		{
			$this->catId  = $catId;
			$this->fields = $this->helper->category->getFilterFields($catId);
		}

		return $this->fields;
	}
}
