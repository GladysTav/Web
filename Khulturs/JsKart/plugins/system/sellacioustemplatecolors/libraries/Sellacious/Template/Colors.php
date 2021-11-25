<?php
/**
 * @version     2.0.0
 * @package     sellacious
 *
 * @copyright   Copyright (C) 2012-2020 Bhartiy Web Technologies. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Saurabh Sabbharwal <info@bhartiy.com> - http://www.bhartiy.com
 */
namespace Sellacious\Template;

// no direct access
defined('_JEXEC') or die;

use Joomla\Registry\Registry;

class Colors
{
	/**
	 * @var    array
	 *
	 * @since   2.0.0
	 */
	protected $colorFields;

	/**
	 * Constructor
	 *
	 * @param   array  $colorsConfig  Sellacious Configuration for colors
	 *
	 * @since   2.0.0
	 */
	public function __construct($colorsConfig)
	{
		$this->colorFields = $colorsConfig;
	}

	/**
	 * Get css code from colors configuration
	 *
	 * @return  string
	 *
	 * @since   2.0.0
	 */
	public function getCss()
	{
		$css = [];

		foreach ($this->colorFields as $name => $field)
		{
			$color = new Registry($field);
			$css[] = $color->get($name . '.' . 'css');
		}

		return implode('', $css);
	}
}
