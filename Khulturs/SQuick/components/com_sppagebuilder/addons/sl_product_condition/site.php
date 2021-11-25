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

class SppagebuilderAddonSL_Product_Condition extends SppagebuilderAddons
{

	public function render()
	{

		$class            = (isset($this->addon->settings->class) && $this->addon->settings->class) ? $this->addon->settings->class : '';
		$title            = (isset($this->addon->settings->title) && $this->addon->settings->title) ? $this->addon->settings->title : '';
		$heading_selector = (isset($this->addon->settings->heading_selector) && $this->addon->settings->heading_selector) ? $this->addon->settings->heading_selector : 'h3';

		$app     = JFactory::getApplication();
		$jInput  = $app->input;
		$product = $jInput->getInt('product');
		$seller  = $jInput->getInt('s');
		$html    = '';
		$helper  = SellaciousHelper::getInstance();


		if ($seller)
		{
			$prodHelper  = new Sellacious\Product($product, 0, $seller);
			$seller_attr = $prodHelper->getSellerAttributes($seller);
			$result      = $helper->product->getItem($product);

			ob_start();
			?>
			<div class="seller-info">
				<?php $allowed_listing_type = (array) $helper->config->get('allowed_listing_type'); ?>
				<?php if ((count($allowed_listing_type) != 1) && $result->type != 'electronic'): ?>
					<span class="label label-info"><?php echo JText::_('COM_SELLACIOUS_PRODUCT_CONDITION'); ?>
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
			<?php
			$html = ob_get_clean();
		}

		//Output
		if ($html)
		{
			$output = '<div class="sppb-addon sppb-addon-product-condition ' . $class . '">';
			$output .= ($title) ? '<' . $heading_selector . ' class="sppb-addon-title">' . $title . '</' . $heading_selector . '>' : '';
			$output .= '<div class="sppb-addon-content">';
			$output .= $html;
			$output .= '</div>';
			$output .= '</div>';

			return $output;
		}

		return;
	}

}
