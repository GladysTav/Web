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
JHtml::_('script', 'com_sellacious/util.readmore-text.js', true, true);
$item               = $this->item;
$max_reviews = $this->helper->config->get('number_of_product_reviews');
$reviews     = $this->getReviews($max_reviews);
?>

<?php  if (!empty($reviews))  { ?>
<div class="reviewslist">

    <div class="title-review">
        <h6><?php echo JText::_('COM_SELLACIOUS_COMPARE_PRODCUT_REVIEWS_LIST'); ?></h6>
    </div>
	<?php
	foreach ($reviews as $review): ?>
		<div class="sell-row nomargin">
			<div class="sell-col-xs-12 nopadd">
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
				<div class="read-more-description read-more-ellipsis reviewtyped">
					<h3 class="pr-title"><?php echo $review->title ?></h3>
					<p class="pr-body readmore"><?php echo $review->comment ?></p>
				</div>

			</div>
		</div>
		<?php
	endforeach;
	?>
    <a class="read-more-reviews text-success" href="index.php?option=com_sellacious&view=reviews&product_id=<?php echo $item->get('id') ?>">Read ALL</a>

</div>
<?php } ?>


