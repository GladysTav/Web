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
/** @var  stdClass $tplData */
$item = $tplData;

$url_raw = 'index.php?option=com_sellacious&view=product&p=' . $item->code;
$url     = JRoute::_($url_raw);
$url_m   = JRoute::_($url_raw . '&layout=modal&tmpl=component');
$paths   = (array) $item->images;
$params  = array(
	'title'    => JText::_('COM_SELLACIOUS_PRODUCT_QUICK_VIEW'),
	'url'      => $url_m,
	'height'   => '600',
	'width'    => '800',
	'keyboard' => true,
);
echo JHtml::_('bootstrap.renderModal', 'sell-modal-' . $item->code, $params);

$c_currency = $this->helper->currency->current('code_3');
$s_currency = $this->helper->currency->forSeller($item->seller_uid, 'code_3');
$me         = JFactory::getUser();

$msgUndefined = JText::_('COM_SELLACIOUS_PROFILE_VALUE_NOT_FOUND');
//$feature      = $this->helper->product->getAttributesByType($item->id, 'features');

?>



    <li class="pro-box product-box" id="list-wisht">
        <div>

        <?php $link_detail = $this->helper->config->get('product_detail_page'); ?>
        <a href="#" class="btn-remove hasTooltip" data-item="<?php echo $item->code ?>"
           title="Remove"><i class="fa fa-close"></i> <?php //echo JText::_('COM_SELLACIOUS_PRODUCT_REMOVE_FROM_WISHLIST'); ?></a>

        <div class="image-box thumb-wishlist">
				<span class="product-img bgrollover" style="background-image:url(<?php echo reset($paths) ?>);"
                      data-rollover="<?php echo htmlspecialchars(json_encode($paths)); ?>"></span>
        </div>
        <div class="product-info-box">
            <div class="product-title">
                <a href="<?php echo $link_detail ? $url : 'javascript:void(0);' ?>" title="<?php echo $item->title; ?>">
                    <?php echo $item->title;
                    echo $item->variant_title ? ' - <small>' . $item->variant_title . '</small>' : '';
                    echo $item->seller_company ? ' - <small>' . $item->seller_company . '</small>' : ''; ?></a>
            </div>

            <!-- BEGIN: seller/admin can directly jump to backend for edit -->
            <?php if ($this->helper->access->check('product.edit') ||
                ($this->helper->access->checkAny(array('basic.own', 'seller.own', 'pricing.own', 'shipping.own', 'related.own', 'seo.own'), 'product.edit.', $item->id) && $item->seller_uid == $me->id)): ?>
                <?php $editUrl = JUri::root() . JPATH_SELLACIOUS_DIR . '/index.php?option=com_sellacious&view=product&layout=edit&id=' . $item->id; ?>
                <a title="<?php echo JText::_('COM_SELLACIOUS_PRODUCT_LINK_BACKEND_EDIT'); ?>" target="_blank" class="btn hasTooltip edit-product" href="<?php echo $editUrl; ?>">
                    <i class="fa fa-pencil-square"></i>
                </a>
            <?php endif; ?>
            <!-- END: seller/admin can directly jump to backend for edit -->

            <div class="pricearea">
                <?php
                $allowed_price_display = (array) $this->helper->config->get('allowed_price_display');
                $security              = $this->helper->config->get('contact_spam_protection');

                if ($item->price_display == 0):
                    $price_display = $this->helper->config->get('product_price_display');
                    $price_d_pages = (array) $this->helper->config->get('product_price_display_pages');

                    if ($price_display > 0 && in_array('products', $price_d_pages)):
                        $price = round($item->sales_price, 2) >= 0.01 ? $this->helper->currency->display($item->sales_price, $s_currency, $c_currency, true) : JText::_('COM_SELLACIOUS_PRODUCT_PRICE_FREE');

                        if ($price_display == 2 && round($item->list_price, 2) >= 0.01): ?>
                            <div class="product-price"><?php echo $price; ?></div>
                            <div class="old-price">
                                <del><?php echo $this->helper->currency->display($item->list_price, $s_currency, $c_currency, true) ?></del>
                                <span class="product-offer"><?php echo strtoupper(JText::_('COM_SELLACIOUS_PRODUCT_OFFER')); ?></span>
                            </div>
                        <?php
                        else: ?>
                            <div class="product-price pull-left"><?php echo $price ?></div><?php
                        endif;
                    endif;
                elseif ($item->price_display == 1 && in_array(1, $allowed_price_display)): ?>
                    <div class="center btn-toggle">
                        <button type="button"
                                class="btn btn-mini btn-warning pull-left" data-toggle="true"><?php
                            echo JText::_('COM_SELLACIOUS_PRODUCT_PRICE_DISPLAY_CALL_US') ?></button>
                        <button type="button" class="btn btn-mini btn-warning pull-left hidden" data-toggle="true"><?php
                            if ($security):
                                $text = $this->helper->media->writeText($item->seller_mobile, 2, true);
                                ?><img src="data:image/png;base64,<?php echo $text; ?>"/><?php
                            else:
                                echo $item->seller_mobile;
                            endif; ?></button>
                    </div>
                <?php
                elseif ($item->price_display == 2 && in_array(2, $allowed_price_display)): ?>
                    <div class="center btn-toggle">
                        <button type="button"
                                class="btn btn-mini btn-warning pull-left" data-toggle="true"><?php
                            echo JText::_('COM_SELLACIOUS_PRODUCT_PRICE_DISPLAY_EMAIL_US') ?></button>
                        <button type="button" class="btn btn-mini btn-warning pull-left hidden"
                                data-toggle="true"><?php
                            if ($security):
                                $text = $this->helper->media->writeText($item->seller_email, 2, true);
                                ?><img src="data:image/png;base64,<?php echo $text; ?>"/><?php
                            else:
                                echo $item->seller_email;
                            endif; ?>
                        </button>
                    </div>
                <?php
                elseif ($item->price_display == 3 && in_array(3, $allowed_price_display)):
                    $title   = JText::sprintf('COM_SELLACIOUS_PRODUCT_PRICE_DISPLAY_OPEN_QUERY_FORM_FOR', $this->escape($item->title), $this->escape($item->variant_title));
                    $options = array(
                        'title'    => $title,
                        'backdrop' => 'static',
                        'height'   => '520',
                        'keyboard' => true,
                        'url'      => "index.php?option=com_sellacious&view=product&p={$item->code}&layout=query&tmpl=component",
                    );

                    echo JHtml::_('bootstrap.renderModal', "query-form-{$item->code}", $options);
                    ?>
                    <div class="productquerybtn">
                        <a href="#query-form-<?php echo $item->code ?>" role="button" data-toggle="modal" class="btn btn-default">
                            <i class="fa fa-file-text"></i><?php echo JText::_('COM_SELLACIOUS_PRODUCT_PRICE_DISPLAY_OPEN_QUERY_FORM'); ?>
                        </a>
                    </div>
                <?php
                endif; ?>
            </div>

            <div class="clearfix"></div>


            <a href="#" class="btn-remove hasTooltip" data-item="<?php echo $item->code ?>"
               title="Remove"><i class="fa fa-close"></i> <?php //echo JText::_('COM_SELLACIOUS_PRODUCT_REMOVE_FROM_WISHLIST'); ?></a>

            <div class="clearfix"></div>

            <div class="rating-stock">
                <?php $allow_rating = $this->helper->config->get('product_rating'); ?>
                <?php $rating_pages = (array) $this->helper->config->get('product_rating_display'); ?>

                <?php if ($allow_rating && in_array('products', $rating_pages)): ?>
                    <span class="product-stars"><?php echo $this->helper->core->getStars($item->rating, true, 5.0); ?></span>
                <?php endif; ?>

                <span class="stock-level"><?php echo $item->stock_capacity ?
                        '<span class="label label-success">' . strtoupper(JText::_('COM_SELLACIOUS_PRODUCT_IN_STOCK')) . '</span>' : '<span class="label label-warning">' . strtoupper(JText::_('COM_SELLACIOUS_PRODUCT_OUT_OF_STOCK')) . '</span>' ?></span>


            </div>

            <?php if (trim($item->description) != ''): ?>
                <div class="product-text"><?php echo JHtml::_('string.truncate', strip_tags($item->description), 190) ?></div>
            <?php endif; ?>

            <div class="product-box-foot">
                <?php
                $allow_checkout = $this->helper->config->get('allow_checkout');
                $compare_allow  = $this->helper->product->isComparable($item->product_id);
                $compare_pages  = (array) $this->helper->config->get('product_compare_display');
                $cart_pages     = (array) $this->helper->config->get('product_add_to_cart_display');
                $buynow_pages	= (array) $this->helper->config->get('product_buy_now_display');
                $show_modal		= (array) $this->helper->config->get('product_quick_detail_pages');
                $display_stock	= $this->helper->config->get('frontend_display_stock');

                if ($item->price_display == 0):
                    if (in_array('products', $cart_pages) || in_array('products', $buynow_pages)):
                        if ($allow_checkout):
                            if ((int) $item->stock_capacity > 0): ?>
                                <?php if (in_array('products', $cart_pages)): ?>
                                    <button title="<?php echo JText::_('COM_SELLACIOUS_PRODUCT_ADD_TO_CART'); ?>" type="button" class="hasTooltip btn btn-add-cart add" data-item="<?php echo $item->code; ?>">
                                        <i class="fa fa-cart-plus"></i>

                                        <?php if ($display_stock):
                                            //echo '('. (int) $item->stock_capacity . ')';
                                        endif; ?>

                                    </button>
                                <?php endif; ?>

                                <?php if (in_array('products', $buynow_pages)): ?>
                                    <button title="<?php echo JText::_('COM_SELLACIOUS_PRODUCT_BUY_NOW'); ?>" type="button" class="btn  hasTooltip btn-add-cart buy" data-item="<?php echo $item->code; ?>" data-checkout="true">
                                        <i class="fa fa-rocket"></i>
                                    </button>
                                <?php endif;
                            else: ?>
                            <?php endif;
                        endif;
                    endif;
                endif; ?>

                <?php if (in_array('products', $show_modal)): ?>
                    <a  title="<?php echo JText::_('COM_SELLACIOUS_PRODUCT_QUICK_VIEW'); ?>" href="#sell-modal-<?php echo $item->code; ?>" role="button" data-toggle="modal" class="btn hasTooltip btn-quick-view">
                        <i class="fa fa-eye"></i>
                    </a>
                <?php endif; ?>

                <?php if ($compare_allow && in_array('products', $compare_pages)): ?>
                    <a  title="<?php echo JText::_('COM_SELLACIOUS_PRODUCT_COMPARE'); ?>" class="btn product-compare">
                        <input type="checkbox" class="btn-compare-input" data-item="<?php echo $item->code; ?>" /><i class="fa fa-balance-scale"></i>
                    </a>
                <?php endif; ?>

            </div>
            <div class="clearfix"></div>
        </div>
        <div class="clearfix"></div>
        </div>

    </li>




