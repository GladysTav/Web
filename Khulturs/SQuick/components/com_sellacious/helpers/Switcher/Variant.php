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

class Variant
{
	/**
	 * @var   int
	 *
	 * @since   1.7.0
	 */
	public $id;

	/**
	 * @var   string
	 *
	 * @since   1.7.0
	 */
	public $code;

	/**
	 * @var   int
	 *
	 * @since   1.7.0
	 */
	public $stock;

	/**
	 * @var   array
	 *
	 * @since   1.7.0
	 */
	public $specification;

	/**
	 * Constructor.
	 *
	 * @param   int     $id
	 * @param   string  $code
	 * @param   int     $stock
	 * @param   array   $specification
	 *
	 * @since   1.7.0
	 */
	public function __construct($id, $code, $stock, $specification)
	{
		$this->id            = (int) $id;
		$this->code          = (string) $code;
		$this->stock         = (int) $stock;
		$this->specification = is_array($specification) ? $specification : array();
	}
}
