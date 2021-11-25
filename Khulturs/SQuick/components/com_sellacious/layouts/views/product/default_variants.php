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
use Sellacious\Product;

/** @var SellaciousViewProduct $this */

$variants = $this->item->get('variants');
/** @var  array  $tplData */
$product = $tplData;

if (!isset($variants[0]) || (count($variants) == 1 && $variants[0]->variant_id == $this->item->get('variant_id')))
{
	return;
}

$c_currency = $this->helper->currency->current('code_3');
?>
<div class="ctech-clearfix"></div>
<a name="variants-list">&nbsp;</a>
<hr class="isolate"/>
<div class="sell-infobox variants-list">
	<h5 class="center"><?php echo JText::_('COM_SELLACIOUS_PRODUCT_HEADING_BLOCK_VARIANTS'); ?></h5>
	<div class="ctech-container-fluid">
		<div class="ctech-row">
			<?php foreach ($variants as $i => $variant):
				/** @var Registry $item */
				$item       = new Registry($variant);
				$item->set('stock_capacity', $item->get('seller.stock_capacity'));
				$s_currency = $this->helper->currency->forSeller($item->get('seller.seller_uid'), 'code_3');
				$images     = $item->get('images');
				$attributes = $this->helper->variant->getAttributes($variant->variant_id);

				if ($variant->variant_id == null)
				{
					$product    = new Product($variant->id);
					$attributes = $product->getSpecifications(false, array('variant'));
				}

				$link           = JRoute::_('index.php?option=com_sellacious&view=product&p=' . $item->get('code'));
				$rating_display = (array) $this->helper->config->get('product_rating_display');
				$priceHandler   = PriceHelper::getHandler($item->get('seller.pricing_type'));
				?>
				<div class="ctech-col-md-6 single-variant">
					<div class="variant-thumb bgrollover">
						<span style="background-image: url('<?php echo reset($images); ?>')"
								data-rollover="<?php echo htmlspecialchars(json_encode($images)); ?>"></span>
					</div>

					<div class="variant-info">
						<div class="variant-title">
							<a href="<?php echo $link; ?>"><?php echo $item->get('title'); ?> <?php
								echo $item->get('variant_title') ? ' - ' . $item->get('variant_title') : ''; ?></a>
						</div>
						<div class="variant-price">
							<?php echo $priceHandler->renderLayout('price.minimal', $item);?>
						</div>
						<div class="variant-rating">
							<?php if ($this->helper->config->get('product_rating') && (in_array('product', $rating_display))): ?>
								<?php $rating = $item->get('rating.rating'); ?>
								<?php $stars = round($item->get('rating.rating', 0) * 2); ?>
								<div class="product-rating rating-stars">
									<span class="star-<?php echo $stars ?> fa fa-star solid-icon"></span><span class="star-<?php echo 10 - $stars ?> fa fa-star regular-icon"></span>
									<?php if ($stars > 0.0): ?>
										<span class="ctech-text-primary"><?php echo number_format($item->get('rating.rating', 0), 1) ?></span>
									<?php endif; ?>
									</span>
								</div><br/>
							<?php endif; ?>
						</div>

					<div class="variant-action-buttons">
						<?php
						echo $this->loadTemplate('wishlist');

						echo $priceHandler->renderLayout('checkout-buttons.small', $item);
						?>
					</div>
					</div>
					<div class="clearfix"></div>
					<div class="ctech-row">

						<div class="ctech-col-md-12">
							<?php
							foreach ($attributes as $attribute)
							{
								if ($variant->variant_id == null)
								{
									?>
									<div class="main-product-variant variant-choice">
										<h5><?php echo $attribute->title ?></h5>
										<div class="radio">
											<?php if ($attribute->type == 'color'): ?>
												<label class="colors-option">
													<input type="radio" class="variant_spec">
													<?php echo $attribute->value ?>
												</label>
											<?php else: ?>
												<label class="variant-options">
													<input type="radio" class="variant_spec">
													<?php
													if (is_string($attribute->value)):
														echo $attribute->value;
													elseif (is_array($attribute->value)):
														echo $attribute->value[0];
													endif;
													?>
												</label>
											<?php endif; ?>
										</div>
									</div>
									<?php
								}
								else
								{
									foreach ($attribute as $attr)
									{
										?>
										<div class="main-product-variant variant-choice">
										<h5><?php echo $attr->title ?></h5>
										<div class="radio">
											<?php if ($attr->type == 'color'): ?>
												<label class="colors-option">
													<input type="radio" class="variant_spec">
													<?php echo $attr->value ?>
												</label>
											<?php else: ?>
												<label class="variant-options">
													<input type="radio" class="variant_spec">
													<?php
													if (is_string($attr->value)):
														echo $attr->value;
													elseif (is_array($attr->value)):
														echo $attr->value[0];
													endif;
													?>
												</label>
											<?php endif; ?>
										</div>
										</div>
										<?php
									}
								}
							}
							?>
						</div>
					</div>
				</div>
			<?php endforeach; ?>
		</div>
	</div>
</div>
