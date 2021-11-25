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

class SppagebuilderAddonSL_Product_Images extends SppagebuilderAddons
{

	public function render()
	{

		$class = (isset($this->addon->settings->class) && $this->addon->settings->class) ? $this->addon->settings->class : '';
		$title = (isset($this->addon->settings->title) && $this->addon->settings->title) ? $this->addon->settings->title : '';
		$heading_selector = (isset($this->addon->settings->heading_selector) && $this->addon->settings->heading_selector) ? $this->addon->settings->heading_selector : 'h3';

		$product_zoom_enabled = (isset($this->addon->settings->product_zoom_enabled) && $this->addon->settings->product_zoom_enabled) ? $this->addon->settings->product_zoom_enabled : 'true';
		$product_zoom_type = (isset($this->addon->settings->product_zoom_type) && $this->addon->settings->product_zoom_type) ? $this->addon->settings->product_zoom_type : 'lens';
		$product_zoom_border_width = (isset($this->addon->settings->product_zoom_border_width) && $this->addon->settings->product_zoom_border_width) ? $this->addon->settings->product_zoom_border_width : '8';
		$product_zoom_border_color = (isset($this->addon->settings->product_zoom_border_color) && $this->addon->settings->product_zoom_border_color) ? $this->addon->settings->product_zoom_border_color : 'rgba(0, 0, 0, 0.1)';
		$product_zoom_size = (isset($this->addon->settings->product_zoom_size) && $this->addon->settings->product_zoom_size) ? $this->addon->settings->product_zoom_size : '200';

		$app     = JFactory::getApplication();
		$jInput  = $app->input;
		$product = $jInput->getInt('product');
		$variant = $jInput->getInt('v');
		$html    = '';

		$helper = SellaciousHelper::getInstance();

		//Options
		if ($product)
		{
			JHtml::_('script', 'com_sellacious/jquery.ez-plus.js', true, true);

			$p_info = $helper->product->getItem($product);

			$doc = JFactory::getDocument();
			$doc->setTitle($p_info->title);

			$images = $helper->product->getImages($product, $variant);

			$imH = (int) $helper->config->get('image_slider_height', 270);
			$imW = (int) $helper->config->get('image_slider_width', 270);
			$imA = (int) $helper->config->get('image_slider_size_adjust') ?: 'contain';

			?>

			<script>
				(function($){

					$(function(){
						var $slider = $('.products-slider-detail');
						var item_count = $slider.find('a').length;

						if (item_count == 1) $slider.addClass('onethumb');

						$slider.owlCarousel({
							margin: 5,
							dots: false,
							autoWidth: true,
							nav: item_count >= 6,
							mouseDrag: item_count >= 6,
							touchDrag: item_count >= 6,
							navText: ['<i class="fa fa-angle-left"></i>', '<i class="fa fa-angle-right"></i>'],
							responsive: {
								0: {items: 5}
							}
						});

						var $image = $('.image-detail .product-img');

						$('.products-slider-detail a').click(function () {
							var $thumb = $(this).find('.thumb-img');
							var src = $thumb.attr('data-src');
							var srcZ = $thumb.attr('data-zoom-image');
							$image.css('background-image', 'url("' + src + '")');

							var EZP = $image.data('ezPlus');
							if (EZP) EZP.swaptheimage(src, srcZ);
							return false;
						});

						$image.ezPlus({
							attrImageZoomSrc: 'zoom-image', // attribute to plugin use for zoom
							borderColour: '<?php echo $product_zoom_border_color ?>',
							borderSize: <?php echo $product_zoom_border_width ?>,
							cursor: 'inherit', // user should set to what they want the cursor as, if they have set a click function
							easing: true,
							easingAmount: 10,
							zoomType: 'lens',
							lensShape: 'round', // add option
							lensSize: <?php echo $product_zoom_size ?>,
							lensFadeIn: 300,
							lensFadeOut: 300,
							enabled: <?php echo $product_zoom_enabled ?>,
							scrollZoom: false,
							scrollZoomIncrement: 0.1,  //steps of the scrollzoom
							zIndex: 92,
							showLens: true,
							responsive: true,
							respond: [
								{
									range: '581-991',
									zoomType: 'lens',
									lensShape: 'round',
									lensSize: 260

								},
								{
									range: '0-580',
									zoomType: 'lens',
									lensShape: 'round',
									lensSize: 180

								}
							]
						});
					});

				})(jQuery);
			</script>
			<?php
			ob_start();
			?>
			<style>
				<?php if($product_zoom_type == 'lens'):?>
					.zoomContainer {
						overflow: hidden;
					}
				<?php endif; ?>

				.image-detail .product-img {
					height: <?php echo $imH ?>px;
					min-width: <?php echo $imW ?>px;
					width: 100%;
					background-size: <?php echo $imA ?>;
					background-repeat: no-repeat;
					background-position: center;
				}
			</style>
			<div id="product-images-container">
				<div class="productdetail-img">
					<?php $image = reset($images) ?>
					<div class="image-detail">
						<div class="product-img" style="background-image: url(<?php echo $image ?>);" data-zoom-image="<?php echo $image ?>"
							 data-src="<?php echo htmlspecialchars($image) ?>"></div>
					</div>
					<div class="products-slider-detail owl-carousel owl-theme">
						<?php foreach ($images as $i => $image): ?>
							<a href="#"><span class="thumb-img" style="background-image: url(<?php echo $image ?>);" data-zoom-image="<?php echo $image ?>"
								data-src="<?php echo htmlspecialchars($image) ?>"></span></a>
						<?php endforeach; ?>
					</div>
				</div>
			</div>
			<?php
			$html = ob_get_clean();
		}

		//Output
		if ($html)
		{
			$output = '<div class="sppb-addon sppb-addon-product-img ' . $class . '">';
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
		return array(
			JURI::base(true) . '/components/com_sppagebuilder/assets/css/sellacious/owl.carousel.min.css',
			JURI::base(true) . '/components/com_sppagebuilder/assets/css/sellacious/sl-detail.image.css'
		);

	}

	public function scripts()
	{
		return array(
			JURI::base(true) . '/components/com_sppagebuilder/assets/js/sellacious/owl.carousel.js'
		);
	}

}
