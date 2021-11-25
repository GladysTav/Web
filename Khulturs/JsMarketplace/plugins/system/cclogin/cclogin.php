<?php
/**
 * @package     Joomla.Plugin
 * @subpackage  System.cache
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * Component Creator social login
 *
 * @since  1.5
 */
class PlgSystemCcLogin extends JPlugin
{
	/**
	 * Register class
	 *
	 * @return  void
	 *
	 * @since   1.5
	 */
	public function onAfterInitialise()
	{
		JLoader::register('CclHelpersCcl', JPATH_ROOT . '/components/com_ccl/helpers/ccl.php');
	}
}

/**
 * Declare function for make maintenance easier
 *
 * @return string
 *
 * @since 1.0
 */
function renderSocialLoginButtons()
{
	return CclHelpersCcl::renderSocialLoginButtons();
}
