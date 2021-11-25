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
use Joomla\Registry\Registry;
use Sellacious\Cache\Reader\ProductsCacheReader;
use Sellacious\Price\PriceHelper;

defined('_JEXEC') or die ('restricted aceess');

class SppagebuilderAddonSL_Product_CartButtons extends SppagebuilderAddons
{

	public function render()
	{

		$class              = (isset($this->addon->settings->class) && $this->addon->settings->class) ? $this->addon->settings->class : '';
		$title              = (isset($this->addon->settings->title) && $this->addon->settings->title) ? $this->addon->settings->title : '';
		$heading_selector   = (isset($this->addon->settings->heading_selector) && $this->addon->settings->heading_selector) ? $this->addon->settings->heading_selector : 'h3';
		$show_addtocart_btn = (isset($this->addon->settings->show_addtocart_btn) && $this->addon->settings->show_addtocart_btn) ? $this->addon->settings->show_addtocart_btn : '0';
		$addtocart_btn_text = (isset($this->addon->settings->addtocart_btn_text) && $this->addon->settings->addtocart_btn_text) ? $this->addon->settings->addtocart_btn_text : '';
		$addtocart_class 	= (isset($this->addon->settings->addtocart_btn_type) && $this->addon->settings->addtocart_btn_type) ? ' sppb-btn-' . $this->addon->settings->addtocart_btn_type : '';
		$addtocart_class   .= (isset($this->addon->settings->addtocart_btn_shape) && $this->addon->settings->addtocart_btn_shape) ? ' sppb-btn-' . $this->addon->settings->addtocart_btn_shape : ' sppb-btn-round';
		$addtocart_class   .= (isset($this->addon->settings->addtocart_btn_appearance) && $this->addon->settings->addtocart_btn_appearance) ? ' sppb-btn-' . $this->addon->settings->addtocart_btn_appearance : '';
		$show_buynow_btn    = (isset($this->addon->settings->show_buynow_btn) && $this->addon->settings->show_buynow_btn) ? $this->addon->settings->show_buynow_btn : '0';
		$buynow_btn_text 	= (isset($this->addon->settings->buynow_btn_text) && $this->addon->settings->buynow_btn_text) ? $this->addon->settings->buynow_btn_text : '';
		$buynow_class 		= (isset($this->addon->settings->buynow_btn_type) && $this->addon->settings->buynow_btn_type) ? ' sppb-btn-' . $this->addon->settings->buynow_btn_type : '';
		$buynow_class      .= (isset($this->addon->settings->buynow_btn_shape) && $this->addon->settings->buynow_btn_shape) ? ' sppb-btn-' . $this->addon->settings->buynow_btn_shape : ' sppb-btn-round';
		$buynow_class      .= (isset($this->addon->settings->buynow_btn_appearance) && $this->addon->settings->buynow_btn_appearance) ? ' sppb-btn-' . $this->addon->settings->buynow_btn_appearance : '';
		$show_seperator          = (isset($this->addon->settings->show_seperator) && $this->addon->settings->show_seperator) ? $this->addon->settings->show_seperator : '0';
		$show_seperator_position = (isset($this->addon->settings->show_seperator_position) && $this->addon->settings->show_seperator_position) ? $this->addon->settings->show_seperator_position : 'top';

		JHtml::_('script', 'com_sellacious/fe.view.product.js', false, true);
		JHtml::_('stylesheet', 'sellacious/ctech-bootstrap.css', false, true);
		JHtml::_('script', 'sellacious/ctech-bootstrap.js', false, true);

		$app     = JFactory::getApplication();
		$input  = $app->input;
		$product = $input->getInt('product');
		$seller  = $input->getInt('s');
		$variant = $input->getInt('v');
		$html    = '';

		$helper = SellaciousHelper::getInstance();

		//Options
		$prodHelper = new Sellacious\Product($product, $variant, $seller);
		$seller_attr = $prodHelper->getSellerAttributes($seller);
		$priceHandler  = PriceHelper::getHandler($seller_attr->pricing_type);

		$loader = new ProductsCacheReader;

		$loader->filterValue('product_id', $product, '=');

		$item = $loader->getItem();

		ob_start();
		?>
			<div class="ctech-wrapper cart-buttons">
					<?php echo $priceHandler->renderLayout('checkout-buttons.default', new Registry($item)); ?>
			</div>
			<div class="clearfix"></div>
		<?php
		$html = ob_get_clean();

		//Output
		if ($html)
		{
			$output = '<div class="sppb-addon sppb-addon-product-cartbuttons ' . $class . '">';
			$output .= ($title) ? '<' . $heading_selector . ' class="sppb-addon-title">' . $title . '</' . $heading_selector . '>' : '';
			$output .= '<div class="sppb-addon-content">';
			$output .= $html;
			$output .= '</div>';
			$output .= '</div>';

			return $output;
		}

		return;
	}

	public function css() {

		$addon_id = '#sppb-addon-' .$this->addon->id;
		$layout_path = JPATH_ROOT . '/components/com_sppagebuilder/layouts';

		$css_path = new JLayoutFile('addon.css.button', $layout_path);

		$cartoptions = new stdClass;
		$cartoptions->button_type = (isset($this->addon->settings->addtocart_btn_type) && $this->addon->settings->addtocart_btn_type) ? $this->addon->settings->addtocart_btn_type : '';
		$cartoptions->button_appearance = (isset($this->addon->settings->addtocart_btn_appearance) && $this->addon->settings->addtocart_btn_appearance) ? $this->addon->settings->addtocart_btn_appearance : '';
		$cartoptions->button_color = (isset($this->addon->settings->addtocart_btn_color) && $this->addon->settings->addtocart_btn_color) ? $this->addon->settings->addtocart_btn_color : '';
		$cartoptions->button_color_hover = (isset($this->addon->settings->addtocart_btn_color_hover) && $this->addon->settings->addtocart_btn_color_hover) ? $this->addon->settings->addtocart_btn_color_hover : '';
		$cartoptions->button_background_color = (isset($this->addon->settings->addtocart_btn_background_color) && $this->addon->settings->addtocart_btn_background_color) ? $this->addon->settings->addtocart_btn_background_color : '';
		$cartoptions->button_background_color_hover = (isset($this->addon->settings->addtocart_btn_background_color_hover) && $this->addon->settings->addtocart_btn_background_color_hover) ? $this->addon->settings->addtocart_btn_background_color_hover : '';
		$cartoptions->button_fontstyle = (isset($this->addon->settings->addtocart_btn_fontstyle) && $this->addon->settings->addtocart_btn_fontstyle) ? $this->addon->settings->addtocart_btn_fontstyle : '';
		$cartoptions->button_letterspace = (isset($this->addon->settings->addtocart_btn_letterspace) && $this->addon->settings->addtocart_btn_letterspace) ? $this->addon->settings->addtocart_btn_letterspace : '';

		$buyoptions = new stdClass;
		$buyoptions->button_type = (isset($this->addon->settings->buynow_btn_type) && $this->addon->settings->buynow_btn_type) ? $this->addon->settings->buynow_btn_type : '';
		$buyoptions->button_appearance = (isset($this->addon->settings->buynow_btn_appearance) && $this->addon->settings->buynow_btn_appearance) ? $this->addon->settings->buynow_btn_appearance : '';
		$buyoptions->button_color = (isset($this->addon->settings->buynow_btn_color) && $this->addon->settings->buynow_btn_color) ? $this->addon->settings->buynow_btn_color : '';
		$buyoptions->button_color_hover = (isset($this->addon->settings->buynow_btn_color_hover) && $this->addon->settings->buynow_btn_color_hover) ? $this->addon->settings->buynow_btn_color_hover : '';
		$buyoptions->button_background_color = (isset($this->addon->settings->buynow_btn_background_color) && $this->addon->settings->buynow_btn_background_color) ? $this->addon->settings->buynow_btn_background_color : '';
		$buyoptions->button_background_color_hover = (isset($this->addon->settings->buynow_btn_background_color_hover) && $this->addon->settings->buynow_btn_background_color_hover) ? $this->addon->settings->buynow_btn_background_color_hover : '';
		$buyoptions->button_fontstyle = (isset($this->addon->settings->buynow_btn_fontstyle) && $this->addon->settings->buynow_btn_fontstyle) ? $this->addon->settings->buynow_btn_fontstyle : '';
		$buyoptions->button_letterspace = (isset($this->addon->settings->buynow_btn_letterspace) && $this->addon->settings->buynow_btn_letterspace) ? $this->addon->settings->buynow_btn_letterspace : '';

		$css = '';
		$css .= $css_path->render(array('addon_id' => $addon_id, 'options' => $cartoptions, 'id' => 'btn-add-to-cart'));
		$css .= $css_path->render(array('addon_id' => $addon_id, 'options' => $buyoptions, 'id' => 'btn-buy-now'));

		return $css;
	}

	public function stylesheets()
	{
		return array(JURI::base(true) . '/components/com_sppagebuilder/assets/css/sellacious/sl-productstyle.css');

	}
}
