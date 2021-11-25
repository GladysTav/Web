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
use Joomla\Registry\Registry;
use Sellacious\Media\Image\ImageHelper;
use Sellacious\Media\Image\ResizeImage;

defined('_JEXEC') or die;

/** @var  SellaciousViewProducts  $this */

$storeId   = $this->state->get('store.id');
$me        = JFactory::getUser();
$store     = JFactory::getUser($storeId);
$seller    = $this->helper->seller->getItem(array('user_id' => $storeId));
$seller    = new Registry($seller);
$params    = new Registry($seller->get('params'));
$storeName = $seller->get('store_name')?: $store->name;
$rateable  = (array) $this->helper->config->get('allow_ratings_for');
$rating    = $this->helper->rating->getSellerRating($storeId);

$logo    = ImageHelper::getImages('sellers', $seller->get('id'), 'logo');
$logo    = ImageHelper::getResized($logo, 250, 250, true, 100, ResizeImage::RESIZE_EXACT_WIDTH);
$logo    = ImageHelper::getUrls($logo);

$additionalInfo = array();
$dispatcher     = $this->helper->core->loadPlugins();
$dispatcher->trigger('onRenderSellerInfo', array('com_sellacious.store', $seller, &$additionalInfo));

$seller->set('reviewsUrl', JRoute::_('index.php?option=com_sellacious&view=reviews&seller_uid=' . $seller->get('user_id')));

$logoSize = $this->helper->config->get('store_logo_image_size', 'cover');
?>

<div class="store-header">
	<div class="store-logo-container">
		<span class="logo" style="background-image: url('<?php echo reset($logo); ?>'); background-size: <?php echo $logoSize ?>"></span>
	</div>
	<div class="store-info-container">
		<h4 class="store-name">
           <?php echo $storeName; ?>
    </h4>
		<p class="store-address"><?php echo $seller->get('store_address'); ?></p>

		<?php if (!(!(in_array('seller', $rateable)) || ($this->helper->config->get('store_details_show_rating') == 0 ) || ($this->helper->config->get('show_zero_rating')== 0 && $rating->rating == '0'))): ?>
			<?php $stars = round($rating->rating * 2); ?>
			<div class="store-rating rating-stars">
				<span class="star-<?php echo $stars ?> fa fa-star solid-icon"></span><span class="star-<?php echo 10 - $stars ?> fa fa-star regular-icon"></span>
				<span class="rating-number ctech-text-primary"><?php echo round($rating->rating, 1); ?></span>
			</div>
		<?php endif; ?>

		<div class="store-icons">
			<?php echo $this->loadTemplate('favorite'); ?>
			<?php
			$message_enabled         = $this->helper->config->get('enable_fe_messages', 0);
			$store_chat_button_pages = (array)$this->helper->config->get('store_chat_button_display');
			$storeId                 = $seller->get('user_id');

			if ($message_enabled && in_array('store', $store_chat_button_pages) && $storeId != $me->id): ?>
				<div class="chat-link ctech-float-left">
					<a class="hasTooltip" title="<?php echo JText::_('COM_SELLACIOUS_STORE_CHAT_WITH_STORE') ?>"href="<?php echo JRoute::_('index.php?option=com_sellacious&view=messages&recipient=' . $storeId . '&context=seller&ref=' . $storeId); ?>">
						<i class="fa fa-envelope ctech-text-dark"></i>
					</a>
				</div>
			<?php endif; ?>

			<?php
			$coupons_button_display_pages = (array)$this->helper->config->get('coupons_button_display');
			$seller_coupons               = $this->helper->coupon->count(array('list.where' => array('a.seller_uid = ' . $storeId, 'a.state = 1')));

			if (in_array('store', $coupons_button_display_pages) && $seller_coupons): ?>
				<a class="hasTooltip coupons-link ctech-float-left" title="<?php echo JText::_('COM_SELLACIOUS_STORE_COUPONS') ?>" href="<?php echo JRoute::_('index.php?option=com_sellacious&view=coupons&filter[seller_uid]=' . $storeId); ?>">
					<i class="fas fa-ticket-alt ctech-text-dark"></i>
				</a>
			<?php endif; ?>

			<?php
			$discounts_button_display_pages = (array)$this->helper->config->get('discounts_button_display');
			$seller_discounts               = $this->helper->shopRule->count(array('list.where' => array('a.seller_uid = ' . $storeId, 'a.state = 1')));

			if (in_array('store', $discounts_button_display_pages) && $seller_discounts): ?>
				<a class="hasTooltip discounts-link ctech-float-left" title="<?php echo JText::_('COM_SELLACIOUS_STORE_DISCOUNTS') ?>" href="<?php echo JRoute::_('index.php?option=com_sellacious&view=shoprules&type=discount&filter[seller_uid]=' . $storeId); ?>">
					<i class="fa fa-gift ctech-text-dark"></i>
				</a>
			<?php endif; ?>

			<?php if ($this->helper->config->get('show_store_location', 1)):
				echo $this->loadTemplate('location');
			endif; ?>
		</div>
		<div class="clearfix"></div>
		<?php
		$socialLinks = (array)$this->helper->config->get('social_links_display');
		if (in_array('store', $socialLinks)): ?>
			<div class="social-icons">
				<?php if ($facebook = $params->get('social_share_link.facebook')): ?>
					<a class="ctech-float-left ctech-text-primary" href="<?php echo $facebook?>" target="_blank"><i class="fab fa-facebook-square"></i></a>
				<?php endif; ?>
				<?php if ($twitter = $params->get('social_share_link.twitter')): ?>
					<a class="ctech-float-left ctech-text-primary" href="<?php echo $twitter?>" target="_blank"><i class="fab fa-twitter-square"></i></a>
				<?php endif; ?>
				<?php if ($insta = $params->get('social_share_link.instagram')): ?>
					<a class="ctech-float-left ctech-text-primary" href="<?php echo $insta?>" target="_blank"><i class="fab fa-instagram"></i></a>
				<?php endif; ?>
				<?php if ($linkedin = $params->get('social_share_link.linkedin')): ?>
					<a class="ctech-float-left ctech-text-primary" href="<?php echo $linkedin?>" target="_blank"><i class="fab fa-linkedin"></i></a>
				<?php endif; ?>
			</div>
			<div class="clearfix"></div>
		<?php endif; ?>
		<?php
		foreach ($additionalInfo as $info)
		{
			echo $info;
		}
		?>
	</div>
</div>
