<?php
/**
 * @version     1.7.3
 * @package     Sellacious Best Selling Products Module
 *
 * @copyright   Copyright (C) 2012-2019 Bhartiy Web Technologies. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Mohd Kareemuddin <info@bhartiy.com> - http://www.bhartiy.com
 */

// no direct access
use Sellacious\Media\Image\ImageHelper;
use Sellacious\Media\Image\ResizeImage;
use Sellacious\Product;

defined('_JEXEC') or die('Restricted access');
JHtml::_('jquery.framework');
JHtml::_('stylesheet', JUri::root() . 'modules/mod_sellacious_bestsellingproducts/assets/css/owl.carousel.min.css');

JHtml::_('script', 'com_sellacious/util.modal.js', false, true);
JHtml::_('stylesheet', 'com_sellacious/util.modal.css', null, true);

if ($helper->config->get('product_compare')):
	JHtml::_('script', 'com_sellacious/util.compare.js', false, true);
endif;

JHtml::_('script', 'com_sellacious/fe.view.sellacious.js', false, true);
JHtml::_('script', 'com_sellacious/util.cart.aio.js', false, true);

if ($layoutView != 'product'):
	JHtml::_('script', 'com_sellacious/fe.view.products.js', false, true);
endif;

JHtml::_('stylesheet', 'com_sellacious/fe.component.css', null, true);
JHtml::_('stylesheet', 'com_sellacious/font-awesome.min.css', null, true);
JHtml::_('stylesheet', 'mod_sellacious_bestsellingproducts/style.css', null, true);
JHtml::_('stylesheet', 'com_sellacious/fe.view.cart.aio.css', null, true);

?>
<div class="mod-sellacious-bestsellingproducts bestsellingproducts-carousel-layout <?php echo $class_sfx; ?>">
	<div class="bsp-productcarousel owl-carousel" id="bsp-productcarousel<?php echo $module->id; ?>">
		<?php foreach ($products AS $product):
			$splCategoryClass = '';

			if ($splStandOut && $product->spl_category_ids)
			{
				$splCategoryClasses = explode(',', $product->spl_category_ids);
				$splCategoryClass = 'spl-cat-' . reset($splCategoryClasses);
			}

			$prodHelper  = new Product($product->product_id, $product->variant_id, $product->seller_uid);
			$item        = $helper->product->getItem($product->id);
			$link_detail = $helper->config->get('product_detail_page');
			$ratings     = $helper->rating->getProductRating($product->id, 0, $product->seller_uid);
			$price       = $prodHelper->getPrice($product->seller_uid, 1, $c_cat);
			$code        = $prodHelper->getCode($product->seller_uid);
			$seller_attr = $prodHelper->getSellerAttributes($product->seller_uid);
			$reviewsUrl  = JRoute::_('index.php?option=com_sellacious&view=reviews&product_id=' . $product->id);

			if (!is_object($seller_attr)):
				$seller_attr                 = new stdClass();
				$seller_attr->price_display  = 0;
				$seller_attr->stock_capacity = 0;
			endif;

			$item                = array_merge((array) $item, (array) $price);
			$item                = (object) $item;
			$seller_info         = ModSellaciousBestSellingProducts::getSellerInfo($product->seller_uid);
			$item->seller_email  = $seller_info->seller_email;
			$item->seller_mobile = $seller_info->seller_mobile;
			$item->shoprules     = $helper->shopRule->toProduct($item, false, true);

			if (abs($price->list_price) >= 0.01)
			{
				$item->list_price = $item->list_price_final;
			}

			$ratings = $ratings->rating;

			$images      = $prodHelper->getProductImages();

			$thumbs 	 = ImageHelper::getResized($images, 50, 50, true, 85, ResizeImage::RESIZE_FIT);
			$thumbs      = ImageHelper::getUrls($thumbs);

			$images      = ImageHelper::getResized($images, 250, 250, true, 85, ResizeImage::RESIZE_FIT);
			$images      = ImageHelper::getUrls($images);

			$url   = 'index.php?option=com_sellacious&view=product&p=' . $code;
			$url_m = JRoute::_($url . '&layout=modal&tmpl=component');

			$sl_params = array(
				'title'    => JText::_('MOD_SELLACIOUS_BESTSELLINGPRODUCTS_QUICK_VIEW'),
				'url'      => $url_m,
				'height'   => '600',
				'width'    => '800',
				'keyboard' => true,
			);
			echo JHtml::_('bootstrap.renderModal', 'modal-' . $module->id . '-' . $code, $sl_params);

			$s_currency = $helper->currency->forSeller($price->seller_uid, 'code_3');

			$options = array(
				'title'    => JText::_('MOD_SELLACIOUS_BESTSELLINGPRODUCTS_CART_TITLE'),
				'backdrop' => 'static',
			);
			?>

			<div class="item">
				<div class="bsp-product-box <?php echo $splCategoryClass; ?>" data-rollover="container">
					<div class="image-box">
                        <a href="<?php echo $link_detail ? $url : 'javascript:void(0);' ?>">
                        <span class="product-img" style="background-image:url(<?php echo reset($images); ?>)"></span>
                        </a>
						<div class="product-images">
							<ul class="product-images-block">
								<?php
								$index = 0;
								foreach ($thumbs as $thumb): ?>
									<li class="product-images-list" style="background-image:url(<?php echo $thumb ?>);" data-image="<?php echo $images[$index]; ?>"></li>
									<?php
									$index++;
								endforeach; ?>
							</ul>
						</div>
                        <?php
                        if ((in_array('products', (array) $helper->config->get('splcategory_badge_display'))) && ($splCategoryClass)):
                            $badges = $helper->media->getImages('splcategories.badge', (int) $splCategory, false);

                            if ($badges): ?>
                                <img src="<?php echo reset($badges) ?>" class="spl-cat-badge"/><?php
                            endif;
                        endif;
                        ?>
					</div>
					<div class="bsp-product-info-box">

                        <?php $allow_checkout = $helper->config->get('allow_checkout');
                        $compare_allow  = $helper->product->isComparable($product->id);
                        $display_stock	= $helper->config->get('frontend_display_stock');

                        if ($seller_attr->price_display == 0 && !($login_to_see_price && $me->guest)): ?>
                            <div class="product-action-btn">
                                <?php if ($allow_checkout && $seller_attr->price_display == 0): ?>
                                    <?php if ((int) $seller_attr->stock_capacity > 0): ?>
                                        <?php if ($displayaddtocartbtn == '1'): ?>
                                            <button title="<?php echo JText::_('COM_SELLACIOUS_PRODUCT_ADD_TO_CART'); ?>"  type="button" class="hasTooltip btn btn-add-cart add" data-item="<?php echo $code; ?>">
                                                <i class="fa fa-cart-plus"></i>
                                                <?php if ($display_stock):
                                                    echo '('. (int) $seller_attr->stock_capacity . ')';
                                                endif ;?>
                                            </button>
                                        <?php endif; ?>

                                        <?php if ($displaybuynowbtn == '1'): ?>
                                            <button title="<?php echo JText::_('COM_SELLACIOUS_PRODUCT_BUY_NOW'); ?>"  type="button" class="hasTooltip  btn btn-add-cart buy" data-item="<?php echo $code; ?>" data-checkout="true">
                                                <i class="fa fa-rocket"></i>
                                            </button>
                                        <?php endif; ?>
                                    <?php else: ?>
                                        <button class="hasTooltip  btn lbl-no-stock btn-primary">
                                            <!--<i class="fa fa-times"></i> --><?php echo JText::_('MOD_SELLACIOUS_BESTSELLINGPRODUCTS_OUT_OF_STOCK'); ?>
                                        </button>
                                    <?php endif; ?>
                                <?php endif; ?>

                                <?php if ($compare_allow && $displaycomparebtn == '1'): ?>
                                    <label title="<?php echo JText::_('COM_SELLACIOUS_PRODUCT_COMPARE'); ?>" class="product-compare btn"><?php //echo JText::_('MOD_SELLACIOUS_BESTSELLINGPRODUCTS_COMPARE'); ?>
                                        <input type="checkbox" class="hasTooltip btn-compare" data-item="<?php echo $code; ?>" /><i class="fa fa-balance-scale"></i></label>
                                <?php endif; ?>

	                            <?php if ($displayquickviewbtn == '1'): ?>
									<a href="#modal-<?php echo $module->id; ?>-<?php echo $code; ?>" role="button" data-toggle="modal" class="btn btn-quick-view">
										<i class="fa fa-eye"></i>
									</a>
	                            <?php endif; ?>
                            </div>

                        <?php endif; ?>
						<div class="bsp-product-title">
							<a href="<?php echo $link_detail ? $url : 'javascript:void(0);' ?>" title="<?php echo $product->title; ?>">
								<?php echo $product->title; ?>
							</a>
						</div>

						<?php $allow_rating = $helper->config->get('product_rating'); ?>

						<?php if ($allow_rating && $displayratings == '1'): ?>
							<div class="product-stars">
								<?php echo $helper->core->getStars($ratings, true, 5.0) ?>
							</div>
						<?php endif; ?>

						<?php
						$allowed_price_display = (array) $helper->config->get('allowed_price_display');
						$security              = $helper->config->get('contact_spam_protection');

						if ($login_to_see_price && $me->guest)
						{
							?>
							<a href="<?php echo $login_url ?>"><?php echo JText::_('MOD_SELLACIOUS_BESTSELLINGPRODUCTS_PRICE_DISPLAY_LOGIN_TO_VIEW'); ?></a>
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
									<div class="bsp-product-price"><?php echo $price; ?></div>
									<div class="old-price">
										<del><?php echo $helper->currency->display($item->list_price, $s_currency, $c_currency, true); ?></del>
										<span class="bsp-product-offer"><?php echo strtoupper(JText::_('MOD_SELLACIOUS_BESTSELLINGPRODUCTS_OFFER')); ?></span>
									</div>
									<?php
								}
								else
								{
									?>
									<div class="bsp-product-price pull-left"><?php echo $price; ?></div>
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
									echo JText::_('MOD_SELLACIOUS_BESTSELLINGPRODUCTS_PRICE_DISPLAY_CALL_US'); ?></button>
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
									echo JText::_('MOD_SELLACIOUS_BESTSELLINGPRODUCTS_PRICE_DISPLAY_EMAIL_US'); ?></button>
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
								'title'    => (JText::sprintf('MOD_SELLACIOUS_BESTSELLINGPRODUCTS_PRICE_DISPLAY_OPEN_QUERY_FORM_FOR',
									addslashes($item->title), isset($item->variant_title) ? addslashes($item->variant_title) : '')),
								'backdrop' => 'static',
								'height'   => '520',
								'keyboard' => true,
								'url'      => "index.php?option=com_sellacious&view=product&p={$code}&layout=query&tmpl=component",
							);

							echo JHtml::_('bootstrap.renderModal', "query-form-{$code}", $options);
							?>
							<div class="productquerybtn">
								<a href="#query-form-<?php echo $code; ?>" role="button" data-toggle="modal" class="btn btn-primary">
									<i class="fa fa-file-text"></i> <?php echo JText::_('MOD_SELLACIOUS_BESTSELLINGPRODUCTS_PRICE_DISPLAY_OPEN_QUERY_FORM'); ?>
								</a>
							</div>
							<?php
						}
						?>
						<div class="clearfix"></div>

						<?php if ($featurelist == '1'):
							$features = $item->features;
							if (count($features)): ?>
								<ul class="bsp-product-features">
									<?php
									foreach ($features as $feature)
									{
										echo '<li>' . htmlspecialchars($feature) . '</li>';
									}
									?>
								</ul>
								<div class="clearfix"></div>
							<?php endif; ?>
						<?php endif;?>





						<div class="clearfix"></div>
					</div>
				</div>
			</div>
		<?php endforeach; ?>
		<div class="clearfix"></div>
	</div>
</div>

<?php
if ($autoplayopt == "1"):
	$autoplayvalue = 'true';
else:
	$autoplayvalue = 'false';
endif;
?>

<script type="text/javascript">
	jQuery(document).ready(function ($) {

		var owl = $('#bsp-productcarousel<?php echo $module->id ?>');
		owl.owlCarousel({
			nav: true,
			navText: [
				"<i class='fa fa-angle-left'></i>",
				"<i class='fa fa-angle-right'></i>"
			],
			rewind: true,
			autoplay: <?php echo $autoplayvalue; ?>,
			autoplayTimeout: <?php echo $autoplayspeed; ?>,
			autoplayHoverPause: true,
			margin: <?php echo $gutter; ?>,
			responsive: {
				0: {
					items: <?php echo $responsive0to500; ?>
				},
				500: {
					items: <?php echo $responsive500; ?>
				},
				992: {
					items: <?php echo $responsive992; ?>
				},
				1200: {
					items: <?php echo $responsive1200; ?>
				},
				1400: {
					items: <?php echo $responsive1400; ?>
				}
			}
		});
	});
</script>
<?php
JHtml::_('script', JUri::root() . 'modules/mod_sellacious_bestsellingproducts/assets/js/owl.carousel.js');
?>
