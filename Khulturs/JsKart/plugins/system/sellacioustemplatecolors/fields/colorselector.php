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
use Joomla\Registry\Registry;

defined('_JEXEC') or die;

JFormHelper::loadFieldClass('Radio');

/**
 * Form Field class.
 *
 * @since   2.0.0
 */
class JFormFieldColorSelector extends JFormField
{
	/**
	 * The field type.
	 *
	 * @var  string
	 *
	 * @since   2.0.0
	 */
	protected $type = 'ColorSelector';

	/**
	 * The field type.
	 *
	 * @var  int[]
	 *
	 * @since   2.0.0
	 */
	protected $allow;

	/**
	 * The field layout.
	 *
	 * @var  string
	 *
	 * @since   2.0.0
	 */
	protected $layout = 'sellacious.form.field.colorselector.colorselector';

	/**
	 * Whether the current user is premium or not.
	 *
	 * @var  boolean
	 *
	 * @since   2.0.0
	 */
	protected $isPremium = false;

	/**
	 * The CSS selector on which colors are to be applied.
	 *
	 * @var  string
	 *
	 * @since   2.0.0
	 */
	protected $selector;

	/**
	 * Whether to give an option to choose ':hover' color of the selector or not.
	 *
	 * @var  boolean
	 *
	 * @since   2.0.0
	 */
	protected $hover;

	/**
	 * The template of the selector to be displayed for live color changing.
	 *
	 * @var  string
	 *
	 * @since   2.0.0
	 */
	protected $template;

	/**
	 * Any pseudo selector for which colors are to be applied.
	 *
	 * @var  string
	 *
	 * @since   2.0.0
	 */
	protected $pseudo_selector = null;

	/**
	 * CSS attribute which is to be applied to the pseudo selector.
	 *
	 * @var  string
	 *
	 * @since   2.0.0
	 */
	protected $pseudo_attribute = null;

	/**
	 * CSS attributes to be applied on the selector.
	 *
	 * @var  string
	 *
	 * @since   2.0.0
	 */
	protected $attributes = array();

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
		parent::setup($element, $value, $group);

		try
		{
			$helper = SellaciousHelper::getInstance();
		}
		catch (Exception $e){}

		$value = new Registry($value);

		$this->isPremium        = (bool) $helper->access->isSubscribed();
		$this->selector         = (string) $element['selector'];
		$this->hover            = (string) $element['hover'];
		$this->template         = (string) $element->template;
		$this->pseudo_selector  = (string) $element['pseudo'] ?: null;
		$this->pseudo_attribute = (string) $element['pseudo_attribute'] ?: null;

		foreach ($element->attribute as $attr)
		{
			$val   = $value->get($this->fieldname . '.' . $attr['property'] . '.' . 'value') ?: $attr['default'];
			$hover = $value->get($this->fieldname . '.' . $attr['property'] . '.' . 'hover') ?: $attr['hover'];
			$this->attributes[] = array(
				'name'         => (string) $attr['name'],
				'label'        => (string) $attr['label'],
				'default'      => (string) $attr['default'],
				'value'        => (string) $val,
				'current'      => (string) $val,
				'property'     => (string) $attr['property'],
				'hover'        => (string) $attr['hover'],
				'hoverValue'   => (string) $hover,
				'hoverCurrent' => (string) $hover,
			);
		}

		return true;
	}

	/**
	 * Allow to override renderer include paths in child fields
	 *
	 * @return  array
	 *
	 * @since   2.0.0
	 */
	protected function getLayoutPaths()
	{
		$paths   = parent::getLayoutPaths();
		$paths[] = dirname(__DIR__) . '/layouts';

		return $paths;
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
		$data['fieldName']        = $this->fieldname;
		$data['isPremium']        = $this->isPremium;
		$data['selector']         = $this->selector;
		$data['hover']            = $this->hover;
		$data['attributes']       = $this->attributes;
		$data['pseudo_selector']  = $this->pseudo_selector;
		$data['pseudo_attribute'] = $this->pseudo_attribute;
		$data['attributes']       = $this->attributes;
		$data['template']         = $this->template;

		return $data;
	}
}
