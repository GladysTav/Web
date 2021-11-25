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

class SppagebuilderAddonSL_Product_Gallery extends SppagebuilderAddons
{

	public function render()
	{

		$class               = (isset($this->addon->settings->class) && $this->addon->settings->class) ? $this->addon->settings->class : '';
		$title               = (isset($this->addon->settings->title) && $this->addon->settings->title) ? $this->addon->settings->title : '';
		$heading_selector    = (isset($this->addon->settings->heading_selector) && $this->addon->settings->heading_selector) ? $this->addon->settings->heading_selector : 'h3';
		$box_title  		 = (isset($this->addon->settings->gallery_title) && $this->addon->settings->gallery_title) ? $this->addon->settings->gallery_title : '';
		$show_product_images = (isset($this->addon->settings->show_product_images) && $this->addon->settings->show_product_images) ? $this->addon->settings->show_product_images : '0';
		$show_variant_attr   = (isset($this->addon->settings->show_variant_attr) && $this->addon->settings->show_variant_attr) ? $this->addon->settings->show_variant_attr : '0';
		$show_thumbs         = (isset($this->addon->settings->show_thumbs) && $this->addon->settings->show_thumbs) ? $this->addon->settings->show_thumbs : '0';
		$show_slide_arrow    = (isset($this->addon->settings->show_slide_arrow) && $this->addon->settings->show_slide_arrow) ? $this->addon->settings->show_slide_arrow : '0';
		$show_slide_dots     = (isset($this->addon->settings->show_slide_dots) && $this->addon->settings->show_slide_dots) ? $this->addon->settings->show_slide_dots : '0';

		$app            = JFactory::getApplication();
		$jInput         = $app->input;
		$product        = $jInput->getInt('product');
		$helper         = SellaciousHelper::getInstance();
		$output         = '';
		$params         = array();
		$variant_images = array();
		$variant_thumb  = array();
		$variant_title  = array();

		if (!empty($product))
		{
			jimport('joomla.filesystem.file');

			$category = $helper->product->getCategories($product);

			if (!empty($category[0]))
			{
				$result = $helper->category->getItem($category[0]);
				$params = json_decode($result->params, true);
			}

			$variants = $helper->product->getVariants($product);

			if ($show_product_images)
			{
				$productImages = $helper->media->getImages('products.images', $product);
				if (!empty($productImages))
				{
					foreach ($productImages as $p => $productImage)
					{
						$img_name = JFile::stripExt(basename($productImage));

						if ($img_name != 'no_image')
						{
							$variant_images[] = $productImage;
							$variant_thumb[]  = $productImage;
						}
					}
				}
			}

			if($show_variant_attr){
				foreach ($variants as $v => $variant)
				{
					$variantImages = $helper->media->getImages('variants.images', $variant->id);

					if (!empty($variantImages))
					{
						foreach ($variantImages as $variantImage)
						{
							$img_name = JFile::stripExt(basename($variantImage));

							if ($img_name != 'no_image')
							{
								$variant_images[] = $variantImage;
								$variant_thumb[]  = $variantImages[0];
								$variant_title[]  = $variant->title;
							}
						}
					}
				}

			}

			$variant_thumb = array_unique($variant_thumb);
			$variant_title = array_unique($variant_title);
		}

		if (!empty($variant_images))
		{
			ob_start();

			?>
		<div class="sppb-addon sppb-addon-sl-product-gallery sl-gallery <?php echo $class ?>">
			<?php if ($title) : ?>
			<<?php echo $heading_selector ?> class="sppb-addon-title"><?php echo $title ?></<?php echo $heading_selector ?>>
		<?php endif; ?>
			<div class="sppb-addon-content">
				<div class="sppb-sl-product-gallery clearfix">
					<?php echo ($box_title) ?'<h3>' . $box_title . '</h3>' : ''; ?>
					<div class="sppb-sl-product-galleryinner">
						<div class="sppb-sl-product-gallery-main">
							<?php foreach ($variant_images as $image) : ?>
								<div class="sl-product-slides">
									<a href="<?php echo $image ?>" class="sppb-sl-product-gallery-btn">
										<img class="sppb-img-responsive" src="<?php echo $image ?>" alt="Product Image">
										<span class="product-img" style="background-image: url(<?php echo $image ?>);"><i class="fa fa-search-plus"></i></span>
									</a>
								</div>
							<?php endforeach; ?>
							<?php if ($show_slide_dots) : ?>
								<div class="image-sl-dot-nav">
									<?php for ($i = 1; $i <= count($variant_images); $i++) : ?>
										<span class="sl-dot btn-primary" onclick="currentSlide(<?php echo $i ?>)"></span>
									<?php endfor; ?>
								</div>
							<?php endif; ?>
							<?php if ($show_slide_arrow) : ?>
								<div class="image-sl-nav-btn">
									<a class="prev" onclick="plusSlides(-1)"><i class="fa fa-angle-left"></i></a>
									<a class="next" onclick="plusSlides(1)"><i class="fa fa-angle-right"></i></a>
								</div>
							<?php endif; ?>
						</div>

						<div class="sl-img-nav nav-bar">
							<?php if ($show_variant_attr && !$show_thumbs && !$show_product_images) : ?>
								<div class="sppb-sl-product-gallery-otherinfo">
									<?php if (isset($params['gallery_thumb_title'])): ?>
										<div class="sl-product-title"><h4><?php echo $params['gallery_thumb_title'] ?></h4>
										</div>
									<?php endif; ?>

									<div class="sl-product-small-title">
										<?php foreach ($variant_title as $v => $variantTitle): ?>
											<span class="v-title" id="v-title<?php echo $v + 1 ?>"><?php echo $variantTitle; ?></span>
										<?php endforeach; ?>
									</div>

									<div class="sl-product-small-thumbs">
										<?php foreach ($variant_thumb as $v => $variantThumb): ?>
											<a href="javascript:void(0)" onclick="thumbSlide(<?php echo $v + 1 ?>)">
												<img class="sppb-img-responsive" src="<?php echo $variantThumb; ?>" alt="Variant Image">
											</a>
										<?php endforeach; ?>
									</div>
								</div>
							<?php endif; ?>

							<?php if ($show_thumbs) : ?>
								<div class="sppb-sl-product-gallery-thumbs">
									<div class="sl-slides-container">
										<div class="owl-carousel sl-thumb-slides">
											<?php foreach ($variant_images as $v => $variantImage): ?>
												<div class="sl-product-thumb">
													<div class="item">
														<a href="javascript:void(0)" onclick="currentSlide(<?php echo $v + 1 ?>)">
															<img class="sppb-img-responsive" src="<?php echo $variantImage; ?>" alt="Variant Image">
														</a>
													</div>
												</div>
											<?php endforeach; ?>
										</div>
									</div>
									<a class="sl-leftarrow"><i class="fa fa-angle-left"></i></a>
									<a class="sl-rightarrow"><i class="fa fa-angle-right"></i></a>
								</div>
							<?php endif; ?>
						</div>
					</div>
				</div>
			</div>
			</div>
			<?php
			$output = ob_get_clean();
		}

		return $output;
	}

	public function stylesheets()
	{
		return array(
			JURI::base(true) . '/components/com_sppagebuilder/assets/css/magnific-popup.css',
			JURI::base(true) . '/components/com_sppagebuilder/assets/css/sellacious/sl-gallery.css',
			JURI::base(true) . '/components/com_sppagebuilder/assets/css/sellacious/owl.carousel.min.css'
		);

	}
	public function scripts()
	{
		return array(
			JURI::base(true) . '/components/com_sppagebuilder/assets/js/jquery.magnific-popup.min.js',
			JURI::base(true) . '/components/com_sppagebuilder/assets/js/sellacious/owl.carousel.js'
		);
	}

	public function js()
	{
		ob_start();
		?>
		var slideIndex = 1;

		function plusSlides(n) {
		showSlides(slideIndex += n);
		}

		function currentSlide(n) {
		showSlides(slideIndex = n);
		}

		function thumbSlide(n) {
		jQuery('.v-title').css('display', 'none');
		jQuery('#v-title' + n).fadeIn("slow");
		showSlides(slideIndex = n);
		}

		function showSlides(n) {
		var i;
		var slides = document.getElementsByClassName("sl-product-slides");
		var dots = document.getElementsByClassName("sl-dot");

		if (n > slides.length) {slideIndex = 1}
		if (n < 1) {slideIndex = slides.length}

		for (i = 0; i < slides.length; i++) {
		slides[i].style.display = "none";
		}

		for (i = 0; i < dots.length; i++) {
		dots[i].className = dots[i].className.replace(" active", "");
		}

		jQuery(slides[slideIndex-1]).fadeIn("slow");

		if (dots.length > 0)
		{
		dots[slideIndex-1].className += " active";
		}
		}

		jQuery(function($){
		$('.v-title').css('display', 'none');
		showSlides(slideIndex);

		var view = $(".sl-thumb-slides");
		var move = "100px";
		var sliderLimit = -750

		$("#right-arrow").click(function(){

		var currentPosition = parseInt(view.css("left"));
		if (currentPosition >= sliderLimit)
		view.stop(false,true).animate({left:"-="+move},{ duration: 400})
		});

		$("#left-arrow").click(function(){

		var currentPosition = parseInt(view.css("left"));
		if (currentPosition < 0)
		view.stop(false,true).animate({left:"+="+move},{ duration: 400})
		});

		$(document).magnificPopup({
		delegate: ".sppb-sl-product-gallery-btn",
		type: "image",
		mainClass: "mfp-no-margins mfp-with-zoom",
		gallery:{
		enabled:true
		},
		image: {
		verticalFit: true
		},
		zoom: {
		enabled: true,
		duration: 300
		}
		});

		var sl_owl = $('.sl-thumb-slides');
		sl_owl.owlCarousel({
		items:4,
		nav : false,
		rewind:true,
		responsiveClass:true,
		responsive:{
		0:{
		items:2
		},
		420:{
		items:3
		},
		600:{
		items:4
		},
		767:{
		items:5
		},
		992:{
		items:4
		}
		}
		});
		$('.sl-rightarrow').click(function() {
		sl_owl.trigger('next.owl.carousel');
		})
		$('.sl-leftarrow').click(function() {
		sl_owl.trigger('prev.owl.carousel', [300]);
		})

		});
		<?php
		$js = ob_get_clean();

		return $js;
	}

}
