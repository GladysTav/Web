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

/** @var SellaciousViewUserReviews $this */
$app = JFactory::getApplication();

$listOrder = $this->escape($this->state->get('list.ordering'));
$listDirn  = $this->escape($this->state->get('list.direction'));

// Load the behaviors.
JHtml::_('jquery.framework');
JHtml::_('bootstrap.tooltip');

JHtml::_('ctech.bootstrap');

JHtml::_('script', 'com_sellacious/fe.view.orders.tile.js', array('version' => S_VERSION_CORE, 'relative' => true));
JHtml::_('stylesheet', 'com_sellacious/fe.component.css', array('version' => S_VERSION_CORE, 'relative' => true));
JHtml::_('stylesheet', 'com_sellacious/util.rating.css', array('version' => S_VERSION_CORE, 'relative' => true));
JHtml::_('stylesheet', 'com_sellacious/fe.view.reviews.css', array('version' => S_VERSION_CORE, 'relative' => true));
JHtml::_('stylesheet', 'com_sellacious/font-awesome.min.css', array('version' => S_VERSION_CORE, 'relative' => true));

$reviews = $this->items;
$author  = $this->getAuthorInfo();

$reviewsBy = $this->state->get('filter.reviews_by', '');
?>
<div class="ctech-wrapper">
	<h2><?php echo $author->get('name'); ?></h2>

	<form action="<?php echo JUri::getInstance()->toString(array('path', 'query', 'fragment')) ?>"
		  method="post" name="adminForm" id="adminForm">
		<?php if (!empty($reviews)):
			$productReviews = array();
			$sellerReviews  = array();

			foreach ($reviews as $review):
				if ($review->type == 'product'):
					$productReviews[] = $review;
				elseif ($review->type == 'seller'):
					$sellerReviews[] = $review;
				endif;
			endforeach;
			?>
			<?php if (!empty($productReviews) && (!$reviewsBy || $reviewsBy == 'product')): ?>
			<h4><?php echo JText::_('COM_SELLACIOUS_REVIEWS_PRODUCT'); ?></h4>
			<div class="rating-box sell-infobox">
				<div class="reviewslist">
					<?php foreach ($productReviews as $review):
						$code = $this->helper->product->getCode($review->product_id, 0, $review->seller_uid);
						$url  = JRoute::_('index.php?option=com_sellacious&view=product&p=' . $code);
						?>
						<div class="ctech-row nomargin">
							<div class="ctech-col-sm-3 nopadd">
								<div class="reviewauthor">
									<div class="rating-stars rating-stars">
										<span class="star-<?php echo $review->rating * 2 ?> fa fa-star solid-icon"></span><span class="star-<?php echo 10 - ($review->rating * 2) ?> fa fa-star regular-icon"></span>
										<span class="ctech-text-primary"><?php echo number_format($review->rating, 1); ?></span>
									</div>
									<a href="<?php echo $url ?>"><h4 class="pr-author"><?php echo $review->product_title ?></h4></a>
									<h5 class="pr-date"><?php echo JHtml::_('date', $review->created, 'M d, Y'); ?></h5>
								</div>
							</div>
							<div class="ctech-col-sm-9 nopadd">
								<div class="reviewtyped">
									<h3 class="pr-title"><?php echo $review->title ?></h3>
									<p class="pr-body"><?php echo nl2br($review->comment); ?></p>
								</div>
							</div>
						</div>
					<?php endforeach; ?>
				</div>
			</div>
		<?php endif; ?>

			<?php if (!empty($sellerReviews) && (!$reviewsBy || $reviewsBy == 'seller')): ?>
			<h4><?php echo JText::_('COM_SELLACIOUS_REVIEWS_SELLER'); ?></h4>
			<div class="rating-box sell-infobox">
				<div class="reviewslist">
					<?php foreach ($sellerReviews as $review):
						$url = JRoute::_('index.php?option=com_sellacious&view=store&id=' . $review->seller_uid);
						?>
						<div class="ctech-row nomargin">
							<div class="ctech-col-sm-3 nopadd">
								<div class="reviewauthor">
									<div class="rating-stars rating-stars">
										<span class="star-<?php echo $review->rating * 2 ?> fa fa-star solid-icon"></span><span class="star-<?php echo 10 - ($review->rating * 2) ?> fa fa-star regular-icon"></span>
										<span class="ctech-text-primary"><?php echo number_format($review->rating, 1); ?></span>
									</div>
									<a href="<?php echo $url ?>"><h4 class="pr-author"><?php echo $review->seller_title ?></h4></a>
									<h5 class="pr-date"><?php echo JHtml::_('date', $review->created, 'M d, Y'); ?></h5>
								</div>
							</div>
							<div class="ctech-col-sm-9 nopadd">
								<div class="reviewtyped">
									<h3 class="pr-title"><?php echo $review->title ?></h3>
									<p class="pr-body"><?php echo nl2br($review->comment); ?></p>
								</div>
							</div>
						</div>
					<?php endforeach; ?>
				</div>
			</div>
		<?php endif; ?>
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
