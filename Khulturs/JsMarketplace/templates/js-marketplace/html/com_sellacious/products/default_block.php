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
use Sellacious\Media\Image\ResizeImage;
use Sellacious\Media\Image\ImageHelper;

/** @var  SellaciousViewProducts  $this */
/** @var  stdClass  $tplData */
$item = $tplData;

$me         = JFactory::getUser();
$url_raw    = 'index.php?option=com_sellacious&view=product&p=' . $item->code;
$url        = JRoute::_($url_raw);
$url_m      = JRoute::_($url_raw . '&layout=modal&tmpl=component');
$reviewsUrl = JRoute::_('index.php?option=com_sellacious&view=reviews&product_id=' . $item->id);
$login_to_see_price    = $this->helper->config->get('login_to_see_price', 0);

$params = array(
	'title'    => JText::_('COM_SELLACIOUS_PRODUCT_QUICK_VIEW'),
	'url'      => $url_m,
	'height'   => '600',
	'width'    => '800',
	'keyboard' => true,
);
echo JHtml::_('bootstrap.renderModal', 'sell-modal-' . $item->code, $params);

$c_currency = $this->helper->currency->current('code_3');
$s_currency = $this->helper->currency->forSeller($item->seller_uid, 'code_3');
$cat_id     = (int) $item->spl_listing_catid;

$product = new \Sellacious\Product($item->id, $item->variant_id, $item->seller_uid);

$images      = $product->getProductImages();

$thumbs 	 = ImageHelper::getResized($images, 50, 50, true, 85, ResizeImage::RESIZE_FIT);
$thumbs      = ImageHelper::getUrls($thumbs);

$images      = ImageHelper::getResized($images, 250, 250, true, 85, ResizeImage::RESIZE_FIT);
$images      = ImageHelper::getUrls($images);

$colCount = $this->helper->config->get('products_cols', 2);
$sellCols = '';
if ($colCount == '4'):
	$sellCols = 'sell-col-md-3 sell-col-sm-4 sell-col-xs-6';
elseif ($colCount == '3'):
	$sellCols = 'sell-col-md-4 sell-col-sm-6 sell-col-xs-6';
elseif ($colCount == '2'):
	$sellCols = 'sell-col-xs-6';
elseif ($colCount == 'auto' ):
	$sellCols = 'auto-adjust';
endif;

?>

<div class="product-wrap <?php echo $sellCols ?>">
	<div class="product-box spl-cat-<?php echo (int) $item->spl_listing_catid; ?>">

		<?php $link_detail = $this->helper->config->get('product_detail_page'); ?>

		<div class="image-box">

			<?php if ($link_detail): ?>
			<a href="<?php echo $url ?>" title="<?php echo htmlspecialchars($item->title) ?>">
				<?php else: ?>
				<a href="javascript:void(0);">
					<?php endif; ?>
					<span class="product-img" style="background-image:url(<?php echo reset($images) ?>);"></span>
				</a>


				<?php if (in_array('categories', (array) $this->helper->config->get('splcategory_badge_display'))):
					$badges = $this->helper->media->getImages('splcategories.badge', (int) $item->spl_listing_catid, false);

					if (count($badges)): ?>
						<img src="<?php echo reset($badges) ?>" class="spl-cat-badge"/><?php
					endif;
				endif; ?>

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


		</div>

		<div class="product-info-box">

            <div class="product-title">
				<a href="<?php echo $link_detail ? $url : 'javascript:void(0);' ?>" title="<?php echo $item->title; ?>">
					<?php echo $item->title;
					echo $item->variant_title ? ' - <small>' . $item->variant_title . '</small>' : ''; ?></a>
			</div>


            <!-- BEGIN: seller/admin can directly jump to backend for edit -->
            <?php if ($this->helper->access->check('product.edit') ||
                ($this->helper->access->checkAny(array('basic.own', 'seller.own', 'pricing.own', 'shipping.own', 'related.own', 'seo.own'), 'product.edit.', $item->id) && $item->seller_uid == $me->id)): ?>
                <?php $editUrl = JUri::root() . JPATH_SELLACIOUS_DIR . '/index.php?option=com_sellacious&view=product&layout=edit&id=' . $item->id; ?>
                <a title=" <?php echo JText::_('COM_SELLACIOUS_PRODUCT_LINK_BACKEND_EDIT'); ?>" target="_blank" class="btn  edit-product" href="<?php echo $editUrl; ?>">
                    <i class="fa fa-pencil-square"></i>
                </a>
            <?php endif; ?>
            <!-- END: seller/admin can directly jump to backend for edit -->

            <?php $allow_rating = $this->helper->config->get('product_rating'); ?>
            <?php $rating_pages = (array) $this->helper->config->get('product_rating_display'); ?>



			<?php
			$allowed_price_display = (array) $this->helper->config->get('allowed_price_display');
			$security              = $this->helper->config->get('contact_spam_protection');
			$login_to_see_price    = $this->helper->config->get('login_to_see_price', 0);

			$current_url = JUri::getInstance()->toString();
			$login_url   = JRoute::_('index.php?option=com_users&view=login&return=' . base64_encode($current_url), false);

			if ($login_to_see_price && $me->guest):
				?>
				<a href="<?php echo $login_url ?>"><?php echo JText::_('COM_SELLACIOUS_PRODUCT_PRICE_DISPLAY_LOGIN_TO_VIEW'); ?></a>
			<?php
			elseif ($item->price_display == 0):
				$price_display = $this->helper->config->get('product_price_display');
				$price_d_pages = (array) $this->helper->config->get('product_price_display_pages');

				if ($price_display > 0 && in_array('categories', $price_d_pages)):
					$price = round($item->sales_price, 2) >= 0.01 ? $this->helper->currency->display($item->sales_price, $s_currency, $c_currency, true) : JText::_('COM_SELLACIOUS_PRODUCT_PRICE_FREE');

					if ($price_display == 2 && round($item->list_price, 2) >= 0.01): ?>
						<div class="product-price"><?php echo $price; ?></div>
						<div class="old-price">
							<del><?php echo $this->helper->currency->display($item->list_price, $s_currency, $c_currency, true); ?></del>
							<span class="product-offer"><?php echo strtoupper(JText::_('COM_SELLACIOUS_PRODUCT_OFFER')); ?></span>
						</div>
					<?php
					else: ?>
						<div class="product-price pull-left"><?php echo $price; ?></div><?php
					endif; ?>
					<div class="clearfix"></div><?php
				endif;
			elseif ($item->price_display == 1 && in_array(1, $allowed_price_display)): ?>
				<div class="btn-toggle btn-price-toggle">
					<button type="button" class="btn call-for-price" data-toggle="true"><?php
						echo JText::_('COM_SELLACIOUS_PRODUCT_PRICE_DISPLAY_CALL_US'); ?></button>
					<button type="button" class="btn call-for-price-number hidden" data-toggle="true"><?php
						$mobile = $item->seller_mobile ? $item->seller_mobile : JText::_('COM_SELLACIOUS_PRODUCT_PRICE_DISPLAY_NO_NUMBER');

						if ($security):
							$text = $this->helper->media->writeText($mobile, 2, true);
							?><img src="data:image/png;base64,<?php echo $text; ?>"/><?php
						else:
							echo $mobile;
						endif; ?>
					</button>
				</div>
				<div class="clearfix"></div><?php
			elseif ($item->price_display == 2 && in_array(2, $allowed_price_display)): ?>
				<div class="btn-toggle btn-price-toggle">
					<button type="button" class="btn email-for-price" data-toggle="true"><?php
						echo JText::_('COM_SELLACIOUS_PRODUCT_PRICE_DISPLAY_EMAIL_US'); ?></button>
					<button type="button" class="btn email-for-price-email hidden" data-toggle="true"><?php
						$seller_email = $item->seller_email ? $item->seller_email : JText::_('COM_SELLACIOUS_PRODUCT_PRICE_DISPLAY_NO_EMAIL');

						if ($security):
							$text = $this->helper->media->writeText($seller_email, 2, true);
							?><img src="data:image/png;base64,<?php echo $text; ?>"/><?php
						else:
							echo $seller_email;
						endif; ?>
					</button>
				</div>
			<?php
			elseif ($item->price_display == 3 && in_array(3, $allowed_price_display)):
				$title	 = JText::sprintf('COM_SELLACIOUS_PRODUCT_PRICE_DISPLAY_OPEN_QUERY_FORM_FOR', $this->escape($item->title), $this->escape($item->variant_title));
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
					<a href="#query-form-<?php echo $item->code; ?>" role="button" data-toggle="modal" class="btn btn-primary">
						<i class="fa fa-file-text"></i> <?php echo JText::_('COM_SELLACIOUS_PRODUCT_PRICE_DISPLAY_OPEN_QUERY_FORM'); ?>
					</a>
				</div>
			<?php
			endif; ?>



            <?php if ($allow_rating && in_array('products', $rating_pages)): ?>
                <div class="product-stars">
                    <a class="review-link" href="<?php echo $reviewsUrl ?>">
                        <?php echo $this->helper->core->getStars($item->rating, true, 5.0); ?>
                    </a>
                </div>
            <?php endif; ?>


            <?php
			$features_pages = (array) $this->helper->config->get('product_features_list');

			if (in_array('products', $features_pages)):
				$features = json_decode($item->variant_features, true);
				$features = array_filter((array) $features, 'trim');

				if (count($features) == 0):
					$features = json_decode($item->features, true);
					$features = array_filter((array) $features, 'trim');
				endif;

				if (count($features)): ?>
					<ul class="product-features">
						<?php foreach ($features as $feature):
							echo '<li>' . htmlspecialchars($feature) . '</li>';
						endforeach; ?>
						<a href="#" ></a>
					</ul>
				<?php endif; ?>
			<?php endif; ?>

			<?php if ($item->variant_count > 1 || $item->seller_count > 1): ?>
				<div class="var-seller-count">
					<?php if ($item->variant_count > 1): ?>
						<div class="variant-count"><?php echo JText::sprintf('COM_SELLACIOUS_PRODUCT_COUNT_VARIANTS', $item->variant_count) ?></div>
					<?php endif; ?>

					<?php if ($item->seller_count > 1): ?>
						<div class="seller-count"><?php echo JText::sprintf('COM_SELLACIOUS_PRODUCT_COUNT_SELLERS', $item->seller_count) ?></div>
					<?php endif; ?>
					<div class="clearfix"></div>
				</div>
			<?php endif; ?>

                    <?php
                    $allow_checkout = $this->helper->config->get('allow_checkout');
                    $compare_allow  = $this->helper->product->isComparable($item->id);
                    $compare_pages  = (array) $this->helper->config->get('product_compare_display');
                    $cart_pages     = (array) $this->helper->config->get('product_add_to_cart_display');
                    $buynow_pages   = (array) $this->helper->config->get('product_buy_now_display');
                    $show_modal     = (array) $this->helper->config->get('product_quick_detail_pages');
                    $display_stock	= $this->helper->config->get('frontend_display_stock');

                    if ($item->price_display == 0 && !($login_to_see_price && $me->guest)):
                        if (in_array('products', $cart_pages) || in_array('products', $buynow_pages) || $allow_checkout): ?>
                            <div class="product-action-btn">
                                <div class="action-bottom">
									<?php if ((int) $item->stock_capacity > 0): ?>
										<?php if ($allow_checkout): ?>
											<?php if (in_array('products', $cart_pages)): ?>
												<button title="<?php echo JText::_('COM_SELLACIOUS_PRODUCT_ADD_TO_CART'); ?>" type="button" class="btn hasTooltip btn-add-cart add" data-item="<?php echo $item->code; ?>">
													<i class="fa fa-cart-plus"></i>
													<?php if ($display_stock):
														echo '('. (int) $item->stock_capacity . ')';
													endif; ?>
												</button>
											<?php endif; ?>

											<?php if (in_array('products', $buynow_pages)): ?>
												<button title="<?php echo JText::_('COM_SELLACIOUS_PRODUCT_BUY_NOW'); ?>" type="button" class="btn hasTooltip btn-add-cart buy" data-item="<?php echo $item->code; ?>" data-checkout="true">
													<i class="fa fa-rocket" aria-hidden="true"></i>
												</button>
											<?php endif; ?>
										<?php endif; ?>

										<?php if (in_array('products', $show_modal)): ?>
											<a  title="<?php echo JText::_('COM_SELLACIOUS_PRODUCT_QUICK_VIEW'); ?>" href="#sell-modal-<?php echo $item->code; ?>" role="button" data-toggle="modal" class="btn hasTooltip btn-quick-view">
												<i class="fa fa-eye"></i>
											</a>
										<?php endif; ?>

									<?php else: ?>
										<hr class="isolate">
										<?php if ($allow_checkout): ?>
											<button class="btn lbl-no-stock btn-primary">
												<i class="fa fa-times"></i> <?php echo JText::_('COM_SELLACIOUS_PRODUCT_OUT_OF_STOCK'); ?>
											</button>
										<?php endif; ?>

										<?php if (in_array('products', $show_modal)): ?>
											<a  title="<?php echo JText::_('COM_SELLACIOUS_PRODUCT_QUICK_VIEW'); ?>" href="#sell-modal-<?php echo $item->code; ?>" role="button" data-toggle="modal" class="btn btn-quick-view">
												<i class="fa fa-eye"></i>
											</a>
										<?php endif; ?>
									<?php endif; ?>
                                    <?php if ($compare_allow && in_array('products', $compare_pages)): ?>
                                        <label title="<?php echo JText::_('COM_SELLACIOUS_PRODUCT_COMPARE'); ?>" class="product-compare btn hasTooltip">
                                            <input type="checkbox" class="btn-compare" data-item="<?php echo $item->code; ?>" /><i class="fa  fa-balance-scale"></i> </label>
                                    <?php endif; ?>

                               </div>
                         </div>

                        <!--      On mouse hover buttons  -->
                        <div  id="action-btn-hover" class="product-action-btn-hover" style="display: none;">
                                <?php if ($allow_checkout): ?>
                                    <?php if ((int) $item->stock_capacity > 0): ?>
                                        <?php if (in_array('products', $cart_pages)): ?>
                                            <button title="<?php echo JText::_('COM_SELLACIOUS_PRODUCT_ADD_TO_CART'); ?>" type="button" class="btn hasTooltip btn-add-cart add" data-item="<?php echo $item->code; ?>">
                                                <i class="fa fa-cart-plus"></i>
                                                <?php if ($display_stock):
                                                    echo '('. (int) $item->stock_capacity . ')';
                                                endif; ?>
                                                <?php echo JText::_('COM_SELLACIOUS_PRODUCT_ADD_TO_CART'); ?>
                                            </button>
                                        <?php endif; ?>

                                        <?php if (in_array('products', $buynow_pages)): ?>
                                            <button title="<?php echo JText::_('COM_SELLACIOUS_PRODUCT_BUY_NOW'); ?>" type="button" class="btn hasTooltip btn-add-cart buy" data-item="<?php echo $item->code; ?>" data-checkout="true">
                                                <i class="fa fa-rocket" aria-hidden="true"></i>
                                            </button>
                                        <?php endif; ?>

                                        <?php if (in_array('products', $show_modal)): ?>
                                            <a  title="<?php echo JText::_('COM_SELLACIOUS_PRODUCT_QUICK_VIEW'); ?>" href="#sell-modal-<?php echo $item->code; ?>" role="button" data-toggle="modal" class="btn hasTooltip btn-quick-view">
                                                <i class="fa fa-eye"></i><?php echo JText::_('COM_SELLACIOUS_PRODUCT_BUY_NOW'); ?>
                                            </a>
                                        <?php endif; ?>

                                    <?php else: ?>
                                        <hr class="isolate">
                                        <button class="btn lbl-no-stock btn-primary">
                                            <i class="fa fa-times"></i> <?php echo JText::_('COM_SELLACIOUS_PRODUCT_OUT_OF_STOCK'); ?>
                                        </button>

                                        <?php if (in_array('products', $show_modal)): ?>
                                            <a  title="<?php echo JText::_('COM_SELLACIOUS_PRODUCT_QUICK_VIEW'); ?>" href="#sell-modal-<?php echo $item->code; ?>" role="button" data-toggle="modal" class="btn btn-default btn-quick-view">
                                                <i class="fa fa-eye"></i><?php echo JText::_('COM_SELLACIOUS_PRODUCT_QUICK_VIEW'); ?>
                                            </a>
                                        <?php endif; ?>
                                    <?php endif; ?>
                                <?php endif; ?>
                                <?php if ($compare_allow && in_array('products', $compare_pages)): ?>
                                    <label title="<?php echo JText::_('COM_SELLACIOUS_PRODUCT_COMPARE'); ?>" class="product-compare btn hasTooltip">
                                        <input type="checkbox" class="btn-compare" data-item="<?php echo $item->code; ?>" /><i class="fa  fa-balance-scale"></i><?php echo JText::_('COM_SELLACIOUS_PRODUCT_COMPARE'); ?> </label>
                                <?php endif; ?>
                        </div>




                    <?php endif;
                    endif; ?>


					<div class="clearfix"></div>

  <div class="list-view-btn">
            <?php
            $allow_checkout = $this->helper->config->get('allow_checkout');
            $compare_allow  = $this->helper->product->isComparable($item->id);
            $compare_pages  = (array) $this->helper->config->get('product_compare_display');
            $cart_pages     = (array) $this->helper->config->get('product_add_to_cart_display');
            $buynow_pages   = (array) $this->helper->config->get('product_buy_now_display');
            $show_modal     = (array) $this->helper->config->get('product_quick_detail_pages');
            $display_stock	= $this->helper->config->get('frontend_display_stock');

            if ($item->price_display == 0 && !($login_to_see_price && $me->guest)):
                if (in_array('products', $cart_pages) || in_array('products', $buynow_pages)): ?>
                    <div class="product-action-btn">
                        <?php if ($allow_checkout): ?>
                            <?php if ((int) $item->stock_capacity > 0): ?>
                                <?php if (in_array('products', $cart_pages)): ?>
                                    <button type="button" class="btn btn-primary btn-add-cart add" data-item="<?php echo $item->code; ?>">
                                        <i class="fa fa-shopping-cart"></i>
	                                    <?php echo JText::_('COM_SELLACIOUS_PRODUCT_ADD_TO_CART'); ?>
                                        <?php if ($display_stock):
                                            echo '('. (int) $item->stock_capacity . ')';
                                        endif; ?>
                                    </button>
                                <?php endif; ?>

                                <?php if (in_array('products', $buynow_pages)): ?>
                                    <button type="button" class="btn btn-info btn-add-cart buy" data-item="<?php echo $item->code; ?>" data-checkout="true">
                                        <i class="fa fa-bolt" aria-hidden="true"></i> <?php echo JText::_('COM_SELLACIOUS_PRODUCT_BUY_NOW'); ?>
                                    </button>
                                <?php endif; ?>
                            <?php else: ?>
                                <button class="btn lbl-no-stock btn-primary">
                                    <i class="fa fa-times"></i> <?php echo JText::_('COM_SELLACIOUS_PRODUCT_OUT_OF_STOCK'); ?>
                                </button>
                            <?php endif; ?>
	                        <?php if ($compare_allow && in_array('products', $compare_pages)): ?>
								<label title="<?php echo JText::_('COM_SELLACIOUS_PRODUCT_COMPARE'); ?>" class="product-compare btn btn-sm hasTooltip btn btn-dark">
									<input type="checkbox" class="btn-compare" data-item="<?php echo $item->code; ?>" /><i class="fa  fa-balance-scale"></i><span><?php echo JText::_('COM_SELLACIOUS_PRODUCT_COMPARE'); ?></span> </label>
	                        <?php endif; ?>
	                        <?php if (in_array('products', $show_modal)): ?>
								<a  title="<?php echo JText::_('COM_SELLACIOUS_PRODUCT_QUICK_VIEW'); ?>" href="#sell-modal-<?php echo $item->code; ?>" role="button" data-toggle="modal" class="btn btn btn-outline-dark btn-quick-view">
									<i class="fa fa-eye"></i><?php echo JText::_('COM_SELLACIOUS_PRODUCT_QUICK_VIEW'); ?>
								</a>
	                        <?php endif; ?>
                        <?php endif; ?>
                    </div>
                <?php endif;
            endif; ?>
</div>








			<div class="clearfix"></div>
		</div>
		<div class="clearfix"></div>
	</div>
</div>
