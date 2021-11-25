<?php
/**
 * @version     2.0.0
 * @package     sellacious
 *
 * @copyright   Copyright (C) 2012-2020 Bhartiy Web Technologies. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Aditya Chakraborty <info@bhartiy.com> - http://www.bhartiy.com
 */

namespace Sellacious\Template;

// no direct access
defined('_JEXEC') or die;

use JFactory;

/**
 * @package  Sellacious\Template
 *
 * @since     2.0.0
 */
class SiteNameTemplateVariable extends TemplateVariable
{
	/**
	 * Replace the relevant variable with respective value
	 *
	 * @param   AbstractTemplate  $template
	 * @param   string            $text
	 * @param   array             $data
	 *
	 * @return  string
	 *
	 * @since   2.0.0
	 */
	public function parse(AbstractTemplate $template, $text, $data)
	{
		$replacement = JFactory::getConfig()->get('sitename');
		$text        = str_ireplace('%' . $this->name . '%', $replacement, $text);

		return $text;
	}
}
