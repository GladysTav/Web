<?php
/**
 * @version     1.7.4
 * @package     sellacious
 *
 * @copyright   Copyright (C) 2012-2019 Bhartiy Web Technologies. All rights reserved.
 * @license     SPL Sellacious Private License; see http://www.sellacious.com/spl.html
 * @author      Aditya Chakraborty <info@bhartiy.com> - http://www.bhartiy.com
 */
// no direct access.
defined('_JEXEC') or die;

/**
 * Form Field class for textbox and units of time combo input.
 *
 */
class JFormFieldTimeCombo extends JFormField
{
	/**
	 * The field type.
	 *
	 * @var   string
	 */
	protected $type = 'timecombo';

	/**
	 * Name of the layout being used to render the field
	 *
	 * @var    string
	 *
	 * @since  1.6.0
	 */
	protected $layout = 'field_timecombo';

	/**
	 * Path of the layout
	 *
	 * @var    string
	 *
	 * @since  1.6.0
	 */
	protected $layoutPath = '';

	/**
	 * Client Id (0 = Site, 1 = Admin, 2 = Sellacious)
	 *
	 * @var    string
	 *
	 * @since  1.6.0
	 */
	protected $clientId = 2;

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
	 * @since   1.6.0
	 */
	public function setup(SimpleXMLElement $element, $value, $group = null)
	{
		if (!parent::setup($element, $value, $group))
		{
			return false;
		}

		$this->__set('layout', !empty($this->element['layout']) ? (string) $this->element['layout'] : $this->layout);
		$this->__set('layoutPath', (string) $this->element['layoutPath']);
		$this->__set('clientId', $this->element['clientId'] != '' ? (int) $this->element['clientId'] : $this->clientId);

		return true;
	}

	/**
	 * Method to set certain otherwise inaccessible properties of the form field object.
	 *
	 * @param   string  $name   The property name for which to set the value.
	 * @param   mixed   $value  The value of the property.
	 *
	 * @return  void
	 *
	 * @since   1.6.0
	 */
	public function __set($name, $value)
	{
		switch ($name)
		{
			case 'layout':
			case 'layoutPath':
			case 'clientId':
				$this->$name = (string) $value;
				break;
			default:
				parent::__set($name, $value);
		}
	}

	/**
	 * Method to get the field options.
	 *
	 * @return  string  The field html markup.
	 * @since   1.6
	 */
	protected function getInput()
	{
		// Initialize some field attributes.
		$hint         = $this->translateHint ? JText::_($this->hint) : $this->hint;
		$size         = !empty($this->size) ? ' size="' . $this->size . '"' : '';
		$maxLength    = !empty($this->maxLength) ? ' maxlength="' . $this->maxLength . '"' : '';
		$class        = !empty($this->class) ? $this->class : '';
		$readonly     = $this->readonly ? ' readonly' : '';
		$disabled     = $this->disabled ? ' disabled' : '';
		$required     = $this->required ? ' required aria-required="true"' : '';
		$hint         = $hint ? ' placeholder="' . $hint . '"' : '';
		$autocomplete = !$this->autocomplete ? ' autocomplete="off"' : ' autocomplete="' . $this->autocomplete . '"';
		$autocomplete = $autocomplete == ' autocomplete="on"' ? '' : $autocomplete;
		$autofocus    = $this->autofocus ? ' autofocus' : '';
		$spellcheck   = $this->spellcheck ? '' : ' spellcheck="false"';
		$pattern      = !empty($this->pattern) ? ' pattern="' . $this->pattern . '"' : '';
		$inputmode    = !empty($this->inputmode) ? ' inputmode="' . $this->inputmode . '"' : '';
		$dirname      = !empty($this->dirname) ? ' dirname="' . $this->dirname . '"' : '';

		// Initialize JavaScript field attributes.
		$onchange = !empty($this->onchange) ? ' onchange="' . $this->onchange . '"' : '';

		// Including fallback code for HTML5 non supported browsers.
		JHtml::_('jquery.framework');
		JHtml::_('script', 'system/html5fallback.js', array('version' => S_VERSION_CORE, 'relative' => true));

		$name    = $this->name;
		$id      = $this->id;
		$options = $this->getOptions();

		$b_value = array('m' => '0', 'u' => 'minutes');
		$value   = array_merge($b_value, array_intersect_key((array) $this->value, $b_value));

		$layoutData = compact('hint', 'size', 'maxLength', 'class', 'readonly', 'disabled', 'required',
			'autocomplete', 'autofocus', 'spellcheck', 'pattern', 'inputmode', 'dirname', 'onchange',
			'name', 'id', 'options', 'value');

		$html = $this->renderTimeField($layoutData, $this->layout);

		return $html;
	}

	/**
	 * Method to get the field options.
	 *
	 * @return  stdClass[]  The field option objects.
	 *
	 * @since   11.1
	 */
	protected function getOptions()
	{
		$options = array(
			'minutes' => 'minutes',
			'hours'   => 'hours'
		);

		return $options;
	}

	/**
	 * Render Timings layout
	 *
	 * @param   array  $data  The timings data
	 *
	 * @return  string
	 *
	 * @since   1.7.0
	 *
	 * @throws  \Exception
	 */
	public function renderTimeField($data, $layoutName)
	{
		ob_start();
		$layoutPath = JPluginHelper::getLayoutPath('system', 'sellacioushyperlocal', $layoutName);

		if (is_file($layoutPath))
		{
			$displayData = $data;

			unset($namespace, $layout);

			/**
			 * Variables available to the layout
			 *
			 * @var  $this
			 * @var  $layoutPath
			 * @var  $displayData
			 */
			include $layoutPath;
		}

		return ob_get_clean();
	}
}
