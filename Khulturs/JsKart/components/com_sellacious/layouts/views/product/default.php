<?php
/**
 * @version     2.0.0
 * @package     sellacious
 *
 * @copyright   Copyright (C) 2012-2020 Bhartiy Web Technologies. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Izhar Aazmi <info@bhartiy.com> - http://www.bhartiy.com
 */
// no direct access
use Joomla\Registry\Registry;
use Sellacious\Price\PriceHelper;

defined('_JEXEC') or die;

/** @var SellaciousViewProduct $this */
JHtml::_('behavior.framework');
JHtml::_('behavior.formvalidator');
JHtml::_('jquery.framework');
JHtml::_('ctech.select2');
JHtml::_('ctech.bootstrap');
JHtml::_('bootstrap.tooltip', '.hasTooltip');

// We may later decide not to use cart aio assets and separate the logic
JHtml::_('script', 'sellacious/util.anchor.js', false, true);
JHtml::_('script', 'com_sellacious/util.cart.aio.js', false, true);
JHtml::_('script', 'com_sellacious/fe.view.product.js', true, true);

JHtml::_('stylesheet', 'com_sellacious/font-awesome.min.css', null, true);
JHtml::_('stylesheet', 'com_sellacious/fe.component.css', null, true);
JHtml::_('stylesheet', 'com_sellacious/util.rating.css', null, true);
JHtml::_('stylesheet', 'com_sellacious/fe.view.cart.aio.css', null, true);
JHtml::_('stylesheet', 'com_sellacious/fe.view.cart.css', null, true);
JHtml::_('stylesheet', 'com_sellacious/fe.view.product.css', null, true);

$item               = $this->item;
$marketPlace        = $this->helper->config->get('multi_seller');
$login_to_see_price = $this->helper->config->get('login_to_see_price', 0);
$multiVariant       = $this->helper->config->get('multi_variant', 0);
$variantSeparate    = $multiVariant == 2;
$showFeatures       = $this->helper->config->inList('product', 'product_features_list');
$showManufacturer   = $this->helper->config->inList('product', 'manufacturer_details_display');

$me           = JFactory::getUser();
$code         = $this->state->get('product.code');
$samplemedia  = $this->getSampleMedia();
$preview_url  = $this->item->get('preview_url');
$preview_mode = $this->item->get('preview_mode');

$showlisting          = $this->helper->config->get('show_allowed_listing_type');
$allowed_listing_type = (array) $this->helper->config->get('allowed_listing_type');
$conditionbox         = ($showlisting && (count($allowed_listing_type) != 1));
$exchangeReturn       = ($item->get('exchange_days')) || ($item->get('return_days'));
$showAddToCart        = $this->helper->config->inList('product', 'product_add_to_cart_display');

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

$variantId = $item->get('variant_id');

if ($variantSeparate && $variantId)
{
	$reviewsUrl = JRoute::_('index.php?option=com_sellacious&view=reviews&product_id=' . $item->get('id') . '&variant_id=' . $variantId);
}
else
{
	$reviewsUrl = JRoute::_('index.php?option=com_sellacious&view=reviews&product_id=' . $item->get('id'));
}

$seller_reviewsUrl = JRoute::_('index.php?option=com_sellacious&view=reviews&seller_uid=' . $item->get('seller_uid'));

$path          = $this->get('_path');
$templatePaths = $path['template'];
$priceHandler  = PriceHelper::getHandler($item->get('pricing_type', 'hidden'));
?>
<div class="ctech-wrapper">
	<?php echo JHtml::_('form.token'); ?>
	<div class="product-single">
		<div class="ctech-container-fluid">
			<div class="ctech-row">
				<div class="ctech-col-md-5 ctech-p-0">
					<?php echo $this->loadTemplate('images'); ?>
					<div class="ctech-clearfix"></div>

					<div class="product_download_file">
						<?php if ($preview_url && $preview_mode): ?>
							<div class="preview_btn">
								<a href="<?php echo $preview_url; ?>" target="<?php echo $preview_mode; ?>" class="ctech-btn ctech-btn-primary">
									<?php echo JText::_('COM_SELLACIOUS_PRODUCT_EPRODUCT_PREVIEW_BTN'); ?>
								</a>
							</div>
						<?php endif; ?>
						<?php if (isset($samplemedia->id) && $samplemedia->id > 0): ?>
							<div class="esamplefile">
								<a download href="<?php echo $samplemedia->path; ?>" class="ctech-btn ctech-btn-primary">
									<i class="fa fa-download"></i> <?php echo JText::_('COM_SELLACIOUS_PRODUCT_EPRODUCT_DOWNLOAD_SAMPLE'); ?></a>
							</div>
						<?php endif; ?>
					</div>
					<div class="ctech-clearfix"></div>
				</div>
				<div id="product-info" class="ctech-col-md-7">
					<div class="maintitlearea">
						<?php if (in_array('product', (array) $this->helper->config->get('splcategory_badge_display')) && is_array($item->get('special_listings'))): ?>
							<div class="badge-area"><?php
								foreach ($item->get('special_listings') as $spl_cat):
									$splCatParams = new Registry($spl_cat->params);
									$badgeOptions = $splCatParams->get('badge.options', 'icon');
									$badgeText    = $splCatParams->get('badge.text');
									$badges       = $this->helper->media->getImages('splcategories.badge', (int) $spl_cat->catid, false);

									if ($badgeOptions == 'icon' && count($badges)): ?>
										<img src="<?php echo reset($badges) ?>" alt="Badge" class="spl-badge"/><?php
									elseif ($badgeOptions == 'text' && $badgeText):
										$color = $splCatParams->get('badge.styles.color');
										$style = $color ? 'style="color: ' . $color . ';"' : ''; ?>
										<div class="spl-badge-text hasTooltip" title="<?php echo $badgeText; ?>" <?php echo $style; ?>><?php echo $badgeText; ?></div><?php
									endif;
								endforeach; ?>
							</div>
						<?php endif; ?>
						<h1><?php echo $item->get('title');
							echo $item->get('variant_title') ? ' - <small>' . $item->get('variant_title') . '</small>' : ''; ?></h1>

						<?php if ($item->get('local_sku')): ?>
							<div class="clearfix"></div>
							<div class="product_sku ctech-text-primary"><?php echo JText::_('COM_SELLACIOUS_PRODUCT_SKU') . $item->get('local_sku'); ?></div>
						<?php endif; ?>

						<!-- BEGIN: seller/admin can directly jump to backend for edit -->
						<?php $actions = array('basic.own', 'seller.own', 'shipping.own', 'related.own', 'seo.own');

						if ($this->helper->access->check('product.edit') ||
							($this->helper->access->checkAny($actions, 'product.edit.', $item->get('id')) && $item->get('seller_uid') == $me->id)): ?>
							<?php $editUrl = JUri::root() . JPATH_SELLACIOUS_DIR . '/index.php?option=com_sellacious&view=product&layout=edit&id=' . $item->get('id'); ?>
							<a target="_blank" class="ctech-btn ctech-btn-mini ctech-btn-default edit-product ctech-float-right"
							   href="<?php echo $editUrl; ?>"><i
										class="fa fa-pen-square"></i> </a>&nbsp;
						<?php endif; ?>
						<!-- END: seller/admin can directly jump to backend for edit -->

						<?php echo $this->loadTemplate('wishlist') ?>

						<?php $rating = $item->get('rating.rating');
						$rating_display = $this->helper->config->inList('product', 'product_rating_display');

						if ($rating_display && !($rating == 0 && ($this->helper->config->get('show_zero_rating') == 0))): ?>
							<?php if ($this->helper->config->get('product_rating') && ($rating_display)): ?>
							<?php $stars = round($item->get('rating.rating', 0) * 2); ?>

							<div class="product-rating rating-stars">
								<span class="star-<?php echo $stars ?> fa fa-star solid-icon"></span><span class="star-<?php echo 10 - $stars ?> fa fa-star regular-icon"></span>
								<a href="<?php echo $reviewsUrl ?>"><?php echo number_format($item->get('rating.rating', 0), 1) ?></a>
							</div>
							<div class="product-rating rating-stars condition-box">

								<?php if ($marketPlace || $conditionbox || $exchangeReturn): ?>
									<?php if ($showlisting && $item->get('type') != 'electronic' && array_intersect(array(2, 3), $allowed_listing_type)): ?>
										<span class="conditionbox ctech-alert ctech-alert-success">
											<span class="ctech-label ctech-label-info"><?php echo JText::_('COM_SELLACIOUS_PRODUCT_CONDITION'); ?></span>
												<span class="ctech-text-dark condition-label">
													<?php
														$list_type = $item->get('listing_type');

														// What if this is a not allowed listing type value?
														if ($list_type == 1)
														{
															echo JText::plural('COM_SELLACIOUS_PRODUCT_FIELD_LISTING_TYPE_VALUE', $list_type);
														}
														else
														{
															echo JText::plural('COM_SELLACIOUS_PRODUCT_FIELD_ITEM_CONDITION_VALUE', ($list_type * 10) + (int) $item->get('item_condition'));
														}
													?>
												</span>
										</span>
									<?php endif;
								endif; ?>
							</div>
							<?php endif; ?>
						<?php endif; ?>
					</div>

					<?php
					$mfr          = array(
						'list.select' => "a.id, IF(a.title = '', u.name, a.title) AS title",
						'list.join'   => array(array('inner', '#__users u ON u.id = a.user_id')),
						'user_id'     => $item->get('manufacturer_id'),
					);
					$manufacturer = $this->helper->manufacturer->loadObject($mfr);
					?>

					<?php if (isset($manufacturer->id) && $showManufacturer): ?>
						<div class="ctech-clearfix"></div>
						<div class="manufacturer-name">
							<span class="manufacturer"><?php echo JText::_('COM_SELLACIOUS_PRODUCT_MANUFACTURER'); ?>:</span>
							<a href="<?php echo $urlM ?>" class="hasTooltip mnf-name" title="Manufacturer"><?php echo $manufacturer->title; ?></a>
						</div>
					<?php endif; ?>
					<div class="ctech-clearfix"></div>
					<hr class="isolate"/>
					<div class="ctech-clearfix"></div>
					<div class="ctech-row">
						<div class="<?php echo ($marketPlace || $conditionbox || $exchangeReturn) ? 'ctech-col-md-7' : 'ctech-col-12' ?>">
							<div class="pricearea">
								<?php
								echo $priceHandler->renderLayout('price.default', $item);
								echo $priceHandler->renderLayout('price.alternate', $item);
								?>

								<div class="ctech-clearfix"></div>

								<?php if ($this->helper->config->get('show_shipping_info_on_detail') && $item->get('type') !== 'electronic'): ?>
									<div class="ctech-text-left product-ship-cost">
										<?php
										echo JText::_('COM_SELLACIOUS_PRODUCT_SHIPPING_ICON');

										$flat_ship = $item->get('flat_shipping');
										$ship_fee  = $item->get('shipping_flat_fee');

										if ($flat_ship == 0)
										{
											echo JText::_('COM_SELLACIOUS_PRODUCT_SHIPPING_FEE_IN_CART');
										}
										elseif (round($ship_fee, 2) > 0)
										{
											$s_currency = $this->helper->currency->forSeller($item->get('seller_uid'), 'code_3');
											$c_currency = $this->helper->currency->current('code_3');
											$fee        = $this->helper->currency->display($ship_fee, $s_currency, $c_currency, true);

											echo JText::sprintf('COM_SELLACIOUS_PRODUCT_SHIPPING_FEE_FLAT', $fee);
										}
										else
										{
											echo JText::_('COM_SELLACIOUS_PRODUCT_SHIPPING_FEE_FREE');
										}
										?>
									</div>
								<?php endif; ?>
							</div>

							<?php echo $priceHandler->renderLayout('quantity-box.default', $item); ?>
							<?php if ($form = $this->helper->cart->getCheckoutForm(false, 'product', $code)): ?>
								<form action="<?php echo JUri::getInstance()->toString(array('path', 'query', 'fragment')) ?>" method="post" class="checkout_form">
									<?php echo JLayoutHelper::render('sellacious.product.forms.checkout_questions', array('form' => $form)); ?>
								</form>
							<?php endif; ?>

							<div class="product_download_file_mobile">
								<?php if ($preview_url && $preview_mode): ?>
									<div class="preview_btn">
										<a href="<?php echo $preview_url; ?>" target="<?php echo $preview_mode; ?>" class="ctech-btn ctech-btn-primary">
											<?php echo JText::_('COM_SELLACIOUS_PRODUCT_EPRODUCT_PREVIEW_BTN'); ?>
										</a>
									</div>
								<?php endif; ?>

								<?php if (isset($samplemedia->id) && $samplemedia->id > 0): ?>
									<div class="esamplefile">
										<a download href="<?php echo $samplemedia->path; ?>" class="ctech-btn ctech-btn-primary">
											<i class="fa fa-download"></i> <?php echo JText::_('COM_SELLACIOUS_PRODUCT_EPRODUCT_DOWNLOAD_SAMPLE'); ?></a>
									</div>
								<?php endif; ?>
							</div>

							<?php if ($showFeatures): ?>
								<?php
								$features = array_filter((array) json_decode($item->get('variant_features'), true), 'trim');

								if (!$features):
									$features = array_filter((array) json_decode($item->get('features'), true), 'trim');
								endif;

								if ($features): ?>
								<div class="<?php echo (count($offers = $item->get('offers'))) ? 'ctech-col-12' : 'ctech-col-12' ?>">
									<ul class="product-features"><?php
										foreach ($features as $feature):
											echo '<li>' . htmlspecialchars($feature) . '</li>';
										endforeach; ?>
									</ul>
									</div><?php
								endif; ?>
							<?php endif; ?>

							<?php if ($item->get('introtext')): ?>
								<blockquote class="introtext"><?php echo nl2br($item->get('introtext')); ?></blockquote>
							<?php endif; ?>
						</div>

						<?php if ($marketPlace || $conditionbox || $exchangeReturn): ?>
							<div class="ctech-col-md-5">
								<div class="product-actions">
									<?php if ($marketPlace): ?>
										<div class="seller-details">
											<div class="seller-info">
												<h4><?php echo JText::_('COM_SELLACIOUS_PRODUCT_SOLD_BY'); ?></h4>
												<span class="seller-name">
													<?php echo $item->get('seller_store', $item->get('seller_name', $item->get('seller_company', $item->get('seller_username')))); ?>
												</span>
												<?php
												$show_seller_rating = $this->helper->config->get('show_seller_rating');
												$rating             = $item->get('seller_rating.rating'); ?>

												<?php
												if (!($show_seller_rating == 0 || ($this->helper->config->get('show_zero_rating') == 0) && $rating == 0)):
														$stars = round($item->get('seller_rating.rating', 0) * 2); ?>
														<div class="rating-stars">
															<span class=" star-<?php echo $stars ?> fa fa-star solid-icon"></span><span class=" star-<?php echo 10- $stars ?> fa fa-star regular-icon"></span>
															<a href="<?php echo $seller_reviewsUrl?>"></a>
														</div>
													<?php endif; ?>

												<div class="store-link">
													<i class="fas fa-location-arrow"></i> <a class="ctech-text-info" href="<?php echo JRoute::_('index.php?option=com_sellacious&view=store&id=' . $item->get('seller_uid')); ?>"><?php echo JText::_('COM_SELLACIOUS_PRODUCT_GO_TO_STORE'); ?></a>
												</div>
												<?php
												$message_enabled         = $this->helper->config->get('enable_fe_messages', 0);
												$store_chat_button_pages = (array)$this->helper->config->get('store_chat_button_display');
												$storeId                 = $item->get('seller_uid');

												if ($message_enabled && in_array('product', $store_chat_button_pages) && $storeId != $me->id):
												?>
													<div class="chat-link">
														<i class="fa fa-comment"></i> <a class="ctech-text-info"
																								href="<?php echo JRoute::_('index.php?option=com_sellacious&view=messages&recipient=' . $storeId . '&context=product&ref=' . $code); ?>"><?php echo JText::_('COM_SELLACIOUS_PRODUCT_CHAT_WITH_STORE'); ?></a>
													</div>
												<?php endif; ?>
												<?php if ($item->get('sellers')): ?>
													<?php echo $this->loadTemplate('sellers'); ?>
												<?php endif; ?>
											</div>
										</div>
									<?php endif; ?>

									<?php if ($exchangeReturn): ?>
										<div class="exchange_box">
											<?php if ($item->get('exchange_days')): ?>
												<?php if ($item->get('exchange_tnc')):
													$options = array(
														'backdrop' => 'static',
													);
													echo JHtml::_('ctechBootstrap.modal', 'exchange_tnc', JText::sprintf('COM_SELLACIOUS_PRODUCT_EXCHANGE_REPLACEMENT_GUARANTEE_DAYS_N', (int) $item->get('exchange_days')), $item->get('exchange_tnc'), '', $options);
												endif; ?>
												<div class="replacement-info">
													<i class="fa fa-sync"></i>
													<?php echo JText::sprintf('COM_SELLACIOUS_PRODUCT_EXCHANGE_REPLACEMENT_GUARANTEE_DAYS_N', (int) $item->get('exchange_days')); ?>
													<?php if ($item->get('exchange_tnc')): ?>
														<a href="#exchange_tnc" role="button" data-toggle="ctech-modal">[<i class="fa fa-question"></i>]</a>
													<?php endif; ?>
												</div>
											<?php endif; ?>

											<?php if ($item->get('return_days')): ?>
												<?php if ($item->get('return_tnc')):
													$options = array(
														'backdrop' => 'static',
													);
													echo JHtml::_('ctechBootstrap.modal', 'return_tnc', JText::sprintf('COM_SELLACIOUS_PRODUCT_EXCHANGE_EASY_RETURN_DAYS_N', (int) $item->get('return_days')), $item->get('return_tnc'), '', $options);
												endif; ?>
												<div class="replacement-info">
													<i class="fa fa-sync"></i>
													<?php echo JText::sprintf('COM_SELLACIOUS_PRODUCT_EXCHANGE_EASY_RETURN_DAYS_N', (int) $item->get('return_days')); ?>
													<?php if ($item->get('return_tnc')): ?>
														<a href="#return_tnc" role="button" data-toggle="ctech-modal">[<i class="fa fa-question"></i>]</a>
													<?php endif; ?>
												</div>
											<?php endif; ?>
										</div>
									<?php endif; ?>
								</div>
							</div>
						<?php endif; ?>

						<?php if ($item->get('stock_capacity') > 0 && JPath::find($templatePaths, 'default_cart_attributes.php')):
							echo $this->loadTemplate('cart_attributes');
						endif; ?>

						<div class="ctech-clearfix"></div>
					</div>

					<?php if ($showFeatures || (count($offers = $item->get('offers'))) || (count($taxes = $item->get('taxes')))): ?>
						<div class="ctech-row discount_tax_btn">
							<?php if (count($offers = $item->get('offers'))): ?>
								<div class="<?php echo $features ? 'ctech-col-5' : 'ctech-col-12' ?>">
									<div class="offer-info">
										<h4 class="offer-info-header"><?php echo JText::plural('COM_SELLACIOUS_PRODUCT_OFFER_COUNT_N', count($offers)) ?>
											| <?php
											echo JText::_('COM_SELLACIOUS_PRODUCT_APPLICATION_ON_CHECKOUT'); ?></h4>
										<div class="offerslist">
											<?php
											foreach ($offers as $offer)
											{
												$lang_key = 'COM_SELLACIOUS_PRODUCT_OFFER_ITEM_TEXT' . ($offer->inclusive && $offer->apply_rule_on_price_display ? '_INCLUSIVE' : '');
												echo '<div class="offerblock">' . JText::sprintf($lang_key, $offer->title) . '</div>';
											}
											?>
										</div>
									</div>
									<div class="ctech-clearfix"></div>
								</div>
							<?php endif; ?>

							<?php if (count($taxes = $item->get('taxes'))): ?>
								<div class="ctech-col-12">
									<hr class="isolate"/>
									<div class="tax-info">
										<h4 class="tax-info-header"><?php echo JText::plural('COM_SELLACIOUS_PRODUCT_TAX_COUNT_N', count($taxes)) ?>
											| <?php
											echo JText::_('COM_SELLACIOUS_PRODUCT_APPLICATION_ON_CHECKOUT'); ?></h4>
										<div class="taxeslist">
											<?php
											foreach ($taxes as $tax)
											{
												$lang_key = 'COM_SELLACIOUS_PRODUCT_TAX_ITEM_TEXT' . ($tax->inclusive && $tax->apply_rule_on_price_display ? '_INCLUSIVE' : '');
												echo '<div class="taxblock">' . JText::sprintf($lang_key, $tax->title) . '</div>';
											}
											?>
										</div>
									</div>
									<div class="ctech-clearfix"></div>
								</div>
							<?php endif; ?>
						</div>
					<?php endif; ?>

					<?php if ($attachments = $this->item->get('attachments')): ?>
						<hr class="isolate"/>
						<div class="attachment-area">
							<h4><?php echo JText::_('COM_SELLACIOUS_PRODUCT_HEADING_BLOCK_ATTACHMENTS'); ?></h4>
							<div class="media-attachments">
								<ul class="media-attachment-row">
									<?php foreach ($attachments as $attachment): ?>
										<?php $downloadLink = JRoute::_(JUri::base(true) . '/index.php?option=com_sellacious&task=media.download&id=' . $attachment->id); ?>
										<li><a href="<?php echo $downloadLink ?>" class="attach-link-view"><?php echo $attachment->original_name ?></a>
										</li>
									<?php endforeach; ?>
								</ul>
							</div>
						</div>
					<?php endif; ?>

					<?php
					echo $this->loadTemplate('variant_switcher');
					?>
					<div class="checkout-buttons">
						<?php echo $priceHandler->renderLayout('checkout-buttons.default', $item); ?>
					</div>

					<hr class="isolate"/>
					<?php echo $this->loadTemplate('toolbar'); ?>

					<div class="ctech-clearfix"></div>
				</div>
				<div class="ctech-clearfix"></div>
			</div>
		</div>

		<?php if ($item->get('variants')): ?>
			<?php echo $this->loadTemplate('variants', $item); ?>
		<?php endif; ?>

		<div class="ctech-clearfix"></div>

		<?php if ($item->get('description')): ?>
			<div class="description-box sell-infobox">
				<h5><?php echo JText::_('COM_SELLACIOUS_PRODUCT_DESCRIPTION'); ?></h5>
				<div class="desc-text sell-info-inner">
					<?php echo $item->get('description') ?>
				</div>
			</div>
		<?php endif; ?>

		<?php echo $this->loadTemplate('physical'); ?>

		<?php
		$whatsInBox = $this->helper->config->get('show_whats_in_box', 1);
		$in_box    = $item->get('whats_in_box');
		$pkg_items = $item->get('package_items');

		if ($whatsInBox && ($in_box || $pkg_items)): ?>
			<div class="sell-infobox package-box">
			<h5><?php echo JText::_('COM_SELLACIOUS_PRODUCT_WHAT_IN_BOX'); ?></h5><?php
			if ($in_box): ?>
				<div class="package-inner">
				<?php echo $in_box ?>
				</div><?php
			endif;

			if ($pkg_items):
				echo $this->loadTemplate('packages', $pkg_items);
			endif; ?>
			</div><?php
		endif;

		if ($item->get('specifications')):
			echo $this->loadTemplate('specifications');
		endif;

		if ($this->helper->config->get('product_rating')): ?>
			<div class="rating-box sell-infobox">
				<h5><?php echo JText::_('COM_SELLACIOUS_TITLE_RATINGS') ?></h5>
				<?php
				echo $this->loadTemplate('ratings');
				echo $this->loadTemplate('rating');

				if ($this->helper->config->get('show_product_reviews') == '1')
				{
					echo $this->loadTemplate('reviews');
				}
				?>
			</div>
		<?php endif;
		$form = $this->getQuestionForm();

		// Only proceed if it is a valid JForm
		if (!$form)
		{
			return;
		}

		if ($this->helper->config->get('product_questions')): ?>
			<div class="questionarea-box sell-infobox">
				<h5><?php echo JText::_('COM_SELLACIOUS_PRODUCT_QUESTION_ASK') ?></h5><?php
				echo $this->loadTemplate('question');
				echo $this->loadTemplate('questions'); ?>
			</div>
		<?php endif; ?>
	</div>
</div>
