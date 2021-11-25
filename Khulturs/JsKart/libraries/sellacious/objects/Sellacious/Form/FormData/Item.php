<?php
/**
 * @version     2.0.0
 * @package     sellacious
 *
 * @copyright   Copyright (C) 2012-2020 Bhartiy Web Technologies. All rights reserved.
 * @license     SPL Sellacious Private License; see http://www.sellacious.com/spl.html
 * @author      Aditya Chakraborty <info@bhartiy.com> - http://www.bhartiy.com
 */

namespace Sellacious\Form\FormData;

// no direct access.
defined('_JEXEC') or die;

/**
 * Class for Form Data Item
 *
 * @since   2.0.0
 */
class Item
{
	/**
	 * The name of the item
	 *
	 * @var   string
	 *
	 * @since   2.0.0
	 */
	protected $name;

	/**
	 * The value of the item
	 *
	 * @var   mixed
	 *
	 * @since   2.0.0
	 */
	protected $value;

	/**
	 * The label of the item
	 *
	 * @var   string
	 *
	 * @since   2.0.0
	 */
	protected $label;

	/**
	 * Item constructor.
	 * @param   string  $name   Name of the item
	 * @param   mixed   $value  Value of the item
	 *
	 * @since   2.0.0
	 */
	public function __construct($name, $value = null)
	{
		$this->name  = $name;
		$this->value = $value;
	}

	/**
	 * @param   string  $name
	 *
	 * @since   2.0.0
	 */
	public function setName($name)
	{
		$this->name = $name;
	}

	/**
	 * @param   mixed  $value
	 *
	 * @since   2.0.0
	 */
	public function setValue($value)
	{
		$this->value = $value;
	}

	/**
	 * @param   string  $label
	 *
	 * @since   2.0.0
	 */
	public function setLabel($label)
	{
		$this->label = $label;
	}

	/**
	 * @return  string
	 *
	 * @since   2.0.0
	 */
	public function getName()
	{
		return $this->name;
	}

	/**
	 * @return  mixed
	 *
	 * @since   2.0.0
	 */
	public function getValue()
	{
		return $this->value;
	}

	/**
	 * @return  string
	 *
	 * @since   2.0.0
	 */
	public function getLabel()
	{
		return $this->label;
	}
}
