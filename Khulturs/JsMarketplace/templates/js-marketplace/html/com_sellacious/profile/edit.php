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

/** @var SellaciousViewUser $this */

JHtml::_('jquery.framework');
JHtml::_('script', 'com_sellacious/util.noframes.js', false, true);

JHtml::_('behavior.keepalive');
JHtml::_('bootstrap.loadCss');
JHtml::_('bootstrap.tooltip');
JHtml::_('behavior.formvalidator');

JText::script('COM_SELLACIOUS_VALIDATION_FORM_FAILED');

JHtml::_('script', 'com_sellacious/util.validator-mobile.js', false, true);
JHtml::_('stylesheet', 'com_sellacious/font-awesome.min.css', null, true);
JHtml::_('stylesheet', 'com_sellacious/fe.view.profile.css', null, true);
JHtml::_('stylesheet', 'com_sellacious/fe.view.seller.css', null, true);

JHtml::_('stylesheet', 'com_sellacious/fe.view.wishlist.css', null, true);


$fieldsets = $this->form->getFieldsets();
$accordion = array('parent' => true, 'toggle' => false, 'active' => 'profile_accordion_basic');
$me         = JFactory::getUser();


echo JHtml::_('bootstrap.startAccordion', 'profile_accordion', $accordion);
?>
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


<div class="row">

    <?php if (!$me->guest): ?>
        <div  class="col-sm-3 left-bar-wish hidden-xs hidden-sm">
            <?php
            jimport('joomla.application.module.helper');
            $modules = JModuleHelper::getModules('component-left');
            foreach ($modules as $module):
                $renMod = JModuleHelper::renderModule($module);

                if (!empty($renMod) && ($module->module == "mod_sellacious_inner_sidemenu")):?>
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
    <?php endif; ?>


    <div class="<?php echo  $me->guest ? 'col-sm-12' : 'col-lg-9 col-sm-12' ?> register_seller_box">
        <div class="profile-user-cont">
			<div class="reg-seller-heading">
				<h2><?php echo JText::_('COM_SELLACIOUS_USER_PROFILE') ?>
					<a href="<?php echo JRoute::_('index.php?option=com_sellacious&view=addresses'); ?>"
					   class="manage mb-3  pull-right"><i class="fa fa-gear"></i> <?php echo JText::_('COM_SELLACIOUS_ADDRESSES_MANAGE_LABEL') ?></a></h2>
			</div>

			<form action="<?php echo JUri::getInstance()->toString(); ?>"
				  method="post" name="adminForm" id="adminForm" class="form-validate form-horizontal" enctype="multipart/form-data">
				<button type="submit" class="top-submit btn btn-primary pull-right rounded-3 btn-sm"><?php
					echo strtoupper(JText::_('COM_SELLACIOUS_SAVE')); ?></button>


				<div class="clearfix"></div>

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
				<div class="box-bottom">
					<div class="control-group captcha-input">
						<div class="controls col-md-12 captcha"><?php echo $this->form->getInput('captcha'); ?></div>
					</div>
					<div class="clearfix"></div>

					<br>
					<div class="o-hidden">
						<button type="submit" class="submit-bottom btn btn-primary pull-right rounded-3"><?php
							echo strtoupper(JText::_('COM_SELLACIOUS_SAVE')); ?></button>
					</div>

					<input type="hidden" name="task" value="profile.save"/>
					<?php echo JHtml::_('form.token'); ?>
				</div>
			</form>
		</div>
    </div>
</div>

<?php

$script = <<<js

	jQuery(document).ready(function($) {
	  $('.register_seller_box').find('.accordion-group').first().find('.accordion-heading a').click();
	});

js;

JFactory::getDocument()->addScriptDeclaration($script);

echo JHtml::_('bootstrap.endAccordion'); ?>
<div class="clearfix"></div>
