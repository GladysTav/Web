<?php
/**
 * @version     2.0.0
 * @package     sellacious
 *
 * @copyright   Copyright (C) 2012-2020 Bhartiy Web Technologies. All rights reserved.
 * @license     SPL Sellacious Private License; see http://www.sellacious.com/spl.html
 * @author      Aditya Chakraborty <info@bhartiy.com> - http://www.bhartiy.com
 */
// no direct access
defined('_JEXEC') or die;

JFormHelper::loadFieldClass('List');

/**
 * Field class
 *
 * @since   2.0.0
 */
class JFormFieldEncoding extends JFormFieldList
{
	/**
	 * The form field type.
	 *
	 * @var    string
	 *
	 * @since  2.0.0
	 */
	public $type = 'Encoding';

	/**
	 * Method to get the field options.
	 *
	 * @return  array  The field option objects.
	 *
	 * @since   2.0.0
	 */
	protected function getOptions()
	{
		$options = parent::getOptions();

		$options[] = JHtml::_('select.option', '', '- ' . JText::_('JSELECT') . ' -');

		foreach (mb_list_encodings() as $encoding)
		{
			$options[] = JHtml::_('select.option', $encoding, strtoupper($encoding));
		}

		return $options;
	}
}
