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

use Joomla\Utilities\ArrayHelper;

/** @var   SellaciousViewProduct $this */
JHtml::_('stylesheet', 'com_sellacious/util.bootstrap-progress.css', null, true);

$stats   = $this->getReviewStats();
$reviews = $this->getReviews();
?>
<div class="ratins-stats">
	<div class="sell-row">
		<div class="sell-col-xs-12 sell-col-sm-2  nopadd">
			<div class="ratingaverage">
				<div class="star-lg"><?php echo number_format($this->item->get('rating.rating', 0), 1); ?></div>
				<h4 class="avg-rating"><?php echo JText::plural('COM_SELLACIOUS_PRODUCT_RATING_AVERAGE_BASED_ON', $this->item->get('rating.count', 0)); ?></h4>
			</div>
		</div>
		<div class="sell-col-xs-12 sell-col-sm-6 nopadd">
			<table class="rating-statistics">
				<tbody>
				<?php for ($i = 1; $i <= 5; $i ++): ?>
					<?php
					$stat    = ArrayHelper::getValue($stats, $i, null);
					$count   = isset($stat->count) ? $stat->count : 0;
					$percent = isset($stat) ? ($stat->count / $stat->total * 100) : 0;
					?>
					<tr>
						<td class="nowrap" style="width:90px;">
							<div class="rating-stars rating-stars-md star-<?php echo $i * 2 ?>">
								&nbsp;<?php echo number_format($i, 1); ?></div>
						</td>
						<td class="nowrap rating-progress">
							<div class="progress progress-sm">
								<div class="progress-bar" role="progressbar" style="width: <?php echo $percent ?>%"></div>
								<div class="progress"></div>
							</div>
						</td>
						<td class="nowrap"
							style="width:60px;"><?php echo $count; ?> <?php echo JText::_('COM_SELLACIOUS_PRODUCT_HEADING_RATINGS'); ?> </td>
					</tr>
				<?php endfor; ?>
				</tbody>
			</table>
		</div>

		<div class="sell-col-md-4 review-latest">
			<?php if ($reviews): ?>
				<div class="reviewslist latest-rev">
					<?php $review = end($reviews); ?>
					<div class="sell-row nomargin">
						<div class="sell-col-xs-12 nopadd">
							<div class="title-review pt-0 ">
								<h5 class="rev-latest">Latest Reviews</h5>

							</div>
							<div class="reviewauthor">
								<h5 class="pr-date text-right"><?php echo JHtml::_('date', $review->created, 'M d, Y'); ?></h5>

								<div class="rating-stars rating-stars-md star-<?php echo $review->rating * 2 ?>">
									<span class="starcounts"><?php echo number_format($review->rating, 1); ?></span>
								</div>

								<h4 class="pr-author text-left">By&nbsp;<?php echo $review->author_name ?></h4>
								<?php if ($review->buyer == 1): ?>
									<div class="buyer-badge"><?php echo JText::_('COM_SELLACIOUS_PRODUCT_CERTIFIED_BUYER'); ?></div>
								<?php endif; ?>
							</div>
						</div>
						<div class="sell-col-xs-12 nopadd">
							<div class="reviewtyped">
								<h3 class="pr-title"><?php echo $review->title ?></h3>
								<p class="pr-body"><?php echo $review->comment ?></p>
							</div>
						</div>
					</div>
				</div>
			<?php else: ?>
				<div class="row latest-no-reviews">
					<div class="col-md-3 text-right">
						<div class="star-lg"></div>
					</div>
					<div class="col-md-9">
						<p class="no-reviews">Be the <strong>first</strong> to <br /> <h4>Review!</h4></p>
					</div>
				</div>
			<?php endif; ?>
		</div>
	</div>
</div>
<div class="clearfix"></div>
