<?php
/**
 * @version     2.0.0
 * @package     sellacious
 *
 * @copyright   Copyright (C) 2012-2020 Bhartiy Web Technologies. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Izhar Aazmi <info@bhartiy.com> - http://www.bhartiy.com
 */
// no direct access
defined('_JEXEC') or die;

/** @var  SellaciousViewUser $this */
JHtml::_('behavior.formvalidator');
JHtml::_('jquery.framework');
JHtml::_('behavior.keepalive');
JHtml::_('bootstrap.tooltip');
JHtml::_('ctech.bootstrap');
JHtml::_('ctech.select2');

JHtml::_('script', 'com_sellacious/util.validator-mobile.js', false, true);
JHtml::_('script', 'com_sellacious/fe.view.profile.js', false, true);

JHtml::_('stylesheet', 'com_sellacious/fe.component.css', null, true);
JHtml::_('stylesheet', 'com_sellacious/fe.view.profile.css', null, true);
JHtml::_('stylesheet', 'com_sellacious/fe.view.custom-attributes.css',  array('relative' => true, 'version' => S_VERSION_CORE));

JText::script('COM_SELLACIOUS_VALIDATION_FORM_FAILED');
?>
<script>
	Joomla.submitbutton = function (task, form) {
		if (document.formvalidator.isValid(document.getElementById('register-form'))) {
			Joomla.submitform(task, form);
		}
	}
</script>
<?php
$fieldsets = $this->form->getFieldsets();
$tabs      = array('active' => 'profile_tabs_basic', 'vertical' => true);
?>
<div class="ctech-wrapper">
	<form action="<?php echo JRoute::_('index.php?option=com_sellacious&view=register&catid=' . $this->state->get('seller.catid')); ?>"
		  method="post" id="register-form" name="register-form" class="profile-tabs">

		<?php
		echo JHtml::_('ctechBootstrap.startTabs', 'profile_tabs', $tabs);
		// Get a list of configured segments
		$segments = $this->helper->config->get('profile_fieldset_order');

		// Display configured segments
		if (is_array($segments))
		{
			foreach ($segments as $segment)
			{
				// The captcha segment is not listed so we won't need to check for it here to skip
				if (!empty($fieldsets[$segment]))
				{
					try
					{
						echo $this->loadTemplate('fieldset', array($fieldsets[$segment]));
					}
					catch (Exception $e)
					{
					}

					unset($fieldsets[$segment]);
				}
				// There are multiple custom fieldsets with names like: fs_12, fs_103
				elseif ($segment == 'custom')
				{
					foreach (array_keys($fieldsets) as $key)
					{
						if (preg_match('/^fs_\d+$/i', $key))
						{
							try
							{
								echo $this->loadTemplate('fieldset', array($fieldsets[$key], $segment));
							}
							catch (Exception $e)
							{
							}

							unset($fieldsets[$key]);
						}
					}
				}
			}
		}

		// Display remaining segments except captcha
		foreach (array_keys($fieldsets) as $key)
		{
			if ($key != 'captcha')
			{
				try
				{
					echo $this->loadTemplate('fieldset', array($fieldsets[$key]));
				}
				catch (Exception $e)
				{
				}

				unset($fieldsets[$key]);
			}
		}
		?>

		<div class="clearfix"></div>
		<br>


		<?php echo JHtml::_('ctechBootstrap.endTabs'); ?><?php
		$fields = $this->form->getFieldset('captcha');

		foreach ($fields as $field):
			if ($field->hidden):
				echo $field->input;
			else:
				?>
				<div class="ctech-aside-md">
					<div class="ctech-form-group">
						<?php if ($field->label): ?>
							<div class="ctech-col-form-label"><?php echo $field->label ?></div>
							<div class="ctech-col-form-input"><?php echo $field->input ?></div>
						<?php else: ?>
							<div class="ctech-col-form-input"><?php echo $field->input ?></div>
						<?php endif; ?>
					</div>
				</div>
			<?php
			endif;
		endforeach;
		?>

		<div class="clearfix"></div>
		<br>

		<div class="ctech-aside-md">
			<div class="ctech-form-group text-right">
				<div class="ctech-col-form-input">
					<button type="button" class="ctech-btn ctech-btn-primary"
							onclick="return Joomla.submitbutton('register.save', this.form);"><?php echo JText::_('JSUBMIT') ?></button>
				</div>
			</div>
		</div>

		<input type="hidden" name="task"/>
		<?php echo JHtml::_('form.token'); ?>

	</form>
	<div class="clearfix"></div>

	<script type="text/javascript">
		jQuery(document).ready(function ($) {
			$('.profile-tabs').find('select').select2();
		})
	</script>
</div>
