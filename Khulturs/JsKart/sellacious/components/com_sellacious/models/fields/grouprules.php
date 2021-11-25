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

use Sellacious\Access\Access;
use Sellacious\Access\AccessHelper;
use Sellacious\User\UserGroupHelper;

/**
 * Form Field class for the Joomla Platform.
 * Field for assigning permissions to groups for a given asset
 *
 * @see    JAccess
 * @since  11.1
 */
class JFormFieldGroupRules extends JFormField
{
	/**
	 * The form field type.
	 *
	 * @var    string
	 * @since  11.1
	 */
	protected $type = 'GroupRules';

	/**
	 * The section.
	 *
	 * @var    string
	 * @since  3.2
	 */
	protected $section;

	/**
	 * The component.
	 *
	 * @var    string
	 * @since  3.2
	 */
	protected $component;

	/**
	 * The user group for which to set the access.
	 *
	 * @var   int
	 *
	 * @since  1.2.0
	 */
	protected $groupId;

	/**
	 * The asset name for which to set the access.
	 *
	 * @var   string
	 *
	 * @since  1.2.0
	 */
	protected $asset;

	/**
	 * The setting to specify whether to list actions in groups (0|1).
	 *
	 * @var    int
	 *
	 * @since  1.2.0
	 */
	protected $accordion;

	/**
	 * Name of the layout being used to render the field
	 *
	 * @var    string
	 *
	 * @since  2.0.0
	 */
	protected $layout = 'com_sellacious.formfield.grouprules';

	/**
	 * Method to get certain otherwise inaccessible properties from the form field object.
	 *
	 * @param   string $name The property name for which to the the value.
	 *
	 * @return  mixed  The property value or null.
	 *
	 * @since   1.2.0
	 */
	public function __get($name)
	{
		switch ($name)
		{
			case 'section':
			case 'component':
			case 'asset':
			case 'groupId':
			case 'accordion':
				return $this->$name;
		}

		return parent::__get($name);
	}

	/**
	 * Method to set certain otherwise inaccessible properties of the form field object.
	 *
	 * @param   string $name  The property name for which to the the value.
	 * @param   mixed  $value The value of the property.
	 *
	 * @return  void
	 *
	 * @since   1.2.0
	 */
	public function __set($name, $value)
	{
		switch ($name)
		{
			case 'section':
			case 'component':
			case 'asset':
			case 'groupId':
			case 'accordion':
				$this->$name = (string) $value;
				break;

			default:
				parent::__set($name, $value);
		}
	}

	/**
	 * Method to attach a JForm object to the field.
	 *
	 * @param   SimpleXMLElement  $element  The SimpleXMLElement object representing the <field /> tag for the form field object.
	 * @param   mixed             $value    The form field value to validate.
	 * @param   string            $group    The field name group control value. This acts as as an array container for the field.
	 *                                      For example if the field has name="foo" and the group value is set to "bar" then the
	 *                                      full field name would end up being "bar[foo]".
	 *
	 * @return  boolean  True on success.
	 *
	 * @see     JFormField::setup()
	 * @since   1.2.0
	 */
	public function setup(SimpleXMLElement $element, $value, $group = null)
	{
		$return = parent::setup($element, $value, $group);

		if ($return)
		{
			$this->section   = $this->element['section'] ? (string) $this->element['section'] : '';
			$this->component = $this->element['component'] ? (string) $this->element['component'] : '';
			$this->asset     = $this->element['asset'] ? (string) $this->element['asset'] : 'root.1';
			$this->groupId   = $this->element['group_id'] ? (int) $this->element['group_id'] : 1;
			$this->accordion = $this->element['accordion'] ? (int) $this->element['accordion'] : 1;

			$this->asset = ($this->component && $this->section == 'component') ? $this->component : $this->asset;
		}

		return $return;
	}

	/**
	 * Method to get the field input markup for Access Control Lists.
	 * Optionally can be associated with a specific component and section.
	 *
	 * @return  string  The field input markup.
	 *
	 * @since   1.2.0
	 */
	protected function getInput()
	{
		JHtml::_('bootstrap.tooltip');

		$assetId = AccessHelper::getAssetId($this->asset);

		$data = new stdClass;

		$data->id         = $this->id;
		$data->name       = $this->name;
		$data->component  = $this->component;
		$data->section    = $this->section;
		$data->asset      = $this->asset;
		$data->assetId    = $assetId;
		$data->groupId    = $this->groupId;
		$data->group      = $this->getUserGroup();
		$data->actions    = $this->getActions();
		$data->assetRules = Access::getAssetRules($assetId, false, false);

		$layout = $this->accordion ? '.accordion' : '.grid';

		return JLayoutHelper::render($this->layout . $layout, $data);
	}

	/**
	 * Get the active user group
	 *
	 * @return  stdClass
	 *
	 * @since   1.2.0
	 */
	protected function getUserGroup()
	{
		$group = UserGroupHelper::get($this->groupId);

		if ($group)
		{
			$group->value   = $group->id;
			$group->text    = $group->title;
			$group->inherit = $group->level > 0 && $this->component;
		}

		return $group;
	}

	/**
	 * Method to return a list of actions from the component's access.xml file for which permissions can be set.
	 *
	 * @return  stdClass[]  The list of actions available
	 *
	 * @since   1.2.0
	 */
	protected function getActions()
	{
		if ($this->accordion)
		{
			return AccessHelper::getActionGroups($this->component, $this->section);
		}
		else
		{
			return AccessHelper::getActions($this->component, $this->section);
		}
	}

	/**
	 * Method to get the field label markup.
	 *
	 * @return  string  The field label markup.
	 *
	 * @since   1.5.2
	 */
	protected function getLabel()
	{
		return '';
	}
}
