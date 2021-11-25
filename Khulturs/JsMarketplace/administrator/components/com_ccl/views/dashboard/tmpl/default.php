<?php

/**
 * @version    CVS: 1.0.0
 * @package    Com_Ccl
 * @author     Component Creator <info@component-creator.com>
 * @copyright  2016 Component Creator
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die;

JHtml::_('bootstrap.popover');
$pluginsEnabled = false;
?>

    <style>
        .social-plugin-box {
            padding: 15px;
            border: 1px solid #CCCCCC;
        }

        .social-plugin-box.enabled {
            border: 1px solid #EEEEEE;
            background-color: #EEEEEE;
        }

        #render-options {
            margin-top: 30px;
        }

        @media only screen and (max-device-width: 767px) {
            .social-plugin-box {
                margin-bottom: 10px;
            }
        }

        .enable-plugin-form {
            display: inline;
        }

        div.modal {
            width: 40%;
            margin-left: -20%;
        }
    </style>

    <form action="<?php echo JRoute::_('index.php?option=com_ccl'); ?>" method="post" name="adminForm"
          id="adminForm">

        <div class="span12">
			<?php if (!$this->isUserPluginEnabled): ?>
                <div class="alert alert-error"><?php echo JText::_('COM_CCL_DASHBOARD_USER_PLUGIN_NOT_ENABLED'); ?></div>
			<?php endif; ?>
			<?php if (!$this->isSystemPluginEnabled): ?>
                <div class="alert alert-error"><?php echo JText::_('COM_CCL_DASHBOARD_SYSTEM_PLUGIN_NOT_ENABLED'); ?></div>
			<?php endif; ?>
            <h1><?php echo JText::_('COM_CCL_DASHBOARD_HEADER'); ?></h1>
            <p><?php echo JText::sprintf('COM_CCL_DASHBOARD_DESCRIPTION', 'https://www.component-creator.com', 'https://www.component-creator.com'); ?></p>
            <div class="span12">
				<?php if ($this->isUserPluginEnabled && $this->isSystemPluginEnabled): ?>
					<?php foreach ($this->plugins as $plugin): ?>
						<?php if ($plugin->enabled && !$pluginsEnabled): ?>
							<?php $pluginsEnabled = true; ?>
						<?php endif; ?>
                        <div class="span3 social-plugin-box <?php echo $plugin->enabled ? 'enabled' : ''; ?>">
                            <h3 class="text-center"><?php echo JText::sprintf('COM_CCL_LOGIN_PROVIDER_TEXT', ucfirst($plugin->element)); ?></h3>
                            <p class="text-center">
								<?php echo JText::sprintf('Status: %s', $plugin->enabled ? JText::_('COM_CCL_ENABLED') : JText::_('COM_CCL_DISABLED')); ?>
                            </p>
                            <p class="text-center">
                                <a class="btn btn-<?php echo $plugin->enabled ? 'primary' : 'success'; ?>"
                                   href="#<?php echo $plugin->element; ?>-modal" role="button" data-toggle="modal"
                                   type="button"><?php echo $plugin->enabled ? JText::_('COM_CCL_DETAILS') : JText::_('COM_CCL_ENABLED'); ?></a>
                            </p>
                        </div>
					<?php endforeach; ?>
				<?php endif; ?>
            </div>

			<?php if ($pluginsEnabled): ?>
                <div class="span12" id="render-options">
                    <div class="span12">
                        <h3><?php echo JText::_('COM_CCL_PUBLISH_BUTTONS_HEADER'); ?></h3>
                    </div>

                    <div class="span6">
                        <h3><?php echo JText::_('COM_CCL_PUBLISH_BUTTONS_USING_MODULE_HEADER'); ?></h3>
                        <p><?php echo JText::_('COM_CCL_PUBLISH_BUTTONS_USING_MODULE_P1'); ?></p>
                        <p><?php echo JText::_('COM_CCL_PUBLISH_BUTTONS_USING_MODULE_P2'); ?></p>
                        <a href="<?php echo JRoute::_('index.php?option=com_modules&task=module.edit&id=' . $this->moduleIdentifier); ?>"
                           class="btn">
							<?php echo JText::_('COM_CCL_PUBLISH_BUTTONS_USING_MODULE_MODULE_CONFIGURATION_BTN'); ?>
                        </a>

                        <h3><?php echo JText::_('COM_CCL_DASHBOARD_OVERRIDE_MESSAGE_MODULE_HEADER'); ?></h3>
                        <p>
							<?php echo JText::sprintf('COM_CCL_DASHBOARD_OVERRIDE_MESSAGE_MODULE_DESCRIPTION', '&#x3C;website_path&#x3E;/modules/mod_ccl/tmpl/default.php', $this->templateOverrideDirectory . '/mod_ccl/default.php'); ?>
                        </p>
                    </div>

                    <div class="span6">
                        <h3><?php echo JText::_('COM_CCL_PUBLISH_BUTTONS_USING_SNIPPET_HEADER'); ?></h3>
                        <p><?php echo JText::_('COM_CCL_PUBLISH_BUTTONS_USING_SNIPPET_P1'); ?></p>
                        <pre>&lt;?php echo renderSocialLoginButtons(); ?&gt;</pre>

                        <h3><?php echo JText::_('COM_CCL_DASHBOARD_OVERRIDE_MESSAGE_SNIPPET_HEADER'); ?></h3>
                        <p>
							<?php echo JText::sprintf(
								'COM_CCL_DASHBOARD_OVERRIDE_MESSAGE_SNIPPET_LOGIN_BTN_DESCRIPTION',
								'&#x3C;website_path&#x3E;/layouts/components/com_ccl/authentication/login-button.php',
								$this->templateOverrideDirectory . '/layouts/components/com_ccl/authentication/login-button.php'
							); ?>
                        </p>
                        <p>
							<?php echo JText::sprintf(
								'COM_CCL_DASHBOARD_OVERRIDE_MESSAGE_SNIPPET_LOGIN_BTN_SEPARATOR_DESCRIPTION',
								'&#x3C;website_path&#x3E;/layouts/components/com_ccl/authentication/button-separator.php',
								$this->templateOverrideDirectory . '/layouts/components/com_ccl/authentication/button-separator.php'
							); ?>
                        </p>
                    </div>
                </div>
			<?php endif; ?>
        </div>

        <input type="hidden" name="task" value=""/>
        <input type="hidden" name="boxchecked" value="0"/>
		<?php echo JHtml::_('form.token'); ?>
    </form>

<?php if ($this->isUserPluginEnabled && $this->isSystemPluginEnabled): ?>
	<?php foreach ($this->plugins as $plugin): ?>

        <div id="<?php echo $plugin->element; ?>-modal" class="modal hide fade" tabindex="-1" role="dialog"
             aria-labelledby="myModalLabel"
             aria-hidden="true">
            <div class="modal-body">
                <h2><?php echo JText::sprintf('COM_CCL_DASHBOARD_SOCIAL_PROVIDER_MODAL_HEADER', ucfirst($plugin->element)); ?></h2>
                <p><?php echo JText::sprintf('COM_CCL_DASHBOARD_SOCIAL_PROVIDER_MODAL_DESCRIPTION', ucfirst($plugin->element), ucfirst($plugin->element)); ?></p>
                <p>
                    <a href="#"><?php echo JText::sprintf('COM_CCL_DASHBOARD_SOCIAL_PROVIDER_MODAL_CREATE_APP_LINK', ucfirst($plugin->element)); ?></a>
                </p>
                <p>
                    <strong><?php echo JText::_('COM_CCL_DASHBOARD_SOCIAL_PROVIDER_MODAL_AUTHENTICATION_URL'); ?></strong>
					<?php echo JText::_('COM_CCL_DASHBOARD_SOCIAL_PROVIDER_MODAL_AUTHENTICATION_URL_DESCRIPTION'); ?>
                </p>

				<?php $loginUrls = CclHelpersCcl::getLoginUrl($plugin->element); ?>
                <ul>
	                <?php if (is_array($loginUrls)): ?>
		                <?php foreach ($loginUrls as $languageCode => $loginUrl): ?>
                            <li><?php echo $languageCode; ?>: <?php echo $loginUrl; ?></li>
		                <?php endforeach; ?>
	                <?php else: ?>
                        <li><?php echo $loginUrls; ?></li>
	                <?php endif; ?>
                </ul>
				<?php if (!$plugin->enabled): ?>
                    <form action="<?php echo JRoute::_('index.php?option=com_ccl&task=enablePlugin'); ?>"
                          class="enable-plugin-form"
                          method="post">
                        <input type="hidden" name="plugin" value="<?php echo $plugin->extension_id; ?>">
                        <button class="btn btn-success"><?php echo JText::_('COM_CCL_DASHBOARD_SOCIAL_PROVIDER_MODAL_ACTIVATE_BTN'); ?></button>
                    </form>
				<?php endif; ?>

                <button class="btn" data-dismiss="modal" aria-hidden="true">
					<?php echo JText::_($plugin->enabled ? 'COM_CCL_DASHBOARD_SOCIAL_PROVIDER_MODAL_CLOSE' : 'COM_CCL_DASHBOARD_SOCIAL_PROVIDER_MODAL_CANCEL_ACTIVATION_BTN'); ?>
                </button>
            </div>
        </div>
	<?php endforeach; ?>
<?php endif; ?>