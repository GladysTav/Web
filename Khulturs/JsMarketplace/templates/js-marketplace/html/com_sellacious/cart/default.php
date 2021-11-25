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

/** @var SellaciousViewCart $this */
JHtml::_('behavior.framework');
JHtml::_('jquery.framework');
JHtml::_('script', 'com_sellacious/util.noframes.js', false, true);

JHtml::_('script', 'media/com_sellacious/js/plugin/select2-3.5/select2.js', false, false);
JHtml::_('stylesheet', 'media/com_sellacious/js/plugin/select2-3.5/select2.css', null, false);

JHtml::_('script', 'com_sellacious/util.cart.aio.js', false, true);
JHtml::_('script', 'com_sellacious/fe.view.cart.js', false, true);

JHtml::_('stylesheet', 'com_sellacious/font-awesome.min.css', null, true);
JHtml::_('stylesheet', 'com_sellacious/fe.component.css', null, true);
JHtml::_('stylesheet', 'com_sellacious/fe.view.cart.aio.css', null, true);
JHtml::_('stylesheet', 'com_sellacious/fe.view.cart.css', null, true);

JText::script('COM_SELLACIOUS_CART_CONFIRM_CLEAR_CART_ACTION_MESSAGE');

$user      = JFactory::getUser();
$arg       = new stdClass;
$arg->cart = $this->cart;
?>
<div class="w100p" id="cart-wrapper">
	<div id="cart-container">
		<!-- Fake modal div -->
		<div id="cart-items">
			<?php $layout = 'com_sellacious.cart.aio.' . ($this->cart->count() == 0 ? 'empty' : 'items_modal'); ?>
			<?php echo JLayoutHelper::render($layout, $arg); ?>
		</div>
	</div>




    <?php
    jimport('joomla.application.module.helper');
    $modules = JModuleHelper::getModules('seller-product');
    foreach ($modules as $module):
        $renMod = JModuleHelper::renderModule($module);

        if (!empty($renMod) && ($module->module == "mod_sellacious_sellerproducts")):?>
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
<input type="hidden" name="<?php echo JSession::getFormToken() ?>" id="formToken" value="1"/>
