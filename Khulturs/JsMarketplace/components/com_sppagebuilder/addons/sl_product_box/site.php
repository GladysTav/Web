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

use Sellacious\Product;
use Sellacious\Seller;

class SppagebuilderAddonSL_Product_Box extends SppagebuilderAddons
{

	public function render()
	{

		$class            = (isset($this->addon->settings->class) && $this->addon->settings->class) ? $this->addon->settings->class : '';
		$title            = (isset($this->addon->settings->title) && $this->addon->settings->title) ? $this->addon->settings->title : '';
		$heading_selector = (isset($this->addon->settings->heading_selector) && $this->addon->settings->heading_selector) ? $this->addon->settings->heading_selector : 'h3';
		$box_title  	  = (isset($this->addon->settings->box_title) && $this->addon->settings->box_title) ? $this->addon->settings->box_title : '';

		$app     = JFactory::getApplication();
		$input  = $app->input;
		$product = $input->getInt('product');
		$seller  = $input->getInt('s');
		$html    = '';

		if ($seller)
		{
			$prodHelper = new Sellacious\Product($product, 0, $seller);

			$helper	 = SellaciousHelper::getInstance();
			$package_items = $helper->package->getProducts($product, true);

			$seller_attr = $prodHelper->getSellerAttributes($seller);
			$in_box      = $seller_attr->whats_in_box;

			ob_start();
			if ($in_box)
			{
				?>
				<div class="moreinfo-box">
					<?php echo ($box_title) ?'<h3>' . $box_title . '</h3>' : ''; ?>
					<div class="innermoreinfo">
						<div class="specificationgroup"><?php echo $in_box ?></div>
					</div>
				</div>
				<?php
			}
			if ($package_items)
			{	?>
				<div class="moreinfo-box">
					<?php echo ($box_title) ?'<h3>' . $box_title . '</h3>' : ''; ?>
					<div class="innermoreinfo">
						<div class="packages-items specificationgroup">
							<?php
							foreach ($package_items as $item)
							{
								$paths = $helper->product->getImages($item->product_id, $item->variant_id);
								$code  = $helper->product->getCode($item->product_id, $item->variant_id, $seller);
								$url   = JRoute::_('index.php?option=com_sellacious&view=product&p=' . $code);

								$link_detail = $helper->config->get('product_detail_page');
								?>
								<div class="product-box">
									<div class="image-box">
										<?php if ($link_detail): ?>
										<a href="<?php echo $url ?>">
											<?php else: ?>
											<a>
												<?php endif; ?>
												<img src="<?php echo reset($paths) ?>" title="<?php echo htmlspecialchars($item->product_title) ?>"/>
											</a>
									</div>
									<div class="product-info-box">
										<h3 class="product-title"><?php
											if ($link_detail): ?>
											<a href="<?php echo $url ?>">
												<?php else: ?>
												<a>
													<?php endif;
													echo $item->product_title; ?>
													<?php if ($item->variant_title): ?>
														<small><?php echo $item->variant_title ?></small>
													<?php endif ?>
												</a>
										</h3>
										<?php if (($item->product_sku) || ($item->variant_sku)): ?>
											<div class="product-sku-info">
												<strong>SKU:</strong> <?php echo $item->product_sku; ?>
												<?php if ($item->variant_sku): ?>
													- <small><?php echo $item->variant_sku; ?></small>
												<?php endif; ?>
											</div>
										<?php endif;?>
									</div>
									<div class="clearfix"></div>
								</div>
								<?php
							}
							?>
						</div>
					</div>
				</div>
				<?php
			}
			$html = ob_get_clean();
		}

		//Output
		if ($html)
		{
			$output = '<div class="sppb-addon sppb-addon-product-box ' . $class . '">';
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
