<?php
/**
 * @version     1.7.4
 * @package     sellacious
 *
 * @copyright   Copyright (C) 2012-2019 Bhartiy Web Technologies. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Saurabh Sabharwal <info@bhartiy.com> - http://www.bhartiy.com
 */

// no direct access
defined('_JEXEC') or die;

use Joomla\Registry\Registry;

/** @var  SellaciousViewStore  $this */
/** @var  stdClass  $tplData */
$seller = $tplData;

$me         = JFactory::getUser();
$max_reviews = $this->helper->config->get('number_of_store_reviews');

?>
<div class="reviewslist">
	<h3 class="page-header">Store Reviews</h3>
	<?php
	$reviews = array_slice($seller->reviews, 0, $max_reviews);
	foreach ($reviews as $review):
	?>
		<div class="sell-row nomargin">
			<div class="sell-col-xs-3 nopadd">
				<div class="reviewauthor">
					<div class="rating-stars rating-stars-md star-<?php echo $review->rating * 2 ?>">
						<span class="starcounts"><?php echo number_format($review->rating, 1); ?></span>
					</div>
					<h4 class="pr-author"><?php echo $review->author_name ?></h4>
					<h5 class="pr-date"><?php echo JHtml::_('date', $review->created, 'M d, Y'); ?></h5>
					<?php if ($review->buyer == 1): ?>
						<div class="buyer-badge"><?php echo JText::_('COM_SELLACIOUS_PRODUCT_CERTIFIED_BUYER'); ?></div>
					<?php endif; ?>
				</div>
			</div>
			<div class="sell-col-xs-9 nopadd review-data">
				<div class="reviewtyped">
					<h3 class="pr-title"><?php echo $review->title; ?></h3>
					<p class="pr-body"><?php echo $review->comment; ?></p>
				</div>
			</div>
		</div>
	<?php
	endforeach;
	?>
</div>

<div class="text-right">
	<a class="btn btn-primary btn-all-reviews" href="<?php echo $seller->reviewsUrl; ?>"><?php echo JText::_('COM_SELLACIOUS_STORE_SHOW_ALL_REVIEWS'); ?></a>
</div>
<br />
