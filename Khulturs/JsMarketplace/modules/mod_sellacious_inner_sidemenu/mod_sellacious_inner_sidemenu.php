<?php
/**
 * @version     1.1.0
 * @package     Slider Ninja
 *
 * @copyright   Copyright (C) 2017. Bhartiy Web Technologies. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Chandni <info@bhartiy.com> - http://www.bhartiy.com
 */
// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );
// Include the syndicate functions only once
require_once( dirname(__FILE__).'/helper.php' );

$Content = mod_sellacious_inner_sidemenu_Helper::getContent($params);
$helper = SellaciousHelper::getInstance();

require( JModuleHelper::getLayoutPath( 'mod_sellacious_inner_sidemenu' ) );

?>
