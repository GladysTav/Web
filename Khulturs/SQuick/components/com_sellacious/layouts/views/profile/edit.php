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

/** @var SellaciousViewUser $this */

JHtml::_('behavior.formvalidator');
JHtml::_('jquery.framework');
JHtml::_('behavior.keepalive');
JHtml::_('bootstrap.tooltip');

$doc = JFactory::getDocument();

JText::script('COM_SELLACIOUS_VALIDATION_FORM_FAILED');

JHtml::_('ctech.bootstrap');
JHtml::_('ctech.select2');

JHtml::_('script', 'com_sellacious/util.validator-mobile.js', false, true);

JHtml::_('stylesheet', 'com_sellacious/font-awesome.min.css', null, true);
JHtml::_('stylesheet', 'com_sellacious/fe.component.css', null, true);
JHtml::_('stylesheet', 'com_sellacious/fe.view.profile.css', null, true);
JHtml::_('stylesheet', 'com_sellacious/fe.view.custom-attributes.css', array('relative' => true, 'version' => S_VERSION_CORE));

$fieldsets = $this->form->getFieldsets();
$tab       = array('active' => 'profile_tabs_basic', 'vertical' => true);
?>
<script>
	Joomla.submitbutton = function (task, form) {
		if (document.formvalidator.isValid(document.getElementById('adminForm'))) {
			Joomla.submitform(task, form);
		}
	}
</script>
<div class="ctech-wrapper">
	<div class="profile-tabs">
		<a href="<?php echo JRoute::_('index.php?option=com_sellacious&view=addresses'); ?>"
		   class="ctech-btn ctech-btn-primary ctech-btn-sm ctech-float-right">
			<?php echo JText::_('COM_SELLACIOUS_ADDRESSES_MANAGE_LABEL') ?>
		</a>
		<div class="clearfix"></div>
		<br>
		<form action="<?php echo JUri::getInstance()->toString(); ?>"
			  method="post" name="adminForm" id="adminForm" class="form-validate form-horizontal profile-tabs" enctype="multipart/form-data">
			<?php echo JHtml::_('ctechBootstrap.startTabs', 'profile_tabs', $tab); ?>

			<?php
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
							echo $this->loadTemplate('fieldset', $fieldsets[$segment]);
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
									echo $this->loadTemplate('fieldset', $fieldsets[$key]);
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
						echo $this->loadTemplate('fieldset', $fieldsets[$key]);
					}
					catch (Exception $e)
					{
					}

					unset($fieldsets[$key]);
				}
			}
			?>
			<br>
			<div class="control-group captcha-input">
				<div class="controls col-md-12"><?php echo $this->form->getInput('captcha'); ?></div>
			</div>
			<div class="clearfix"></div>

			<?php echo JHtml::_('ctechBootstrap.endTabs'); ?>
			<button type="button" class="ctech-btn ctech-btn-primary ctech-btn-sm ctech-float-right"
					onclick="return Joomla.submitbutton('profile.save', this.form);"><i class="fa fa-save"></i> <?php
				echo strtoupper(JText::_('COM_SELLACIOUS_SAVE')); ?></button>

			<input type="hidden" name="task"/>
			<?php echo JHtml::_('form.token'); ?>
		</form>

	</div>
	<div class="ctech-clearfix"></div>

	<script type="text/javascript">
		jQuery('select').select2();
	</script>
</div>
