<?php
/**
 * @version     2.0.0
 * @package     sellacious
 *
 * @copyright   Copyright (C) 2012-2020 Bhartiy Web Technologies. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Izhar Aazmi <info@bhartiy.com> - http://www.bhartiy.com
 */
namespace Sellacious\Template;

defined('_JEXEC') or die;

use Joomla\Utilities\ArrayHelper;
use Sellacious\Event\EventHelper;

/**
 * @package  Sellacious\Template
 *
 * @since    2.0.0
 */
abstract class AbstractTemplate
{
	/**
	 * The name of this template object. Must be unique for each context
	 *
	 * @var   string
	 *
	 * @since   2.0.0
	 */
	protected $name;

	/**
	 * The replacement values to be used for template parsing, could be computed internally or set externally.
	 *
	 * @var   string[]
	 *
	 * @since   2.0.0
	 */
	protected $values;

	/**
	 * List of template variables with its type
	 *
	 * @var   TemplateVariable[]
	 *
	 * @since  2.0.0
	 */
	protected $variables = array();

	/**
	 * Method to get the name of this template object. Must be unique for each context
	 *
	 * @return  string
	 *
	 * @since   2.0.0
	 */
	abstract public function getName();

	/**
	 * Method to add a custom variable and a resp. sample value to the template to be used during template parse
	 *
	 * @param   TemplateVariable  $variable
	 *
	 * @return  void
	 *
	 * @since   2.0.0
	 */
	public function addVariable($variable)
	{
		$this->variables[] = $variable;
	}

	/**
	 * Get a list of template variable for this template
	 *
	 * @return  TemplateVariable[]
	 *
	 * @since   2.0.0
	 */
	public function getVariables()
	{
		if (!$this->variables)
		{
			$this->loadVariables();
		}

		return $this->variables;
	}

	/**
	 * Get a sample data set from variables for generating preview
	 *
	 * @return  string[]
	 *
	 * @since   2.0.0
	 */
	public function getSample()
	{
		$variables = $this->getVariables();

		return ArrayHelper::getColumn($variables, 'sample', 'name');
	}

	/**
	 * Get the replacement values for the variables. This will be used to parse the template.
	 * Override this to perform any required computations.
	 *
	 * @return  string[]
	 *
	 * @since   2.0.0
	 */
	public function getValues()
	{
		return $this->values;
	}

	/**
	 * Set the replacement values for the variables. This will be used to parse the template.
	 *
	 * @param   string[]  $values
	 *
	 * @return  $this
	 *
	 * @since   2.0.0
	 */
	public function setValues(array $values)
	{
		$this->values = array_change_key_case($values, CASE_LOWER);

		return $this;
	}

	/**
	 * Override this method if you have loops or other complicated template processing logic
	 *
	 * @param   string  $content
	 *
	 * @return  string
	 *
	 * @since   2.0.0
	 */
	public function parse($content)
	{
		$text      = $content;
		$variables = $this->getVariables();
		$values    = $this->getValues();

		foreach ($variables as $variable)
		{
			$text = $variable->parse($this, $text, $values);
		}

		return $text;
	}

	/**
	 * Get an internal data that is used to prepare the values for replacement.
	 *
	 * The <var>TemplateVariable::parse()</var> method may require an internal data,
	 * if there is any such internal data that can be used to parse the variables
	 * then it will be accessible via <var>get()</var> method
	 *
	 * @param   string  $var
	 *
	 * @return  mixed
	 *
	 * @since   2.0.0
	 */
	public function get($var)
	{
		// Only allow access if no explicit getter available
		return property_exists($this, $var) && !method_exists($this, 'get' . ucfirst($var)) ? $this->$var : null;
	}

	/**
	 * Generate a preview of the template using sample data
	 *
	 * @param   string  $content
	 *
	 * @return  string
	 *
	 * @since   2.0.0
	 */
	public function preview($content)
	{
		$sample = $this->getSample();

		return $this->setValues($sample)->parse($content);
	}

	/**
	 * Override this for each context and set default variables and sample before calling this parent method
	 *
	 * @since   2.0.0
	 */
	protected function loadVariables()
	{
		EventHelper::trigger('onLoadTemplateVariables', array('template' => $this));
	}
}
