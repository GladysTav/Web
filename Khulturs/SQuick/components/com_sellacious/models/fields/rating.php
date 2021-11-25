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

/**
 * Field to select a user ID from a modal list.
 *
 * @package     Joomla.Libraries
 * @subpackage  Form
 * @since       1.6
 */
class JFormFieldRating extends JFormField
{
	/**
	 * The form field type.
	 *
	 * @var    string
	 * @since  1.6
	 */
	public $type = 'Rating';

	/**
	 * Method to get the user field input markup.
	 *
	 * @return  string  The field input markup.
	 *
	 * @since   1.6
	 */
	protected function getInput()
	{
		$value  = (int) $this->value;
		$stars  = (int) $this->element['stars'] ? (int) $this->element['stars'] : 5;
		$checks = array_fill(1, $stars, '');

		$checks[$value] = 'checked';

		$html = '<span class="rating">';

		for ($i = $stars; $i > 0; $i--)
		{
			$rating  = $i;
			$checked = $checks[$rating];
			$html    .= <<<HTML
				<input type="radio" class="rating-input" id="{$this->id}-{$rating}" name="{$this->name}" value="{$rating}" {$checked}>
				<label for="{$this->id}-{$rating}" class="rating-star fa fa-star-o"></label>
HTML;
		}

		$html .= '</span>';

		return $html;
	}
}
