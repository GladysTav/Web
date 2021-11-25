<?php
/**
 * @version     1.0.1
 * @package     mod_sellacious_offeredproducts
 *
 * @copyright   Copyright (C) 2017. Bhartiy Web Technologies. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Mohd Kareemuddin <info@bhartiy.com> - http://www.bhartiy.com
 */

// no direct access
defined('_JEXEC') or die('Restricted access');

JHtml::_('stylesheet', 'mod_sellacious_offeredproducts/style.css', null, true);

?>
<div class="mod-sellacious-offeredproducts style-3 <?php echo $class_sfx; ?>">
	<div class="titlearea">
		<h3><?php echo $modtitle; ?></h3>
	</div>
	<div class="products-wrap row nomargin">
		<?php foreach ($products AS $product) :

			$prodHelper = new \Sellacious\Product($product->id);
			$code = $prodHelper->getCode($product->seller_uid);
			$url = 'index.php?option=com_sellacious&view=product&p=' . $code;

			$images      = $helper->product->getImages($product->id);
			$db = JFactory::getDbo();
			$nullDate = $db->getNullDate();

			$offer = '';
			if ($product->sdate != $nullDate && $product->edate != $nullDate)
			{
				$start = strtotime($product->sdate);
				$end = strtotime($product->edate);

				$days = ceil(abs($end - $start) / 86400);

				$offer = '<i class="fa fa-clock-o"></i> ' . $days . ' days left';
			}
			else if ($product->edate != $nullDate)
			{
				$offer = 'Offer till' . JHtml::date($product->edate, 'Y-m-d', true);
			}
			?>
			<div class="col-xxs-12 col-xs-6 col-sm-6 col-md-3 nopadd">
				<div class="product-box">
					<div class="image-box">
						<a href="<?php echo $url; ?>" title="<?php echo $product->title; ?>">
							<span class="product-img" style="background-image: url(<?php echo reset($images); ?>)" >
						</a>
					</div>
					<div class="productdetail">
						<h3 class="product-title">
							<a href="<?php echo $url; ?>" title="<?php echo $product->title; ?>">
								<?php echo $product->title; ?>
							</a>
						</h3>
						<div class="price">
							<?php echo $helper->currency->display($product->product_price, $g_currency, $c_currency, true); ?>
							<?php if ($product->list_price > 0): ?>
								<span class="oldprice">
									<del><?php echo $helper->currency->display($product->list_price, $g_currency, $c_currency, true); ?></del>
								</span>
							<?php endif; ?>
						</div>
						<div class="offer"><?php echo $offer; ?></div>
					</div>
					<div class="clearfix"></div>
				</div>
			</div>
		<?php endforeach; ?>
	</div>
	<div class="clearfix"></div>
</div>
