<?php
/**
 * @version     2.0.0
 * @package     sellacious
 *
 * @copyright   Copyright (C) 2012-2020 Bhartiy Web Technologies. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Izhar Aazmi <info@bhartiy.com> - http://www.bhartiy.com
 */
defined('_JEXEC') or die;

use Joomla\Event\Event;
use Sellacious\Template\AbstractTemplate;

/**
 * Form Field class for the sellacious category list.
 *
 * @since   2.0.0
 */
class JFormFieldTemplateVariables extends JFormField
{
	/**
	 * The field type.
	 *
	 * @var  string
	 *
	 * @since   2.0.0
	 */
	protected $type = 'TemplateVariables';

	/**
	 * Name of the layout being used to render the field
	 *
	 * @var    string
	 *
	 * @since  2.0.0
	 */
	protected $layout = 'sellacious.form.field.templatevariables';

	/**
	 * Method to attach a JForm object to the field.
	 *
	 * @param   SimpleXMLElement $element      The SimpleXMLElement object representing the `<field>` tag for the form field object.
	 * @param   mixed            $value        The form field value to validate.
	 * @param   string           $group        The field name group control value. This acts as as an array container for the field.
	 *                                         For example if the field has name="foo" and the group value is set to "bar" then the
	 *                                         full field name would end up being "bar[foo]".
	 *
	 * @return  boolean
	 *
	 * @since   2.0.0
	 */
	public function setup(SimpleXMLElement $element, $value, $group = null)
	{
		$className = (string) $this->element['template_class'];

		if (strlen($className) === 0 ||  !class_exists($className))
		{
			$this->hidden = true;
		}

		return parent::setup($element, $value, $group);
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
		$variables = array();
		$data      = parent::getLayoutData();

		$className = (string) $this->element['template_class'];

		if (strlen($className) && class_exists($className))
		{
			/** @var  AbstractTemplate  $template */
			$template  = new $className(new Event(''));
			$variables = $template->getVariables();
		}

		$data['variables'] = $variables;

		return $data;
	}

	public function getLabel()
	{
		return null;
	}
}
