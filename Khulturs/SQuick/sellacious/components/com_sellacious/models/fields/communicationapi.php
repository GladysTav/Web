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

use Sellacious\Communication\CommunicationHelper;

JFormHelper::loadFieldClass('List');

/**
 * Form field class
 *
 * @since   2.0.0
 */
class JFormFieldCommunicationApi extends JFormFieldList
{
	/**
	 * The field type
	 *
	 * @var   string
	 *
	 * @since   2.0.0
	 */
	protected $type = 'CommunicationApi';

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
	 * @var   string
	 *
	 * @since   2.0.0
	 */
	protected $handler;

	/**
	 * Method to attach a JForm object to the field.
	 *
	 * @param   SimpleXMLElement  $element  The SimpleXMLElement object representing the `<field>` tag for the form field object.
	 * @param   mixed             $value    The form field value to validate.
	 * @param   string            $group    The field name group control value. This acts as as an array container for the field.
	 *                                      For example if the field has name="foo" and the group value is set to "bar" then the
	 *                                      full field name would end up being "bar[foo]".
	 *
	 * @return  bool  True on success
	 *
	 * @since   2.0.0
	 */
	public function setup(\SimpleXMLElement $element, $value, $group = null)
	{
		$setup = parent::setup($element, $value, $group);

		$this->handler = (string) $this->element['handler'];
		$this->options = $this->getOptions();

		return $setup;
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

		if (isset($cache[$this->handler]))
		{
			return $cache[$this->handler];
		}

		$apis    = CommunicationHelper::getApis($this->handler);
		$options = array();

		foreach ($apis as $api)
		{
			$option = (object) array(
				'value'       => $api->name,
				'text'        => $api->label,
				'description' => $api->description,
				'checked'     => null,
			);

			$options[$api->name] = $option;
		}

		return $cache[$this->handler] = $options;
	}
}
