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

/** @var SellaciousViewProduct $this */
JHtml::_('behavior.framework');
JHtml::_('jquery.framework');
JHtml::_('script', 'com_sellacious/util.noframes.js', false, true);
JHtml::_('script', 'sellacious/util.anchor.js', false, true);

JHtml::_('bootstrap.tooltip', '.hasTooltip');

if ($this->helper->config->get('product_compare')):
	JHtml::_('script', 'com_sellacious/util.compare.js', false, true);
endif;

JHtml::_('script', 'com_sellacious/util.cart.aio.js', false, true);
JHtml::_('script', 'com_sellacious/fe.view.product.js', true, true);

// We may later decide not to use cart aio assets and separate the logic
JHtml::_('script', 'media/com_sellacious/js/plugin/select2-3.5/select2.js', false, false);
JHtml::_('stylesheet', 'media/com_sellacious/js/plugin/select2-3.5/select2.css', null, false);

JHtml::_('stylesheet', 'com_sellacious/font-awesome.min.css', null, true);
JHtml::_('stylesheet', 'com_sellacious/fe.component.css', null, true);
JHtml::_('stylesheet', 'com_sellacious/util.rating.css', null, true);
JHtml::_('stylesheet', 'com_sellacious/fe.view.cart.aio.css', null, true);
JHtml::_('stylesheet', 'com_sellacious/fe.view.product.css', null, true);

$item           = $this->item;
$allow_checkout = $this->helper->config->get('allow_checkout');
$cart_pages     = (array) $this->helper->config->get('product_add_to_cart_display');
$buynow_pages   = (array) $this->helper->config->get('product_buy_now_display');
$display_stock  = $this->helper->config->get('frontend_display_stock');
$c_currency     = $this->helper->currency->current('code_3');
$s_currency     = $this->helper->currency->forSeller($this->item->get('seller_uid'), 'code_3');

$marketPlace        = $this->helper->config->get('multi_seller');
$login_to_see_price = $this->helper->config->get('login_to_see_price', 0);
$prices             = $this->item->get('prices');


$me           = JFactory::getUser();
$samplemedia  = $this->getSampleMedia();
$preview_url  = $this->item->get('preview_url');
$preview_mode = $this->item->get('preview_mode');
$mfr          = array(
	'list.select' => "a.id, IF(a.title = '', u.name, a.title) AS title",
	'list.join'   => array(array('inner', '#__users u ON u.id = a.user_id')),
	'user_id'     => $item->get('manufacturer_id'),
);
$manufacturer = $this->helper->manufacturer->loadObject($mfr);

$current_url = JUri::getInstance()->toString();
$login_url   = JRoute::_('index.php?option=com_users&view=login&return=' . base64_encode($current_url), false);

if ($this->helper->config->get('mfg_link') == 'cats')
{
	$urlM = JRoute::_('index.php?option=com_sellacious&view=categories&parent_id=1&manufacturer_id=' . $item->get('manufacturer_id'));
}
elseif ($this->helper->config->get('mfg_link') == 'products')
{
	$urlM = JRoute::_('index.php?option=com_sellacious&view=products&manufacturer_id=' . $item->get('manufacturer_id'));
}
else
{
	$urlM = 'javascript:void(0)';
}

$reviewsUrl = JRoute::_('index.php?option=com_sellacious&view=reviews&product_id=' . $item->get('id'));
?>

<input type="hidden" name="<?php echo JSession::getFormToken() ?>" id="formToken" value="1"/>

<div class="product-single">
	<!-- Breadcrumbs-->
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
						if ($module->showtitle == 1)
						{ ?>
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

	<div class="row" id="main-info-pro">
		<div class="col-sm-12">
			<div class="row bg-white mr-0 ml-0 mb-4">
				<!-- Product Image-->
				<div class="col-lg-3  js-col-sm-12 image_product_box">
					<?php echo $this->loadTemplate('images'); ?>
					<div class="clearfix"></div>


					<?php if ($preview_url && $preview_mode): ?>
						<div class="preview_btn">
							<a href="<?php echo $preview_url; ?>" target="<?php echo $preview_mode; ?>" class="btn btn-primary">
								<?php echo JText::_('COM_SELLACIOUS_PRODUCT_EPRODUCT_PREVIEW_BTN'); ?>
							</a>
						</div>
					<?php endif; ?>
					<div class="clearfix"></div>
				</div>
				<div id="product-info" class="col-lg-6 col-md-12 js-col-sm-12">
					<div class="maintitlearea">

						<div class="main-product-heading">
							<h1><?php echo $item->get('title');
								echo $item->get('variant_title') ? ' - <small>' . $item->get('variant_title') . '</small>' : ''; ?></h1>
						</div>
						<!-- BEGIN: seller/admin can directly jump to backend for edit -->

						<?php $actions = array('basic.own', 'seller.own', 'pricing.own', 'shipping.own', 'related.own', 'seo.own');

						if ($this->helper->access->check('product.edit') ||
						    ($this->helper->access->checkAny($actions, 'product.edit.', $item->get('id')) && $item->get('seller_uid') == $me->id)): ?>
							<?php $editUrl = JUri::root() . JPATH_SELLACIOUS_DIR . '/index.php?option=com_sellacious&view=product&layout=edit&id=' . $item->get('id'); ?>
							<a title="<?php echo JText::_('COM_SELLACIOUS_PRODUCT_LINK_BACKEND_EDIT'); ?>" target="_blank"
							   class="btn btn-mini edit-product pull-right hasTooltip" href="<?php echo $editUrl; ?>"><i
										class="fa fa-pencil-square"></i></a>&nbsp;
						<?php endif; ?>
						<!-- END: seller/admin can directly jump to backend for edit -->


						<?php if (in_array('product', (array) $this->helper->config->get('splcategory_badge_display')) && is_array($item->get('special_listings'))): ?>
							<?php
							foreach ($item->get('special_listings') as $spl_cat):
								$badges = $this->helper->media->getImages('splcategories.badge', (int) $spl_cat->catid, false);
								if (count($badges)): ?>
									<div class="badge-area">
										<div class="spl-badge ">
											<span class="badge-img" style="background-image:url(<?php echo reset($badges); ?>)"></span>
										</div>
									</div>
								<?php
								endif;
							endforeach; ?>

						<?php endif; ?>
						<div class="tab-box-slider">

							<?php echo $this->loadTemplate('price'); ?>
						</div>

						<?php $rating_display = (array) $this->helper->config->get('product_rating_display'); ?>
						<?php if ($this->helper->config->get('product_rating') && (in_array('product', $rating_display))): ?>
							<?php $stars = round($item->get('rating.rating', 0) * 2); ?>
							<div class="product-rating rating-stars rating-stars-md  star-<?php echo $stars ?>">
								<a href="<?php echo $reviewsUrl ?>"><?php echo number_format($item->get('rating.rating', 0), 1) ?></a>
							</div>
						<?php endif; ?>


						<div class="share_wish_box">

							<?php echo $this->loadTemplate('sharer'); ?>
							<?php echo $this->loadTemplate('wishlist'); ?>

						</div>

						<div class="clearfix"></div>
					</div>

					<div class="top_det">
						<?php if ($manufacturer): ?>
							<div class="manufacturer-name">
								<span class="mnf_name"><?php echo JText::_('COM_SELLACIOUS_PRODUCT_MANUFACTURER'); ?>: </span>
								<a href="<?php echo $urlM ?>" class="hasTooltip" title="Manufacturer"><?php echo $manufacturer->title; ?></a>
							</div>
						<?php endif; ?>
					</div>

					<hr class="isolate"/>

					<?php
					$showlisting          = $this->helper->config->get('show_allowed_listing_type');
					$allowed_listing_type = (array) $this->helper->config->get('allowed_listing_type');
					$conditionbox         = ($showlisting && (count($allowed_listing_type) != 1));
					$exchangeReturn       = ($item->get('exchange_days')) || ($item->get('return_days'));
					?>

					<div class="<?php echo($marketPlace || $conditionbox || $exchangeReturn) ?> price_box">
						<?php
						if ($login_to_see_price && $me->guest):
							?>
							<div class="pricearea">
								<a href="<?php echo $login_url ?>"><?php echo JText::_('COM_SELLACIOUS_PRODUCT_PRICE_DISPLAY_LOGIN_TO_VIEW'); ?></a>
							</div>
						<?php
						else:?>
							<div class="js-hidden-sm">
								<?php echo $this->loadTemplate('price'); ?>
							</div>
						<?php endif;
						?>

						<?php if (isset($samplemedia->id) && $samplemedia->id > 0): ?>
							<div class="esamplefile">
								<a download href="<?php echo $samplemedia->path; ?>" class="btn btn-primary">
									<i class="fa fa-download"></i> <?php echo JText::_('COM_SELLACIOUS_PRODUCT_EPRODUCT_DOWNLOAD_SAMPLE'); ?></a>
							</div>
						<?php endif; ?>
					</div>

					<?php if ((count($offers = $item->get('offers')))): ?>
						<?php if (count($offers = $item->get('offers'))): ?>
							<div class="offer-info">
								<h4 class="offer-info-header"><?php echo JText::plural('COM_SELLACIOUS_PRODUCT_OFFER_COUNT_N', count($offers)) ?>
									| <?php
									echo JText::_('COM_SELLACIOUS_PRODUCT_APPLICATION_ON_CHECKOUT'); ?><span
											class="js-hidden-lg offer-list-icon fa fa-ellipsis-v pull-right"></span></h4>

								<div class="offerslist">
									<?php
									foreach ($offers as $offer)
									{
										$lang_key = 'COM_SELLACIOUS_PRODUCT_OFFER_ITEM_TEXT' . ($offer->inclusive && $offer->apply_rule_on_price_display ? '_INCLUSIVE' : '');
										echo '<div class="offerblock">' . JText::sprintf($lang_key, $offer->title) . '</div>';
									}
									?>
								</div>

								<div class="offerslist-tab">
									<div class="top-share">

										<p class="share-title"><?php echo JText::_('COM_SELLACIOUS_PRODUCT_APPLICATION_ON_CHECKOUT_TAB'); ?></p>
										<span class="pull-right share-close">
                                            <i class="fa fa-close"></i>
                                            </span>
									</div>
									<div class="cont-tab-drop offerslist-tab-cont">
										<?php foreach ($offers as $offer)
										{
											$lang_key = 'COM_SELLACIOUS_PRODUCT_OFFER_ITEM_TEXT' . ($offer->inclusive && $offer->apply_rule_on_price_display ? '_INCLUSIVE' : '');
											echo '<div class="offerblock">' . JText::sprintf($lang_key, $offer->title) . '</div>';
										}
										?>
									</div>
								</div>

							</div>


							<div class="clearfix"></div>
						<?php endif; ?>
					<?php endif; ?>


					<div class="qtyarea">
						<?php
						if ($allow_checkout && $item->get('price_display') == 0):
							if ($item->get('stock_capacity') > 0):
								$options = array(
									'title'    => JText::_('COM_SELLACIOUS_CART_TITLE'),
									'backdrop' => 'static',
								);
								echo JHtml::_('bootstrap.renderModal', 'modal-cart', $options); ?>
								<div class="quantitybox">
								<h4><?php echo JText::_('COM_SELLACIOUS_PRODUCT_BUY_QUANTITY_INPUT_LABEL'); ?></h4>


								<label> <input type="number" name="quantity" id="product-quantity" min="1"
											   data-uid="<?php echo $item->get('code') ?>" value="1"/>
								</label>
								</div><?php
							else: ?>
								<div class="label btn-primary outofstock">
								<?php echo JText::_('COM_SELLACIOUS_PRODUCT_OUT_OF_STOCK') ?></div><?php
							endif;
						endif; ?>
					</div>


					<?php if (is_array($prices) && count($prices) > 1): ?>
						<div class="tab-box-slider bulk-mb ">
							<h4><?php echo JText::_('COM_SELLACIOUS_PRODUCT_BULK_PRICING_VIEW') ?> <span
										class="tab-box-slider bulk-icon fa fa-ellipsis-v pull-right"></span></h4>

						</div>
						<!--            </div>-->


						<?php echo $this->loadTemplate('prices'); ?>
					<?php endif; ?>
					<div class="clearfix"></div>


					<!--    Add Features -->
					<?php if (isset($samplemedia->id) && $samplemedia->id > 0): ?>
						<div class="esamplefile">
							<a download href="<?php echo $samplemedia->path; ?>" class="btn btn-primary">
								<i class="fa fa-download"></i> <?php echo JText::_('COM_SELLACIOUS_PRODUCT_EPRODUCT_DOWNLOAD_SAMPLE'); ?></a>
						</div>
					<?php endif; ?>
					<?php if ((in_array('product', (array) $this->helper->config->get('product_features_list'))) || (count($offers = $item->get('offers')))): ?>
						<div class="feature bg-white o-hidden">
							<?php if (in_array('product', (array) $this->helper->config->get('product_features_list'))): ?>

								<?php
								$features = array_filter((array) json_decode($item->get('variant_features'), true), 'trim');
								if (!$features):
									$features = array_filter((array) json_decode($item->get('features'), true), 'trim');
								endif;

								if ($features): ?>
								<div class="<?php echo (count($offers = $item->get('offers'))) ? 'sell-col-xs-7' : 'sell-col-xs-12' ?>">
									<h4 class="feature-title"><?php echo JText::_('COM_SELLACIOUS_PRODUCT_FEATURE_LABEL'); ?></h4>
									<ul class="product-features"><?php
										foreach ($features as $feature):
											echo '<li>' . htmlspecialchars($feature) . '</li>';
										endforeach; ?>
									</ul>
									</div><?php
								endif; ?>
							<?php endif; ?>
						</div>
					<?php endif; ?>
					<!--   Ends Features here -->

					<?php echo $this->loadTemplate('variant_switcher'); ?>


					<hr class="isolate"/>


					<?php if ($item->get('price_display') == 0 && !($login_to_see_price && $me->guest)): ?>
						<div id="buy-now-box">
							<?php $btnClass = $item->get('stock_capacity') > 0 ? 'btn-add-cart' : ' disabled'; ?>
							<?php if ($allow_checkout && in_array('product', $cart_pages)): ?>
								<button type="button" class="btn btn-warning btn-cart <?php echo $btnClass ?>"
										data-item="<?php echo $item->get('code') ?>"><?php echo strtoupper(JText::_('COM_SELLACIOUS_PRODUCT_ADD_TO_CART')); ?>
									<?php if ($display_stock):
										echo '(' . (int) $item->get('stock_capacity') . ')';
									endif; ?>
								</button>
							<?php endif; ?>
							<?php if ($allow_checkout && in_array('product', $buynow_pages)): ?>
								<button type="button" class="btn btn-info btn-cart <?php echo $btnClass ?>"
										data-item="<?php echo $item->get('code') ?>" data-checkout="true">
									<?php echo strtoupper(JText::_('COM_SELLACIOUS_PRODUCT_BUY_NOW')); ?></button>
							<?php endif; ?>
						</div>
					<?php endif; ?>


					<div class="clearfix"></div>


				</div>
				<div id="product-side-info" class="pl-0 pr-0  col-lg-3 js-col-sm-12">
					<!--Default Seller Name-->
					<?php if ($marketPlace || $conditionbox || $exchangeReturn): ?>
						<div class="product-actions">
							<?php if ($marketPlace): ?>
								<div class="seller-details">
									<div class="seller-info">
										<h4><?php echo JText::_('COM_SELLACIOUS_PRODUCT_SOLD_BY'); ?></h4>
										<p>
											<a href="<?php echo JRoute::_('index.php?option=com_sellacious&view=store&id=' . $item->get('seller_uid')); ?>">
												<?php echo $item->get('seller_store', $item->get('seller_name', $item->get('seller_company', $item->get('seller_username')))); ?></a>
											<?php if ($this->helper->config->get('show_seller_rating')): ?>
												<?php $rating = $item->get('seller_rating.rating'); ?>
												<span class="pull-right label <?php echo ($rating < 3) ? 'js-text-warning' : 'js-text-successs' ?>"><?php
													echo number_format($rating, 1) ?> / 5.0</span>
											<?php endif; ?></p>
										<div class="gostore">
											<i class="fa fa-location-arrow"></i> <a
													href="<?php echo JRoute::_('index.php?option=com_sellacious&view=store&id=' . $item->get('seller_uid')); ?>">Go
												To Store</a>
										</div>
									</div>
								</div>
							<?php endif; ?>

							<?php if ($showlisting): ?>
								<?php if (array_intersect(array(2, 3), $allowed_listing_type)): ?>
									<div class="conditionbox">
										<span class="label label-info"><?php echo JText::_('COM_SELLACIOUS_PRODUCT_CONDITION'); ?>
											<?php
											$list_type = $item->get('listing_type');

											// What if this is a not allowed listing type value
											if ($list_type == 1):
												echo JText::plural('COM_SELLACIOUS_PRODUCT_FIELD_LISTING_TYPE_VALUE', $list_type);
											else:
												$list_cond = $item->get('item_condition');
												echo JText::plural('COM_SELLACIOUS_PRODUCT_FIELD_ITEM_CONDITION_VALUE', $list_type * 10 + (int) $list_cond);
											endif;
											?>
										</span>
									</div>
								<?php endif; ?>
							<?php endif; ?>

							<?php if ($exchangeReturn): ?>
								<div class="exchange_box">
									<?php if ($item->get('exchange_days')): ?>
										<?php if ($item->get('exchange_tnc')):
											$options = array(
												'title'    => JText::sprintf('COM_SELLACIOUS_PRODUCT_EXCHANGE_REPLACEMENT_GUARANTEE_DAYS_N', (int) $item->get('exchange_days')),
												'backdrop' => 'static',
											);
											echo JHtml::_('bootstrap.renderModal', 'exchange_tnc', $options, $item->get('exchange_tnc'));
										endif; ?>
										<div class="replacement-info">
											<i class="fa fa-refresh"></i>
											<?php echo JText::sprintf('COM_SELLACIOUS_PRODUCT_EXCHANGE_REPLACEMENT_GUARANTEE_DAYS_N', (int) $item->get('exchange_days')); ?>
											<?php if ($item->get('exchange_tnc')): ?>
												<a href="#exchange_tnc" role="button" data-toggle="modal">[<i class="fa fa-question"></i>]</a>
											<?php endif; ?>
										</div>
									<?php endif; ?>

									<?php if ($item->get('return_days')): ?>
										<?php if ($item->get('return_tnc')):
											$options = array(
												'title'    => JText::sprintf('COM_SELLACIOUS_PRODUCT_EXCHANGE_EASY_RETURN_DAYS_N', (int) $item->get('return_days')),
												'backdrop' => 'static',
											);
											echo JHtml::_('bootstrap.renderModal', 'return_tnc', $options, $item->get('return_tnc'));
										endif; ?>
										<div class="replacement-info">
											<i class="fa fa-refresh"></i>
											<?php echo JText::sprintf('COM_SELLACIOUS_PRODUCT_EXCHANGE_EASY_RETURN_DAYS_N', (int) $item->get('return_days')); ?>
											<?php if ($item->get('return_tnc')): ?>
												<a href="#return_tnc" role="button" data-toggle="modal">[<i class="fa fa-question"></i>]</a>
											<?php endif; ?>
										</div>
									<?php endif; ?>
								</div>
							<?php endif; ?>
						</div>
					<?php endif; ?>

					<!--  Shipping fee details -->
					<?php if ($this->helper->config->get('show_shipping_info_on_detail')): ?>
						<div class="text-left product-ship-cost">
							<?php
							echo JText::_('COM_SELLACIOUS_PRODUCT_SHIPPING_ICON');

							$flat_ship = $item->get('flat_shipping');
							$ship_fee  = $item->get('shipping_flat_fee');

							if ($flat_ship == 0):
								echo JText::_('COM_SELLACIOUS_PRODUCT_SHIPPING_FEE_IN_CART');
							elseif (round($ship_fee, 2) > 0):
								$fee = $this->helper->currency->display($ship_fee, $s_currency, $c_currency, true);
								echo JText::sprintf('COM_SELLACIOUS_PRODUCT_SHIPPING_FEE_FLAT', $fee);
							else:
								echo JText::_('COM_SELLACIOUS_PRODUCT_SHIPPING_FEE_FREE');
							endif; ?>
						</div>
					<?php endif; ?>


					<?php if ($item->get('introtext')): ?>
						<blockquote class="introtext"><?php echo nl2br($item->get('introtext')) ?></blockquote>
					<?php endif; ?>


					<!--Other  Seller Name-->
					<?php if ($item->get('sellers')): ?>
						<?php echo $this->loadTemplate('sellers'); ?>
					<?php endif; ?>

					<!--  Attachment area -->
					<?php if ($attachments = $this->item->get('attachments')): ?>
						<div class="attachment-area">
							<h4><?php echo JText::_('COM_SELLACIOUS_PRODUCT_HEADING_BLOCK_ATTACHMENTS'); ?></h4>
							<div class="media-attachments">
								<ul class="media-attachment-row">
									<?php foreach ($attachments as $attachment): ?>
										<?php $downloadLink = JRoute::_(JUri::base(true) . '/index.php?option=com_sellacious&task=media.download&id=' . $attachment->id); ?>
										<li><a href="<?php echo $downloadLink ?>"
											   class="attach-link-view"><?php echo $attachment->original_name ?></a></li>
									<?php endforeach; ?>
								</ul>
							</div>
							<hr class="isolate"/>
						</div>
					<?php endif; ?>


					<?php echo $this->loadTemplate('toolbar'); ?>
				</div>
			</div>
		</div>

		<!-- Main-product-bottom-->

		<?php if ($item->get('variants')): ?>
			<div class="variants-box mb-4">
					<?php echo $this->loadTemplate('variants'); ?>
			</div>
		<?php endif; ?>

		<div class="clearfix"></div>

		<div class="col-sm-12  mb-2" id="more-product">
			<!--  seller Products Module   -->
			<?php
			jimport('joomla.application.module.helper');
			$modules = JModuleHelper::getModules('Product-detail-bottom-two');
			foreach ($modules as $module):
				$renMod = JModuleHelper::renderModule($module);

				if (!empty($renMod) && ($module->module == "mod_sellacious_sellerproducts")):?>
					<div class="relatedproducts-mod <?php echo (isset($module->class_sfx)) ? $module->class_sfx : ''; ?>">
						<div>
							<?php
							if ($module->showtitle == 1)
							{ ?>
								<h6><?php echo $module->title ?></h6>
							<?php } ?>
							<div class="moreinfo-cont  innermoreinfo">
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


		<div class="main-tab-product-col mb-2 col-sm-12 col-md-9" id="other-details-1">

			<?php

			$ifspec   = $item->get('specifications');
			$ifdesc   = $item->get('description');
			$ifrating = $this->helper->config->get('product_rating');
			$ifbox    = $item->get('whats_in_box'); ?>
			<?php if ($ifspec || $ifdesc || $ifrating || $ifbox) : ?>


				<div class="moreinfo-cont main-tab-product">
					<?php
					if($ifrating == '1') {
						echo JHtml::_('bootstrap.startTabSet', 'myTab', array('active' => 'ratings'));
					} else if ($ifspec) {
						echo JHtml::_('bootstrap.startTabSet', 'myTab', array('active' => 'specifications'));
					} else if ($ifdesc) {
						echo JHtml::_('bootstrap.startTabSet', 'myTab', array('active' => 'description'));
					} else if ($ifbox) {
						echo JHtml::_('bootstrap.startTabSet', 'myTab', array('active' => 'box'));
					} ?>

					<?php if ($ifrating == '1'): ?>
						<?php echo JHtml::_('bootstrap.addTab', 'myTab', 'ratings', JText::_('COM_SELLACIOUS_TITLE_RATINGS', true)); ?>
						<div class="reviewsandratings">
							<?php echo $this->loadTemplate('ratings');
							echo $this->loadTemplate('rating');
							if ($this->helper->config->get('show_product_reviews'))
							{
								echo $this->loadTemplate('reviews');
							}
							?>
						</div>
						<?php echo JHtml::_('bootstrap.endTab'); ?>
					<?php endif; ?>


					<?php if ($ifspec): ?>
						<?php echo JHtml::_('bootstrap.addTab', 'myTab', 'specifications', JText::_('COM_SELLACIOUS_PRODUCT_SPECIFICATIONS', true)); ?>
						<div class="innerdesc">
							<?php echo $this->loadTemplate('specifications'); ?>
						</div>
						<?php echo JHtml::_('bootstrap.endTab'); ?>
					<?php endif; ?>

					<?php if ($ifdesc): ?>
						<?php echo JHtml::_('bootstrap.addTab', 'myTab', 'description', JText::_('COM_SELLACIOUS_PRODUCT_DESCRIPTION', true)); ?>
						<div class="innerdesc">
							<?php echo $item->get('description') ?>
						</div>
						<?php echo JHtml::_('bootstrap.endTab'); ?>
					<?php endif; ?>

					<?php if ($ifbox): ?>
						<?php echo JHtml::_('bootstrap.addTab', 'myTab', 'box', JText::_('COM_SELLACIOUS_PRODUCT_WHAT_IN_BOX', true)); ?>
						<div class="innerdesc">
							<?php echo $item->get('whats_in_box') ?>
						</div>
					<?php endif; ?>
					<?php echo JHtml::_('bootstrap.endTabSet'); ?>

				</div>
			<?php endif; ?>


		</div>
	</div>

	<div class="mb-2 col-md-3 col-sm-12 related-products related-products-sidebar">
		<?php
		jimport('joomla.application.module.helper');
		$modules = JModuleHelper::getModules('related-products-detailpage');
		foreach ($modules as $module):
			$renMod = JModuleHelper::renderModule($module);

			if (!empty($renMod) && ($module->module == "mod_sellacious_relatedproducts")):?>
				<div class="relatedproducts-mod <?php echo (isset($module->class_sfx)) ? $module->class_sfx : ''; ?>">
					<?php
					if ($module->showtitle == 1)
					{ ?>
						<h5 class="related-heading"><?php echo $module->title ?></h5>
					<?php } ?>
					<div class="innermoreinfo">
						<div class="relatedinner">
							<?php echo trim($renMod); ?>
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
	<!-- Accordian for mobile -->

	<div class="mb-4  panel-group productaccord w-100" role="tablist" id="accordion" aria-multiselectable="true" id="other-details">

		<?php if ($ifrating): ?>
			<div class="panel">
				<div class="panel-heading" role="tab" id="headingreview">
					<h4 class="panel-title">
						<a href="#accordreview" class="<?php echo (!$ifspec && !$ifdesc && $ifrating) ? '' : 'collapsed' ?>"
						   data-toggle="<?php echo ($ifrating) ? 'collapse' : 'collapsed' ?>" data-parent="#accordion"
						   aria-expanded="false" aria-controls="accordreview"><?php echo JText::_('COM_SELLACIOUS_TITLE_RATINGS'); ?></a>
					</h4>
				</div>
				<div class="collapse panel-collapse <?php echo (!$ifspec && !$ifdesc && $ifrating) ? 'in' : '' ?>" role="tabpanel" id="accordreview"
					 aria-labelledby="headingreview">
					<div class="panel-body">
						<div class="reviewsandratings">
							<?php echo $this->loadTemplate('ratings');
							echo $this->loadTemplate('rating');
							echo $this->loadTemplate('reviews'); ?>
						</div>
					</div>
				</div>
			</div>
		<?php endif; ?>


		<?php if ($ifspec): ?>
			<div class="panel">
				<div class="panel-heading" role="tab" id="headingspecs">
					<h4 class="panel-title">
						<a href="#accordspecs" data-toggle="<?php echo ($ifspec) ? 'collapse' : 'collapsed' ?>" data-parent="#accordion"
						   aria-expanded="true" aria-controls="accordspecs"><?php echo JText::_('COM_SELLACIOUS_PRODUCT_SPECIFICATIONS'); ?></a>
					</h4>
				</div>
				<div class="collapse panel-collapse <?php echo ($ifspec) ? 'in' : '' ?>" role="tabpanel" id="accordspecs"
					 aria-labelledby="headingspecs">
					<div class="panel-body">
						<?php echo $this->loadTemplate('specifications'); ?>
					</div>
				</div>
			</div>
		<?php endif; ?>

		<?php if ($ifdesc): ?>
			<div class="panel">
				<div class="panel-heading" role="tab" id="headingdesc">
					<h4 class="panel-title">
						<a href="#accorddesc" class="<?php echo (!$ifspec && $ifdesc) ? '' : 'collapsed' ?>"
						   data-toggle="<?php echo ($ifdesc) ? 'collapse' : 'collapsed' ?>" data-parent="#accordion"
						   aria-expanded="false" aria-controls="accorddesc"><?php echo JText::_('COM_SELLACIOUS_PRODUCT_DESCRIPTION'); ?></a>
					</h4>
				</div>
				<div class="collapse panel-collapse <?php echo (!$ifspec && $ifdesc) ? 'in' : '' ?>" role="tabpanel" id="accorddesc"
					 aria-labelledby="headingdesc">
					<div class="panel-body">
						<?php echo $item->get('description') ?>
					</div>
				</div>
			</div>
		<?php endif; ?>


		<?php if ($ifbox): ?>
			<div class="panel">
				<div class="panel-heading" role="tab" id="headingwhinbox">
					<h4 class="panel-title">
						<a href="#accordwhatinbox" class="<?php echo (!$ifspec && !$ifdesc && !$ifrating && $ifbox) ? '' : 'collapsed' ?>"
						   data-toggle="<?php echo ($ifbox) ? 'collapse' : 'collapsed' ?>" data-parent="#accordion"
						   aria-expanded="false" aria-controls="accordreview"><?php echo JText::_('COM_SELLACIOUS_PRODUCT_WHAT_IN_BOX'); ?></a>
					</h4>
				</div>
				<div class="collapse panel-collapse <?php echo (!$ifspec && !$ifdesc && $ifrating) ? 'in' : '' ?>" role="tabpanel"
					 id="accordwhatinbox" aria-labelledby="headingreview">
					<div class="panel-body">
						<?php echo $item->get('whats_in_box') ?>
					</div>
				</div>
			</div>
		<?php endif; ?>
	</div>


	<div class="mb-4 col-md-12" id="question">
		<?php
		if ($this->helper->config->get('product_questions')): ?>
			<div class="questionarea-box bg-white sell-infobox">
				<h6><?php echo JText::_('COM_SELLACIOUS_PRODUCT_QUESTION_ASK') ?></h6><?php

				echo $this->loadTemplate('question');
				?>
			</div>
		<?php endif; ?>
	</div>
	<?php
	$questions = $this->getQuestions();

	if ($questions && $this->helper->config->get('product_questions')): ?>
		<div class="col-md-12">
			<div class="bg-white questionarea-box sell-infobox">
				<h6><?php echo JText::_('COM_SELLACIOUS_TITLE_QA') ?></h6><?php
				echo $this->loadTemplate('questions'); ?>
			</div>
		</div>
	<?php endif; ?>


	<!--  Recently Viewed Products Module   -->
	<?php
	jimport('joomla.application.module.helper');
	$modules = JModuleHelper::getModules('Product-detail-bottom');
	foreach ($modules as $module):
		$renMod = JModuleHelper::renderModule($module);

		if (!empty($renMod) && ($module->module == "mod_sellacious_recentlyviewedproducts")):?>
			<div class="relatedproducts-mod mb-2 col-sm-12 <?php echo (isset($module->class_sfx)) ? $module->class_sfx : ''; ?>">
				<?php
				if ($module->showtitle == 1)
				{ ?>
					<h6><?php echo $module->title ?></h6>
				<?php } ?>
				<div class="bg-white innermoreinfo">
					<div class="relatedinner">
						<?php echo trim($renMod); ?>
					</div>
				</div>
			</div>
		<?php else: ?>
			<div class="product-bottom <?php echo (isset($module->class_sfx)) ? $module->class_sfx : ''; ?>">
				<?php echo trim($renMod); ?>
			</div>
		<?php endif; ?>

	<?php endforeach; ?>

	<!--	Mobile View toolbar buttons -->
	<div id="buy-now-box-moblie">
		<?php $btnClass = $item->get('stock_capacity') > 0 ? 'btn-add-cart' : ' disabled'; ?>
		<?php if ($allow_checkout && in_array('product', $buynow_pages)): ?>
			<button type="button" class="bottom-cart-detail-page btn btn-primary btn-cart  <?php echo $btnClass ?>"
					data-item="<?php echo $item->get('code') ?>" data-checkout="true">
				<?php echo strtoupper(JText::_('COM_SELLACIOUS_PRODUCT_BUY_NOW')); ?></button>
		<?php endif; ?>
		<?php if ($allow_checkout && in_array('product', $cart_pages)): ?>
			<button type="button" class="btn btn-cart addcart <?php echo $btnClass ?>"
					data-item="<?php echo $item->get('code') ?>"><i
						class="fa fa-shopping-cart"></i> <?php //echo strtoupper(JText::_('COM_SELLACIOUS_PRODUCT_ADD_TO_CART')); ?>
				<?php if ($display_stock):
					echo '(' . (int) $item->get('stock_capacity') . ')';
				endif; ?>
			</button>
		<?php endif; ?>
	</div>

</div>


<script>
	jQuery(function ($) {
		$(".offer-list-icon").click(function () {
			$(".offerslist-tab").slideToggle("slow");
			// $(".dropdown-menu-sm").show()
		});
		$(".share-close").click(function () {
			$(".offerslist-tab").hide("slow")
		});


	});
</script>

