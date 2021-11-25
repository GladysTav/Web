<?php
/**
 * @version     1.7.4
 * @package     sellacious
 *
 * @copyright   Copyright (C) 2012-2019 Bhartiy Web Technologies. All rights reserved.
 * @license     SPL Sellacious Private License; see http://www.sellacious.com/spl.html
 * @author      Aditya Chakraborty <info@bhartiy.com> - http://www.bhartiy.com
 */
namespace Sellacious\Shoprule;

// no direct access.
defined('_JEXEC') or die;

/**
 * This base object will be immutable, however this can be extended
 * and the child classes may allow property write if needed.
 *
 * @package  Sellacious\Shoprule
 *
 * @property-read  $name
 * @property-read  $title
 *
 * @since   1.7.0
 */
class ShopruleHandler
{
	/**
	 * @var   string
	 *
	 * @since   1.7.0
	 */
	protected $_name;

	/**
	 * @var   string
	 *
	 * @since   1.7.0
	 */
	protected $_title;

	/**
	 * Constructor
	 *
	 * @param   string  $name
	 * @param   string  $title
	 *
	 * @since   1.7.0
	 */
	public function __construct($name, $title)
	{
		$this->_name  = (string) $name;
		$this->_title = (string) $title;
	}

	/**
	 * This is an immutable object
	 *
	 * @param   string  $name  The property name
	 *
	 * @return  mixed
	 *
	 * @since   1.7.0
	 */
	public function __get($name)
	{
		$name = '_' . $name;

		if (isset($this->$name))
		{
			return $this->$name;
		}

		return null;
	}

	/**
	 * Convert to string (JSON)
	 *
	 * @return  string
	 *
	 * @since   1.7.0
	 */
	public function __toString()
	{
		$array = array(
			'name'  => $this->name,
			'title' => $this->title,
		);

		return json_encode($array);
	}
}
