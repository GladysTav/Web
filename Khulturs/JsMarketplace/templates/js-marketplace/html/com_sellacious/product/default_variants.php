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

use Joomla\Registry\Registry;
use Sellacious\Product;

$switcher = $this->getVariantSwitcher();
/** @var SellaciousViewProduct $this */
$variants   = $this->item->get('variants');
$allow_checkout     = $this->helper->config->get('allow_checkout');
$cart_pages         = (array) $this->helper->config->get('product_add_to_cart_display');
$buynow_pages       = (array) $this->helper->config->get('product_buy_now_display');
$display_stock      = $this->helper->config->get('frontend_display_stock');
$c_currency         = $this->helper->currency->current('code_3');
$s_currency = $this->helper->currency->forSeller($this->item->get('seller_uid'), 'code_3');

if (!$switcher)
{
	return;
}

$fields = $switcher->getVisibleFields();

if (!$fields)
{
	return;
}

if (!isset($variants[0]) || (count($variants) == 1 && $variants[0]->variant_id == $this->item->get('variant_id')))
{
    return;
}

$c_currency = $this->helper->currency->current('code_3');
?>
<div class="clearfix"></div>

<div class="col-md-12 bg-white variant-wrapper">
	<h6><?php echo JText::_('COM_SELLACIOUS_PRODUCT_HEADING_BLOCK_VARIANTS'); ?></h6>
    <div class="product-sellers">
        <?php
        foreach ($variants as $i => $variant)
        {
            /** @var Registry $item */
            $item       = new Registry($variant);
            $s_currency = $this->helper->currency->forSeller($item->get('seller.seller_uid'), 'code_3');
            $imgs       = $item->get('images');
            $image      = reset($imgs);
			$attributes	= $this->helper->variant->getAttributes($variant->variant_id);

			if ($variant->variant_id == null)
			{
				$product = new Product($variant->id);
				$attributes = $product->getSpecifications(false, array('variant'));
			}

            if ($item->get('variant_id') == $this->item->get('variant_id'))
            {
                continue;
            }
            ?>
            <div class="variant_row row">

                <div class="variant-title col-sm-12">
					<?php $priceAmt = $item->get('seller.price_display') == '0';

					$rating_display = (array) $this->helper->config->get('product_rating_display');?>
                    <a  href="<?php echo JRoute::_('index.php?option=com_sellacious&view=product&p=' . $item->get('code')); ?>">
						<?php echo $item->get('title')?: ''; ?>
                        <?php if ($variant_title = $item->get('variant_title')): ?> - <?php echo $variant_title ?><?php endif; ?>
					</a>
					<span class="d-sm-none varientprice pull-left">
						<?php if($priceAmt): ?>
							<?php echo round($item->get('price.sales_price'), 2) >= 0.01 ? $this->helper->currency->display($item->get('price.sales_price'), $s_currency, $c_currency, true): JText::_('COM_SELLACIOUS_PRODUCT_PRICE_FREE');
						else:
							echo JText::_('COM_SELLACIOUS_NOT_AVAILABLE'); ?>
						<?php endif; ?>
					</span>

	                <?php if ($this->helper->config->get('product_rating') && (in_array('product', $rating_display))): ?>
		                <?php $rating = $item->get('rating.rating'); ?>
		                <?php $stars = round($item->get('rating.rating', 0) * 2); ?>
						<div class="product-rating rating-stars rating-stars-md  star-<?php echo $stars ?> d-sm-none pull-left">
			                <?php if ($stars > 0.0): ?>
				                <?php echo number_format($item->get('rating.rating', 0), 1) ?>
			                <?php endif; ?>
						</div>
	                <?php endif; ?>
                    <span class="wish-var pull-right"><?php  echo $this->loadTemplate('wishlist'); ?></span>

	                <?php
					$priceAmt = $item->get('seller.price_display') == '0';
	                ?>
	                <?php $link = JRoute::_('index.php?option=com_sellacious&view=product&p=' . $item->get('code')); ?>
	                <?php $btnClass = $item->get('stock_capacity') > 0 ? 'btn-add-cart' : ' disabled'; ?>
	                <?php if ($allow_checkout && in_array('product', $buynow_pages)): ?>
						<button type="button" class="btn btn-sm btn-info pull-right <?php echo $btnClass ?>"
								data-item="<?php echo $item->get('code') ?>" data-checkout="true">
							<i class="fa fa-flash"></i></button>
	                <?php endif; ?>
	                <?php if($priceAmt): ?>
						<button type="button" class="btn btn-warning btn-sm btn-add-cart pull-right" data-item="<?php echo $item->get('code') ?>"><i class="fa fa-shopping-cart"></i></button>
	                <?php endif; ?>

					<div class="clearfix"></div>
                </div>
                <div class="p-0 col-4 col-sm-2 col-md-2">
                    <div class="variants_info">
                        <div class="product-thumb">
                            <a href="<?php echo JRoute::_('index.php?option=com_sellacious&view=product&p=' . $item->get('code')) ?>">
                                <span class="product-img bgrollover" style="background-image:url(<?php echo ($image); ?>)"
                                      data-rollover="<?php echo htmlspecialchars(json_encode($image)); ?>"></span>
                            </a>
                        </div>

                    </div>
                </div>
				<div class="col-sm-6 col-8 variant-attr-box">
					<div class="variant-picker">
			            <?php foreach ($attributes as $attribute):
				            if ($variant->variant_id == null) { ?>
								<div class="main-product-variant variant-choice">
									<h5><?php echo $attribute->title ?></h5>
									<div class="radio">
										<?php if($attribute->type == 'color'): ?>
											<label class="colors-option">
												<input type="radio" class="variant_spec">
												<span style="background: <?php echo $attribute->value ?>;"></span>
											</label>
										<?php else: ?>
											<label class="variant-options">
												<input type="radio" class="variant_spec">
												<?php
												if (gettype($attribute->value) == 'string'):
													echo $attribute->value;
												elseif (gettype($attribute->value) == 'array'):
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
								foreach ($attribute as $attr): ?>
									<div class="main-product-variant variant-choice">
										<h5><?php echo $attr->title ?></h5>
										<div class="radio">

											<?php if($attr->type == 'color'): ?>
												<label class="colors-option">
													<input type="radio" class="variant_spec">
													<span style="background: <?php echo $attr->value ?>;"></span>
												</label>
											<?php else: ?>
												<label class="variant-options">
													<input type="radio" class="variant_spec">
													<?php
													if (gettype($attr->value) == 'string'):
														echo $attr->value;
													elseif (gettype($attr->value) == 'array'):
														echo $attr->value[0];
													endif;
													?>
												</label>
											<?php endif; ?>
										</div>
									</div>
								<?php endforeach;
							}
			            endforeach; ?>
						<div class="clearfix"></div>
					</div>
				</div>
				<div class="col-sm-4 col-md-4">
					<div class="row">
						<div class="col-lg-12 col-md-12 col-sm-12 text-right d-none d-sm-block">
								<div class="variantdetail">
									<div class="varientprice">
										<?php if($priceAmt):
											echo round($item->get('price.sales_price'), 2) >= 0.01 ? $this->helper->currency->display($item->get('price.sales_price'), $s_currency, $c_currency, true): JText::_('COM_SELLACIOUS_PRODUCT_PRICE_FREE');
										else:
											echo JText::_('COM_SELLACIOUS_NOT_AVAILABLE');
										endif; ?>
									</div>

									<div class="seller-info">
										<?php if ($this->helper->config->get('product_rating') && (in_array('product', $rating_display))): ?>
											<?php $rating = $item->get('rating.rating'); ?>
											<?php $stars = round($item->get('rating.rating', 0) * 2); ?>
											<div class="product-rating rating-stars rating-stars-md  star-<?php echo $stars ?>">
											<?php if ($stars > 0.0): ?>
												<?php echo number_format($item->get('rating.rating', 0), 1) ?>
											<?php endif; ?>
											</div>
										<?php endif; ?>
									</div>

									<?php if (($item->get('exchange_days')) || ($item->get('return_days'))): ?>
										<div class="exhange_box">
											<?php if ($item->get('exchange_days')): ?>
												<?php if ($item->get('exchange_tnc')):
													$options = array(
														'title'    => JText::sprintf('COM_SELLACIOUS_PRODUCT_EXCHANGE_REPLACEMENT_GUARANTEE_DAYS_N', (int) $item->get('exchange_days')),
														'backdrop' => 'static',
													);
													echo JHtml::_('bootstrap.renderModal', 'exchange_tnc-' . $item->get('code'), $options, $item->get('exchange_tnc'));
												endif; ?>
												<div class="replacement-info">
													<i class="fa fa-refresh"></i>
													<?php echo JText::sprintf('COM_SELLACIOUS_PRODUCT_EXCHANGE_REPLACEMENT_GUARANTEE_DAYS_N', (int) $item->get('exchange_days')); ?>
													<?php if ($item->get('exchange_tnc')): ?>
														<a href="#exchange_tnc-<?php echo $item->get('code') ?>" role="button" data-toggle="modal">[<i class="fa fa-question"></i>]</a>
													<?php endif; ?>
												</div>
											<?php endif; ?>

											<?php if ($item->get('return_days')): ?>
												<?php if ($item->get('return_tnc')):
													$options = array(
														'title'    => JText::sprintf('COM_SELLACIOUS_PRODUCT_EXCHANGE_EASY_RETURN_DAYS_N', (int) $item->get('return_days')),
														'backdrop' => 'static',
													);
													echo JHtml::_('bootstrap.renderModal', 'return_tnc-' . $item->get('code'), $options, $item->get('return_tnc'));
												endif; ?>
												<div class="replacement-info">
													<i class="fa fa-refresh"></i>
													<?php echo JText::sprintf('COM_SELLACIOUS_PRODUCT_EXCHANGE_EASY_RETURN_DAYS_N', (int) $item->get('return_days')); ?>
													<?php if ($item->get('return_tnc')): ?>
														<a href="#return_tnc-<?php echo $item->get('code') ?>" role="button" data-toggle="modal">[<i class="fa fa-question"></i>]</a>
													<?php endif; ?>
												</div>
											<?php endif; ?>
										</div>
									<?php endif; ?>
								</div>
							</div>
					</div>
				</div>
            </div>
            <?php
        }
        ?>

		<div class="clearfix"></div>
    </div>
</div>
