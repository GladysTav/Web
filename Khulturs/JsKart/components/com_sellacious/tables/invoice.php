<?php
/**
 * @version     __DPELOY_VERSION__
 * @package     sellacious
 *
 * @copyright   Copyright (C) 2012-2020 Bhartiy Web Technologies. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Aditya Chakraborty <info@bhartiy.com> - http://www.bhartiy.com
 */
// no direct access.
defined('_JEXEC') or die;

/**
 * Invoice Table class
 *
 * @since  1.7.0
 */
class SellaciousTableInvoice extends SellaciousTable
{
	/**
	 * Constructor
	 *
	 * @param   JDatabaseDriver  &$db  Database instance
	 */
	public function __construct(&$db)
	{
		parent::__construct('#__sellacious_order_invoices', 'id', $db);
	}
}
