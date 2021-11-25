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
 * Template Defaults Table class
 *
 * @since  1.7.0
 */
class SellaciousTableTemplateDefault extends SellaciousTable
{
	/**
	 * Constructor
	 *
	 * @param  JDatabaseDriver  $db  A database connector object
	 *
	 * @since  1.7.0
	 */
	public function __construct(&$db)
	{
		parent::__construct('#__sellacious_viewtemplate_defaults', 'id', $db);
	}
}
