<?php
/**
 * @version     2.1.4
 * @package     SP Page Builder Addons for Sellacious
 *
 * @copyright   Copyright (C) 2016. Bhartiy Web Technologies. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Bhavika Matariya <info@bhartiy.com> - http://www.bhartiy.com
 */

//no direct access
defined('_JEXEC') or die ('restricted aceess');

use Sellacious\Product;
use Sellacious\Media\Image\ImageHelper;

class SppagebuilderAddonSL_Category_Products extends SppagebuilderAddons
{

	public function render()
	{

		$class            = (isset($this->addon->settings->class) && $this->addon->settings->class) ? $this->addon->settings->class : '';
		$title            = (isset($this->addon->settings->title) && $this->addon->settings->title) ? $this->addon->settings->title : '';
		$title_alignment  = (isset($this->addon->settings->title_alignment) && $this->addon->settings->title_alignment) ? $this->addon->settings->title_alignment : '';
		$heading_selector = (isset($this->addon->settings->heading_selector) && $this->addon->settings->heading_selector) ? $this->addon->settings->heading_selector : 'h3';
		$limit            = (isset($this->addon->settings->total_products) && $this->addon->settings->total_products) ? $this->addon->settings->total_products : '8';
		$product_features = (isset($this->addon->settings->product_features) && $this->addon->settings->product_features) ? $this->addon->settings->product_features : 'hide';
		$layout           = (isset($this->addon->settings->layout) && $this->addon->settings->layout) ? $this->addon->settings->layout : 'grid';

		$db     = JFactory::getDBO();
		$app      = JFactory::getApplication();
		$jInput   = $app->input;
		$category = $jInput->getInt('category');
		$html     = '';
		$styles = array();

		$helper = SellaciousHelper::getInstance();

		$products = array();

		if ($limit > 0)
		{
			jimport('joomla.application.component.model');
			JModelLegacy::addIncludePath(JPATH_SITE . '/components/com_sellacious/models');

			/** @var  SellaciousModelProducts $model */
			$model = JModelLegacy::getInstance('Products', 'SellaciousModel', array('ignore_request' => true));
			$model->setState('filter.category_id', $category);
			$model->setState('list.limit', $limit);

			$products = $model->getItems();
		}

		if ($products)
		{
			if ($helper->config->get('product_compare')):
				JHtml::_('script', 'com_sellacious/util.compare.js', false, true);
			endif;

			JHtml::_('script', 'com_sellacious/util.cart.aio.js', false, true);
			JHtml::_('script', 'com_sellacious/fe.view.products.js', false, true);
			JHtml::_('stylesheet', 'com_sellacious/fe.view.cart.aio.css', null, true);

			if ($layout == "grid")
			{
				$slCatProdsLayoutClass   = 'sl-catprod-grid-layout';
				$slCatProdsWrapClass     = 'sl-catprod-grid-wrap';

			}elseif ($layout == "list")
			{
				$slCatProdsLayoutClass   = 'sl-catprod-list-layout';
				$slCatProdsWrapClass     = 'sl-catprod-list-wrap';

			}

			$html .= '<div class="sl-catproducts-box '.$slCatProdsLayoutClass.'">';

			$me    = JFactory::getUser();
			$c_cat = $helper->client->loadResult(array('list.select' => 'category_id', 'user_id' => $me->id));

			foreach ($products as $product)
			{
				$item = $product;

				$queryS = $db->getQuery(true);
				$queryS->select('category_id')->from('#__sellacious_seller_listing')
					->where('product_id = ' . $item->id . '')
					->where('category_id > 0');
				$splCatItem= $db->setQuery($queryS)->loadResult();
				if($splCatItem){
					$slCatProdsSpecialClass = 'spl-cat-'.$splCatItem;

					$splList = $helper->splCategory->getItem((int) $splCatItem );
					$style = '';
					$css   = new \Joomla\Registry\Registry($splList->params);

					foreach ($css as $css_k => $css_v)
					{
						$style .= "$css_k: $css_v;";
					}

					$styles[$splList->id] = ".sl-category-products-grid-layout .spl-cat-$splList->id { $style }" . ".sl-category-products-list-layout .spl-cat-$splList->id { $style }";

				}else{
					$slCatProdsSpecialClass = 'spl-cat-0';
				}


				$url_raw = 'index.php?option=com_sellacious&view=product&p=' . $item->code;
				$url     = JRoute::_($url_raw);
				$url_m   = JRoute::_($url_raw . '&layout=modal&tmpl=component');
				$paths   = ImageHelper::getImage('products', $item->product_id, 'images');

				$params = array(
					'title'    => addslashes($item->product_title),
					'url'      => $url_m,
					'height'   => '600',
					'width'    => '800',
					'keyboard' => true,
				);
				echo JHtml::_('bootstrap.renderModal', 'modal-' . $item->code, $params);

				$c_currency = $helper->currency->current('code_3');
				$s_currency = $helper->currency->forSeller($item->seller_uid, 'code_3');

				$options = array(
					'title'    => JText::_('COM_SELLACIOUS_CART_TITLE'),
					'backdrop' => 'static',
				);

				$allow_checkout = $helper->config->get('allow_checkout');
				$compare_allow  = $helper->product->isComparable($item->id);
				$compare_pages  = (array) $helper->config->get('product_compare_display');
				$cart_pages     = (array) $helper->config->get('product_add_to_cart_display');
				$buynow_pages   = (array) $helper->config->get('product_buy_now_display');
				$show_modal     = (array) $helper->config->get('product_quick_detail_pages');

				if(!$allow_checkout && !$compare_allow){
					$imgclass = 'img-nobtn';
				}
				else{
					$imgclass = '';
				}
				ob_start();
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
				<div class="sl-catproduct-wrap <?php echo $slCatProdsWrapClass;?>">
					<div class="sl-catproduct <?php echo $slCatProdsSpecialClass; ?>" data-rollover="container">

						<?php $link_detail = $helper->config->get('product_detail_page'); ?>

						<div class="image-box <?php echo $imgclass ?>">
							<?php if ($link_detail): ?>
								<a href="<?php echo $url ?>" title="<?php echo htmlspecialchars($item->title) ?>">
							<?php else: ?>
								<a href="javascript:void(0);">
							<?php endif; ?>
									<span class="product-img bgrollover" style="background-image:url(<?php echo reset($paths) ?>);"
										  data-rollover="<?php echo htmlspecialchars(json_encode($paths)); ?>"></span>
								</a>

							<?php
							if (in_array('categories', (array) $helper->config->get('splcategory_badge_display')))
							{
								$badges = $helper->media->getImages('splcategories.badge', (int) $item->spl_listing_catid, false);

								if (count($badges))
								{
									?><img src="<?php echo reset($badges) ?>" class="spl-cat-badge"/><?php
								}
							}
							?>
						</div>
						<div class="sl-catproduct-info-box">
							<?php if ($link_detail): ?>
								<div class="sl-catproduct-title"><a href="<?php echo $url ?>" title="<?php echo $item->title; ?>">
									<?php echo $item->title; ?>&nbsp;<?php echo $item->variant_title; ?></a>
								</div>
							<?php else: ?>
								<div class="sl-catproduct-title"><a href="javascript:void(0);"><?php
									echo $item->title; ?>&nbsp;<?php echo $item->variant_title; ?></a></div>
							<?php endif; ?>

							<?php $allow_rating = $helper->config->get('product_rating'); ?>
							<?php $rating_pages = (array) $helper->config->get('product_rating_display'); ?>

							<?php if ($allow_rating && in_array('categories', $rating_pages)): ?>
								<div class="product-stars">
									<?php echo $helper->core->getStars($item->product_rating->rating, true, 5.0) ?>
								</div>
							<?php endif; ?>

							<hr class="isolate">
							<?php
							$allowed_price_display = (array) $helper->config->get('allowed_price_display');
							$security              = $helper->config->get('contact_spam_protection');
							$price_display         = $helper->config->get('product_price_display');

							if ($price_display == 0)
							{
								$price_d_pages = (array) $helper->config->get('product_price_display_pages');

								if ($price_display > 0 && in_array('categories', $price_d_pages))
								{
									$price = round($item->price_id, 3) > 0 ? $helper->currency->display($item->sales_price, $s_currency, $c_currency, true) : 'N/A';

									if ($price_display == 2 && round($item->list_price, 3) > 0)
									{
										?>
										<div class="sl-catproduct-price"><?php echo $price; ?></div>
										<div class="old-price">
										<del><?php echo $helper->currency->display($item->list_price, $s_currency, $c_currency, true) ?></del>
										<span class="sl-catproduct-offer">OFFER</span>
										</div>
										<?php
									}
									else
									{
										?><div class="sl-catproduct-price pull-left"><?php echo $price; ?></div><?php
									}
									?><div class="clearfix"></div><?php
								}
							}
							elseif ($price_display == 1 && in_array(1, $allowed_price_display))
							{
								?>
								<div class="btn-toggle btn-price-toggle">
									<button type="button" class="btn btn-default" data-toggle="true"><?php
										echo JText::_('COM_SELLACIOUS_PRODUCT_PRICE_DISPLAY_CALL_US'); ?></button>
									<button type="button" class="btn btn-primary hidden" data-toggle="true"><?php
										$mobile = $item->seller_mobile ? $item->seller_mobile : '(NO NUMBER GIVEN)';

										if ($security)
										{
											$text = $helper->media->writeText($mobile, 2, true);
											?><img src="data:image/png;base64,<?php echo $text; ?>"/><?php
										}
										else
										{
											echo $mobile;
										} ?>
									</button>
								</div>
								<div class="clearfix"></div>
								<?php
							}
							elseif ($price_display == 2 && in_array(2, $allowed_price_display))
							{
								?>
								<div class="btn-toggle btn-price-toggle">
									<button type="button" class="btn btn-default" data-toggle="true"><?php
										echo JText::_('COM_SELLACIOUS_PRODUCT_PRICE_DISPLAY_EMAIL_US'); ?></button>
									<button type="button" class="btn btn-primary hidden" data-toggle="true"><?php
										$seller_email = $item->seller_email ? $item->seller_email : '(NO EMAIL GIVEN)';

										if ($security)
										{
											$text = $helper->media->writeText($seller_email, 2, true);
											?><img src="data:image/png;base64,<?php echo $text; ?>"/><?php
										}
										else
										{
											echo $seller_email;
										} ?>
									</button>
								</div>
								<?php
							}
							elseif ($price_display == 3 && in_array(3, $allowed_price_display))
							{
								$modalTitle	 = JText::sprintf('COM_SELLACIOUS_PRODUCT_PRICE_DISPLAY_OPEN_QUERY_FORM_FOR', $item->title, $item->variant_title);
								$options = array(
									'title'    => $modalTitle,
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
							}
							?>
							<div class="clearfix"></div>

							<?php
							if($product_features == "show"):
								$features = json_decode($item->features);
								$features   = array_filter((array) $features, 'trim');

								if (count($features)):
								?>
									<hr class="isolate">
									<ul class="sl-catproduct-features">
										<?php
										foreach ($features as $feature)
										{
											echo '<li>' . htmlspecialchars($feature) . '</li>';
										}
										?>
									</ul>
									<div class="clearfix"></div>
								<?php endif;
							endif; ?>
							<?php if ($price_display == 0 || in_array('categories', $show_modal)): ?>
								<div class="product-action-btn">
									<?php if ($allow_checkout && $item->price_display == 0): ?>
										<?php if ((int) $item->stock_capacity > 0): ?>
											<?php if (in_array('categories', $cart_pages)): ?>
												<hr class="isolate">
												<button type="button" class="btn btn-default btn-add-cart add" data-item="<?php echo $item->code; ?>">
													<i class="fa fa-shopping-cart"></i> Add to Cart
												</button>
											<?php endif; ?>

											<?php if (in_array('categories', $buynow_pages)): ?>
												<button type="button" class="btn btn-primary btn-add-cart buy" data-item="<?php echo $item->code; ?>" data-checkout="true">
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

									<?php if (in_array('categories', $show_modal)): ?>
										<a href="#modal-<?php echo $item->code; ?>" role="button" data-toggle="modal" class="btn btn-primary btn-quick-view">
											<i class="fa fa-search"></i> Quick View
										</a>
									<?php endif; ?>
								</div>
							<?php endif; ?>

							<?php if ($compare_allow && in_array('categories', $compare_pages)): ?>
								<label class="product-compare btn btn-primary">Compare&nbsp;
									<input type="checkbox" class="btn-compare" data-item="<?php echo $item->code; ?>" /></label>
							<?php endif; ?>

							<div class="clearfix"></div>
						</div>
						<div class="clearfix"></div>
					</div>
				</div>

				<?php
				$html .= ob_get_clean();
			}

			$doc = JFactory::getDocument();
			$doc->addStyleDeclaration(implode("\n", $styles));

			$html .= '</div>';
		}

		//Output
		if ($html)
		{
			$output = '<div class="sppb-addon sppb-addon-category-desc ' . $class . '">';
			$output .= ($title) ? '<' . $heading_selector . ' class="sppb-addon-title ' . $title_alignment . '">' . $title . '</' . $heading_selector . '>' : '';
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
		return array(
			JURI::base(true) . '/components/com_sppagebuilder/assets/css/sellacious/sl-categorystyle.css',
			JURI::base(true) . '/components/com_sppagebuilder/assets/css/sellacious/sl-categoryproducts.css'
		);

	}

}
