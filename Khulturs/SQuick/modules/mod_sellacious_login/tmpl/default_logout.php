<?php
/**
 * @version     2.0.0
 * @package     Sellacious Login Module
 *
 * @copyright   Copyright (C) 2012-2020 Bhartiy Web Technologies. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Izhar Aazmi <info@bhartiy.com> - http://www.bhartiy.com
 */
defined('_JEXEC') or die;

JHtml::_('behavior.keepalive');
JHtml::_('ctech.bootstrap');
?>
<div class="ctech-wrapper">
    <form action="<?php echo JRoute::_('index.php'); ?>" method="post" id="login-form" class="form-vertical">

        <div class="login-greeting">
            <?php echo JText::sprintf('MOD_SELLACIOUS_LOGIN_HINAME', htmlspecialchars($user->get('name'), ENT_COMPAT, 'UTF-8')); ?>
        </div>
        <br>
        <div class="logout-button">
            <input type="submit" name="Submit" class="ctech-btn ctech-btn-primary ctech-btn-sm" value="<?php echo JText::_('JLOGOUT'); ?>" />
            <input type="hidden" name="option" value="com_users" />
            <input type="hidden" name="task" value="user.logout" />
            <input type="hidden" name="return" value="<?php echo $return; ?>" />
            <?php echo JHtml::_('form.token'); ?>
        </div>
    </form>
</div>
