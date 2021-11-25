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

/** @var  $this  SellaciousViewSeller */
JHtml::_('behavior.formvalidator');
JHtml::_('script', 'com_sellacious/util.noframes.js', false, true);
JHtml::_('script', 'com_sellacious/util.validator-mobile.js', false, true);

JHtml::_('stylesheet', 'com_sellacious/font-awesome.min.css', null, true);
JHtml::_('stylesheet', 'com_sellacious/fe.component.css', null, true);
JHtml::_('stylesheet', 'com_sellacious/fe.view.seller.css', null, true);
JHtml::_('stylesheet', 'com_sellacious/fe.view.wishlist.css', null, true);


$me         = JFactory::getUser();

?>
<script>
Joomla.submitbutton = function(task, form) {
	if (document.formvalidator.isValid(document.getElementById('seller-form'))) {
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
      <div class="reg-seller-cont">
    <div class="reg-seller-heading">
        <h2><?php echo JText::_('COM_SELLACIOUS_REGISTER_SELLER') ?></h2>
    </div>

    <?php
        $fieldsets = $this->form->getFieldsets();
        $accordion = array('parent' => true, 'toggle' => false, 'active' => 'seller_accordion_basic');

        echo JHtml::_('bootstrap.startAccordion', 'seller_accordion', $accordion);
        ?>

        <form action="<?php echo JRoute::_('index.php?option=com_sellacious&view=seller'); ?>"
              method="post" id="seller-form" name="seller-form" class="form-horizontal form-validate" enctype="multipart/form-data">
            <button type="button" class="top-submit btn btn-primary pull-right rounded-50per btn-sm"
                    onclick="return Joomla.submitbutton('seller.save', this.form);"><?php echo JText::_('JSUBMIT') ?></button>
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

            <div class="clearfix"></div>
            <br>
            <div class="box-bottom">

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
                                <div class="control-label"><?php echo $field->label ?></div>
                                <div class="controls"><?php echo $field->input ?></div>
                            <?php else: ?>
                                <div class="controls col-md-12"><?php echo $field->input ?></div>
                            <?php endif; ?>
                        </div>
                    <?php
                    endif;
                endforeach;
                ?>
            </fieldset>


            <div class="clearfix"></div>
            <br>

            <fieldset>
                <div class="control-group">
                    <div class="controls text-right">
                        <button type="button" class="submit-bottom btn btn-primary pull-right rounded-50per"
                                onclick="return Joomla.submitbutton('seller.save', this.form);"><?php echo JText::_('JSUBMIT') ?></button>
                    </div>
                </div>
            </fieldset>
            </div>

            <input type="hidden" name="task"/>
            <?php echo JHtml::_('form.token'); ?>
        </form>

          <?php

          $script = <<<js

			jQuery(document).ready(function($) {
			  $('#seller_accordion').find('.accordion-group').first().find('.accordion-heading a').click();
			});

js;

          JFactory::getDocument()->addScriptDeclaration($script);

		  echo JHtml::_('bootstrap.endAccordion'); ?>

      </div>
    </div>
</div>




