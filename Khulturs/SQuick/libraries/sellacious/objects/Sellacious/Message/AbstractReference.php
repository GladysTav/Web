<?php
/**
 * @version     2.0.0
 * @package     sellacious
 *
 * @copyright   Copyright (C) 2012-2020 Bhartiy Web Technologies. All rights reserved.
 * @license     SPL Sellacious Private License; see http://www.sellacious.com/spl.html
 * @author      Aditya Chakraborty <info@bhartiy.com> - http://www.bhartiy.com
 */

namespace Sellacious\Message;

// no direct access.
defined('_JEXEC') or die;

use SellaciousHelper;

/**
 * Base Class for Message Reference
 *
 * @since   2.0.0
 */
abstract class AbstractReference
{
	/**
	 * @var  string
	 *
	 * @since   2.0.0
	 */
	protected $value;

	/**
	 * @var  SellaciousHelper
	 *
	 * @since   2.0.0
	 */
	protected $helper;

	/**
	 * AbstractReference constructor.
	 *
	 * @param   string  $value
	 *
	 * @since   2.0.0
	 */
	public function __construct($value)
	{
		$this->value   = $value;
		$this->helper  = SellaciousHelper::getInstance();
	}

	/**
	 * Method to get message reference as text
	 *
	 * @return   string
	 *
	 * @since    2.0.0
	 */
	abstract public function asText();
}
