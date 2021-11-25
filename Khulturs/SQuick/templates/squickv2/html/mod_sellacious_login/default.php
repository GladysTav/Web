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
JHtml::_('bootstrap.tooltip');
JHtml::_('ctech.bootstrap');

JHtml::_('stylesheet', 'mod_sellacious_login/module.css', array('relative' => true, 'version' => S_VERSION_CORE));
JHtml::_('script', 'mod_sellacious_login/module.js', array('relative' => true, 'version' => S_VERSION_CORE));

JText::script('MOD_SELLACIOUS_LOGIN_PLACEHOLDER_PW');
JText::script('MOD_SELLACIOUS_LOGIN_PLACEHOLDER_OTP');
JText::script('MOD_SELLACIOUS_LOGIN_SENDING_OTP');
JText::script('MOD_SELLACIOUS_LOGIN_OTP_NOT_SENT');
JText::script('MOD_SELLACIOUS_LOGIN_LOGIN_FAILED');
JText::script('MOD_SELLACIOUS_LOGIN_INVALID_INPUT');
JText::script('JGLOBAL_PASSWORD');
JText::script('MOD_SELLACIOUS_LOGIN_OTP');

JFactory::getDocument()->addScriptOptions('mod_sellacious_login.params', $loginOpts);
?>
<div class="ctech-wrapper">
    <form action="<?php echo JRoute::_('index.php', true, $params->get('usesecure', 0)); ?>" method="post"
          id="msl-login-form" class="form-inline">

        <?php if ($params->get('pretext')): ?>
            <div class="pretext">
                <p><?php echo $params->get('pretext_tr') ? JText::_($params->get('pretext')) : $params->get('pretext'); ?></p>
            </div>
        <?php endif; ?>

        <div class="msl-container sella-container <?php echo $loginOpts->get('pw') ? 'msl-use-pw' : 'msl-use-otp'; ?>">

            <div class="ctech-clearfix"></div>
            <div class="ctech-alert msl-message ctech-d-none">

            </div>
            <div class="ctech-clearfix"></div>

            <div class="ctech-form-group sella-group">
                <label for="modslgn-username" class="sella-label"><?php echo JText::_('MOD_LOGIN_VALUE_USERNAME'); ?></label>
                    <input type="text" id="modslgn-username" class="ctech-form-control ctech-input-small msl-username ctech-form-control-sm" tabindex="0" size="18"
                           placeholder="<?php echo JText::_('MOD_LOGIN_VALUE_USERNAME'); ?>"/>
					<?php if ($loginOpts->get('otp') && $loginOpts->get('pw')): ?>
						<a href="#" class="msl-use ctech-text-primary">
							<span class="msl-use-otp"><?php echo JText::_('MOD_SELLACIOUS_LOGIN_USE_PW'); ?></span>
							<span class="msl-use-pw"><?php echo JText::_('MOD_SELLACIOUS_LOGIN_USE_OTP'); ?></span>
						</a>
					<?php endif; ?>
            </div>

            <div class="ctech-form-group sella-group">
                <label for="modslgn-passwd"
                       class="msl-password-label sella-label"><?php echo JText::_('JGLOBAL_PASSWORD'); ?></label>
				<?php if ($loginOpts->get('pw')): ?>
					<input type="password" id="modslgn-passwd" class="ctech-form-control ctech-input-small msl-password ctech-form-control-sm" autocomplete="off"
						   tabindex="0" size="18"
						   placeholder="<?php echo JText::_('MOD_SELLACIOUS_LOGIN_PLACEHOLDER_PW'); ?>"/>
				<?php else: ?>
					<input type="text" id="modslgn-passwd" class="ctech-form-control ctech-input-small msl-password ctech-form-control-sm" autocomplete="off"
						   tabindex="0" size="18" maxlength="6"
						   placeholder="<?php echo JText::_('MOD_SELLACIOUS_LOGIN_PLACEHOLDER_OTP'); ?>"/>
				<?php endif; ?>
				<a class="ctech-text-primary msl-otp-resend msl-use-otp"
				   href="#"><?php echo JText::_('MOD_SELLACIOUS_LOGIN_RESEND_PW'); ?></a>
            </div>

             <div class="ctech-form-group">
                <button type="button" tabindex="0"
                        class="ctech-btn ctech-btn-primary ctech-rounded msl-login-button ctech-btn-sm sella-btn"><?php echo JText::_('JLOGIN'); ?></button>
			 </div>

            <ul class="unstyled sella-list">
                <?php if ($allowClientRegistration) : ?>
                    <li>
                        <a class="sella-list-item sella-register"
                           href="<?php echo JRoute::_('index.php?option=com_sellacious&view=register'); ?>">
                            <?php echo JText::_('MOD_SELLACIOUS_LOGIN_REGISTER'); ?> <i class="fa fa-angle-right"></i></a>
                    </li>
                <?php endif; ?>
                <?php if ($allowSellerRegistration) : ?>
                    <li>
                        <a class="sella-list-item sella-seller"
                           href="<?php echo JRoute::_('index.php?option=com_sellacious&view=seller'); ?>">
                            <?php echo JText::_('MOD_SELLACIOUS_LOGIN_REGISTERATION_FOR_SELLER'); ?>
                        <i class="fa fa-angle-right"></i></a>
                    </li>
                <?php endif; ?>
                <li>
                    <a class="sella-list-item sella-reset"
                       href="<?php echo JRoute::_('index.php?option=com_users&view=reset'); ?>">
                        <?php echo JText::_('MOD_SELLACIOUS_LOGIN_FORGOT_YOUR_PASSWORD'); ?></a>
                </li>
            </ul>

                <input type="hidden" name="return" value="<?php echo $return; ?>"/>
            <?php echo JHtml::_('form.token'); ?>
        </div>

        <?php if ($params->get('posttext')) : ?>
            <div class="ctech-posttext">
                <p><?php echo $params->get('posttext_tr') ? JText::_($params->get('posttext')) : $params->get('posttext'); ?></p>
            </div>
        <?php endif; ?>
    </form>
</div>
