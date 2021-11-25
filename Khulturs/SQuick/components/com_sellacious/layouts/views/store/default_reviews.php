<?php
/**
 * @version     2.0.0
 * @package     sellacious
 *
 * @copyright   Copyright (C) 2012-2020 Bhartiy Web Technologies. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Saurabh Sabharwal <info@bhartiy.com> - http://www.bhartiy.com
 */

// no direct access
defined('_JEXEC') or die;

JHtml::_('stylesheet', 'com_sellacious/fe.view.store.css', array('relative' => true, 'version' => S_VERSION_CORE));
JHtml::_('stylesheet', 'com_sellacious/util.rating.css', null, true);
JHtml::_('script', 'com_sellacious/util.readmore-text.js', true, true);

/** @var  SellaciousViewStore  $this */
$storeUid = $this->state->get('store.id');
$limit    = $this->helper->config->get('number_of_store_reviews');
$reviews  = $this->helper->seller->getSellerReviews($storeUid, $limit);

if (count($reviews) == 0)
{
    return;
}
?>
<div class="reviewslist">
	<div class="page-header ctech-clearfix">
		<h4 class="ctech-float-left">Store Reviews</h4>
		<div class="ctech-float-right">
			<a class="ctech-btn ctech-btn-primary btn-all-reviews ctech-btn-sm" href="<?php
			echo JRoute::_('index.php?option=com_sellacious&view=reviews&seller_uid=' . (int) $storeUid); ?>"><?php
				echo JText::_('COM_SELLACIOUS_STORE_SHOW_ALL_REVIEWS'); ?></a>
		</div>
	</div>

	<?php foreach ($reviews as $review): ?>

        <div class="ctech-row nomargin">
			<div class="ctech-col-sm-3 nopadd">
				<div class="reviewauthor">
					<div class="product-rating rating-stars">
						<span class="star-<?php echo $review->rating * 2 ?> fa fa-star solid-icon"></span><span class="star-<?php echo 10 - ($review->rating * 2) ?> fa fa-star regular-icon"></span>
						<span class="ctech-text-primary"><?php echo number_format($review->rating, 1); ?></span>
					</div>
					<h4 class="pr-author"><?php echo $review->author_name ?></h4>
					<h5 class="pr-date"><?php echo JHtml::_('date', $review->created, 'M d, Y'); ?></h5>
					<?php if ($review->buyer == 1): ?>
					<div class="buyer-badge"><?php echo JText::_('COM_SELLACIOUS_PRODUCT_CERTIFIED_BUYER'); ?></div>
					<?php endif; ?>
					</div>
				</div>
			<div class="ctech-col-sm-9 nopadd review-data">
				<div class="reviewtyped">
					<h5 class="pr-title"><?php echo $review->title; ?></h5>
					<p class="pr-body readmore pre-wrap"><?php echo $review->comment; ?></p>
				</div>
			</div>
		</div>

    <?php endforeach; ?>
</div>

