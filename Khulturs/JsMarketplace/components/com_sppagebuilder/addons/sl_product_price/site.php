<?php
/**
 * @version     2.2.0
 * @package     SP Page Builder Addons for Sellacious
 *
 * @copyright   Copyright (C) 2016. Bhartiy Web Technologies. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Bhavika Matariya <info@bhartiy.com> - http://www.bhartiy.com
 */

//no direct access
defined('_JEXEC') or die ('restricted aceess');

class SppagebuilderAddonSL_Product_Price extends SppagebuilderAddons
{

	public function render()
	{

		$class            = (isset($this->addon->settings->class) && $this->addon->settings->class) ? $this->addon->settings->class : '';
		$title            = (isset($this->addon->settings->title) && $this->addon->settings->title) ? $this->addon->settings->title : '';
		$heading_selector = (isset($this->addon->settings->heading_selector) && $this->addon->settings->heading_selector) ? $this->addon->settings->heading_selector : 'h3';

		$app     = JFactory::getApplication();
		$input  = $app->input;
		$product = $input->getInt('product');
		$variant = $input->getInt('v');
		$seller  = $input->getInt('s');

		$html = '';

		$helper = SellaciousHelper::getInstance();

		//Options
		if ($product)
		{
			JHtml::_('script', 'com_sellacious/util.cart.aio.js', false, true);
			JHtml::_('script', 'com_sellacious/fe.view.products.js', false, true);

			if (empty($seller))
			{
				$seller = $helper->product->getSellers($product, false);
				$seller = $seller[0]->seller_uid;
			}

			$me    = JFactory::getUser();
			$c_cat = $helper->client->loadResult(array('list.select' => 'category_id', 'user_id' => $me->id));

			$prodHelper = new Sellacious\Product($product, $variant, $seller);

			$item  = $helper->product->getItem($product);
			$price = (object) $prodHelper->getPrice($seller, 1, $c_cat);
			$code  = $prodHelper->getCode($seller);

			$seller_attr = $prodHelper->getSellerAttributes($seller);

			$sellerHelper = new Sellacious\Seller($seller);
			$sellerInfo = $sellerHelper->getAttributes();

			$s_currency = $helper->currency->forSeller($price->seller_uid, 'code_3');
			$c_currency = $helper->currency->current('code_3');


			if (!is_object($seller_attr))
			{
				$seller_attr                 = new stdClass();
				$seller_attr->price_display  = 0;
				$seller_attr->stock_capacity = 0;

			}

			$item = array_merge((array) $item, (array) $price);
			$item = (object) $item;

			$item->seller_email  = $sellerInfo['email'];
			$item->seller_mobile = $sellerInfo['mobile'];

			$display = $seller_attr->price_display;
			$page_id = 'product';

			//echo '<pre>';print_r($seller_attr); print_r($price); print_r($item); echo '</pre>';

			$allowed_price_display = (array) $helper->config->get('allowed_price_display');
			$security              = $helper->config->get('contact_spam_protection');

			ob_start();

			if ($display == 0)
			{
				$price_display = $helper->config->get('product_price_display');
				$price_d_pages = (array) $helper->config->get('product_price_display_pages');

				if ($item->price_id > 0 && $price_display > 0 && in_array($page_id, $price_d_pages))
				{
					?>
					<div class="pricearea">
						<div class="mainprice">
							<span class="product-price"><?php echo $helper->currency->display($item->sales_price, $s_currency, $c_currency, true) ?></span>
							<?php if ($price_display == 2 && $item->list_price > 0):
								echo JText::_('Selling Price:'); ?>	<strong><del><?php echo $helper->currency->display($item->list_price, $s_currency, $c_currency, true) ?></del></strong>
							<?php endif; ?>
						</div>
						<div class="clearfix"></div>
					</div>
					<?php
				}
			}
			elseif ($display == 1 && in_array(1, $allowed_price_display))
			{
				?>
				<div class="querysend querysend-toggle">
					<button type="button" class="btn btn-default" data-toggle="true"><?php
						echo JText::_('Call for Price') ?></button>
					<button type="button" class="btn btn-primary hidden" data-toggle="true"><?php
						$mobile = $item->seller_mobile ?: JText::_('N/A');

						if ($security)
						{
							$text = $helper->media->writeText($mobile, 4, true);
							?><img src="data:image/png;base64,<?php echo $text; ?>"/><?php
						}
						else
						{
							echo $mobile;
						}
						?></button>
				</div>
				<?php
			}
			elseif ($display == 2 && in_array(2, $allowed_price_display))
			{
				?>
				<div class="querysend querysend-toggle">
					<button type="button"
							class="btn btn-default" data-toggle="true"><?php
						echo JText::_('Email for Price') ?></button>
					<button type="button" class="btn btn-primary hidden" data-toggle="true"><?php
						$seller_email = $item->seller_email ?: JText::_('N/A');

						if ($security)
						{
							$text = $helper->media->writeText($seller_email, 4, true);
							?><img src="data:image/png;base64,<?php echo $text; ?>"/><?php
						}
						else
						{
							echo $item->get('seller_email');
						}
						?></button>
				</div>
				<?php
			}elseif ($display == 3 && in_array(3, $allowed_price_display))
			{

				$options = array(
					'title'    => 'Submit a Query for: ' . addslashes($item->title),
					'backdrop' => 'static',
					'height'   => '450',
					'keyboard' => true,
					'url'      => "index.php?option=com_sellacious&view=product&p=" . $code . "&layout=query&tmpl=component",
				);

				echo JHtml::_('bootstrap.renderModal', "query-form-{$code}", $options);
				?>
				<div class="querysend">
					<a href="#query-form-<?php echo $code ?>" role="button" data-toggle="modal" class="btn btn-primary">
						<i class="fa fa-file-text"></i><?php echo JText::_('Submit a Query') ?>
					</a>
				</div>
				<?php
			}
			?>
			<script>
				jQuery(document).ready(function ($) {
					$('.querysend-toggle').click(function () {
						$(this).find('[data-toggle="true"]').toggleClass('hidden');
					});
				});
			</script>
			<?php
			$html = ob_get_clean();
		}

		//Output
		if ($html)
		{
			$output = '<div class="sppb-addon sppb-addon-product-price ' . $class . '">';
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
		return array(JURI::base(true) . '/components/com_sppagebuilder/assets/css/sellacious/sl-productstyle.css');
	}

}
