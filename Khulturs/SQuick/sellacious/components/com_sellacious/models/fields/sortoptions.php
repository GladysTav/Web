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

/**
 * Sort Options form field class for the Sellacious
 *
 * @since   2.0.0
 */
class SellaciousFormFieldSortOptions extends JFormField
{
	/**
	 * The field type
	 *
	 * @var  string
	 *
	 * @since   2.0.0
	 */
	protected $type = 'SortOptions';

	/**
	 * Name of the layout being used to render the field
	 *
	 * @var    string
	 *
	 * @since  2.0.0
	 */
	protected $layout = 'sellacious.form.field.sortoptions';

	/**
	 * Method to get the field options.
	 *
	 * @return  string  The field option objects.
	 *
	 * @since   2.0.0
	 */
	protected function getInput()
	{
		JHtml::_('jquery.framework');
		JHtml::_('script', 'sellacious/field.sort-options.js', array('relative' => true, 'version' => time()));
		JHtml::_('stylesheet', 'sellacious/field.sort-options.css', array('relative' => true, 'version' => time()));

		return parent::getInput();
	}

	/**
	 * Method to get the data to be passed to the layout for rendering.
	 *
	 * @return  array
	 *
	 * @since   2.0.0
	 */
	protected function getLayoutData()
	{
		$data = parent::getLayoutData();

		$data['columns'] = $this->getOptions();

		return $data;
	}

	/**
	 * Method to get the field options
	 *
	 * @return  array  The field option objects
	 *
	 * @since   2.0.0
	 */
	protected function getOptions()
	{
		$columns = array();
		$options = array();

		// Collect all columns
		foreach ($this->element->xpath('column') as $column)
		{
			$col = new stdClass;

			$col->name    = (string) $column['name'];
			$col->label   = (string) $column['label'];
			$col->options = array();

			$columns[$col->name] = $col;

			foreach ($column->xpath('option') as $option)
			{
				$tmp = (object) array(
					'text'     => trim((string) $option),
					'value'    => (string) $option['value'],
					'column'   => (string) $column['name'],
					'disable'  => (string) $option['disabled'] === 'true',
					'class'    => (string) $option['class'],
					'checked'  => (string) $option['checked'] === 'true',
				);

				$options[$tmp->value] = $tmp;
			}
		}

		// Set group based on current input value
		foreach ($columns as $cn => $col)
		{
			if (is_array($this->value) && isset($this->value[$cn]))
			{
				$opts = explode(':', $this->value[$cn]);

				foreach ($opts as $opt)
				{
					if (isset($options[$opt]))
					{
						$option = $options[$opt];

						$option->column = $cn;

						$columns[$cn]->options[] = $option;

						unset($options[$opt]);
					}
				}
			}
		}

		// Fallback to default group for remaining options
		foreach ($options as $o)
		{
			$columns[$o->column]->options[] = $o;
		}

		return $columns;
	}
}
