<?php
/**
 * @version     2.0.0
 * @package     sellacious
 *
 * @copyright   Copyright (C) 2012-2020 Bhartiy Web Technologies. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Izhar Aazmi <info@bhartiy.com> - http://www.bhartiy.com
 */
// no direct access
use Sellacious\Media\Image\ImageHelper;

defined('_JEXEC') or die;

/** @var SellaciousViewProduct $this */
JHtml::_('jquery.framework');

JHtml::_('script', 'sellacious/owl.carousel.js', false, true);
JHtml::_('stylesheet', 'sellacious/owl.carousel.min.css', null, true);

JHtml::_('stylesheet', 'com_sellacious/owl.theme.default.css', null, true);
JHtml::_('stylesheet', 'com_sellacious/util.detail.image.css', null, true);

if ($this->helper->config->get('image_gallery_enable') || $this->item->get('primary_video_url')):
	JHtml::_('script', 'com_sellacious/jquery.fancybox-plus.js', true, true);
	JHtml::_('stylesheet', 'com_sellacious/jquery.fancybox-plus.css', null, true);
endif;
JHtml::_('script', 'com_sellacious/jquery.ez-plus.js', true, true);
JHtml::_('script', 'com_sellacious/product.detail.js', true, true);
JHtml::_('script', 'com_sellacious/fe.view.product.images.js', true, true);

$imW = (int) $this->helper->config->get('product_img_width', 270);
$imH = (int) $this->helper->config->get('product_img_height', 270);
$imA = $this->helper->config->get('image_slider_size_adjust') ?: 'contain';

$pvUrl   = '';
$pvImage = '';
$playBtn = JHtml::_('image', 'com_sellacious/play-btn.png', null, null, true, 1);;

if ($this->item->get('primary_video_url')):
	$pvUrl   = $this->helper->media->generateVideoEmbedUrl($this->item->get('primary_video_url'));
	$pvImage = $this->helper->media->generateVideoThumb($this->item->get('primary_video_url'));
endif;

$image_size           = $this->helper->config->get('product_img_size_adjust');

$iz_gallery_enable    = (int) $this->helper->config->get('image_gallery_enable', 1);
$iz_navigation_enable = (int) $this->helper->config->get('image_navigation_enable', 1);

$iz_enable       = (int) $this->helper->config->get('image_zoom_enable', 1);
$iz_type         = $this->helper->config->get('image_zoom_type', 'lens');
$iz_border_width = (int) $this->helper->config->get('image_zoom_border_width', 8);
$iz_border_color = $this->helper->config->get('image_zoom_border_color', 'rgba(0, 0, 0, 0.1)');

$iz_lens_size = (int) $this->helper->config->get('image_zoom_lens_size', 200);

$iz_window_width          = (int) $this->helper->config->get('image_zoom_window_width', 400);
$iz_window_height         = (int) $this->helper->config->get('image_zoom_window_height', 400);
$iz_lens_border_width     = (int) $this->helper->config->get('image_zoom_lens_border_width', 1);
$iz_lens_border_color     = $this->helper->config->get('image_zoom_lens_border_color', 'rgba(0, 0, 0, 0.1)');
$iz_lens_background_color = $this->helper->config->get('image_zoom_lens_background_color', 'rgba(255, 255, 255, 0.4)');
$iz_easing_enable         = (int) $this->helper->config->get('image_zoom_easing_enable', 1);

$iz_type_mobile      = $this->helper->config->get('image_zoom_type_mobile', 'lens');
$iz_lens_size_mobile = (int) $this->helper->config->get('image_zoom_lens_size_mobile', '180');

$ezOptions = array();

$ezOptions['borderColour'] = $iz_border_color;
$ezOptions['borderSize']   = $iz_border_width;
$ezOptions['easing']       = $iz_easing_enable ? true : false;
$ezOptions['zoomType']     = $iz_type;

if ($iz_gallery_enable):
	$ezOptions['gallery']  = 'detail-gallery';
	$ezOptions['cursor']   = 'pointer';
endif;

if ($iz_type == 'lens'):
	$ezOptions['lensShape'] = 'round';
	$ezOptions['lensSize']  = $iz_lens_size;
else:
	$ezOptions['zoomWindowHeight']  = $iz_window_height;
	$ezOptions['zoomWindowWidth']   = $iz_window_width;
	$ezOptions['zoomWindowFadeIn']  = 300;
	$ezOptions['zoomWindowFadeOut'] = 300;

	$ezOptions['lensBorderColour'] = $iz_lens_border_color;
	$ezOptions['lensBorderSize']   = $iz_lens_border_width;
	$ezOptions['lensColour']       = $iz_lens_background_color;
	$ezOptions['lensOpacity']      = 1;
endif;

$ezOptions['lensFadeIn']  = 300;
$ezOptions['lensFadeOut'] = 300;
$ezOptions['enabled']     = $iz_enable ? true : false;
$ezOptions['zIndex']      = 92;
$ezOptions['responsive']  = true;

if ($iz_type_mobile == 'lens') :
	$ezOptions['respond']          	  = array(
		array('range' => '0-991', 'zoomType' => 'lens', 'lensShape' => 'round', 'lensSize' => $iz_lens_size_mobile),
	);
else:
	$ezOptions['respond']          	  = array(
		array('range' => '0-991', 'enabled' => false, 'showLens' => false),
	);
endif;


$jsEzOptions = json_encode($ezOptions);
?>
<style>
<?php if($iz_type == 'lens'):?>
.zoomContainer {
	overflow: hidden;
}
<?php endif; ?>
.image-detail .product-img,
.image-detail .product-vid {
	min-width: <?php echo $imW ?>px;
	width: 100%;
	height: <?php echo $imH ?>px;
	background-size: <?php echo $imA ?>;
	position: relative !important;
}
.owl-carousel .owl-item img.play-btn {
	position: absolute;
	left: 25%;
	width: 50%;
	top: 25%;
}
.product-img img.play-btn {
	position: absolute;
	left: 35%;
	width: 30%;
	top: 50%;
}
</style>

<div id="product-images-container" data-fancybox="<?php echo (int) $iz_gallery_enable; ?>" data-playbtn="<?php echo $playBtn; ?>" data-ezoptions="<?php echo htmlspecialchars(json_encode($ezOptions)) ?>">
	<div class="productdetail-img">
		<?php $images = $this->item->get('images'); ?>
		<?php $image  = reset($images) ?>
        <?php if (!$image):
	        $image = !$pvImage ? ImageHelper::getBlank('products', 'images')->getUrl() : $pvImage;
        endif; ?>
		<div class="image-detail">
			<div class="product-img" style="background-image: url('<?php echo $image ?>'); background-size: <?php echo $image_size; ?>" <?php echo count($images) ? "data-zoom-image='{$image}'" : '' ?>
				 data-src="<?php echo htmlspecialchars($image) ?>">
                <?php echo ($image == $pvImage) ? "<img class='play-btn' src='{$playBtn}'>" : '' ?>
			</div>

			<?php if (((count($images) > 1 && !$pvImage) || (count($images) && $pvImage)) && $iz_navigation_enable): ?>
			<div class="slidecontrol">
				<a href="javascript:void(0);" class="prevslide"><i class="fa fa-angle-left"></i></a>
				<a href="javascript:void(0);" class="nextslide"><i class="fa fa-angle-right"></i></a>
			</div>
			<?php endif; ?>
		</div>
		<?php if (count($images) || $pvUrl): ?>
		<div id="detail-gallery"  class="products-slider-detail owl-carousel owl-theme">
			<?php foreach ($images as $i => $image): ?>
				<?php
				$anchorClass = $i == 0 ? 'current' : ''; ?>

				<a href="#" class="<?php echo $anchorClass; ?>" data-fbplus-type="image" data-zoom-image="<?php echo $image ?>">
					<span class="thumb-img" style="background-image: url('<?php echo $image ?>');"
						  data-zoom-image="<?php echo $image ?>" data-src="<?php echo htmlspecialchars($image) ?>">
					</span>
				</a>
			<?php endforeach; ?>
			<?php if (!count($images) || $pvUrl): ?>
			<a href="#" class="product-vid" data-fbplus-type="iframe" data-video-url="<?php echo $pvUrl ?>" data-zoom-image="<?php echo $pvImage ?>">
					<span class="thumb-img" style="background-image: url('<?php echo $pvImage ?>');"
						  data-zoom-image="<?php echo $pvImage ?>" data-src="<?php echo htmlspecialchars($pvImage) ?>">
						<img class="play-btn" src="<?php echo $playBtn ?>">
					</span>
			</a>
			<?php endif; ?>
		</div>
		<?php endif; ?>
	</div>
</div>
