<?php
/**
 * @version     2.0.0
 * @package     sellacious
 *
 * @copyright   Copyright (C) 2012-2020 Bhartiy Web Technologies. All rights reserved.
 * @license     SPL Sellacious Private License; see http://www.sellacious.com/spl.html
 * @author      Izhar Aazmi <info@bhartiy.com> - http://www.bhartiy.com
 */
defined('_JEXEC') or die;

use Sellacious\Config\ConfigHelper;
use Sellacious\Price\PriceHelper;

JFormHelper::loadFieldClass('Choice');

/**
 * Form field class
 *
 * @since   2.0.0
 */
class JFormFieldPriceType extends JFormField
{
	/**
	 * The field type
	 *
	 * @var   string
	 *
	 * @since   2.0.0
	 */
	protected $type = 'PriceType';

	/**
	 * The field options
	 *
	 * @var   array
	 *
	 * @since   2.0.0
	 */
	protected $options = array();

	/**
	 * The selected options
	 *
	 * @var   array
	 *
	 * @since   2.0.0
	 */
	protected $checkedOptions = array();

	/**
	 * Method to get the field input markup
	 *
	 * @return  string  The field input markup
	 *
	 * @since   2.0.0
	 */
	protected function getInput()
	{
		$this->options = $this->getOptions();

		if ($this->multiple)
		{
			$this->value = (array) $this->value;
		}
		elseif (!is_string($this->value) || !array_key_exists($this->value, $this->options))
		{
			$this->value = key($this->options);
		}

		if (count($this->options) < 2 && !$this->multiple)
		{
			return rtrim($this->getRenderer('joomla.form.field.hidden')->render($this->getLayoutData()), PHP_EOL);
		}

		$data = get_object_vars($this);

		return JLayoutHelper::render($this->multiple ? 'joomla.formfield.checkboxes.input' : 'joomla.formfield.radio.input', $data);
	}

	/**
	 * Method to get the field label markup.
	 *
	 * @return  string  The field label markup.
	 *
	 * @since   2.0.0
	 */
	public function getLabel()
	{
		$this->options = $this->getOptions();

		if (count($this->options) < 2 && !$this->multiple)
		{
			return '';
		}

		return parent::getLabel();
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
		static $cache = array();

		$global   = (string) $this->element['use_global'];
		$selected = (string) $this->element['limit'];

		$key = $global . '-' . $selected;

		if (isset($cache[$key]))
		{
			return $cache[$key];
		}

		$selected = array_filter(explode(',', $selected));
		$handlers = PriceHelper::getSelectedHandlers($selected, $global == 'true' ? true : false);

		// $handlers = ArrayHelper::sortObjects($handlers, 'label');
		$options  = array();

		foreach ($handlers as $handler)
		{
			$option    = (object) array(
				'value'       => $handler->name,
				'text'        => $handler->label,
				'description' => $handler->description,
				'checked'     => null,
			);
			$options[$handler->name] = $option;
		}

		return $cache[$key] = $options;
	}
}
