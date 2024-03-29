<?php
/**
 * @version     1.7.3
 * @package     Sellacious Latest Products Module
 *
 * @copyright   Copyright (C) 2012-2019 Bhartiy Web Technologies. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Bhavika Matariya <info@bhartiy.com> - http://www.bhartiy.com
 */

// no direct access
use Sellacious\Media\Image\ImageHelper;
use Sellacious\Media\Image\ResizeImage;

defined('_JEXEC') or die('Restricted access');

JHtml::_('script', 'com_sellacious/util.modal.js', false, true);
JHtml::_('stylesheet', 'com_sellacious/util.modal.css', null, true);

if ($helper->config->get('product_compare')):
    JHtml::_('script', 'com_sellacious/util.compare.js', false, true);
endif;

JHtml::_('script', 'com_sellacious/fe.view.sellacious.js', false, true);
JHtml::_('script', 'com_sellacious/util.cart.aio.js', false, true);

if ($layoutview != 'product'):
    JHtml::_('script', 'com_sellacious/fe.view.products.js', false, true);
endif;

JHtml::_('stylesheet', 'com_sellacious/fe.component.css', null, true);
JHtml::_('stylesheet', 'com_sellacious/font-awesome.min.css', null, true);
JHtml::_('stylesheet', 'mod_sellacious_latestproducts/style.css', null, true);
JHtml::_('stylesheet', 'com_sellacious/fe.view.cart.aio.css', null, true);

?>
<div class="mod-sellacious-latestproducts latest-gridtwo-layout <?php echo $class_sfx; ?>">
    <?php foreach ($products AS $product):
        if ($splCategory):
            $splCategoryClass = 'spl-cat-' . $splCategory;
        else:
            $splCategoryClass = '';
        endif;

        if ($splStandOut && $product->spl_category_ids)
        {
            $splCategoryClasses = explode(',', $product->spl_category_ids);
            $splCategoryClass = 'spl-cat-' . reset($splCategoryClasses);
        }

        $prodHelper  = new \Sellacious\Product($product->id);
        $item        = $helper->product->getItem($product->id);
        $ratings     = $helper->rating->getProductRating($product->id, 0, $product->seller_uid);
        $price       = $prodHelper->getPrice($product->seller_uid, 1, $c_cat);
        $code        = $prodHelper->getCode($product->seller_uid);
        $seller_attr = $prodHelper->getSellerAttributes($product->seller_uid);

        if (!is_object($seller_attr)):
            $seller_attr                 = new stdClass();
            $seller_attr->price_display  = 0;
            $seller_attr->stock_capacity = 0;
        endif;

        $item                = array_merge((array) $item, (array) $price);
        $item                = (object) $item;
        $seller_info         = ModSellaciousLatestProducts::getSellerInfo($product->seller_uid);
        $item->seller_email  = $seller_info->seller_email;
        $item->seller_mobile = $seller_info->seller_mobile;
        $item->shoprules     = $helper->shopRule->toProduct($item, false, true);

        if (abs($price->list_price) >= 0.01)
        {
            $item->list_price = $item->list_price_final;
        }

        $ratings = $ratings->rating;

	    $images      = $prodHelper->getProductImages();

	    $images      = ImageHelper::getResized($images, 150, 150, true, 85, ResizeImage::RESIZE_FIT);
	    $images      = ImageHelper::getUrls($images);

        $url   = 'index.php?option=com_sellacious&view=product&p=' . $code;
        $url_m = JRoute::_($url . '&layout=modal&tmpl=component');

        $sl_params = array(
            'title'    => JText::_('MOD_SELLACIOUS_LATESTPRODUCTS_QUICK_VIEW'),
            'url'      => $url_m,
            'height'   => '600',
            'width'    => '800',
            'keyboard' => true,
        );
        echo JHtml::_('bootstrap.renderModal', 'modal-' . $code, $sl_params);

        $s_currency = $helper->currency->forSeller($price->seller_uid, 'code_3');

        $options = array(
            'title'    => JText::_('MOD_SELLACIOUS_LATESTPRODUCTS_CART_TITLE'),
            'backdrop' => 'static',
        );

        ?>
        <script>
            jQuery(document).ready(function ($) {
                if ($('#modal-cart').length == 0) {
                    var $html = <?php echo json_encode(JHtml::_('bootstrap.renderModal', 'modal-cart', $options)); ?>;
                    $('body').append($html);

                    var $cartModal = $('#modal-cart');
                    var oo = new SellaciousViewCartAIO;
                    oo.token = $('#formToken').attr('name');
                    oo.initCart('#modal-cart .modal-body', true);
                    $cartModal.find('.modal-body').html('<div id="cart-items"></div>');
                    $cartModal.data('CartModal', oo);
                }
            });
        </script>
        <div class="latest-product-wrap-gridtwo">
            <div class="latest-product-box <?php echo $splCategoryClass; ?>" data-rollover="container">
                <div class="image-box">
                    <a href="<?php echo $url; ?>">
                        <img src="<?php echo reset($images); ?>" data-rollover='<?php echo htmlspecialchars(json_encode($images)); ?>' title="<?php echo $product->title; ?>">
                    </a>


                </div>
                <div class="latest-product-info-box">
                    <div class="latest-product-title">
                        <a href="<?php echo $url; ?>" title="<?php echo $product->title; ?>">
                            <p><?php echo $product->title; ?></p>
                        </a>
                    </div>


                    <?php
                    $allowed_price_display = (array) $helper->config->get('allowed_price_display');
                    $security              = $helper->config->get('contact_spam_protection');

                    if ($login_to_see_price && $me->guest)
                    {
                        ?>
                        <a href="<?php echo $login_url ?>"><?php echo JText::_('MOD_SELLACIOUS_LATESTPRODUCTS_PRICE_DISPLAY_LOGIN_TO_VIEW'); ?></a>
                        <?php
                    }
                    elseif ($seller_attr->price_display == 0)
                    {
                        $price_display = $helper->config->get('product_price_display');
                        $price_d_pages = (array) $helper->config->get('product_price_display_pages');

                        if ($price_display > 0 && in_array('products', $price_d_pages))
                        {
                            $price = round($item->sales_price, 2) >= 0.01 ? $helper->currency->display($item->sales_price, $s_currency, $c_currency, true) : JText::_('COM_SELLACIOUS_PRODUCT_PRICE_FREE');

                            if ($price_display == 2 && round($item->list_price, 2) >= 0.01)
                            {
                                ?>
                                <div class="latest-product-price"><?php echo $price; ?></div>
                                <?php
                            }
                            else
                            {
                                ?>
                                <div class="latest-product-price"><?php echo $price; ?></div>
                                <?php
                            }
                            ?>
                            <div class="clearfix"></div>
                            <?php
                        }
                    }
                    elseif ($seller_attr->price_display == 1 && in_array(1, $allowed_price_display))
                    {
                        ?>
                        <div class="btn-toggle btn-price-toggle">
                            <button type="button" class="btn btn-default" data-toggle="true"><?php
                                echo JText::_('MOD_SELLACIOUS_LATESTPRODUCTS_PRICE_DISPLAY_CALL_US'); ?></button>
                            <button type="button" class="btn btn-default hidden" data-toggle="true"><?php

                                if ($security)
                                {
                                    $b64text = $helper->media->writeText($item->seller_mobile, 2, true);
                                    ?><img src="data:image/png;base64,<?php echo $b64text; ?>"/><?php
                                }
                                else
                                {
                                    echo $item->seller_mobile;
                                } ?>
                            </button>
                        </div>
                        <div class="clearfix"></div>
                        <?php
                    }
                    elseif ($seller_attr->price_display == 2 && in_array(2, $allowed_price_display))
                    {
                        ?>
                        <div class="btn-toggle btn-price-toggle">
                            <button type="button" class="btn btn-default" data-toggle="true"><?php
                                echo JText::_('MOD_SELLACIOUS_LATESTPRODUCTS_PRICE_DISPLAY_EMAIL_US'); ?></button>
                            <button type="button" class="btn btn-default hidden" data-toggle="true"><?php

                                if ($security)
                                {
                                    $b64text = $helper->media->writeText($item->seller_email, 2, true);
                                    ?><img src="data:image/png;base64,<?php echo $b64text; ?>"/><?php
                                }
                                else
                                {
                                    echo $item->seller_email;
                                } ?>
                            </button>
                        </div>
                        <?php
                    }
                    elseif ($seller_attr->price_display == 3 && in_array(3, $allowed_price_display))
                    {
                        $options = array(
                            'title'    => (JText::sprintf('MOD_SELLACIOUS_LATESTPRODUCTS_PRICE_DISPLAY_OPEN_QUERY_FORM_FOR',
                                addslashes($item->title), isset($item->variant_title) ? addslashes($item->variant_title) : '')),
                            'backdrop' => 'static',
                            'height'   => '520',
                            'keyboard' => true,
                            'url'      => "index.php?option=com_sellacious&view=product&p={$code}&layout=query&tmpl=component",
                        );

                        echo JHtml::_('bootstrap.renderModal', "query-form-{$code}", $options);
                        ?>

                        <?php
                    }
                    ?>
                    <div class="clearfix"></div>


                    <div class="clearfix"></div>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
    <div class="clearfix"></div>
</div>
