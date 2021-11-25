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

JHtml::_('script', 'com_sellacious/util.modal.js', false, true);
JHtml::_('stylesheet', 'com_sellacious/util.modal.css', null, true);

JHtml::_('script', 'com_sellacious/util.cart.aio.js', false, true);
JHtml::_('script', 'com_sellacious/fe.view.products.js', false, true);

JHtml::_('stylesheet', 'mod_sellacious_offeredproducts/style.css', null, true);
JHtml::_('stylesheet', 'com_sellacious/fe.view.cart.aio.css', null, true);
?>

<div class="mod-sellacious-offeredproducts style-2 <?php echo $class_sfx; ?>">
	<div class="titlearea">
		<h3><?php echo $modtitle; ?></h3>
	</div>
	<div class="row nomargin">
		<?php if (!empty($mainProduct)) : ?>
			<div class="col-md-6 col-sm-12 nopadd">
				<div class="main-product-wrap">
					<?php
					$prodHelper = new \Sellacious\Product($mainProduct->id);

					$images      = $helper->product->getImages($mainProduct->id);
					$code        = $prodHelper->getCode($mainProduct->seller_uid);
					$seller_attr = $prodHelper->getSellerAttributes($mainProduct->seller_uid);

					if (!is_object($seller_attr))
					{
						$seller_attr                 = new stdClass();
						$seller_attr->price_display  = 0;
						$seller_attr->stock_capacity = 0;
					}
					$images  = (array) $images;

					$url = 'index.php?option=com_sellacious&view=product&p=' . $code;
					$url_m   = JRoute::_($url . '&layout=modal&tmpl=component');

					$sl_params = array(
						'title'    => 'Quick View',
						'url'      => $url_m,
						'height'   => '600',
						'width'    => '800',
						'keyboard' => true,
					);
					echo JHtml::_('bootstrap.renderModal', 'modal-' . $code, $sl_params);

					$s_currency = $helper->currency->forSeller($mainProduct->seller_uid, 'code_3');

					$options = array(
						'title'    => JText::_('COM_SELLACIOUS_CART_TITLE'),
						'backdrop' => 'static',
					);

					$allow_checkout = $helper->config->get('allow_checkout');
					$cart_pages     = (array) $helper->config->get('product_add_to_cart_display');

					$db = JFactory::getDbo();
					$nullDate = $db->getNullDate();

					$offer = '';
					if ($mainProduct->sdate != $nullDate && $mainProduct->edate != $nullDate)
					{
						$start = strtotime($mainProduct->sdate);
						$end = strtotime($mainProduct->edate);

						$days = ceil(abs($end - $start) / 86400);

						$offer = $days . ' Days';
					}
					else if ($mainProduct->edate != $nullDate)
					{
						$offer = JHtml::date($mainProduct->edate, 'Y-m-d', true);
					}
					?>
					<div class="image-box">
						<a href="<?php echo $url; ?>" title="<?php echo $mainProduct->title; ?>">
							<span class="product-img" style="background-image: url(<?php echo reset($images); ?>)" >
						</a>
					</div>
					<div class="mainproductdetail">
						<h3 class="product-title">
							<a href="<?php echo $url; ?>" title="<?php echo $mainProduct->title; ?>">
								<?php echo $mainProduct->title; ?>
							</a>
						</h3>
						<div class="price">
							<?php echo $helper->currency->display($mainProduct->product_price, $g_currency, $c_currency, true); ?>
							<?php if ($mainProduct->list_price > 0): ?>
								<span class="oldprice">
									<del> <?php echo $helper->currency->display($mainProduct->list_price, $g_currency, $c_currency, true); ?> </del>
								</span>
							<?php endif; ?>
						</div>
					<div class="offer"><?php echo $offer; ?></div>

					<?php if ($seller_attr->price_display == 0): ?>
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
						<?php if ((int) $seller_attr->stock_capacity > 0): ?>
							<?php if ($allow_checkout && in_array('products', $cart_pages)): ?>
								<button type="button" class="btn btn-default btn-add-cart add" data-item="<?php echo $code; ?>">
									<i class="fa fa-shopping-cart"></i> Add to Cart
								</button>
							<?php endif; ?>
						<?php endif; ?>
					<?php endif; ?>
				</div>
					<div class="clearfix"></div>
				</div>
			</div>
		<?php endif; ?>

		<?php
		if (!empty($mainProduct))
		{
			$mainexist = 'col-sm-12 col-md-6 nopadd';
		} else{
			$mainexist = 'col-sm-12 nopadd';
		}
		?>
		<div class="<?php echo $mainexist; ?>">
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

						$offer = $days . ' Days';
					}
					else if ($product->edate != $nullDate)
					{
						$offer = JHtml::date($product->edate, 'Y-m-d', true);
					}
					?>
					<div class="col-xxs-12 col-xs-6 nopadd">
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
	</div>
</div>
