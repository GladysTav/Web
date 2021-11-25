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
defined('_JEXEC') or die;

/** @var   SellaciousViewProduct $this */
JHtml::_('stylesheet', 'com_sellacious/util.bootstrap-progress.css', null, true);
JHtml::_('script', 'com_sellacious/util.readmore-text.js', true, true);

$multiVariant    = $this->helper->config->get('multi_variant', 0);
$variantSeparate = $multiVariant == 2;

$max_reviews = $this->helper->config->get('number_of_product_reviews');
$reviews     = $this->getReviews($max_reviews);
$item        = $this->item;
$variantId   = $item->get('variant_id', 0);
$reviewUrl   = 'index.php?option=com_sellacious&view=reviews&product_id=' . $item->get('id');

if ($variantSeparate && $variantId)
{
	$reviewUrl .= '&variant_id=' . (int) $variantId;
}
?>
<div class="reviews-container">
	<div class="reviewslist">
		<?php
		foreach ($reviews as $review): ?>
			<div class="single-review">
				<div class="ctech-row nomargin">
					<div class="ctech-col-6">
					<div class="product-rating rating-stars">
						<span class="star-<?php echo $review->rating * 2 ?> fa fa-star solid-icon"></span><span class="star-<?php echo 10 - ($review->rating * 2) ?> fa fa-star regular-icon"></span>
							<span class="ctech-text-primary"><?php echo number_format($review->rating, 1); ?></span>
						</div>
						<p class="pr-author">
							<?php echo $review->author_name ?>
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
					<div class="ctech-col-12">
						<h3 class="pr-title"><?php echo $review->title ?></h3>
						<p class="pr-body readmore pre-wrap"><?php echo nl2br($review->comment); ?></p>
					</div>
				</div>
			</div>
		<?php
		endforeach;
		?>
	</div>

	<?php if (count($reviews) > 0): ?>
		<div class="read-all-link-box">
			<a class="text-info" href="<?php echo $reviewUrl ?>">Read ALL</a>
		</div>
	<?php endif; ?>
</div>
