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
use Sellacious\Seller;

class SppagebuilderAddonSL_Product_SoldBy extends SppagebuilderAddons
{

	public function render()
	{

		$class            = (isset($this->addon->settings->class) && $this->addon->settings->class) ? $this->addon->settings->class : '';
		$title            = (isset($this->addon->settings->title) && $this->addon->settings->title) ? $this->addon->settings->title : '';
		$heading_selector = (isset($this->addon->settings->heading_selector) && $this->addon->settings->heading_selector) ? $this->addon->settings->heading_selector : 'h3';
		$show_condition   = (isset($this->addon->settings->show_condition) && $this->addon->settings->show_condition) ? $this->addon->settings->show_condition : '0';
		$show_ratings     = (isset($this->addon->settings->show_ratings) && $this->addon->settings->show_ratings) ? $this->addon->settings->show_ratings : '0';

		$app     = JFactory::getApplication();
		$jInput  = $app->input;
		$product = $jInput->getInt('product');
		$seller  = $jInput->getInt('s');
		$html    = '';
		$helper  = SellaciousHelper::getInstance();

		if ($seller)
		{
			$prodHelper = new Sellacious\Product($product, 0, $seller);

			$sellerHelper = new Seller($seller);
			$sellerProp   = $sellerHelper->getAttributes();
			$seller_attr  = $prodHelper->getSellerAttributes($seller);
			$ratings      = $helper->rating->getSellerRating($seller);
			$result       = $helper->product->getItem($product);

			ob_start();
			?>
			<div class="product-actions">
				<div class="sl-seller-info seller-info">
					<h4>SOLD BY</h4>
					<p><a href="<?php echo JRoute::_('index.php?option=com_sellacious&view=store&id=' . $seller_attr->seller_uid); ?>">
						<?php echo $sellerProp['company'] ?: $sellerProp['name']; ?></a>
						<?php if ($show_ratings) : ?>
							<?php $rating = $ratings->rating; ?>
							<span class="label <?php echo ($rating < 3) ? 'label-warning' : 'label-success' ?>"><?php echo number_format($rating, 1) ?> / 5.0</span>
						<?php endif; ?>
						<?php $allowed_listing_type = (array) $helper->config->get('allowed_listing_type'); ?>
					</p>
					<?php if (count($allowed_listing_type) != 1 && $show_condition && $result->type != 'electronic'): ?>
						<span class="label label-info">Condition:
							<?php
							$list_type = $seller_attr->listing_type;

							// What if this is a not allowed listing type value
							if ($list_type == 1):
								echo JText::plural('COM_SELLACIOUS_PRODUCT_FIELD_LISTING_TYPE_VALUE', $list_type);
							else:
								$list_cond = $seller_attr->item_condition;
								echo JText::plural('COM_SELLACIOUS_PRODUCT_FIELD_ITEM_CONDITION_VALUE', $list_type * 10 + (int) $list_cond);
							endif;
							?>
						</span>
					<?php endif; ?>
				</div>
			</div>
			<?php
			$html = ob_get_clean();
		}

		//Output
		if ($html)
		{
			$output = '<div class="sppb-addon sppb-addon-product-soldby ' . $class . '">';
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
