<?php
/**
 * @version     1.7.4
 * @package     sellacious
 *
 * @copyright   Copyright (C) 2012-2019 Bhartiy Web Technologies. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Izhar Aazmi <info@bhartiy.com> - http://www.bhartiy.com
 */
defined('_JEXEC') or die;

JHtml::_('jquery.framework');
JHtml::_('formbehavior.chosen', 'select');
JHtml::_('stylesheet', 'com_sellacious/installer.install.css', false, true);

$app     = JFactory::getApplication();
$message = $app->getUserState('com_installer.extension_message', '');

$app->setUserState('com_installer.redirect_url', '');
$app->setUserState('com_installer.message', '');
$app->setUserState('com_installer.extension_message', '');

JText::script('COM_SELLACIOUS_INSTALL_INSTALLATION_CONFIRM_RESET');
?>
<div class="row">
	<form action="<?php echo JRoute::_('index.php?option=com_sellacious') ?>" method="post" name="adminForm" id="adminForm">
		<p><?php echo $message ?></p>
		<div class="span12">
			<div class="alert alert-info center">
				<?php $url = JUri::root() . 'sellacious' ?>
				<?php echo JText::sprintf('COM_SELLACIOUS_INSTALL_BACKOFFICE_LOGIN_NOTE', $url) ?><br><br>
				<button type="button" class="btn btn-primary btn-large strong" onclick="Joomla.submitbutton('', this.form);"><i
							class="icon-out-2"></i> <?php echo JText::_('COM_SELLACIOUS_INSTALL_BACKOFFICE_LAUNCH_BUTTON') ?></button>
				<input type="hidden" name="redirect" value="1"/>
				<label for="auto_redirect" style="margin-top: 10px;">
					<input type="checkbox" name="auto_redirect" id="auto_redirect" style="margin-top: -2px;" value="1"/>
					<?php echo JText::_('COM_SELLACIOUS_INSTALL_BACKOFFICE_AUTO_REDIRECT_CHECK_LABEL'); ?>
				</label>
			</div>
		</div>
		<input type="hidden" name="task" value=""/>
		<?php echo JHtml::_('form.token'); ?>
	</form>
</div>
