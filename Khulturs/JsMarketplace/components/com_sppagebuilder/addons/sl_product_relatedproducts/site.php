<?php
/**
 * @version     2.2.0
 * @package     SP Page Builder Addons for Sellacious
 *
 * @copyright   Copyright (C) 2016. Bhartiy Web Technologies. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Bhavika Matariya <info@bhartiy.com> - http://www.bhartiy.com
 */

//no direct access
defined('_JEXEC') or die ('restricted aceess');

use Sellacious\Product;

class SppagebuilderAddonSL_Product_RelatedProducts extends SppagebuilderAddons
{

	public function render()
	{

		$class            = (isset($this->addon->settings->class) && $this->addon->settings->class) ? $this->addon->settings->class : '';
		$title            = (isset($this->addon->settings->title) && $this->addon->settings->title) ? $this->addon->settings->title : '';
		$title_alignment  = (isset($this->addon->settings->title_alignment) && $this->addon->settings->title_alignment) ? $this->addon->settings->title_alignment : '';
		$heading_selector = (isset($this->addon->settings->heading_selector) && $this->addon->settings->heading_selector) ? $this->addon->settings->heading_selector : 'h3';
		$box_title        = (isset($this->addon->settings->relatedproduct_title) && $this->addon->settings->relatedproduct_title) ? $this->addon->settings->relatedproduct_title : '';
		$limit            = (isset($this->addon->settings->total_products) && $this->addon->settings->total_products) ? $this->addon->settings->total_products : '50';
		$product_features = (isset($this->addon->settings->product_features) && $this->addon->settings->product_features) ? $this->addon->settings->product_features : 'hide';
		$layout           = (isset($this->addon->settings->layout) && $this->addon->settings->layout) ? $this->addon->settings->layout : 'grid';
		$autoplay         = (isset($this->addon->settings->autoplay) && $this->addon->settings->autoplay) ? $this->addon->settings->autoplay : '0';
		$autoplayspeed    = (isset($this->addon->settings->autoplayspeed) && $this->addon->settings->autoplayspeed) ? $this->addon->settings->autoplayspeed : '3000';

		$app     = JFactory::getApplication();
		$product = (int) $app->input->getInt('product');
		$seller  = (int) $app->input->getInt('s');
		$html    = '';

		$helper = SellaciousHelper::getInstance();

		$groups          = $helper->relatedProduct->loadColumn(array('list.select' => 'a.group_alias', 'product_id' => $product));
		$relatedProducts = $helper->relatedProduct->loadColumn(array(
			'list.select' => 'a.product_id',
			'group_alias' => $groups,
			'list.where'  => 'a.product_id !=' . $product,
			'list.start'  => 0,
			'list.limit'  => $limit,
		));
		shuffle($relatedProducts);

		if ($relatedProducts)
		{
			JHtml::_('script', 'com_sellacious/util.modal.js', false, true);
			JHtml::_('stylesheet', 'com_sellacious/util.modal.css', null, true);

			if ($helper->config->get('product_compare')):
				JHtml::_('script', 'com_sellacious/util.compare.js', false, true);
			endif;

			JHtml::_('script', 'com_sellacious/util.cart.aio.js', false, true);
			JHtml::_('script', 'com_sellacious/fe.view.products.js', false, true);

			if ($layout == "grid")
			{
				$slRelatedLayoutClass = 'sl-related-grid-layout';
				$slRelatedWrapClass   = 'sl-related-grid-wrap';

			}
			elseif ($layout == "list")
			{
				$slRelatedLayoutClass = 'sl-related-list-layout';
				$slRelatedWrapClass   = 'sl-related-list-wrap';

			}
			elseif ($layout == "carousel")
			{
				$slRelatedLayoutClass = 'sl-related-carousel-layout owl-carousel';
				$slRelatedWrapClass   = 'sl-related-carousel-wrap';

			}
			$html .= '<div class="moreinfo-box">';
			$html .= ($box_title) ? '<h3>' . $box_title . '</h3>' : '';
			$html .= '<div class="innermoreinfo">';
			$html .= '<div class="sl-relatedproducts-box ' . $slRelatedLayoutClass . '">';

			$me    = JFactory::getUser();
			$c_cat = $helper->client->loadResult(array('list.select' => 'category_id', 'user_id' => $me->id));


			foreach ($relatedProducts as $relatedProduct)
			{
				if ($relatedProduct != $product)
				{
					if (empty($seller))
					{
						$seller = $helper->product->getSellers($product, false);
					}

					$prodHelper  = new Product($relatedProduct, 0, $seller);
					$seller_attr = $prodHelper->getSellerAttributes($seller);

					$item             = $helper->product->getItem($relatedProduct);
					$item->categories = $prodHelper->getCategories();

					$item->images = $helper->product->getImages($relatedProduct);

					$item->code   = $prodHelper->getCode($seller);
					$ratings      = $helper->rating->getProductRating($item->id, 0, $seller);
					$item->rating = $ratings->rating;
					$price        = $prodHelper->getPrice($seller, 1, $c_cat);

					$sellerHelper = new Sellacious\Seller($seller);
					$sellerInfo   = $sellerHelper->getAttributes();

					$item = array_merge((array) $item, (array) $price);
					$item = (object) $item;

					$item->seller_email  = $sellerInfo['email'];
					$item->seller_mobile = $sellerInfo['mobile'];

					$url_raw = 'index.php?option=com_sellacious&view=product&p=' . $item->code;
					$url     = JRoute::_($url_raw);
					$url_m   = JRoute::_($url_raw . '&layout=modal&tmpl=component');
					$paths   = (array) $item->images;

					$allow_checkout = $helper->config->get('allow_checkout');
					$compare_allow  = $helper->product->isComparable($item->id);
					$compare_pages  = (array) $helper->config->get('product_compare_display');
					$cart_pages     = (array) $helper->config->get('product_add_to_cart_display');
					$buynow_pages   = (array) $helper->config->get('product_buy_now_display');

					if (!$allow_checkout && !$compare_allow)
					{
						$imgclass = 'img-nobtn';
					}
					else
					{
						$imgclass = '';
					}

					if ($layout != "carousel")
					{

						$params = array(
							'title'    => 'Quick View',
							'url'      => $url_m,
							'height'   => '600',
							'width'    => '800',
							'keyboard' => true,
						);
						echo JHtml::_('bootstrap.renderModal', 'modal-' . $item->code, $params);

						$options = array(
							'title'    => JText::_('COM_SELLACIOUS_CART_TITLE'),
							'backdrop' => 'static',
						);
					}
					$c_currency = $helper->currency->current('code_3');
					$s_currency = $helper->currency->forSeller($price->seller_uid, 'code_3');


					ob_start();
					?>
					<?php if ($layout != "carousel")
				{ ?>
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
				<?php } ?>
					<div class="sl-related-product-wrap <?php echo $slRelatedWrapClass ?>">
						<div class="related-product-box" data-rollover="container">

							<?php $link_detail = $helper->config->get('product_detail_page'); ?>

							<div class="image-box <?php echo $imgclass; ?>">
								<?php if ($link_detail): ?>
								<a href="<?php echo $url ?>" title="<?php echo htmlspecialchars($item->title) ?>">
									<?php else: ?>
									<a href="javascript:void(0);">
										<?php endif; ?>
										<span class="product-img bgrollover" style="background-image:url(<?php echo reset($paths) ?>);"
											  data-rollover="<?php echo htmlspecialchars(json_encode($paths)); ?>"></span>
									</a>
							</div>
							<div class="related-product-info-box">
								<?php if ($link_detail): ?>
									<div class="related-product-title">
										<a href="<?php echo $url ?>" title="<?php echo $item->title; ?>">
											<?php echo $item->title; ?></a>
									</div>
								<?php else: ?>
									<div class="product-title">
										<a href="javascript:void(0);"><?php echo $item->title; ?></div>
								<?php endif; ?>

								<?php $allow_rating = $helper->config->get('product_rating'); ?>
								<?php $rating_pages = (array) $helper->config->get('product_rating_display'); ?>

								<?php if ($allow_rating && in_array('products', $rating_pages)): ?>
									<div class="product-stars">
										<?php echo $helper->core->getStars($item->rating, true, 5.0) ?>
									</div>
								<?php endif; ?>

								<hr class="isolate">
								<?php
								$allowed_price_display = (array) $helper->config->get('allowed_price_display');
								$security              = $helper->config->get('contact_spam_protection');

								if ($seller_attr->price_display == 0)
								{
									$price_display = $helper->config->get('product_price_display');
									$price_d_pages = (array) $helper->config->get('product_price_display_pages');

									if ($price_display > 0 && in_array('products', $price_d_pages))
									{
										$price = round($item->price_id, 3) > 0 ? $helper->currency->display($item->sales_price, $s_currency, $c_currency, true) : 'N/A';

										if ($price_display == 2 && round($item->list_price, 3) > 0)
										{
											?>
											<div class="related-product-price"><?php echo $price; ?></div>
											<div class="old-price">
												<del><?php echo $helper->currency->display($item->list_price, $s_currency, $c_currency, true); ?></del>
												<span class="related-product-offer">OFFER</span>
											</div>
											<?php
										}
										else
										{
											?>
											<div class="related-product-price pull-left"><?php echo $price; ?></div>
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
									<div class="btn-toggle rp-btn-toggle">
										<button type="button" class="btn btn-default" data-toggle="true"><?php
											echo JText::_('COM_SELLACIOUS_PRODUCT_PRICE_DISPLAY_CALL_US') ?></button>
										<button type="button" class="btn btn-small btn-primary hidden" data-toggle="true"><?php
											$mobile = $item->seller_mobile ? $item->seller_mobile : '(NO NUMBER GIVEN)';

											if ($security)
											{
												$text = $helper->media->writeText($mobile, 2, true);
												?><img src="data:image/png;base64,<?php echo $text; ?>"/><?php
											}
											else
											{
												echo $mobile;
											}
											?></button>
									</div>
									<div class="clearfix"></div>
									<?php
								}
								elseif ($seller_attr->price_display == 2 && in_array(2, $allowed_price_display))
								{
									?>
									<div class="btn-toggle rp-btn-toggle">
										<button type="button" class="btn btn-default" data-toggle="true"><?php
											echo JText::_('COM_SELLACIOUS_PRODUCT_PRICE_DISPLAY_EMAIL_US'); ?></button>
										<button type="button" class="btn btn-small btn-primary hidden" data-toggle="true"><?php
											$seller_email = $item->seller_email ? $item->seller_email : '(NO EMAIL GIVEN)';

											if ($security)
											{
												$text = $helper->media->writeText($seller_email, 2, true);
												?><img src="data:image/png;base64,<?php echo $text; ?>"/><?php
											}
											else
											{
												echo $seller_email;
											}
											?></button>
									</div>
									<?php
								}
								elseif ($seller_attr->price_display == 3 && in_array(3, $allowed_price_display))
								{
									$options = array(
										'title'    => (JText::sprintf('COM_SELLACIOUS_PRODUCT_PRICE_DISPLAY_OPEN_QUERY_FORM_FOR',
											addslashes($item->title), '')),
										'backdrop' => 'static',
										'height'   => '520',
										'keyboard' => true,
										'url'      => "index.php?option=com_sellacious&view=product&p={$item->code}&layout=query&tmpl=component",
									);

									echo JHtml::_('bootstrap.renderModal', "query-form-{$item->code}", $options);
									?>
									<div class="productquerybtn">
										<a href="#query-form-<?php echo $item->code ?>" role="button" data-toggle="modal" class="btn btn-primary">
											<i class="fa fa-file-text"></i> <?php echo JText::_('COM_SELLACIOUS_PRODUCT_PRICE_DISPLAY_OPEN_QUERY_FORM'); ?>
										</a>
									</div>
									<?php
								}
								?>
								<div class="clearfix"></div>

								<?php
								if ($product_features == "show"):
									$features = $item->features;
									$features = array_filter((array) $features, 'trim');

									if (count($features)): ?>
										<hr class="isolate">
										<ul class="related-product-features">
											<?php
											foreach ($features as $feature)
											{
												echo '<li>' . htmlspecialchars($feature) . '</li>';
											}
											?>
										</ul>
										<div class="clearfix"></div>
									<?php endif; ?>
								<?php endif;

								if ($seller_attr->price_display == 0): ?>
									<div class="product-action-btn">
										<?php if ($allow_checkout): ?>
											<?php if ((int) $seller_attr->stock_capacity > 0): ?>
												<?php if (in_array('products', $cart_pages)): ?>
													<hr class="isolate">
													<button type="button" class="btn btn-default btn-add-cart add"
															data-item="<?php echo $item->code; ?>">
														<i class="fa fa-shopping-cart"></i> Add to Cart
													</button>
												<?php endif; ?>

												<?php if (in_array('products', $buynow_pages)): ?>
													<button type="button" class="btn btn-primary btn-add-cart buy"
															data-item="<?php echo $item->code; ?>" data-checkout="true">
														<i class="fa fa-bolt" aria-hidden="true"></i> Buy Now
													</button>
												<?php endif; ?>
											<?php else: ?>
												<hr class="isolate">
												<button class="btn lbl-no-stock btn-primary">
													<i class="fa fa-times"></i> Out of Stock
												</button>
											<?php endif; ?>
										<?php endif; ?>

									</div>
								<?php endif; ?>

								<?php if ($compare_allow && in_array('products', $compare_pages)): ?>
									<label class="product-compare btn btn-primary">Compare&nbsp;
										<input type="checkbox" class="btn-compare" data-item="<?php echo $item->code; ?>"/></label>
								<?php endif; ?>

								<div class="clearfix"></div>
							</div>
							<div class="clearfix"></div>
						</div>
					</div>

					<?php
					$html .= ob_get_clean();
				}
			}

			$html .= '<div class="clearfix"></div>';
			$html .= '</div>';
			$html .= '</div>';
			$html .= '</div>';

			ob_start();
			?>
			<script>
				jQuery(document).ready(function ($) {
					$('.rp-btn-toggle').click(function () {
						$(this).find('[data-toggle="true"]').toggleClass('hidden');
					});
				});
			</script>
			<?php
			$html .= ob_get_clean();

			if ($layout == "carousel")
			{
				if ($autoplay == "1") :
					$autoplayvalue = 'true';
				elseif ($autoplay = "0") :
					$autoplayvalue = 'false';
				endif;

				ob_start();
				?>
				<script type="text/javascript">
					jQuery(document).ready(function ($) {

						var owl = $('.sl-related-carousel-layout');
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
							margin: 8,
							responsive: {
								0: {
									items: 1
								},
								520: {
									items: 2
								},
								992: {
									items: 3
								},
								1200: {
									items: 4
								}
							}
						});

					});
				</script>
				<?php

				$html .= ob_get_clean();
			}

		}

		//Output
		if ($html)
		{
			$output = '<div class="sppb-addon sppb-addon-category-desc ' . $class . '">';
			$output .= ($title) ? '<' . $heading_selector . ' class="sppb-addon-title text-' . $title_alignment . '">' . $title . '</' . $heading_selector . '>' : '';
			$output .= '<div class="sppb-addon-content">';
			$output .= $html;
			$output .= '</div>';
			$output .= '</div>';

			return $output;
		}

		return;
	}

	public function stylesheets()
	{
		$layout = (isset($this->addon->settings->layout) && $this->addon->settings->layout) ? $this->addon->settings->layout : 'grid';
		if ($layout == 'carousel')
		{
			return array(
				JURI::base(true) . '/components/com_sppagebuilder/assets/css/sellacious/owl.carousel.min.css',
				JURI::base(true) . '/components/com_sppagebuilder/assets/css/sellacious/sl-relatedproducts.css',
			);
		}
		else
		{
			return array(
				JURI::base(true) . '/components/com_sppagebuilder/assets/css/sellacious/sl-relatedproducts.css',
			);
		}

	}

	public function scripts()
	{
		return array(JURI::base(true) . '/components/com_sppagebuilder/assets/js/sellacious/owl.carousel.js');
	}

}
