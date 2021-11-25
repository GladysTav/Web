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

/** @var SellaciousViewProduct $this */
$sellers = $this->item->get('sellers');

if (!isset($sellers[0]) || (count($sellers) == 1 && $sellers[0]->seller_uid == $this->item->get('seller_uid')))
{
    return;
}

$c_currency = $this->helper->currency->current('code_3');

$item           = $this->item;
$allow_checkout = $this->helper->config->get('allow_checkout');
$cart_pages     = (array) $this->helper->config->get('product_add_to_cart_display');
$buynow_pages   = (array) $this->helper->config->get('product_buy_now_display');
$display_stock  = $this->helper->config->get('frontend_display_stock');
$c_currency     = $this->helper->currency->current('code_3');
$marketPlace    = $this->helper->config->get('multi_seller');

$me           = JFactory::getUser();
$samplemedia  = $this->getSampleMedia();
$preview_url  = $this->item->get('preview_url');
$preview_mode = $this->item->get('preview_mode');
$manufacturer = $this->helper->manufacturer->getItem(array('user_id' => $item->get('manufacturer_id')));
?>
<div class="clearfix"></div>
<!--<a name="also-selling">&nbsp;</a>-->
<div class="product-sellers-count">
    <a href="#also-selling">
        <i class="fa fa-flash "></i>
        <?php echo JText::plural('COM_SELLACIOUS_PRODUCT_SELLER_COUNT_N_DESC', count($item->get('sellers'))); ?>
        <span class="js-hidden-lg  fa more-avial-seller-icon fa-ellipsis-v pull-right"></span>
    </a>
</div>
<!--<h6 class="bold-text center"><?php //echo JText::_('COM_SELLACIOUS_PRODUCT_HEADING_BLOCK_SELLERS'); ?></h6>-->
<div class="js-hidden-sm sellers_default" >


    <!--		--><?php //echo JText::_('COM_SELLACIOUS_PRODUCT_HEADING_BLOCK_SELLER'); ?>
    <!--		--><?php //echo JText::_('COM_SELLACIOUS_PRODUCT_HEADING_BLOCK_PRICE'); ?>
    <!--		--><?php //echo JText::_('COM_SELLACIOUS_PRODUCT_HEADING_BLOCK_ACTION'); ?>
    <?php
    foreach ($sellers as $i => $seller)
    {
        /** @var Registry $item */
        $item       = new Registry($seller);
        $s_currency = $this->helper->currency->forSeller($item->get('seller_uid'), 'code_3');

        // todo: Add this to config (show current one or not in more sellers) and move to model
        if ($item->get('seller_uid') == $this->item->get('seller_uid'))
        {
            continue;
        }

//printr($item);
        ?>
        <div class="seller-info  sell-top-info">
            <a href="<?php echo JRoute::_('index.php?option=com_sellacious&view=store&id=' . $item->get('seller_uid')); ?>">
                <?php echo $item->get('seller_store', $item->get('seller_name', $item->get('seller_company', $item->get('seller_username')))); ?></a>
            <br>
            <?php if ($this->helper->config->get('show_seller_rating')) : ?>
                <?php $rating = $item->get('seller_rating.rating'); ?>
                <!--<span class="label <?php// echo ($rating < 3) ? 'label-warning' : 'label-success' ?>"><?php //echo number_format($rating, 1) ?> / 5.0</span>-->
                <?php $stars = round($rating * 2); ?>
                <span class="rating-stars star-<?php echo $stars ?>">
							<?php echo number_format($rating, 1) ?>
						</span>

            <?php endif;?>
        </div>

        <?php if ($item->get('exchange_days')): ?>
        <?php if ($item->get('exchange_tnc')):
            $options = array(
                'title'    => JText::sprintf('COM_SELLACIOUS_PRODUCT_EXCHANGE_REPLACEMENT_GUARANTEE_DAYS_N', (int) $item->get('exchange_days')),
                'backdrop' => 'static',
            );
            echo JHtml::_('bootstrap.renderModal', 'exchange_tnc-' . $item->get('code'), $options, $item->get('exchange_tnc'));
        endif; ?>
        <div class="replacement-info">
            <i class="fa fa-refresh"></i>
            <?php echo JText::sprintf('COM_SELLACIOUS_PRODUCT_EXCHANGE_REPLACEMENT_GUARANTEE_DAYS_N', (int) $item->get('exchange_days')); ?>
            <?php if ($item->get('exchange_tnc')): ?>
                <a href="#exchange_tnc-<?php echo $item->get('code') ?>" role="button" data-toggle="modal">[<i class="fa fa-question"></i>]</a>
            <?php endif; ?>
        </div>
    <?php endif; ?>

        <?php if ($item->get('return_days')): ?>
        <?php if ($item->get('return_tnc')):
            $options = array(
                'title'    => JText::sprintf('COM_SELLACIOUS_PRODUCT_EXCHANGE_EASY_RETURN_DAYS_N', (int) $item->get('return_days')),
                'backdrop' => 'static',
            );
            echo JHtml::_('bootstrap.renderModal', 'return_tnc-' . $item->get('code'), $options, $item->get('return_tnc'));
        endif; ?>
        <div class="replacement-info">
            <i class="fa fa-refresh"></i>
            <?php echo JText::sprintf('COM_SELLACIOUS_PRODUCT_EXCHANGE_EASY_RETURN_DAYS_N', (int) $item->get('return_days')); ?>
            <?php if ($item->get('return_tnc')): ?>
                <a href="#return_tnc-<?php echo $item->get('code') ?>" role="button" data-toggle="modal">[<i class="fa fa-question"></i>]</a>
            <?php endif; ?>
        </div>
    <?php endif; ?>


        <span class="product-price-sm"><?php
            echo round($item->get('price.sales_price'), 2) >= 0.01
                ? $this->helper->currency->display($item->get('price.sales_price'), $s_currency, $c_currency, true)
                : JText::_('COM_SELLACIOUS_PRODUCT_PRICE_FREE'); ?></span>
        <?php $link = JRoute::_('index.php?option=com_sellacious&view=product&p=' . $item->get('code')); ?><br>
        <div class="btn-group">
            <a href="<?php echo $link ?>"><button class="btn btn-primary btn-mys "><i class="fa fa-info-circle"></i></button></a>
            <button type="button" class="btn btn-default  btn-add-cart btn-mys " data-item="<?php echo $item->get('code') ?>"><i class="fa fa-shopping-cart"></i></button>
            <!--<button type="button" class="btn btn-success btn-cart-sm btn-add-cart"
							data-item="<?php /*echo $item->get('code') */?>" data-checkout="true"><?php /*echo strtoupper(JText::_('COM_SELLACIOUS_PRODUCT_BUY_NOW')); */?></button>-->
        </div><hr class="isolate">
        <?php
    }

    ?>

</div>




<div class="sellers_default drop-bottom-prices tab-view-sellers-multiples tab-box-slideup">
    <div class="top-share">

        <p class="share-title"><?php echo JText::_('COM_SELLACIOUS_AVAILABLE_SELLERS_TAB'); ?></p>
        <span class="pull-right share-close">
                <i class="fa fa-close"></i>
            </span>
    </div>
    <!--		--><?php //echo JText::_('COM_SELLACIOUS_PRODUCT_HEADING_BLOCK_SELLER'); ?>
    <!--		--><?php //echo JText::_('COM_SELLACIOUS_PRODUCT_HEADING_BLOCK_PRICE'); ?>
    <!--		--><?php //echo JText::_('COM_SELLACIOUS_PRODUCT_HEADING_BLOCK_ACTION'); ?>
    <?php
    foreach ($sellers as $i => $seller)
    {
        /** @var Registry $item */
        $item       = new Registry($seller);
        $s_currency = $this->helper->currency->forSeller($item->get('seller_uid'), 'code_3');

        // todo: Add this to config (show current one or not in more sellers) and move to model
        if ($item->get('seller_uid') == $this->item->get('seller_uid'))
        {
            continue;
        }

//printr($item);
        ?>
        <div class="seller-info-tab-box">
        <div class="seller-info-tab">
            <a href="<?php echo JRoute::_('index.php?option=com_sellacious&view=store&id=' . $item->get('seller_uid')); ?>">
                <?php echo $item->get('seller_store', $item->get('seller_name', $item->get('seller_company', $item->get('seller_username')))); ?></a>
            <br>
            <?php if ($this->helper->config->get('show_seller_rating')) : ?>
                <?php $rating = $item->get('seller_rating.rating'); ?>
                <!--<span class="label <?php// echo ($rating < 3) ? 'label-warning' : 'label-success' ?>"><?php //echo number_format($rating, 1) ?> / 5.0</span>-->
                <?php $stars = round($rating * 2); ?>
                <span class="rating-stars star-<?php echo $stars ?>">
							<?php echo number_format($rating, 1) ?>
						</span>

            <?php endif;?>
        </div>

	<?php if ($item->get('exchange_days')): ?>
        <?php if ($item->get('exchange_tnc')):
            $options = array(
                'title'    => JText::sprintf('COM_SELLACIOUS_PRODUCT_EXCHANGE_REPLACEMENT_GUARANTEE_DAYS_N', (int) $item->get('exchange_days')),
                'backdrop' => 'static',
            );
            echo JHtml::_('bootstrap.renderModal', 'exchange_tnc-' . $item->get('code'), $options, $item->get('exchange_tnc'));
        endif; ?>
        <div class="replacement-info">
            <i class="fa fa-refresh"></i>
            <?php echo JText::sprintf('COM_SELLACIOUS_PRODUCT_EXCHANGE_REPLACEMENT_GUARANTEE_DAYS_N', (int) $item->get('exchange_days')); ?>
            <?php if ($item->get('exchange_tnc')): ?>
                <a href="#exchange_tnc-<?php echo $item->get('code') ?>" role="button" data-toggle="modal">[<i class="fa fa-question"></i>]</a>
            <?php endif; ?>
        </div>
    <?php endif; ?>

        <?php if ($item->get('return_days')): ?>
        <?php if ($item->get('return_tnc')):
            $options = array(
                'title'    => JText::sprintf('COM_SELLACIOUS_PRODUCT_EXCHANGE_EASY_RETURN_DAYS_N', (int) $item->get('return_days')),
                'backdrop' => 'static',
            );
            echo JHtml::_('bootstrap.renderModal', 'return_tnc-' . $item->get('code'), $options, $item->get('return_tnc'));
        endif; ?>
        <div class="replacement-info">
            <i class="fa fa-refresh"></i>
            <?php echo JText::sprintf('COM_SELLACIOUS_PRODUCT_EXCHANGE_EASY_RETURN_DAYS_N', (int) $item->get('return_days')); ?>
            <?php if ($item->get('return_tnc')): ?>
                <a href="#return_tnc-<?php echo $item->get('code') ?>" role="button" data-toggle="modal">[<i class="fa fa-question"></i>]</a>
            <?php endif; ?>
        </div>
    <?php endif; ?>


        <span class="product-price-sm"><?php
            echo round($item->get('price.sales_price'), 2) >= 0.01
                ? $this->helper->currency->display($item->get('price.sales_price'), $s_currency, $c_currency, true)
                : JText::_('COM_SELLACIOUS_PRODUCT_PRICE_FREE'); ?></span>
        <?php $link = JRoute::_('index.php?option=com_sellacious&view=product&p=' . $item->get('code')); ?><br>
        <div class="btn-group">
            <a href="<?php echo $link ?>"><button class="btn btn-primary btn-mys "><i class="fa fa-info-circle"></i></button></a>
            <button type="button" class="btn btn-default  btn-add-cart btn-mys " data-item="<?php echo $item->get('code') ?>"><i class="fa fa-shopping-cart"></i></button>
            <!--<button type="button" class="btn btn-success btn-cart-sm btn-add-cart"
							data-item="<?php /*echo $item->get('code') */?>" data-checkout="true"><?php /*echo strtoupper(JText::_('COM_SELLACIOUS_PRODUCT_BUY_NOW')); */?></button>-->
        </div>
        </div>
        <hr class="isolate">

        <?php
    }

    ?>

</div>

<script>
    jQuery(function($){
        $(".more-avial-seller-icon").click(function(){
            $(".tab-view-sellers-multiples").slideToggle("slow");
            // $(".dropdown-menu-sm").show()
        });
        $(".share-close").click(function(){
            $(".tab-view-sellers-multiples").hide("slow")
        });


    });
</script>

