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

use Joomla\Registry\Registry;

/** @var SellaciousViewOrder $this */
JHtml::_('behavior.framework');
JHtml::_('jquery.framework');

JHtml::_('script', 'media/com_sellacious/js/plugin/serialize-object/jquery.serialize-object.min.js', false, false);
JHtml::_('script', 'com_sellacious/fe.view.order.payment.js', true, true);

JHtml::_('stylesheet', 'com_sellacious/font-awesome.min.css', null, true);
JHtml::_('stylesheet', 'com_sellacious/fe.component.css', null, true);
JHtml::_('stylesheet', 'com_sellacious/fe.view.order.payment.css', null, true);
JHtml::_('stylesheet', 'com_sellacious/fe.view.order.css', null, true);
JHtml::_('stylesheet', 'com_sellacious/fe.view.wishlist.css', null, true);

$order = new Registry($this->item);
$items = $order->get('items');

$hasShippingAddress = $this->helper->order->hasShippingAddress($order->get('id'));
$c_currency         = $this->helper->currency->current('code_3');
$me         = JFactory::getUser();

?>

<!--BreadCrumbs-->
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

<!--Content Page -->

<div class="row">

<!--Side Bar-->
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


    <div class="<?php echo  $me->guest ? 'col-sm-12' : 'col-sm-9' ?> order_pro_box">
        <div class="complete-order-box" id="orderForm">
            <div class="order-heading">
        <div class="head-sec">
            <span class="heading-order"><?php echo JText::sprintf('COM_SELLACIOUS_ORDER_HEADING_PAYMENT', $order->get('order_number')) ?></span>
        </div>
            </div>
            <div  class="order-form-fields">
                <div class="row">
                    <div class="col-sm-12">
                        <ul class="order-detail-top-right pull-right">
                            <li><?php echo JText::_('COM_SELLACIOUS_ORDER_HEADING_DATE_LABEL'); ?> <?php echo JHtml::_('date', $order->get('created'), 'D, F d, Y h:i A'); ?></li>
                            <li><?php echo JText::_('COM_SELLACIOUS_ORDER_HEADING_ID'); ?>:&nbsp;<?php echo $order->get('order_number') ?> <small>(<?php echo JText::plural('COM_SELLACIOUS_ORDER_PREFIX_TOTAL_ITEMS_N', count($items)); ?>)</small></li>
                        </ul>
                        <ul class="backlink-box pull-left order-detail-top-left">
                            <li class="backlink">
                                <a  title="<?php echo JText::_("COM_SELLACIOUS_ORDER_GO_BACK"); ?>" href="<?php echo JRoute::_('index.php?option=com_sellacious&view=orders')?>"><i class="hasTooltip fa fa-long-arrow-left"></i> </a>

                            </li>

                        </ul>
                    </div>
                </div>

            <div class="row">
             <div class="col-sm-12 fieldset">
                 <div class="row">
                     <div class="col-sm-6">
            <div id="address-viewer">
                <div class="w100p">
                    <?php if ($hasShippingAddress) : ?>
                        <div id="address-shipping-text">
                            <div class="address_label"><?php echo JText::_('COM_SELLACIOUS_ORDER_SHIPPING_ADDRESS_LABEL'); ?></div>
                            <span class="address_name"><?php echo $order->get('st_name') ?></span>

                            <?php if($order->get('st_mobile')): ?>
                                <span class="address_mobile"><i class="fa fa-mobile-phone fa-lg"></i>
                                    <?php echo $order->get('st_mobile') ?></span>
                            <?php endif; ?>
                            <?php if($order->get('st_address')): ?>
                                <span class="address_address"><?php echo $order->get('st_address') ?></span>
                            <?php endif; ?>
                            <?php if($order->get('st_landmark')): ?>
                                <span class="address_landmark"><?php echo $order->get('st_landmark') ?>,</span>
                            <?php endif; ?>
                            <?php if($order->get('st_district')): ?>
                                <span class="address_district"><?php echo $order->get('st_district') ?>,</span>
                            <?php endif; ?>
                            <?php if($order->get('st_state')): ?>
                                <span class="address_state_loc"><?php echo $order->get('st_state') ?>,</span>
                            <?php endif; ?>
                            <?php if($order->get('st_zip')): ?>
                                <span class="address_zip"> - <?php echo $order->get('st_zip') ?></span>
                            <?php endif; ?>
                            <?php if($order->get('st_country')): ?>
                                <span class="address_country"><?php echo $order->get('st_country') ?></span>
                            <?php endif; ?>
                        </div>
                        <div class="clearfix"></div>
                    <?php endif; ?>
                    <div id="address-billing-text">
                        <div class="address_label"><?php echo JText::_('COM_SELLACIOUS_ORDER_BILLING_ADDRESS_LABEL'); ?></div>
                        <span class="address_name"><?php echo $order->get('bt_name') ?></span>
                        <?php if($order->get('bt_mobile')): ?>
                            <span class="address_mobile"><i class="fa fa-mobile-phone fa-lg"></i>
                                <?php echo $order->get('bt_mobile') ?></span><br/>
                        <?php endif; ?>
                        <?php if($order->get('bt_address')): ?>
                            <span class="address_address"><?php echo $order->get('bt_address') ?></span>
                        <?php endif; ?>
                        <?php if($order->get('bt_landmark')): ?>
                            <span class="address_landmark"><?php echo $order->get('bt_landmark') ?>,</span>
                        <?php endif; ?>
                        <?php if($order->get('bt_district')): ?>
                            <span class="address_district"><?php echo $order->get('bt_district') ?>,</span>
                        <?php endif; ?>
                        <?php if($order->get('bt_state')): ?>
                            <span class="address_state_loc"><?php echo $order->get('bt_state') ?>,</span>
                        <?php endif; ?>
                        <?php if($order->get('bt_zip')): ?>
                            <span class="address_zip"> - <?php echo $order->get('bt_zip') ?></span>
                        <?php endif; ?>
                        <?php if($order->get('bt_country')): ?>
                            <span class="address_country"><?php echo $order->get('bt_country') ?></span>
                        <?php endif; ?>
                    </div>
                    <div class="clearfix"></div>
                </div>
            </div>
                     </div>
                     <?php if (!empty($items)): ?>
                         <div class="col-sm-6">
                             <?php $oShoprules = $order->get('shoprules'); ?>

                             <?php if (!empty($oShoprules)): ?>
                                 <table class="w100p shoprule-info">
                                     <thead>
                                     <tr>
                                         <th colspan="4">
                                             <?php echo JText::_('COM_SELLACIOUS_ORDER_HEADING_SHOPRULE_SUMMARY') ?>
                                         </th>
                                     </tr>
                                     </thead>
                                     <tbody>
                                     <?php
                                     foreach ($oShoprules as $rule)
                                     {
                                         if ($rule->change != 0)
                                         {
                                             ?>
                                             <tr>
                                                 <td>
                                                     <?php echo str_repeat('|&mdash;', $rule->level - 1) ?>
                                                     <?php echo $this->escape($rule->title); ?>
                                                 </td>
                                                 <td class="text-right nowrap" style="width:150px;">
                                                     <em>
                                                         <?php
                                                         $rule_base = $this->helper->currency->display($rule->input, $order->get('currency'), $c_currency, true);

                                                         if ($rule->percent)
                                                         {
                                                             $change_value = number_format($rule->amount, 2);
                                                             echo sprintf('@%s%% on %s', $change_value, $rule_base);
                                                         }
                                                         else
                                                         {
                                                             $change_value = $this->helper->currency->display($rule->amount, $order->get('currency'), $c_currency, true);
                                                             echo sprintf('%s over %s', $change_value, $rule_base);
                                                         }
                                                         ?>
                                                     </em>
                                                 </td>
                                                 <td class="text-right nowrap" style="width:90px;">
                                                     <?php
                                                     $value = $this->helper->currency->display(abs($rule->change), $order->get('currency'), $c_currency, true);
                                                     echo $rule->change >= 0 ? '(+) ' . $value : '(-) ' . $value;
                                                     ?>
                                                 </td>
                                                 <td class="text-right nowrap" style="width:90px;">
                                                     <?php echo $this->helper->currency->display($rule->output, $order->get('currency'), $c_currency, true) ?></td>
                                             </tr>
                                             <?php
                                         }
                                     }

                                     if (abs($order->get('cart_taxes') - 0.00) >= 0.01)
                                     {
                                         ?>
                                         <tr>
                                             <th class="text-right" colspan="3"><?php echo JText::_('COM_SELLACIOUS_ORDER_CART_TAXES'); ?></th>
                                             <th class="text-right"><?php
                                                 echo $this->helper->currency->display($order->get('cart_taxes'), $order->get('currency'), $c_currency, true) ?></th>
                                         </tr>
                                         <?php
                                     }

                                     if (abs($order->get('cart_discounts') - 0.00) >= 0.01)
                                     {
                                         ?>
                                         <tr>
                                             <th class="text-right" colspan="3"><?php echo JText::_('COM_SELLACIOUS_ORDER_CART_DISCOUNTS'); ?></th>
                                             <th class="text-right"><?php
                                                 echo $this->helper->currency->display($order->get('cart_discounts'), $order->get('currency'), $c_currency, true) ?></th>
                                         </tr>
                                         <?php
                                     }

                                     if ($coupon = $order->get('coupon'))
                                     {
                                         ?>
                                         <tr>
                                             <th class="text-left" colspan="3">
                                                 <?php echo JText::_('COM_SELLACIOUS_ORDER_HEADING_COUPON'); ?>: <span class="copoun-label  text-normal"><?php echo $this->escape($coupon->code) ?></span>
                                             </th>
                                             <th class="text-right ">
                                                 (-) <?php echo $this->helper->currency->display($coupon->amount, $order->get('currency'), $c_currency, true) ?>
                                             </th>
                                         </tr>
                                         <?php
                                     }

                                     if (!empty($items)): ?>
                                     <tr>
                                         <th class="text-right" colspan="3"><?php echo JText::_('COM_SELLACIOUS_ORDERS_TOTAL_SHIPPING_AMOUNT') ?></th>
                                         <td class="text-right" style="width: 90px;"><?php
                                             echo $this->helper->currency->display($order->get('product_shipping'), $order->get('currency'), $c_currency, true) ?></td>
                                     </tr>
                                     <?php endif;?>

                                     <tr class="order-grand-total">
                                         <td colspan="3" class="text-right nowrap"><?php echo JText::_('COM_SELLACIOUS_ORDER_HEADING_GRAND_TOTAL'); ?></td>
                                         <td class="text-right grand-total-price nowrap" style="width:90px;"><?php
                                             echo $this->helper->currency->display($order->get('grand_total'), $order->get('currency'), $c_currency, true) ?>
                                         </td>
                                     </tr>


                                     <?php ?>
                                     </tbody>
                                 </table>
                             <?php endif; ?>
                         </div>
                     <?php endif; ?>
                 </div>
             </div>
            </div>




                <?php if (!empty($items)): ?>
            <div class="order-items w100p">
                <div class="order-product-box-body">
                <?php foreach ($items as $oi):
                    $code     = $this->helper->product->getCode($oi->product_id, $oi->variant_id, $oi->seller_uid);
                    $p_url    = JRoute::_('index.php?option=com_sellacious&view=product&p=' . $code);
                    $title    = trim(sprintf('%s - %s', $oi->product_title, $oi->variant_title), '- ');
                    $images   = $this->helper->product->getImages($oi->product_id, $oi->variant_id);
                    $statuses = $this->helper->order->getStatusLog($oi->order_id, $oi->item_uid);
                    $rows     = count($oi->shoprules) + 1;
                    ?>
                    <div class="order-product-box">
                        <table class="top-oi-status w100p">
                            <?php foreach ($statuses as $si => $status): ?>
                                <tr class="<?php echo $si > 2 ? 'hidden toggle-element' : ''; ?>">
                                    <td class="nowrap" style="width:90px;"><?php
                                        echo JHtml::_('date', $status->created, 'M d, Y h:i A'); ?></td>
                                    <td class="text-right">
                                        <abbr class="hasTooltip" data-placement="top" title="<?php
                                        echo $status->customer_notes ?>"><?php echo $status->s_title ?></abbr>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </table>
                        <div class="row">
                            <div class="col-sm-8  box-left-product">
                            <div class="v-top image-product">
                                <a href="<?php echo $p_url ?>">
                                    <span style="background-image: url(<?php echo reset($images) ?>)"></span>
                            </div>
                            <div class="v-top product-basic-detail">
                                <?php echo $oi->package_items ? JText::_('COM_SELLACIOUS_CART_PACKAGE_ITEM_LABEL') : ''; ?>
                                <a class="product-title" href="<?php echo $p_url ?>"><?php echo $this->escape($title) ?></a>
                                <p class="price"><?php echo $this->helper->currency->display($oi->sub_total, $order->get('currency'), $c_currency, true); ?></p>
                                <?php if (abs($oi->shipping_amount) >= 0.01): ?>
                                    <small><?php echo JText::_('COM_SELLACIOUS_ORDER_ITEM_SHIPPING_AMOUNT_LABEL') ?>
                                        <?php echo $this->helper->currency->display($oi->shipping_amount, $order->get('currency'), $c_currency, true); ?></small>
                                    <br/>
                                <?php endif; ?><br>
                                <?php echo JText::plural('COM_SELLACIOUS_ORDER_PREFIX_ITEM_QUANTITY_N', $oi->quantity) ?>
                                <br/>
                                <?php if ($oi->package_items): ?>
                                    <hr class="simple">
                                    <ol class="package-items">
                                        <?php
                                        foreach ($oi->package_items as $pkg_item):
                                            $url = JRoute::_('index.php?option=com_sellacious&view=product&p=' . $pkg_item->code);
                                            $pk_title = trim(sprintf('%s - %s', $pkg_item->product_title, $pkg_item->variant_title), '- ');
                                            $pk_sku = trim(sprintf('%s-%s', $pkg_item->product_sku, $pkg_item->variant_sku), '- ')
                                            ?>
                                            <li><a class="dark-link-off" href="<?php echo $url ?>"><?php echo $pk_title ?> (<?php echo $pk_sku ?>)</a></li><?php
                                        endforeach;
                                        ?>
                                    </ol>
                                <?php endif; ?>
                                <?php echo JText::sprintf('COM_SELLACIOUS_ORDER_PREFIX_ITEM_SELLER', $oi->seller_company) ?>

                            </div>


                            </div>
                            <?php if (!empty($oi->shoprules)): ?>
                            <div class="col-sm-12 ">
                                <table class="w100p shoprule-info" >
                                    <?php
                                    foreach ($oi->shoprules as $ri => $rule)
                                    {
                                        settype($rule, 'object');

                                        if ($rule->change != 0)
                                        {
                                            ?>
                                            <tr>
                                                <td>
                                                    <?php echo str_repeat('|&mdash;', $rule->level - 1) ?>
                                                    <?php echo $this->escape($rule->title); ?>
                                                </td>
                                                <td class="text-right nowrap" style="width:150px;">
                                                    <em>
                                                        <?php
                                                        $rule_base = $this->helper->currency->display($rule->input, $order->get('currency'), $c_currency, true);

                                                        if ($rule->percent)
                                                        {
                                                            $change_value = number_format($rule->amount, 2);
                                                            echo sprintf('@%s%% on %s', $change_value, $rule_base);
                                                        }
                                                        else
                                                        {
                                                            $change_value = $this->helper->currency->display($rule->amount, $order->get('currency'), $c_currency, true);
                                                            echo sprintf('%s over %s', $change_value, $rule_base);
                                                        }
                                                        ?>
                                                    </em>
                                                </td>
                                                <td class="text-right nowrap" style="width:90px;">
                                                    <small><?php echo JText::_('COM_SELLACIOUS_ORDER_SHOPRULE_INCLUSIVE_LABEL'); ?></small>
                                                    <?php // echo $this->helper->currency->display($rule->output, $order->get('currency'), $c_currency, true)
                                                    ?></td>
                                                <td class="text-right nowrap" style="width:90px;">
                                                    <?php
                                                    $value = $this->helper->currency->display(abs($rule->change), $order->get('currency'), $c_currency, true);
                                                    echo $rule->change >= 0 ? '(+) ' . $value : '(-) ' . $value;
                                                    ?>
                                                </td>
                                            </tr>
                                            <?php
                                        }
                                    }
                                    ?>
                                </table>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
                </div>

            <br>





            <div id="payment-forms">
                <?php
                $options       = array('debug' => 0);
                $args          = new stdClass;
                $args->methods = $this->helper->paymentMethod->getMethods('cart', true, $order->get('customer_uid') ?: false, $order->get('id'));
                $html          = JLayoutHelper::render('com_sellacious.payment.forms', $args, '', $options);

                echo $html;
                ?>
            </div>
        <?php else: ?>
            <h5><em><?php echo JText::_('COM_SELLACIOUS_ORDER_NO_ITEM_MESSAGE'); ?></em></h5>
        <?php endif; ?>
        <input type="hidden" id="order_id" name="order_id" value="<?php echo $order->get('id') ?>" />
        <?php echo JHtml::_('form.token'); ?>
        </div>

        </div>
</div>
<div class="clearfix"></div>
<script type="text/javascript">
	jQuery(function ($) {
		$('#payment-methods').find('.accordion-group').first().find('.accordion-heading a').click();
	});
</script>
