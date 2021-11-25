<?php
/**
 * @version     2.0.0
 * @package     sellacious
 *
 * @copyright   Copyright (C) 2012-2020 Bhartiy Web Technologies. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Aditya Chakraborty <info@bhartiy.com> - http://www.bhartiy.com
 */
// no direct access
defined('_JEXEC') or die;

use Joomla\Registry\Registry;
use Joomla\Utilities\ArrayHelper;
use Sellacious\Product;

JHtml::_('stylesheet', 'com_sellacious/util.bootstrap-progress.css', null, true);

/** @var SellaciousViewOrders $this */
$app = JFactory::getApplication();

$multiVariant    = $this->helper->config->get('multi_variant', 0);
$variantSeparate = $multiVariant == 2;

$listOrder = $this->escape($this->state->get('list.ordering'));
$listDirn  = $this->escape($this->state->get('list.direction'));

// Load the behaviors.
JHtml::_('jquery.framework');
JHtml::_('bootstrap.tooltip');

JHtml::_('ctech.bootstrap');

JHtml::_('script', 'com_sellacious/fe.view.orders.tile.js', true, true);
JHtml::_('script', 'com_sellacious/util.readmore-text.js', true, true);
JHtml::_('stylesheet', 'com_sellacious/fe.component.css', null, true);
JHtml::_('stylesheet', 'com_sellacious/util.rating.css', null, true);
JHtml::_('stylesheet', 'com_sellacious/fe.view.reviews.css', null, true);
JHtml::_('stylesheet', 'com_sellacious/font-awesome.min.css', null, true);

$reviews     = $this->items;
$link_detail = $this->helper->config->get('product_detail_page');
$rateable    = (array) $this->helper->config->get('allow_ratings_for');
$stats       = $this->getReviewStats();
?>
<div class="ctech-wrapper">
	<?php
	if (!empty($this->seller->id)):
		$seller = new Joomla\Registry\Registry($this->seller);
		$logo = $this->helper->media->getImage('sellers.logo', $seller->get('id'));
		$storeLink = JRoute::_('index.php?option=com_sellacious&view=store&id=' . $seller->get('user_id'));
		?>
		<div id="seller-info">
			<div class="sellerdata">
				<h2>
					<a href="<?php echo $storeLink; ?>">
						<?php echo $seller->get('store_name') ?: $seller->get('title') ?>
					</a>
				</h2>
				<?php if ($this->helper->config->get('show_store_product_count') == '1' && $seller->get('product_count')): ?>
					<div class="product-count">
						<span class="product-count-label"><?php echo JText::_('COM_SELLACIOUS_SELLER_PRODUCT_COUNT_N') ?></span><?php echo JText::_($seller->get('product_count')); ?>
					</div>
				<?php endif; ?>
				<?php if ($seller->get('store_address')): ?>
					<div class="store-address"><?php echo nl2br($seller->get('store_address')) ?></div>
				<?php endif; ?>
				<?php if (in_array('seller', $rateable)): ?>
					<?php $stars = round($seller->get('rating.rating', 0) * 2); ?>
					<div class="product-rating rating-stars">
						<span class="star-<?php echo $stars ?> fa fa-star solid-icon"></span><span class="star-<?php echo 10 - $stars ?> fa fa-star regular-icon"></span>
						<span class="ctech-text-primary"><?php echo number_format($seller->get('rating.rating', 0), 1) ?>
							<?php echo '-' . ' ' . JText::plural('COM_SELLACIOUS_RATINGS_COUNT_N', $seller->get('rating.count')); ?></span>
					</div>
				<?php endif; ?>
			</div>
			<div class="seller-logoarea">
				<img class="seller-logo" src="<?php echo $logo ?>"
					 alt="<?php echo htmlspecialchars($seller->get('title'), ENT_COMPAT, 'UTF-8'); ?>">
			</div>
			<div class="clearfix"></div>
		</div>
		<?php if (!empty($this->seller_reviews)): ?>
		<div class="rating-box sell-infobox">
			<div class="reviewslist">
				<?php foreach ($this->seller_reviews as $sreview): ?>
					<div class="ctech-row nomargin">
						<div class="ctech-col-sm-3 nopadd">
							<div class="reviewauthor">
								<div class="product-rating rating-stars">
									<span class="star-<?php echo $sreview->rating * 2 ?> fa fa-star solid-icon"></span><span class="star-<?php echo 10 - ($sreview->rating * 2) ?> fa fa-star regular-icon"></span>
								<span class="ctech-text-primary"><?php echo number_format($sreview->rating, 1); ?></span>
								</div>
								<h4 class="pr-author"><?php echo $sreview->author_name ?></h4>
								<h5 class="pr-date"><?php echo JHtml::_('date', $sreview->created, 'M d, Y'); ?></h5>
								<?php if ($sreview->buyer == 1): ?>
									<div class="buyer-badge"><?php echo JText::_('COM_SELLACIOUS_PRODUCT_CERTIFIED_BUYER'); ?></div>
								<?php endif; ?>
							</div>
						</div>
						<div class="ctech-col-sm-9 nopadd">
							<div class="reviewtyped">
								<h3 class="pr-title"><?php echo $sreview->title ?></h3>
								<p class="pr-body readmore pre-wrap"><?php echo nl2br($sreview->comment); ?></p>
							</div>
						</div>
					</div>
				<?php endforeach; ?>
			</div>
		</div>
		<h3><?php echo JText::_('COM_SELLACIOUS_REVIEWS_PRODUCT'); ?></h3>
	<?php endif; ?>
	<?php endif; ?>

	<?php if ($this->state->get('filter.product_id', 0)):
		$productId = $this->state->get('filter.product_id', 0);
		$variantId = $this->state->get('filter.variant_id', 0);

		if ($variantSeparate)
		{
			$product       = new Product($productId, $variantId);
			$productRating = new Registry($this->helper->rating->getProductRating($productId, $variantId));
			$productImage  = $this->helper->product->getImage($productId, $variantId);
			$productTitle  = $product->get('title') . ($variantId ? ' - ' . $product->get('variant_title') : '');
		}
		else
		{
			$product       = new Product($productId);
			$productRating = new Registry($this->helper->rating->getProductRating($productId));
			$productImage  = $this->helper->product->getImage($productId);
			$productTitle  = $product->get('title');
		}
		?>
		<div id="product-info">
			<div class="productdata">
				<a href="<?php echo $link_detail ? 'index.php?option=com_sellacious&view=product&p=' . $product->getCode() : 'javascript:void(0);' ?>">
					<h2><?php echo $productTitle ?></h2>
				</a>
				<?php if (in_array('product', $rateable)): ?>
					<?php $stars = round($productRating->get('rating', 0) * 2); ?>
					<div class="product-rating rating-stars">
					<span class="star-<?php echo $stars ?> fa fa-star solid-icon"></span><span class="star-<?php echo 10 - $stars ?> fa fa-star regular-icon"></span>
						<span class="ctech-text-primary"><?php echo number_format($productRating->get('rating', 0), 1) ?></span>
						<?php echo '<span> – </span>';
						echo JText::plural('COM_SELLACIOUS_RATINGS_COUNT_N', $productRating->get('count')); ?>
					</div>
				<?php endif; ?>
			</div>
			<div class="product-logoarea">
				<img class="product-logo" src="<?php echo $productImage ?>"
					 alt="<?php echo htmlspecialchars($product->get('title'), ENT_COMPAT, 'UTF-8'); ?>">
			</div>
			<?php if ($this->helper->config->get('show_stats_reviews') == '1'): ?>
				<table class="rating-statistics">
					<tbody>
					<?php for ($i = 1; $i <= 5; $i++): ?>
						<?php
						$stat    = ArrayHelper::getValue($stats, $i, null);
						$count   = isset($stat->count) ? $stat->count : 0;
						$percent = isset($stat) ? ($stat->count / $stat->total * 100) : 0;
						?>
						<tr>
							<td class="nowrap" style="width:90px;">
							<div class="product-rating rating-stars">
								<span class="star-<?php echo $i * 2 ?> fa fa-star solid-icon"></span><span class="star-<?php echo 10- ($i * 2) ?> fa fa-star regular-icon"></span>
								&nbsp;<span class="ctech-text-primary"><?php echo number_format($i, 1); ?></span></div>
							</td>
							<td class="nowrap rating-progress">
								<div class="progress progress-sm">
									<div class="progress">
										<div class="progress-bar" role="progressbar" style="width: <?php echo $percent ?>%"></div>
									</div>
								</div>
							</td>
							<td class="nowrap"
								style="width:60px;"><?php echo $count; ?><?php echo JText::_('COM_SELLACIOUS_PRODUCT_HEADING_RATINGS'); ?> </td>
						</tr>
					<?php endfor; ?>
					</tbody>
				</table>
			<?php endif; ?>
			<div class="clearfix"></div>
		</div>
	<?php endif; ?>

	<form action="<?php echo JUri::getInstance()->toString(array('path', 'query', 'fragment')) ?>"
		  method="post" name="adminForm" id="adminForm">
		<?php if (!empty($reviews)): ?>
			<div class="reviews-container">
				<div class="reviewslist">
					<?php
						foreach ($reviews as $review):

						$code = $this->helper->product->getCode($review->product_id, $review->variant_id, $review->seller_uid);
						$url    = JRoute::_('index.php?option=com_sellacious&view=product&p=' . $code);
						$link_detail = $this->helper->config->get('product_detail_page');
						?>
						<div class="single-review">
							<div class="ctech-row nomargin">
								<div class="ctech-col-6">
									<div class="product-rating rating-stars">
										<span class="star-<?php echo $review->rating * 2 ?> fa fa-star solid-icon"></span><span class="star-<?php echo 10 - ($review->rating * 2) ?> fa fa-star regular-icon"></span>
										<span class="ctech-text-primary"><?php echo number_format($review->rating, 1); ?></span>
									</div>
									<p class="pr-author">
										<?php echo $review->author_name; ?>
										<?php
										if (!empty($this->seller->id)):
										    echo " - ". JText::sprintf('COM_SELLACIOUS_PRODUCT_AUTHOR_FOR')
										?>
											<span class="product-title">
												<a href="<?php echo $link_detail ? $url : 'javascript:void(0);' ?>">
												<?php
												echo $review->product->get('title') ?: ''; ?></a>
											</span>

										<?php endif; ?>

									</p>
								</div>
								<div class="ctech-col-6 ctech-text-right">
									<p class="pr-date"><?php echo JHtml::_('date', $review->created, 'M d, Y'); ?></p>
									<?php if ($review->buyer == 1): ?>
										<div class="buyer-badge"><?php echo JText::_('COM_SELLACIOUS_PRODUCT_CERTIFIED_BUYER'); ?></div>
									<?php endif; ?>
								</div>
							</div>
							<hr class="isolate"/>
							<div class="ctech-row review">
								<div class="ctech-col-12 nopadd">
									<h3 class="pr-title"><?php echo $review->title ?></h3>
									<p class="pr-body readmore pre-wrap"><?php echo nl2br($review->comment); ?></p>
								</div>
							</div>
						</div>
                        <?php
                        endforeach;
                        ?>
				</div>
			</div>

		<?php endif; ?>
		<table class="w100p">
			<tr>
				<td class="text-center">
					<div class="pagination"><?php echo $this->pagination->getPagesLinks(); ?></div>
				</td>
			</tr>
			<tr>
				<td class="text-center">
					<?php echo $this->pagination->getResultsCounter(); ?>
				</td>
			</tr>
		</table>

		<input type="hidden" name="task" value=""/>
		<input type="hidden" name="filter_order" value="<?php echo $listOrder; ?>"/>
		<input type="hidden" name="filter_order_Dir" value="<?php echo $listDirn; ?>"/>

		<?php
		if ($tmpl = $app->input->get('tmpl'))
		{
			?><input type="hidden" name="tmpl" value="<?php echo $tmpl ?>"/><?php
		}

		if ($layout = $app->input->get('layout'))
		{
			?><input type="hidden" name="layout" value="<?php echo $layout ?>"/><?php
		}

		echo JHtml::_('form.token');
		?>
	</form>
</div>
