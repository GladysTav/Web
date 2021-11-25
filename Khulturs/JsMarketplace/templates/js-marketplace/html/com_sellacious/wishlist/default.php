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

/** @var  SellaciousViewWishlist $this */
JHtml::_('behavior.framework');
JHtml::_('jquery.framework');
JHtml::_('script', 'com_sellacious/util.noframes.js', false, true);

if ($this->helper->config->get('product_compare')):
	JHtml::_('script', 'com_sellacious/util.compare.js', false, true);
endif;

JHtml::_('script', 'com_sellacious/fe.view.sellacious.js', false, true);
JHtml::_('script', 'com_sellacious/util.cart.aio.js', false, true);
JHtml::_('script', 'com_sellacious/fe.view.wishlist.js', false, true);

JHtml::_('stylesheet', 'com_sellacious/font-awesome.min.css', null, true);
JHtml::_('stylesheet', 'com_sellacious/fe.component.css', null, true);
JHtml::_('stylesheet', 'com_sellacious/fe.view.cart.aio.css', null, true);
JHtml::_('stylesheet', 'com_sellacious/util.rating.css', null, true);
JHtml::_('stylesheet', 'com_sellacious/fe.view.wishlist.css', null, true);
$me         = JFactory::getUser();
$msgUndefined = JText::_('COM_SELLACIOUS_PROFILE_VALUE_NOT_FOUND');

$doc = JFactory::getDocument();

if (!($url = $this->helper->config->get('shop_more_redirect'))):
	$url = JRoute::_('index.php?option=com_sellacious&view=products');
endif;
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

    <div class="<?php echo  $me->guest ? 'col-sm-12' : 'col-sm-9' ?> wish_pro_box">
        <div id="products-box" class="wishlist_sec">
            <div class="wishlist-heading">
                <h2><?php echo JText::_('COM_SELLACIOUS_PRODUCT_WISHLIST') ?><span> (<?php echo $this->pagination->get('total');?>&nbsp;<?php echo JText::_('COM_SELLACIOUS_PRODUCT_WISHLIST_ITEMS') ?>)</span></h2>
            </div>

            <ul class="product-box myaccount-wishlist">


            <?php

            foreach ($this->items as $item)
            {
                echo $this->loadTemplate('block', $item);
            }
            ?>
            </ul>

            <fieldset class="hidden" id="empty-wishlist">
                <h1><?php echo JText::_('COM_SELLACIOUS_CART_EMPTY_WISHLIST_NOTICE') ?></h1>
                <h5><?php echo JText::_('COM_SELLACIOUS_CART_EMPTY_WISHLIST_MESSAGE') ?></h5><br/>
                <a class="btn btn-primary strong no-underline strong" href="<?php echo $url ?>">
                    <?php echo JText::_('COM_SELLACIOUS_WISHLIST_CONTINUE_SHOPPING') ?></a>
            </fieldset>

            <div class="clearfix"></div>
        </div>

    </div>
</div>




<input type="hidden" id="formToken" name="<?php echo JSession::getFormToken() ?>" value="1">
<?php
$options = array(
	'title'    => JText::_('COM_SELLACIOUS_CART_TITLE'),
	'backdrop' => 'static',
);
echo JHtml::_('bootstrap.renderModal', 'modal-cart', $options, '<div id="cart-items"></div>');
