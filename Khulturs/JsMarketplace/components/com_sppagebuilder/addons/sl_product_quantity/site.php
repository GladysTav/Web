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

class SppagebuilderAddonSL_Product_Quantity extends SppagebuilderAddons
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
			if (empty($seller))
			{
				$seller = $helper->product->getSellers($product, false);
				$seller = $seller[0]->seller_uid;
			}

			$prodHelper  = new Sellacious\Product($product, $variant, $seller);
			$seller_attr = $prodHelper->getSellerAttributes($seller);

			if (!is_object($seller_attr))
			{
				$seller_attr                 = new stdClass();
				$seller_attr->stock_capacity = 0;
			}

			ob_start();
			?>
			<div class="product-quantity">
				<div class="w100p">
					<?php if ($seller_attr->stock_capacity > 0)
					{ ?>
						<div class="quantitybox">
							<label><?php echo JText::_('COM_SELLACIOUS_PRODUCT_BUY_QUANTITY_INPUT_LABEL'); ?>
								<input type="number" name="quantity" id="product-quantity" min="1" max="100" value="1" />
							</label>
						</div>
					<?php }
					else
					{ ?>
						<div class="label btn-primary outofstock"><?php echo JText::_('COM_SELLACIOUS_PRODUCT_OUT_OF_STOCK') ?></div>
					<?php }
					?>
				</div>
			</div>
			<?php
			$html = ob_get_clean();
		}

		//Output
		if ($html)
		{
			$output = '<div class="sppb-addon sppb-addon-product-quantity ' . $class . '">';
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

