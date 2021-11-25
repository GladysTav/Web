<?php
/**
 * @version     1.7.4
 * @package     sellacious
 *
 * @copyright   Copyright (C) 2012-2019 Bhartiy Web Technologies. All rights reserved.
 * @license     SPL Sellacious Private License; see http://www.sellacious.com/spl.html
 * @author      Aditya Chakraborty <info@bhartiy.com> - http://www.bhartiy.com
 */
// No direct access.
defined('_JEXEC') or die;

JFormHelper::loadFieldClass('editor');

/**
 * Form Field class for the Joomla Platform.
 * Provides an input field for wysiwyg editor only when user clicks on edit button within
 */
class JFormFieldTemplatePreview extends JFormFieldHidden
{
	/**
	 * The form field type.
	 *
	 * @var    string
	 * @since  1.7.0
	 */
	public $type = 'TemplatePreview';

	/**
	 * Method to get the field input markup for the file field.
	 * Field attributes allow specification of a maximum file size and a string
	 * of accepted file extensions.
	 *
	 * @return  string  The field input markup.
	 *
	 * @since   1.7.0
	 *
	 * @note    The field does not include an upload mechanism.
	 * @see     JFormFieldMedia
	 */
	protected function getInput()
	{
		$id  = $this->id;
		$url = JURI::root() . JPATH_SELLACIOUS_DIR . '/index.php?option=com_sellacioustemplates&view=template&layout=preview&tmpl=component';

		$html = <<<HTML
		<div style="height: 950px;">
			<iframe 
				id="{$id}"
				style="width: 100%; height: 100%;"
				src="{$url}"
				frameborder="0" 
				allowfullscreen
			>	
			</iframe>
		</div>
HTML;

		return $html;
	}
}
