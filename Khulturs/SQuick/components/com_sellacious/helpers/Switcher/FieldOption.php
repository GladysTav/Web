<?php
/**
 * @version     2.0.0
 * @package     sellacious
 *
 * @copyright   Copyright (C) 2012-2020 Bhartiy Web Technologies. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Izhar Aazmi <info@bhartiy.com> - http://www.bhartiy.com
 */
namespace Sellacious\UI\VariantSwitcher;

// no direct access
defined('_JEXEC') or die;

class FieldOption
{
	/**
	 * @var   mixed
	 *
	 * @since   1.7.0
	 */
	public $value;

	/**
	 * The list of bound variants that are in stock
	 *
	 * @var   Variant[]
	 *
	 * @since  1.7.0
	 */
	public $variantsAvailable = array();

	/**
	 * The list of bound variants that are out of stock
	 *
	 * @var   Variant[]
	 *
	 * @since  1.7.0
	 */
	public $variantsNotAvailable = array();

	/**
	 * Constructor
	 *
	 * @param  mixed  $value
	 *
	 * @since  1.7.0
	 */
	public function __construct($value)
	{
		$this->value = $value;
	}

	/**
	 * Bind a variant item id, this can be looked up against the switcher object
	 *
	 * @param   Variant  $variant
	 *
	 * @return  void
	 *
	 * @since  1.7.0
	 */
	public function addVariant(Variant $variant)
	{
		if ($variant->stock >= 0)
		{
			$this->variantsAvailable[] = $variant;
		}
		else
		{
			$this->variantsNotAvailable[] = $variant;
		}
	}
}
