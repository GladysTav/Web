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
$me         = JFactory::getUser();
JHtml::_('stylesheet', 'com_sellacious/fe.view.seller.css', null, true);
JHtml::_('stylesheet', 'com_sellacious/fe.view.wishlist.css', null, true);


?>
<!--JLog::add(JText::_('COM_SELLACIOUS_SELLER_REGISTER_ALREADY_REGISTERED'), JLog::NOTICE, 'jerror');-->
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


    <div class="<?php echo  $me->guest ? 'col-sm-12' : 'col-sm-12 col-lg-9' ?> register_seller_box">
        <div class="reg-seller-cont">
            <div class="reg-seller-heading">
                <h2><?php echo JText::_('COM_SELLACIOUS_REGISTER_SELLER') ?></h2>
            </div>

            <div class="message-seller">

              <p> <i class="fa fa-warning"></i> <?php echo JText::_('COM_SELLACIOUS_SELLER_REGISTER_REGISTERED_ALREADY')?></p>

            </div>


        </div>
    </div>
</div>
