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

use Joomla\Registry\Registry;
use Sellacious\Price\PriceHelper;

/** @var SellaciousViewProduct $this */
$sellers = $this->item->get('sellers');

$seller_reviewsUrl = JRoute::_('index.php?option=com_sellacious&view=reviews&seller_uid=' . $this->item->get('seller_uid'));

if (!isset($sellers[0]) || (count($sellers) == 1 && $sellers[0]->seller_uid == $this->item->get('seller_uid')))
{
	return;
}
?>
<div class="ctech-clearfix"></div>
<hr class="isolate"/>
<h4><?php echo JText::_('COM_SELLACIOUS_PRODUCT_HEADING_BLOCK_SELLERS'); ?></h4>
<div class="sellers-list">
	<?php
	foreach ($sellers as $i => $seller):

		/** @var Registry $item */
		$item         = new Registry($seller);
		$priceHandler = PriceHelper::getHandler($item->get('pricing_type'));
		?>
		<div class="seller-single">
			<a class="seller-title" href="<?php echo JRoute::_('index.php?option=com_sellacious&view=store&id=' . $item->get('seller_uid')); ?>">
				<?php echo $item->get('seller_store', $item->get('seller_name', $item->get('seller_company', $item->get('seller_username')))); ?>
			</a>
			<br/>
			<?php
			$show_seller_rating = $this->helper->config->get('show_seller_rating');
			$rating             = $item->get('seller_rating.rating');

			if ($this->helper->config->get('show_seller_rating')) :
				if (!($show_seller_rating == 0 || ($rating == 0 && $this->helper->config->get('show_zero_rating') == 0))) :
					$stars = round($item->get('seller_rating.rating', 0) * 2); ?>
						<div class="rating-stars">
							<span class=" star-<?php echo $stars ?> solid-icon"></span><span class=" star-<?php echo 10- $stars ?> regular-icon"></span>
							<a href="<?php echo $seller_reviewsUrl?>"></a>
						</div>
				<?php endif;
			endif; ?>

			<div class="seller-product-action">
				<div class="seller-product-price">
					<?php echo $priceHandler->renderLayout('price.minimal', $item); ?>
				</div>
				<div class="seller-product-buttons">
					<?php $link = JRoute::_('index.php?option=com_sellacious&view=product&p=' . $item->get('code')); ?>
					<a href="<?php echo $link ?>" class="ctech-btn ctech-btn-primary btn-cart-sm">
						<i class="fa fa-info-circle"></i>
					</a>
					<?php echo $priceHandler->renderLayout('checkout-buttons.small', $item); ?>
				</div>
			</div>
		</div>
	<?php endforeach; ?>
</div>
