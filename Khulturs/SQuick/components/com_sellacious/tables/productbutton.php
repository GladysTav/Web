<?php
/**
 * @version     2.0.0
 * @package     sellacious
 *
 * @copyright   Copyright (C) 2012-2020 Bhartiy Web Technologies. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Izhar Aazmi <info@bhartiy.com> - http://www.bhartiy.com
 */
// no direct access
defined('_JEXEC') or die;

/**
 * Product Table class
 *
 * @since   1.6.0
 */
class SellaciousTableProductButton extends SellaciousTable
{
	/**
	 * Constructor
	 *
	 * @param   JDatabaseDriver  $db  A database connector object
	 *
	 * @since   1.6.0
	 */
	public function __construct(&$db)
	{
		$this->_array_fields = array('params');

		parent::__construct('#__sellacious_product_buttons', 'id', $db);
	}
}