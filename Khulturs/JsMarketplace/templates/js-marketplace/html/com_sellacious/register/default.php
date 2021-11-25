<?php
/**
 * @version     1.7.3
 * @package     sellacious
 *
 * @copyright   Copyright (C) 2012-2019 Bhartiy Web Technologies. All rights reserved.
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

JHtml::_('script', 'com_sellacious/util.noframes.js', false, true);
JHtml::_('script', 'com_sellacious/util.validator-mobile.js', false, true);

JText::script('COM_SELLACIOUS_VALIDATION_FORM_FAILED');

JHtml::_('script', 'com_sellacious/fe.view.profile.js', false, true);
JHtml::_('stylesheet', 'com_sellacious/fe.component.css', null, true);
JHtml::_('stylesheet', 'com_sellacious/fe.view.profile.css', null, true);
JHtml::_('stylesheet', 'com_sellacious/fe.view.seller.css', null, true);
JHtml::_('stylesheet', 'com_sellacious/fe.register.css', null, true);


?>
<script>
	Joomla.submitbutton = function(task, form) {
		if (document.formvalidator.isValid(document.getElementById('register-form'))) {
			Joomla.submitform(task, form);
		}
	}
</script>

<div class="page-brdcrmb">
	<?php
	jimport('joomla.application.module.helper');
	$modules = JModuleHelper::getModules('breadcrumbs');
	foreach ($modules as $module):
		$renMod = JModuleHelper::renderModule($module);

		if (!empty($renMod) && ($module->module == "mod_breadcrumbs")):?>
			<div class="relatedproducts <?php echo (isset($module->class_sfx)) ? $module->class_sfx : ''; ?>">
				<div class="moreinfo-box">
					<?php
					if ($module->showtitle == 1) { ?>
						<h3><?php echo $module->title ?></h3>
					<?php } ?>
					<div class="innermoreinfo">
						<div class="relatedinner">
							<?php echo trim($renMod); ?>
						</div>
					</div>
				</div>
			</div>
		<?php else: ?>
			<div class="product-bottom <?php echo (isset($module->class_sfx)) ? $module->class_sfx : ''; ?>">
				<?php echo trim($renMod); ?>
			</div>
		<?php endif; ?>

	<?php endforeach; ?>
</div>

<?php
$fieldsets = $this->form->getFieldsets();
$accordion = array('parent' => true, 'toggle' => false, 'active' => 'profile_accordion_basic');

echo JHtml::_('bootstrap.startAccordion', 'profile_accordion', $accordion);
?>

<div class="reg-seller-heading">
    <h2>Create your Js-Marketplace Account</h2>
</div>


<form action="<?php echo JRoute::_('index.php?option=com_sellacious&view=register&catid=' . $this->state->get('seller.catid')); ?>"
	method="post" id="register-form" name="register-form" class="register-form form-validate form-horizontal">
    <fieldset class="mb-3">
        <div class="control-group">
            <div class="controls text-right">
                <button type="button" class="top-submit btn btn-primary pull-right btn-sm"
                        onclick="return Joomla.submitbutton('register.save', this.form);"><?php echo JText::_('JSUBMIT') ?></button>
            </div>
        </div>
    </fieldset>
    <div class="accordion-box">
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
    </div>

	<div class="clearfix"></div>
	<br>

	<fieldset class="w100p captcha-fieldset">
		<?php
		$fields = $this->form->getFieldset('captcha');

		foreach ($fields as $field):
			if ($field->hidden):
				echo $field->input;
			else:
				?>
				<div class="control-group">
					<?php if ($field->label): ?>
						<!--<div class="control-label"><?php echo $field->label ?></div>-->
						<div class="controls"><?php echo $field->input ?></div>
					<?php else: ?>
						<div class="controls col-md-12"><?php echo $field->input ?></div>
					<?php endif; ?>
				</div>
			<?php
			endif;
		endforeach;
		?>
        <div class="control-group submit-bottom">
            <div class="controls text-right">
                <button type="button" class="btn btn-primary pull-right btn-sm"
                        onclick="return Joomla.submitbutton('register.save', this.form);"><?php echo JText::_('JSUBMIT') ?></button>
				<input type="hidden" name="return" value="<?php echo base64_encode(JUri::getInstance()->toString()); ?>" />
			</div>
        </div>
	</fieldset>


	<fieldset>

	</fieldset>

    <div class="clearfix"></div>


    <input type="hidden" name="task"/>
	<?php echo JHtml::_('form.token'); ?>
</form>

<?php

$script = <<<js

	jQuery(document).ready(function($) {
	  $('#profile_accordion').find('.accordion-group').first().find('.accordion-heading a').click();
	});

js;

JFactory::getDocument()->addScriptDeclaration($script);

echo JHtml::_('bootstrap.endAccordion'); ?>
<div class="clearfix"></div>
