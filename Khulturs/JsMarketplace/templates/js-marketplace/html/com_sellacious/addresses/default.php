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

/** @var SellaciousViewAddresses $this */
JHtml::_('behavior.framework');
JHtml::_('jquery.framework');
JHtml::_('bootstrap.loadCss');
JHtml::_('script', 'com_sellacious/util.noframes.js', false, true);

JHtml::_('script', 'media/com_sellacious/js/plugin/select2-3.5/select2.js', false, false);
JHtml::_('stylesheet', 'media/com_sellacious/js/plugin/select2-3.5/select2.css', null, false);
JHtml::_('stylesheet', 'com_sellacious/fe.view.wishlist.css', null, true);
JHtml::_('script', 'media/com_sellacious/js/plugin/serialize-object/jquery.serialize-object.min.js', false, false);
JHtml::_('script', 'com_sellacious/fe.view.addresses.js', false, true);

JHtml::_('stylesheet', 'com_sellacious/fe.component.css', null, true);
JHtml::_('stylesheet', 'com_sellacious/fe.view.addresses.css', null, true);
JHtml::_('stylesheet', 'com_sellacious/font-awesome.min.css', null, true);

JText::script('COM_SELLACIOUS_USER_CONFIRM_ADDRESS_REMOVE_MESSAGE');
$me   = JFactory::getUser();
$user = JFactory::getUser();
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

    <div class="<?php echo  $me->guest ? 'col-sm-12' : 'col-sm-9' ?> profile_address_box">
        <div class="profile-address-cont">
            <div class="profile-address-heading">
                <h2><?php echo JText::_('COM_SELLACIOUS_USER_PROFILE_ADDRESS') ?>
                    <a href="#address-form-0" role="button" data-toggle="modal" class="mb-3 btn-add-address pull-right"><i class="fa fa-plus"></i> <?php echo JText::_('COM_SELLACIOUS_CART_USER_ADDRESS_FORM_ADD_TITLE'); ?></a>
                </h2>
            </div>
            <div id="addresses" class="cart-aio text-center">
                <div id="address-editor">
                    <ul id="address-items"></ul>
                    <div id="address-modals"></div>
                    <?php
                    $body    = JLayoutHelper::render('com_sellacious.user.address.form');
                    $options = array(
                        'title'    => JText::_('COM_SELLACIOUS_CART_USER_ADDRESS_FORM_ADD_TITLE'),
                        'backdrop' => 'static',
                        'size'     => 'xs',
                        'footer'   => '<button type="button" class="btn btn-primary btn-save-address"><i class="fa fa-save"></i> ' . JText::_('COM_SELLACIOUS_PRODUCT_SAVE') . '</button>'
                    );
                    echo JHtml::_('bootstrap.renderModal', 'address-form-0', $options, $body);
                    ?>
                    <div class="clearfix"></div>

                    <div class="clearfix"></div>
                </div>
                <div class="clearfix"></div>
            </div>
        </div>
        </div>

    </div>



<div class="w100p">

</div>
<input type="hidden" name="<?php echo JSession::getFormToken() ?>" id="formToken" value="1"/>
