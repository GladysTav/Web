<?php
/**
 * @version     2.0.0
 * @package     sellacious
 *
 * @copyright   Copyright (C) 2012-2020 Bhartiy Web Technologies. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Aditya Chakraborty <info@bhartiy.com> - http://www.bhartiy.com
 */
// no direct access
defined('_JEXEC') or die;

/**
 * User favorites Table class
 *
 * @since  2.0.0
 */
class SellaciousTableUserFavorite extends SellaciousTable
{
	/**
	 * Constructor
	 *
	 * @param  JDatabaseDriver  $db  A database connector object
	 *
	 * @since  2.0.0
	 */
	public function __construct(&$db)
	{
		parent::__construct('#__sellacious_user_favorites', 'id', $db);
	}
}
