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

class SppagebuilderAddonSL_Product_Wishlist extends SppagebuilderAddons
{

	public function render()
	{

		$class                   = (isset($this->addon->settings->class) && $this->addon->settings->class) ? $this->addon->settings->class : '';
		$title                   = (isset($this->addon->settings->title) && $this->addon->settings->title) ? $this->addon->settings->title : '';
		$heading_selector        = (isset($this->addon->settings->heading_selector) && $this->addon->settings->heading_selector) ? $this->addon->settings->heading_selector : 'h3';
		$show_wishlist_btn       = (isset($this->addon->settings->show_wishlist_btn) && $this->addon->settings->show_wishlist_btn) ? $this->addon->settings->show_wishlist_btn : '0';
		$wishlist_btn_text       = (isset($this->addon->settings->wishlist_btn_text) && $this->addon->settings->wishlist_btn_text) ? $this->addon->settings->wishlist_btn_text : '';
		$wishlist_class          = (isset($this->addon->settings->wishlist_btn_type) && $this->addon->settings->wishlist_btn_type) ? ' sppb-btn-' . $this->addon->settings->wishlist_btn_type : '';
		$wishlist_class          .= (isset($this->addon->settings->wishlist_btn_shape) && $this->addon->settings->wishlist_btn_shape) ? ' sppb-btn-' . $this->addon->settings->wishlist_btn_shape : ' sppb-btn-round';
		$wishlist_class          .= (isset($this->addon->settings->wishlist_btn_appearance) && $this->addon->settings->wishlist_btn_appearance) ? ' sppb-btn-' . $this->addon->settings->wishlist_btn_appearance : '';
		$show_seperator          = (isset($this->addon->settings->show_seperator) && $this->addon->settings->show_seperator) ? $this->addon->settings->show_seperator : '0';
		$show_seperator_position = (isset($this->addon->settings->show_seperator_position) && $this->addon->settings->show_seperator_position) ? $this->addon->settings->show_seperator_position : 'top';

		JHtml::_('ctech.select2');
		JHtml::_('ctech.bootstrap');
		JHtml::_('script','/components/com_sppagebuilder/assets/js/sellacious/fe.view.product.js', true, true);

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
			?>

			<?php
			if ($show_wishlist_btn)
				{?>
				<div class="ctech-wrapper product-wishlist-container"><?php
				if ($user->guest)
				{
				$url   = JRoute::_('index.php?option=com_sellacious&view=product&p=' . $code, false);
				$login = JRoute::_('index.php?option=com_users&view=login&return=' . base64_encode($url), false);
				?>
				<a class="btn-wishlist btn-wishlist-notLoggedIn ctech-text-primary ctech-border-primary" data-guest="true"
				   data-href="<?php echo htmlspecialchars($login) ?>"><i class="fa fa-heart-o"></i></a><?php
				}
				elseif ($helper->wishlist->check($code, null))
				{
					$url = JRoute::_('index.php?option=com_sellacious&view=wishlist', false);
					?>
				<a class="btn-wishlist btn-wishlist-loggedIn ctech-text-danger ctech-border-danger"
				   data-href="<?php echo htmlspecialchars($url) ?>"><i class="fa fa-heart"></i></a><?php
				}
				else
				{
					?>
				<a class="btn-wishlist ctech-text-danger ctech-border-danger"
				   data-item="<?php echo $code ?>"><i class="fa fa-heart-o"></i></a><?php
				} ?>
				</div><?php
			}
			?>
			<div class="clearfix"></div>
			<?php

			if($show_seperator && $seperatorbottom || $seperatorboth){ ?>
				<hr class="isolate">
			<?php }
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

		$wishlistoptions = new stdClass;
		$wishlistoptions->button_type = (isset($this->addon->settings->wishlist_btn_type) && $this->addon->settings->wishlist_btn_type) ? $this->addon->settings->wishlist_btn_type : '';
		$wishlistoptions->button_appearance = (isset($this->addon->settings->wishlist_btn_appearance) && $this->addon->settings->wishlist_btn_appearance) ? $this->addon->settings->wishlist_btn_appearance : '';
		$wishlistoptions->button_color = (isset($this->addon->settings->wishlist_btn_color) && $this->addon->settings->wishlist_btn_color) ? $this->addon->settings->wishlist_btn_color : '';
		$wishlistoptions->button_color_hover = (isset($this->addon->settings->wishlist_btn_color_hover) && $this->addon->settings->wishlist_btn_color_hover) ? $this->addon->settings->wishlist_btn_color_hover : '';
		$wishlistoptions->button_background_color = (isset($this->addon->settings->wishlist_btn_background_color) && $this->addon->settings->wishlist_btn_background_color) ? $this->addon->settings->wishlist_btn_background_color : '';
		$wishlistoptions->button_background_color_hover = (isset($this->addon->settings->wishlist_btn_background_color_hover) && $this->addon->settings->wishlist_btn_background_color_hover) ? $this->addon->settings->wishlist_btn_background_color_hover : '';
		$wishlistoptions->button_fontstyle = (isset($this->addon->settings->wishlist_btn_fontstyle) && $this->addon->settings->wishlist_btn_fontstyle) ? $this->addon->settings->wishlist_btn_fontstyle : '';
		$wishlistoptions->button_letterspace = (isset($this->addon->settings->wishlist_btn_letterspace) && $this->addon->settings->wishlist_btn_letterspace) ? $this->addon->settings->wishlist_btn_letterspace : '';

		$wishlistborder      = (isset($this->addon->settings->wishlist_btn_border) && $this->addon->settings->wishlist_btn_border) ? $this->addon->settings->wishlist_btn_border : '0';
		$wishlistborderstyle = (isset($this->addon->settings->wishlist_btn_border_style) && $this->addon->settings->wishlist_btn_border_style) ? 'border-style: ' . $this->addon->settings->wishlist_btn_border_style . ';' : '';
		$wishlistborderstyle .= (isset($this->addon->settings->wishlist_btn_border_color) && $this->addon->settings->wishlist_btn_border_color) ? 'border-color: ' . $this->addon->settings->wishlist_btn_border_color . ';' : '';
		$wishlistborderstyle .= (isset($this->addon->settings->wishlist_btn_border_width) && $this->addon->settings->wishlist_btn_border_width) ? 'border-width: ' . (int) $this->addon->settings->wishlist_btn_border_width . 'px;' : '';

		$css = '';
		$css .= $css_path->render(array('addon_id' => $addon_id, 'options' => $wishlistoptions, 'id' => 'btn-wishlist'));

		if($wishlistborder) {
			$css .= $addon_id . ' #btn-wishlist {' . $wishlistborderstyle .'}';
			$css .= $addon_id . ' #btn-wishlist:hover,' . $addon_id . ' #btn-wishlist:focus {';
			$css .= 'border-color:' . $wishlistoptions->button_background_color_hover .'}';
			$css .= $addon_id . ' #btn-wishlist:focus {';
			$css .= 'background-color:' . $wishlistoptions->button_background_color_hover. ';';
			$css .= 'color:' . $wishlistoptions->button_color_hover. '}';
		}

		return $css;
	}

	public function stylesheets()
	{
		return array(JURI::base(true) . '/components/com_sppagebuilder/assets/css/sellacious/sl-product-wishlist.css');
	}
}
