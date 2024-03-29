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
 * Manufacturer Table class
 */
class SellaciousTableListing extends SellaciousTable
{
	/**
	 * Constructor
	 *
	 * @param JDatabaseDriver  $db  A database connector object
	 */
	public function __construct(&$db)
	{
		parent::__construct('#__sellacious_seller_listing', 'id', $db);
	}
}
