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
 * Table for download log of e-product media
 *
 * @since   1.2.0
 */
class SellaciousTableEProductDownload extends SellaciousTable
{
	/**
	 * Constructor
	 *
	 * @param   JDatabaseDriver  $db
	 *
	 * @since   1.2.0
	 */
	public function __construct(&$db)
	{
		parent::__construct('#__sellacious_eproduct_downloads', 'id', $db);
	}
}
