<?php
/**
 * @version     2.1.4
 * @package     SP Page Builder Addons for Sellacious
 *
 * @copyright   Copyright (C) 2016. Bhartiy Web Technologies. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Bhavika Matariya <info@bhartiy.com> - http://www.bhartiy.com
 */

//no direct access
defined('_JEXEC') or die ('restricted aceess');

use Sellacious\Product;

class SppagebuilderAddonSL_Product_Variants extends SppagebuilderAddons
{

	public function render()
	{

		$class            = (isset($this->addon->settings->class) && $this->addon->settings->class) ? $this->addon->settings->class : '';
		$title            = (isset($this->addon->settings->title) && $this->addon->settings->title) ? $this->addon->settings->title : '';
		$heading_selector = (isset($this->addon->settings->heading_selector) && $this->addon->settings->heading_selector) ? $this->addon->settings->heading_selector : 'h3';
		$box_title  	  = (isset($this->addon->settings->variant_title) && $this->addon->settings->variant_title) ? $this->addon->settings->variant_title : '';
		$show_price       = (isset($this->addon->settings->show_price) && $this->addon->settings->show_price) ? $this->addon->settings->show_price : '1';
		$show_image       = (isset($this->addon->settings->show_image) && $this->addon->settings->show_image) ? $this->addon->settings->show_image : '1';
		$show_ratings     = (isset($this->addon->settings->show_ratings) && $this->addon->settings->show_ratings) ? $this->addon->settings->show_ratings : '1';
		$show_condition   = (isset($this->addon->settings->show_condition) && $this->addon->settings->show_condition) ? $this->addon->settings->show_condition : '1';
		$show_detail_btn  = (isset($this->addon->settings->show_detail_btn) && $this->addon->settings->show_detail_btn) ? $this->addon->settings->show_detail_btn : '1';
		$detail_btn_title = (isset($this->addon->settings->detail_btn_title) && $this->addon->settings->detail_btn_title) ? $this->addon->settings->detail_btn_title : 'Details';
		$show_cart_btn    = (isset($this->addon->settings->show_cart_btn) && $this->addon->settings->show_cart_btn) ? $this->addon->settings->show_cart_btn : '1';
		$cart_btn_title   = (isset($this->addon->settings->cart_btn_title) && $this->addon->settings->cart_btn_title) ? $this->addon->settings->cart_btn_title : 'Add to Cart';

		$app     = JFactory::getApplication();
		$input  = $app->input;
		$product = $input->getInt('product');
		$v       = $input->getInt('v');
		$html    = '';
		$helper  = SellaciousHelper::getInstance();

		if ($product)
		{

			$productClass = new Product($product, 0);
			$variant_ids  = $productClass->getVariants();

			$variants = array();
			$me       = JFactory::getUser();
			$c_cat    = $helper->client->loadResult(array('list.select' => 'category_id', 'user_id' => $me->id));

			foreach ($variant_ids as $v_id)
			{
				if ($v_id != $v)
				{
					$vProduct = new Product($product, $v_id);

					$oVariant = (object) $vProduct->getAttributes();

					$oVariant->price = $vProduct->getPrice(null, 1, $c_cat);
					$vSeller_uid     = $oVariant->price->seller_uid;

					$oVariant->code   = $vProduct->getCode($vSeller_uid);
					$oVariant->seller = $vProduct->getSellerAttributes($vSeller_uid);
					$oVariant->images = $vProduct->getImages(true, true);

					if (isset($oVariant->seller->stock_capacity) && $oVariant->seller->stock_capacity > 0 && abs($oVariant->price->sales_price) >= 0.01)
					{
						$variants[] = $oVariant;
					}
				}
			}
			$c_currency = $helper->currency->current('code_3');

			JHtml::_('script', 'com_sellacious/util.cart.aio.js', false, true);
			JHtml::_('script', 'com_sellacious/fe.view.products.js', false, true);

			$options = array(
				'title'    => JText::_('COM_SELLACIOUS_CART_TITLE'),
				'backdrop' => 'static',
			);

			ob_start();

			if (!empty($variants))
			{
				?>
				<script>
					jQuery(document).ready(function ($) {
						if ($('#modal-cart').length == 0) {
							var $html = <?php echo json_encode(JHtml::_('bootstrap.renderModal', 'modal-cart', $options)); ?>;
							$('body').append($html);

							var $cartModal = $('#modal-cart');
							var oo = new SellaciousViewCartAIO;
							oo.token = $('#formToken').attr('name');
							oo.initCart('#modal-cart .modal-body', true);
							$cartModal.find('.modal-body').html('<div id="cart-items"></div>');
							$cartModal.data('CartModal', oo);
						}
					});
				</script>
				<div class="moreinfo-box">
					<?php echo ($box_title) ? '<h3>' . $box_title . '</h3>' : ''; ?>
					<div class="product-sellers">
						<?php
						foreach ($variants as $i => $variant)
						{
							/** @var \Joomla\Registry\Registry $item */
							$item       = new \Joomla\Registry\Registry($variant);
							$s_currency = $helper->currency->forSeller($item->get('seller_uid'), 'code_3');
							$imgs       = $item->get('images');
							$image      = reset($imgs);

							//classes conditions
							$btnarea = $show_detail_btn || $show_cart_btn;

							$document = JFactory::getDocument();
							$style    = '';
							if (!$show_detail_btn && $show_cart_btn || $show_detail_btn && !$show_cart_btn)
							{
								$style = '.variantinner .btngrouparea{padding-top:24px;}';
								$style .= '@media (max-width: 991px){.varientprice {padding-top:10px; min-height: 50px;} .variantinner .btngrouparea {padding-top: 4px;}}';
								$style .= '@media (max-width: 600px){.variantinner .btngrouparea{padding-top:0px;}}';
							};

							// cols conditions
							if ($show_price && $btnarea)
							{
								$variantmain = "col-md-5 col-sm-12";
								$pricecols   = "col-md-3 col-sm-6 col-xs-6 col-xxs-12";
								$btncols     = "col-md-4 col-sm-6 col-xs-6 col-xxs-12";
							}
							elseif (!$show_price && $btnarea)
							{
								$variantmain = "col-sm-8";
								$btncols     = "col-sm-4";
								//$style .= "@media (max-width: 991px){ .varientprice{border-left:none;}}";
							}
							elseif ($show_price && !$btnarea)
							{
								$variantmain = "col-sm-8";
								$btncols     = "col-sm-4";
							}
							elseif (!$show_price && !$btnarea)
							{
								$variantmain = "col-sm-12";
								$style       = ".variantdetail {min-height: auto;}";
							};

							$document->addStyleDeclaration($style);

							?>
							<div class="variant_row row">
								<div class="<?php echo $variantmain ?>">
									<div class="variants_info">
										<?php if ($show_image): ?>
											<div class="product-thumb">
												<a href="<?php echo JRoute::_('index.php?option=com_sellacious&view=product&p=' . $item->get('code')) ?>">
													<img src="<?php echo $image; ?>" alt=""/>
												</a>
											</div>
										<?php endif; ?>
										<div class="variantdetail">
											<div class="seller-info">
												<a href="<?php echo JRoute::_('index.php?option=com_sellacious&view=product&p=' . $item->get('code')) ?>">
													<?php echo $item->get('title'); ?><?php echo $item->get('variant_title'); ?>
												</a>
												<?php if ($show_ratings): ?>
													<?php $rating = $item->get('rating.rating'); ?>
													<span class="label <?php echo ($rating < 3) ? 'label-warning' : 'label-success' ?>"><?php echo number_format($rating, 1) ?> / 5.0</span>
												<?php endif; ?>
											</div>

											<?php if ($item->get('exchange_days')): ?>
												<?php if ($item->get('exchange_tnc')):
													$options = array(
														'title'    => '<strong>' . (int) $item->get('exchange_days') . ' Days</strong> Replacement Guarantee',
														'backdrop' => 'static',
													);
													echo JHtml::_('bootstrap.renderModal', 'exchange_tnc-' . $item->get('code'), $options, $item->get('exchange_tnc'));
												endif; ?>
												<div class="replacement-info">
													<i class="fa fa-refresh"></i>
													<strong> <?php echo (int) $item->get('exchange_days') ?> Days</strong> Replacement
													<?php if ($item->get('exchange_tnc')): ?>
														<a href="#exchange_tnc-<?php echo $item->get('code') ?>" role="button" data-toggle="modal">[<i class="fa fa-question"></i>]</a>
													<?php endif; ?>
												</div>
											<?php endif; ?>

											<?php if ($item->get('return_days')): ?>
												<?php if ($item->get('return_tnc')):
													$options = array(
														'title'    => '<strong>' . (int) $item->get('return_days') . ' Days</strong> Easy Return',
														'backdrop' => 'static',
													);
													echo JHtml::_('bootstrap.renderModal', 'return_tnc-' . $item->get('code'), $options, $item->get('return_tnc'));
												endif; ?>
												<div class="replacement-info">
													<i class="fa fa-refresh"></i>
													<strong> <?php echo (int) $item->get('return_days') ?> Days</strong> Easy Return
													<?php if ($item->get('return_tnc')): ?>
														<a href="#return_tnc-<?php echo $item->get('code') ?>" role="button" data-toggle="modal">[<i class="fa fa-question"></i>]</a>
													<?php endif; ?>
												</div>
											<?php endif; ?>

											<?php if ($show_condition) : ?>
												<div class="varientotherinfo">
													<?php $allowed_listing_type = (array) $helper->config->get('allowed_listing_type'); ?>
													<?php if (count($allowed_listing_type) != 1): ?>
														<span class="label label-info margin-top-10">Condition:
															<?php
															$list_type = $item->get('listing_type');

															// What if this is a not allowed listing type value
															if ($list_type == 1):
																echo JText::plural('COM_SELLACIOUS_PRODUCT_FIELD_LISTING_TYPE_VALUE', $list_type);
															else:
																$list_cond = $item->get('item_condition');
																echo JText::plural('COM_SELLACIOUS_PRODUCT_FIELD_ITEM_CONDITION_VALUE', $list_type * 10 + (int) $list_cond);
															endif;
															?>
                                                								</span>
													<?php endif; ?>
												</div>
											<?php endif; ?>
										</div>
									</div>
								</div>

								<?php if ($show_price): ?>
									<div class="<?php echo $pricecols ?>">
										<div class="varientprice">
											<?php echo $helper->currency->display($item->get('price.sales_price'), $s_currency, $c_currency, true) ?>
										</div>
									</div>
								<?php endif; ?>

								<?php if ($btnarea) : ?>
									<div class="<?php echo $btncols ?>">
										<div class="variantinner">
											<div class="btngrouparea">
												<?php if ($show_detail_btn) : ?>
													<?php $link = JRoute::_('index.php?option=com_sellacious&view=product&p=' . $item->get('code')); ?>
													<a href="<?php echo $link ?>" class="btn btn-primary"><?php echo $detail_btn_title; ?></a>
												<?php endif; ?>
												<?php if ($show_cart_btn) : ?>
													<button type="button" class="btn btn-default btn-add-cart"
															data-item="<?php echo $item->get('code') ?>"><?php echo $cart_btn_title; ?>
													</button>
												<?php endif; ?>
											</div>
										</div>
									</div>
								<?php endif; ?>
							</div>
							<?php
						}
						?>
					</div>
				</div>
				<?php
			}
			$html = ob_get_clean();

		}

		//Output
		if ($html)
		{
			$output = '<div class="sppb-addon sppb-addon-product-variants ' . $class . '">';
			$output .= ($title) ? '<' . $heading_selector . ' class="sppb-addon-title">' . $title . '</' . $heading_selector . '>' : '';
			$output .= '<div class="sppb-addon-content">';
			$output .= $html;
			$output .= '</div>';
			$output .= '</div>';

			return $output;
		}

		return;
	}

	public function stylesheets()
	{
		return array(JURI::base(true) . '/components/com_sppagebuilder/assets/css/sellacious/sl-productoptions.css');
	}

}
