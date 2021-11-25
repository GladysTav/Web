<?php
/**
 * @version     2.0.0
 * @package     sellacious
 *
 * @copyright   Copyright (C) 2012-2020 Bhartiy Web Technologies. All rights reserved.
 * @license     SPL Sellacious Private License; see http://www.sellacious.com/spl.html
 * @author      Aditya Chakraborty <info@bhartiy.com> - http://www.bhartiy.com
 */
// No direct access.
defined('_JEXEC') or die;

/**
 * Form Field class for CKInlinEditor.
 * Provides an input field for inline wysiwyg editor
 *
 * @since   1.7.0
 */
class JFormFieldCkInlineEditor extends JFormFieldHidden
{
	/**
	 * The form field type.
	 *
	 * @var    string
	 * @since  1.7.0
	 */
	public $type = 'ckInlineEditor';

	/**
	 * Method to get the field input markup.
	 *
	 * @return  string  The field input markup.
	 *
	 * @since   1.7.0
	 */
	protected function getInput()
	{
		$input = parent::getInput();

		$value = $this->value;
		$html  = '<div id="'. $this->id . '_editor">' . $value . '</div>' . $input;

		JHtml::_('jquery.framework');
		JHtml::_('script', 'com_sellacious/plugin/ckeditor/ckeditor.js', false, true);

		$doc = JFactory::getDocument();
		$doc->addStyleDeclaration('
			div[contenteditable] {
			    outline: 1px solid blue;
			}
		');
		$doc->addScriptDeclaration("
			jQuery(document).ready(function () {
				let editor = document.getElementById('" . $this->id . "_editor');
				editor.setAttribute('contenteditable', true);

				CKEDITOR.inline('" . $this->id . "_editor', {
					extraPlugins: 'sourcedialog'
				});
			});
		");

		return $html;
	}
}
