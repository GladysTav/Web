<?php
/**
 * @version    CVS: 1.0.0
 * @package    Com_Ccl
 * @author     Component Creator <info@component-creator.com>
 * @copyright  2016 Component Creator
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
// No direct access
defined('_JEXEC') or die;

JHtml::_('behavior.keepalive');
JHtml::_('behavior.formvalidator');
?>

<div id="enter-password-form">
    <h1><?php echo JText::_('COM_CCL_PASSWORD_VIEW_ENTER_PASSWORD'); ?></h1>
    <form action="<?php echo JRoute::_('index.php?option=com_users&task=user.login'); ?>" method="post"
          class="form-validate">
        <fieldset>
            <p><?php echo JText::sprintf('COM_CCL_PASSWORD_VIEW_ENTER_PASSWORD_DESCRIPTION', ucfirst($this->auth)); ?> </p>
            <div class="control-group">
                <input type="password" name="password" id="jform_password" value="" class="form-control"
                       size="30" required="" aria-required="true" aria-invalid="true"></div>
            <div class="control-group">
            </div>

            <button type="submit" class="btn btn-primary">
				<?php echo JText::_('COM_CCL_PASSWORD_VIEW_SEND_BUTTON'); ?>
            </button>

            <input type="hidden" name="username" value="<?php echo $this->username; ?>">
        </fieldset>
		<?php echo JHtml::_('form.token'); ?>
    </form>
</div>
