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
use Sellacious\Price\PriceHelper;

defined('_JEXEC') or die;

/** @var  SellaciousViewProduct  $this */
JHtml::_('behavior.framework');
JHtml::_('jquery.framework');
JHtml::_('ctech.select2');
JHtml::_('ctech.bootstrap');
JHtml::_('bootstrap.tooltip', '.hasTooltip');

// We may later decide not to use cart aio assets and separate the logic
JHtml::_('script', 'com_sellacious/util.cart.aio.js', false, true);
JHtml::_('script', 'com_sellacious/fe.view.product.js', true, true);

JHtml::_('stylesheet', 'com_sellacious/font-awesome.min.css', null, true);
JHtml::_('stylesheet', 'com_sellacious/fe.component.css', null, true);
JHtml::_('stylesheet', 'com_sellacious/util.rating.css', null, true);
JHtml::_('stylesheet', 'com_sellacious/fe.view.cart.aio.css', null, true);
JHtml::_('stylesheet', 'com_sellacious/fe.view.product.css', null, true);

$item              = $this->item;
$marketPlace       = $this->helper->config->get('multi_seller');
$showManufacturer  = $this->helper->config->inList('product_modal', 'manufacturer_details_display');

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

JText::script('COM_SELLACIOUS');

$path          = $this->get('_path');
$templatePaths = $path['template'];

$priceHandler = PriceHelper::getHandler($item->get('pricing_type', 'hidden'));

// Suppress compare bar for now, it should not be loaded in the modal at all. Work for later
$this->document->addStyleDeclaration('#compare-bar { display: none; } .product_quickview { padding: 20px; }');
?>
<div class="ctech-wrapper">
	<?php echo JHtml::_('form.token'); ?>
	<div class="product_quickview ctech-row">
		<div class="ctech-col-sm-6">
			<?php echo $this->loadTemplate('images'); ?>
			<div class="ctech-clearfix"></div>
		</div>
		<div id="product-info" class="ctech-col-sm-6">
			<div class="maintitlearea">
				<?php if (in_array('product', (array) $this->helper->config->get('splcategory_badge_display')) && is_array($item->get('special_listings'))): ?>
					<div class="badge-area"><?php
						foreach ($item->get('special_listings') as $spl_cat):
							$badges = $this->helper->media->getImages('splcategories.badge', (int) $spl_cat->catid, false);
							if (count($badges)): ?>
								<img src="<?php echo reset($badges) ?>" class="spl-badge" alt=""/><?php
							endif;
						endforeach; ?>
					</div>
				<?php endif; ?>
				<h1><?php echo $item->get('title');
					echo $item->get('variant_title') ? ' - <small>' . $item->get('variant_title') . '</small>' : ''; ?></h1>

				<?php echo $this->loadTemplate('wishlist') ?>

				<?php $rating_display = (array) $this->helper->config->get('product_rating_display'); ?>
				<?php if ($this->helper->config->get('product_rating') && (in_array('product_modal', $rating_display))): ?>
					<?php $stars = round($item->get('rating.rating', 0) * 2); ?>
					<div class="fa fa-star-o product-rating rating-stars star-<?php echo $stars ?>"><?php echo number_format($item->get('rating.rating', 0), 1) ?></div>
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
			<div class="ctech-row">
				<?php
				$showlisting          = $this->helper->config->get('show_allowed_listing_type');
				$allowed_listing_type = (array) $this->helper->config->get('allowed_listing_type');
				$conditionbox         = ($showlisting && (count($allowed_listing_type) != 1));
				$exchangeReturn       = ($item->get('exchange_days')) || ($item->get('return_days'));
				?>

				<div class="<?php echo ($marketPlace || $conditionbox || $exchangeReturn) ? 'ctech-col-7' : 'ctech-col-12' ?>">
					<?php
					echo $priceHandler->renderLayout('price.minimal', $item);

					echo $priceHandler->renderLayout('quantity-box.default', $item);
					?>
				</div>

				<?php if ($marketPlace || $conditionbox || $exchangeReturn): ?>
					<div class="ctech-col-5">
						<div class="product-actions">

							<?php if ($marketPlace): ?>
								<div class="seller-details">
									<div class="seller-info">
										<h4><?php echo JText::_('COM_SELLACIOUS_PRODUCT_SOLD_BY'); ?></h4>
										<p>
											<a href="<?php echo JRoute::_('index.php?option=com_sellacious&view=store&id=' . $item->get('seller_uid')); ?>">
												<?php echo $item->get('seller_store', $item->get('seller_name', $item->get('seller_company', $item->get('seller_username')))); ?></a>
											<?php if ($this->helper->config->get('show_seller_rating')) : ?>
												<?php $rating = $item->get('seller_rating.rating'); ?>
												<span class="ctech-label <?php echo ($rating < 3) ? 'label-warning' : 'label-success' ?>"><?php echo number_format($rating, 1) ?> / 5.0</span>
											<?php endif; ?></p>
									</div>
								</div>
							<?php endif; ?>

							<?php if ($showlisting): ?>
								<?php if (array_intersect(array(2, 3), $allowed_listing_type)): ?>
									<div class="conditionbox">
									<span class="ctech-label ctech-label-info"><?php echo JText::_('COM_SELLACIOUS_PRODUCT_CONDITION'); ?>
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
												'title'    => JText::sprintf('COM_SELLACIOUS_PRODUCT_EXCHANGE_EASY_RETURN_DAYS_N', (int) $item->get('return_days')),
												'backdrop' => 'static',
											);
											echo JHtml::_('bootstrap.renderModal', 'return_tnc', $options, $item->get('return_tnc'));
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

				<?php if (JPath::find($templatePaths, 'default_cart_attributes.php'))
				{
					echo $this->loadTemplate('cart_attributes');
				}
				?>

				<div class="ctech-clearfix"></div>
			</div>

			<?php if ((in_array('product_modal', (array) $this->helper->config->get('product_features_list'))) || (count($offers = $item->get('offers')))): ?>
				<hr class="isolate"/>
				<div class="ctech-row">
					<?php if (in_array('product_modal', (array) $this->helper->config->get('product_features_list'))): ?>
						<?php
						$features = array_filter((array) json_decode($item->get('variant_features'), true), 'trim');

						if (!$features):
							$features = array_filter((array) json_decode($item->get('features'), true), 'trim');
						endif;

						if ($features): ?>
						<div class="<?php echo (count($offers = $item->get('offers'))) ? 'ctech-col-7' : 'ctech-col-12' ?>">
							<ul class="product-features"><?php
								foreach ($features as $feature):
									echo '<li>' . htmlspecialchars($feature) . '</li>';
								endforeach; ?>
							</ul>
							</div><?php
						endif; ?>
					<?php endif; ?>

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
										$lang_key = 'COM_SELLACIOUS_PRODUCT_OFFER_ITEM_TEXT' . ($offer->inclusive ? '_INCLUSIVE' : '');
										echo '<div class="offerblock">' . JText::sprintf($lang_key, $offer->title) . '</div>';
									}
									?>
								</div>
							</div>
							<div class="ctech-clearfix"></div>
						</div>
					<?php endif; ?>
				</div>
			<?php endif; ?>


			<?php if ($item->get('introtext')): ?>
				<blockquote class="introtext"><?php echo nl2br($item->get('introtext')); ?></blockquote>
			<?php endif; ?>

			<hr class="isolate"/>
			<?php echo $this->loadTemplate('toolbar'); ?>

			<div class="ctech-clearfix"></div>

			<?php echo $priceHandler->renderLayout('checkout-buttons.block', $item); ?>
		</div>
	</div>
	<div class="clearfix"></div>
</div>
