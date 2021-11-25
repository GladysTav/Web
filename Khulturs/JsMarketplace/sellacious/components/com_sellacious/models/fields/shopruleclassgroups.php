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
 * Form Field class for Shop rule class Groups.
 *
 * @since   1.7.1
 */
class JFormFieldShopRuleClassGroups extends JFormField
{
	/**
	 * The field type.
	 *
	 * @var    string
	 *
	 * @since  1.7.1
	 */
	protected $type = 'ShopRuleClassGroups';

	/**
	 * Method to get the field options.
	 *
	 * @return  string  The field input.
	 *
	 * @throws  \Exception
	 *
	 * @since   1.7.1
	 */
	protected function getInput()
	{
		$options = array();

		$classType     = (string) $this->element['class_type'];
		$allowNew      = $this->element['allow_new'] ? $this->element['allow_new'] : true;
		$selectionSize = $this->element['selection_size'] ? $this->element['selection_size'] : 0;

		$helper = SellaciousHelper::getInstance();
		$groups = $helper->shopRuleClass->getAllClasses($classType);
		$values = is_array($this->value) ? $this->value : array_filter(explode(',', $this->value));

		foreach ($groups as $group)
		{
			$options[] = (object) array('id' => $group->id, 'text' => $group->title);
		}

		foreach ($values as &$value)
		{
			$value = (object) array('id' => $value, 'text' => $value);
		}

		// Initialize some field attributes.
		$disabled = $this->disabled ? ' disabled' : '';

		// Initialize JavaScript field attributes.
		$html = array();

		$values = htmlspecialchars(json_encode($values));
		$html[] = '<input type="hidden" name="' . $this->name . '" id="' . $this->id . '" class="' . $this->class . '" ' . $disabled .
			' data-tags="' . htmlspecialchars(json_encode($options)) . '"' . ' data-value="' . $values . '"/>';

		JHtml::_('jquery.framework');
		$token = JSession::getFormToken();

		$script = <<<JS
		(function ($) {
			$(document).ready(function () {
				var o = new ShopRuleClassGroup;
				o.init('#{$this->id}', '{$token}', {$allowNew}, {$selectionSize});
			})
		})(jQuery);
JS;
		JFactory::getDocument()->addScriptDeclaration($script);
		JHtml::_('script', 'com_sellacious/field.' . strtolower($this->type) . '.js', false, true);

		return implode($html);
	}
}
