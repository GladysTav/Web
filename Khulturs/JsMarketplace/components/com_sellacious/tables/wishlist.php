<?php
/**
 * @version     1.7.4
 * @package     sellacious
 *
 * @copyright   Copyright (C) 2012-2019 Bhartiy Web Technologies. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Izhar Aazmi <info@bhartiy.com> - http://www.bhartiy.com
 */
// no direct access
defined('_JEXEC') or die;

/**
 * Coupon Table class
 */
class SellaciousTableWishlist extends SellaciousTable
{
	/**
	 * Constructor
	 *
	 * @param  JDatabaseDriver  $db  A database connector object
	 */
	public function __construct(&$db)
	{
		$this->_array_fields = array('params');

		parent::__construct('#__sellacious_wishlist', 'id', $db);
	}
}
