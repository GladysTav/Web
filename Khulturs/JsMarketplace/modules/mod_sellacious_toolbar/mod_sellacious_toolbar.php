<?php
/**
 * @version     1.7.4
 * @package     Sellacious Toolbar Module
 *
 * @copyright   Copyright (C) 2012-2019 Bhartiy Web Technologies. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Aditya Chakraborty <info@bhartiy.com> - http://www.bhartiy.com
 */

// no direct access
defined('_JEXEC') or die('Restricted access');

// Include the helper functions only once
JLoader::register('ModSellaciousToolbar', __DIR__ . '/helper.php');
jimport('sellacious.loader');

$helper    = SellaciousHelper::getInstance();
$user      = JFactory::getUser();
$userStatus = ModSellaciousToolbar::isUserValid();

$class_sfx       = $params->get('class_sfx', '');
$bar_position    = $params->get('bar_position', 'top');

if ($userStatus == 'hide'):
	return;
endif;

$logo                  = $params->get('logo', '');
$user_menu             = $params->get('usermenu', 1);
$dashboard_menu        = $params->get('show_dashboard_menu', 1);
$orders_menu           = $params->get('show_orders_menu', 1);
$unread_orders_setting = $params->get('unread_orders_setting', '');
$unread_orders_setting = is_array($unread_orders_setting) ? array_filter($unread_orders_setting) : array_filter(explode(',', $unread_orders_setting));
$add_product           = $params->get('show_add_product_menu', 1);
$product_catalog       = $params->get('show_product_catalog_menu', 1);
$buy_now               = $params->get('show_buy_now_menu', 1);
$edit_product          = $params->get('show_edit_product_menu', 1);
$messages_menu         = $params->get('show_messages_menu', 1);
$sell_with_us          = $params->get('sell_with_us_link');
$register_link         = $params->get('register_link');
$siteurl               = JUri::root();
$sitename              = $app->get('sitename');
$sellaciousUrl         = $siteurl . JPATH_SELLACIOUS_DIR . '/';
$profileUrl            = $sellaciousUrl . 'index.php?option=com_sellacious&view=profile';
$dashboardUrl          = $sellaciousUrl . 'index.php?option=com_sellacious&view=dashboard';
$ordersUrl             = $sellaciousUrl . 'index.php?option=com_sellacious&view=orders';
$productsUrl           = $sellaciousUrl . 'index.php?option=com_sellacious&view=products';
$addProductUrl         = $sellaciousUrl . 'index.php?option=com_sellacious&task=product.add';
$buyNowBtnUrl          = $sellaciousUrl . 'index.php?option=com_sellacious&task=productbutton.add';
$messagesUrl           = $sellaciousUrl . 'index.php?option=com_sellacious&view=messages';
$returnurl             = base64_encode(JUri::getInstance()->toString());
$editProductUrl        = ModSellaciousToolbar::getEditProductLink();
$avatar                = $helper->media->getImage('user.avatar', $user->id, false);
$avatar                = $avatar ? '<img class="user-avatar" src="' . $avatar . '" alt="">' : JHtml::_('image', 'mod_sellacious_toolbar/no-profile-image.png', '', 'class="user-avatar"', true);
$dashboard             = $user->authorise('core.admin') ? JText::_('MOD_SELLACIOUS_TOOLBAR_MENU_ADMIN_DASHBOARD') : ($helper->staff->is($user->id) ? JText::_('MOD_SELLACIOUS_TOOLBAR_MENU_STAFF_DASHBOARD') : JText::_('MOD_SELLACIOUS_TOOLBAR_MENU_SELLER_DASHBOARD'));
$unread_messages       = ModSellaciousToolbar::getUnreadMessages();
$new_orders            = ModSellaciousToolbar::getNewOrders($unread_orders_setting);

require JModuleHelper::getLayoutPath('mod_sellacious_toolbar', 'default');
