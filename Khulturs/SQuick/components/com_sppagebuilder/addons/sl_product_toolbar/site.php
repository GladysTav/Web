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

class SppagebuilderAddonSL_Product_Toolbar extends SppagebuilderAddons
{

	public function render()
	{

		$class                   = (isset($this->addon->settings->class) && $this->addon->settings->class) ? $this->addon->settings->class : '';
		$title                   = (isset($this->addon->settings->title) && $this->addon->settings->title) ? $this->addon->settings->title : '';
		$heading_selector        = (isset($this->addon->settings->heading_selector) && $this->addon->settings->heading_selector) ? $this->addon->settings->heading_selector : 'h3';
		$show_review_btn         = (isset($this->addon->settings->show_review_btn) && $this->addon->settings->show_review_btn) ? $this->addon->settings->show_review_btn : '0';
		$review_btn_text         = (isset($this->addon->settings->review_btn_text) && $this->addon->settings->review_btn_text) ? $this->addon->settings->review_btn_text : '';
		$review_class            = (isset($this->addon->settings->review_btn_type) && $this->addon->settings->review_btn_type) ? ' sppb-btn-' . $this->addon->settings->review_btn_type : '';
		$review_class            .= (isset($this->addon->settings->review_btn_shape) && $this->addon->settings->review_btn_shape) ? ' sppb-btn-' . $this->addon->settings->review_btn_shape : ' sppb-btn-round';
		$review_class            .= (isset($this->addon->settings->addtocart_btn_appearance) && $this->addon->settings->review_btn_appearance) ? ' sppb-btn-' . $this->addon->settings->review_btn_appearance : '';
		$show_compare_btn        = (isset($this->addon->settings->show_compare_btn) && $this->addon->settings->show_compare_btn) ? $this->addon->settings->show_compare_btn : '0';
		$compare_btn_text        = (isset($this->addon->settings->compare_btn_text) && $this->addon->settings->compare_btn_text) ? $this->addon->settings->compare_btn_text : '';
		$compare_class           = (isset($this->addon->settings->compare_btn_type) && $this->addon->settings->compare_btn_type) ? ' sppb-btn-' . $this->addon->settings->compare_btn_type : '';
		$compare_class           .= (isset($this->addon->settings->compare_btn_shape) && $this->addon->settings->compare_btn_shape) ? ' sppb-btn-' . $this->addon->settings->compare_btn_shape : ' sppb-btn-round';
		$compare_class           .= (isset($this->addon->settings->compare_btn_appearance) && $this->addon->settings->compare_btn_appearance) ? ' sppb-btn-' . $this->addon->settings->compare_btn_appearance : '';
		$show_seperator          = (isset($this->addon->settings->show_seperator) && $this->addon->settings->show_seperator) ? $this->addon->settings->show_seperator : '0';
		$show_seperator_position = (isset($this->addon->settings->show_seperator_position) && $this->addon->settings->show_seperator_position) ? $this->addon->settings->show_seperator_position : 'top';

		$app     = JFactory::getApplication();
		$jInput  = $app->input;
		$product = $jInput->getInt('product');
		$seller  = $jInput->getInt('s');
		$variant = $jInput->getInt('v');
		$html    = '';

		$helper = SellaciousHelper::getInstance();

		//Options
		if ($product)
		{
			if (empty($seller))
			{
				$seller = $helper->product->getSellers($product, false);
				$seller = $seller[0]->seller_uid;
			}
			$code    = $helper->product->getCode($product, $variant, $seller);
			$user    = JFactory::getUser();
			$page_id = 'product';

			ob_start();

				$seperatorposition = array($show_seperator_position);
				$seperatortop      = in_array('top', $seperatorposition);
				$seperatorbottom   = in_array('bottom', $seperatorposition);
				$seperatorboth     = in_array('both', $seperatorposition);

				if($show_seperator && $seperatortop || $seperatorboth){
					echo '<hr class="isolate">';
				}
			?>
			<div class="moreaction">
				<input type="hidden" name="<?php echo JSession::getFormToken() ?>" id="formToken" value="1" />
				<?php if ($show_compare_btn && $show_compare_btn && ($compare_btn_text != ''))
				{ ?>
					<?php if ($helper->config->get('product_compare') && in_array($page_id, (array) $helper->config->get('product_compare_display'))): ?>
					<button type="button" id="btn-compare" class="btn-compare fa fa-copy <?php echo $compare_class ?>"
						data-item="<?php echo $code ?>"><h5>Add to Compare</h5><h5>Remove from Compare</h5>
					</button>
				<?php endif; ?>
					<?php if ($show_review_btn && $show_review_btn && ($review_btn_text != ''))
					{ ?>
					<button type="button" id="btn-review" class="btn-review fa fa-edit <?php echo $review_class ?>"
						data-item="<?php echo $code ?>"><h5><?php echo $review_btn_text ?></h5></button>
					<?php
					} ?>
				<?php
				} ?>
			</div>
			<div class="clearfix"></div>
			<?php

			if($show_seperator && $seperatorbottom || $seperatorboth)
			{ ?>
				<hr class="isolate"><?php
			}
			$html = ob_get_clean();
		}

		//Output
		if ($html)
		{
			$output = '<div class="sppb-addon sppb-addon-product-toolbar ' . $class . '">';
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

		$reviewoptions = new stdClass;
		$reviewoptions->button_type = (isset($this->addon->settings->review_btn_type) && $this->addon->settings->review_btn_type) ? $this->addon->settings->review_btn_type : '';
		$reviewoptions->button_appearance = (isset($this->addon->settings->review_btn_appearance) && $this->addon->settings->review_btn_appearance) ? $this->addon->settings->review_btn_appearance : '';
		$reviewoptions->button_color = (isset($this->addon->settings->review_btn_color) && $this->addon->settings->review_btn_color) ? $this->addon->settings->review_btn_color : '';
		$reviewoptions->button_color_hover = (isset($this->addon->settings->review_btn_color_hover) && $this->addon->settings->review_btn_color_hover) ? $this->addon->settings->review_btn_color_hover : '';
		$reviewoptions->button_background_color = (isset($this->addon->settings->review_btn_background_color) && $this->addon->settings->review_btn_background_color) ? $this->addon->settings->review_btn_background_color : '';
		$reviewoptions->button_background_color_hover = (isset($this->addon->settings->review_btn_background_color_hover) && $this->addon->settings->review_btn_background_color_hover) ? $this->addon->settings->review_btn_background_color_hover : '';
		$reviewoptions->button_fontstyle = (isset($this->addon->settings->review_btn_fontstyle) && $this->addon->settings->review_btn_fontstyle) ? $this->addon->settings->review_btn_fontstyle : '';
		$reviewoptions->button_letterspace = (isset($this->addon->settings->review_btn_letterspace) && $this->addon->settings->review_btn_letterspace) ? $this->addon->settings->review_btn_letterspace : '';

		$compareoptions = new stdClass;
		$compareoptions->button_type = (isset($this->addon->settings->compare_btn_type) && $this->addon->settings->compare_btn_type) ? $this->addon->settings->compare_btn_type : '';
		$compareoptions->button_appearance = (isset($this->addon->settings->compare_btn_appearance) && $this->addon->settings->compare_btn_appearance) ? $this->addon->settings->compare_btn_appearance : '';
		$compareoptions->button_color = (isset($this->addon->settings->compare_btn_color) && $this->addon->settings->compare_btn_color) ? $this->addon->settings->compare_btn_color : '';
		$compareoptions->button_color_hover = (isset($this->addon->settings->compare_btn_color_hover) && $this->addon->settings->compare_btn_color_hover) ? $this->addon->settings->compare_btn_color_hover : '';
		$compareoptions->button_background_color = (isset($this->addon->settings->compare_btn_background_color) && $this->addon->settings->compare_btn_background_color) ? $this->addon->settings->compare_btn_background_color : '';
		$compareoptions->button_background_color_hover = (isset($this->addon->settings->compare_btn_background_color_hover) && $this->addon->settings->compare_btn_background_color_hover) ? $this->addon->settings->compare_btn_background_color_hover : '';
		$compareoptions->button_fontstyle = (isset($this->addon->settings->compare_btn_fontstyle) && $this->addon->settings->compare_btn_fontstyle) ? $this->addon->settings->compare_btn_fontstyle : '';
		$compareoptions->button_letterspace = (isset($this->addon->settings->compare_btn_letterspace) && $this->addon->settings->compare_btn_letterspace) ? $this->addon->settings->compare_btn_letterspace : '';

		$reviewborder      = (isset($this->addon->settings->review_btn_border) && $this->addon->settings->review_btn_border) ? $this->addon->settings->review_btn_border : '0';
		$reviewborderstyle = (isset($this->addon->settings->review_btn_border_style) && $this->addon->settings->review_btn_border_style) ? 'border-style: ' . $this->addon->settings->review_btn_border_style . ';' : '';
		$reviewborderstyle .= (isset($this->addon->settings->review_btn_border_color) && $this->addon->settings->review_btn_border_color) ? 'border-color: ' . $this->addon->settings->review_btn_border_color . ';' : '';
		$reviewborderstyle .= (isset($this->addon->settings->review_btn_border_width) && $this->addon->settings->review_btn_border_width) ? 'border-width: ' . (int) $this->addon->settings->review_btn_border_width . 'px;' : '';

		$compareborder      = (isset($this->addon->settings->compare_btn_border) && $this->addon->settings->compare_btn_border) ? $this->addon->settings->compare_btn_border : '0';
		$compareborderstyle = (isset($this->addon->settings->compare_btn_border_style) && $this->addon->settings->compare_btn_border_style) ? 'border-style: ' . $this->addon->settings->compare_btn_border_style . ';' : '';
		$compareborderstyle .= (isset($this->addon->settings->compare_btn_border_color) && $this->addon->settings->compare_btn_border_color) ? 'border-color: ' . $this->addon->settings->compare_btn_border_color . ';' : '';
		$compareborderstyle .= (isset($this->addon->settings->compare_btn_border_width) && $this->addon->settings->compare_btn_border_width) ? 'border-width: ' . (int) $this->addon->settings->compare_btn_border_width . 'px;' : '';

		$css = '';
		$css .= $css_path->render(array('addon_id' => $addon_id, 'options' => $reviewoptions, 'id' => 'btn-review'));
		$css .= $css_path->render(array('addon_id' => $addon_id, 'options' => $compareoptions, 'id' => 'btn-compare'));

		if($reviewborder) {
			$css .= $addon_id . ' #btn-review {' . $reviewborderstyle .'}';
			$css .= $addon_id . ' #btn-review:hover,' . $addon_id . ' #btn-review:focus {';
			$css .= 'border-color:' . $reviewoptions->button_background_color_hover . '}';
			$css .= $addon_id . ' #btn-review:focus {';
			$css .= 'background-color:' . $reviewoptions->button_background_color_hover. ';';
			$css .= 'color:' . $reviewoptions->button_color_hover .'}';
		}
		if($compareborder) {
			$css .= $addon_id . ' #btn-compare {' . $compareborderstyle . '}';
			$css .= $addon_id . ' #btn-compare:hover,' . $addon_id . ' #btn-compare:focus {';
			$css .= 'border-color:' . $compareoptions->button_background_color_hover . '}';
			$css .= $addon_id . ' #btn-compare:focus {';
			$css .= 'background-color:' . $compareoptions->button_background_color_hover. ';';
			$css .= 'color:' . $compareoptions->button_color_hover. '}';
		}

		return $css;
	}

	public function stylesheets()
	{
		return array(JURI::base(true) . '/components/com_sppagebuilder/assets/css/sellacious/sl-product-toolbar.css');
	}

	public function scripts()
	{
		$site = JFactory::getApplication();

		return array(
			JURI::base(true) . '/components/com_sppagebuilder/assets/js/sellacious/sl-product-compare.js',
			JURI::base(true) . '/components/com_sppagebuilder/assets/js/sellacious/sl-product-review.js',
		);
	}
}
