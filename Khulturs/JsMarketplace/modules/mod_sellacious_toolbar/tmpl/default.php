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

JHtml::_('bootstrap.framework');
JHtml::_('stylesheet', 'mod_sellacious_toolbar/default.css', null, true);
JHtml::_('script', 'mod_sellacious_toolbar/default.js', null, true);
JHtml::_('stylesheet', 'com_sellacious/font-awesome.min.css', null, true);
?>
<div class="mod-sellacious-toolbar <?php echo $bar_position . ' ' . $class_sfx; ?>">
	<div class="toolbar">
		<div class="toolbar-inner">
			<ul class="toolbar-nav">
				<?php if ($userStatus == 'loggedIn'): ?>
					<?php if ($logo): ?>
						<li>
							<a class="brand" href="<?php echo $siteurl;?>" name="top">
								<img class="logo-icon" src="<?php echo $logo ?>" alt="<?php echo htmlspecialchars($sitename) ?>">
							</a>
						</li>
					<?php endif; ?>
					<?php if ($dashboard_menu): ?>
					<li><a target=_blank href="<?php echo $dashboardUrl;?>"><i class="fa fa-dashboard"></i> <span><?php echo $dashboard;?></span></a></li>
					<li class="divider-vertical"></li>
					<?php endif; ?>

					<?php if ($orders_menu): ?>
					<li><a target=_blank href="<?php echo $ordersUrl;?>"><i class="fa fa-paperclip"></i> <span><?php echo JText::_('MOD_SELLACIOUS_TOOLBAR_MENU_ORDERS');?></span><sup class="notifications"><?php echo count($new_orders); ?></sup></a></li>
					<li class="divider-vertical"></li>
					<?php endif; ?>

					<?php if ($add_product || $product_catalog): ?>
					<li class="dropdown">
						<a href="javascript:void"><i class="fa fa-book"></i> <span><?php echo JText::_('MOD_SELLACIOUS_TOOLBAR_MENU_PRODUCTS');?></span></a>
						<ul class="dropdown-menu">
							<?php if ($add_product): ?>
							<li><a target=_blank href="<?php echo $addProductUrl;?>"><i class="fa fa-plus"></i> <?php echo JText::_('MOD_SELLACIOUS_TOOLBAR_MENU_ADD_PRODUCT');?></a></li>
							<?php endif; ?>
							<?php if ($product_catalog): ?>
							<li><a target=_blank href="<?php echo $productsUrl;?>"><i class="fa fa-book"></i> <?php echo JText::_('MOD_SELLACIOUS_TOOLBAR_MENU_PRODUCT_CATALOG');?></a></li>
							<?php endif; ?>
						</ul>
					</li>
					<li class="divider-vertical"></li>
					<?php endif; ?>

					<?php if ($buy_now): ?>
					<li><a target=_blank href="<?php echo $buyNowBtnUrl;?>"><i class="fa fa-plus"></i> <span><?php echo JText::_('MOD_SELLACIOUS_TOOLBAR_MENU_BTN_BUY_NOW');?></span></a></li>
					<li class="divider-vertical"></li>
					<?php endif;?>

					<?php if ($edit_product && $editProductUrl): ?>
					<li><a target=_blank href="<?php echo $editProductUrl;?>"><i class="fa fa-pencil"></i> <span><?php echo JText::_('MOD_SELLACIOUS_TOOLBAR_MENU_EDIT_PRODUCT');?></span></a></li>
					<li class="divider-vertical"></li>
					<?php endif; ?>

					<?php if ($messages_menu): ?>
					<li><a target=_blank href="<?php echo $messagesUrl;?>"><i class="fa fa-envelope"></i> <span><?php echo JText::_('MOD_SELLACIOUS_TOOLBAR_MENU_MESSAGES');?></span><sup class="notifications"><?php echo count($unread_messages); ?></sup></a></li>
					<li class="divider-vertical"></li>
					<?php endif; ?>
					<?php if ($user_menu): ?>
					<li class="dropdown pull-right profile-menu">
						<a class="btn dropdown-toggle" data-toggle="dropdown" href="#">
							<span><?php echo JText::sprintf('MOD_SELLACIOUS_TOOLBAR_MENU_USER_GREETING', $user->get('name'));?></span>
							<?php echo $avatar; ?>
						</a>
						<ul class="dropdown-menu">
							<li><a target="_blank" href="<?php echo $profileUrl; ?>"><i class="icon-pencil"></i> <?php echo JText::_('MOD_SELLACIOUS_TOOLBAR_MENU_EDIT_PROFILE');?></a></li>
							<li>
								<form action="<?php echo JRoute::_('index.php', true); ?>" method="post" name="toolbar-logout-form" class="form-vertical toolbar-logout-form">
									<a href="#" class="logout-link">
										<i class="icon-share"></i>
										<span class="logout-button">
											<input type="submit" name="Submit" class="btn btn-primary" value="<?php echo JText::_('JLOGOUT'); ?>" />
										</span>
									</a>
									<input type="hidden" name="option" value="com_users" />
									<input type="hidden" name="task" value="user.logout" />
									<input type="hidden" name="return" value="<?php echo $returnurl; ?>" />
									<?php echo JHtml::_('form.token'); ?>
								</form>
							</li>
						</ul>
					</li>
					<?php endif; ?>
				<?php elseif ($userStatus == 'loggedOut' && $user_menu == 1): ?>
					<li class="pull-right toolbar-login-button">
						<a href="#">
							<span><?php echo JText::_('JLOGIN'); ?></span> <i class="fa fa-user"></i>
						</a>
						<div class="login-menu">
							<form action="<?php echo JRoute::_('index.php?option=com_users&task=user.login'); ?>" method="post" class="form-validate">
								<input type="text" placeholder="Username" name="username">
								<input type="password" placeholder="Password" name="password">
								<input type="submit" value="<?php echo JText::_('JLOGIN'); ?>">
								<input type="hidden" name="return" value="<?php echo $returnurl; ?>" />
								<?php if($register_link): ?>
									<a href="<?php echo $register_link; ?>" class="register-link"><?php echo JText::_('JREGISTER'); ?></a>
								<?php endif; ?>

								<?php echo JHtml::_('form.token'); ?>
							</form>
						</div>
					</li>
					<?php if($sell_with_us): ?>
						<li class="pull-right">
							<a href="<?php echo $sell_with_us; ?>">
								<span><?php echo JText::_('MOD_SELLACIOUS_TOOLBAR_MENU_SELL_WITH_US_LINK'); ?> <i class="fa fa-money"></i></span>
							</a>
						</li>
					<?php endif; ?>
				<?php endif; ?>
			</ul>
		</div>
		<!--/.navbar-inner -->
	</div>
	<!--/.navbar -->
</div>
