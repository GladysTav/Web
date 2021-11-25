/**
 * @version     2.0.0
 * @package     sellacious
 *
 * @copyright   Copyright (C) 2012-2020 Bhartiy Web Technologies. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Saurabh Sabharwal <info@bhartiy.com> - http://www.bhartiy.com
 */

jQuery(function ($) {
	$(document).ready(function() {
		const container = $('#product-images-container');
		const image     = $('.image-detail .product-img');

		const fancyBox  = container.data('fancybox');
		const playBtn   = container.data('playbtn');
		const ezOptions = container.data('ezoptions');

		//For Previous Slide
		$('.prevslide').on('click',function(){
			let thumbs = $('.products-slider-detail').find('a');
			let thumb  = $('.products-slider-detail').find('a.current');
			let index  = thumbs.index(thumb) === 0 ? thumbs.length - 1 : thumbs.index(thumb) - 1;

			let prevthumb = thumbs.eq(index);
			thumb.removeClass('current');
			prevthumb.addClass('current').trigger('click');
		});

		//For Next Slide
		$('.nextslide').on('click',function() {
			let thumbs = $('.products-slider-detail').find('a');
			let thumb = $('.products-slider-detail').find('a.current');

			let index = thumbs.index(thumb) === thumbs.length - 1 ? 0 : thumbs.index(thumb) + 1;

			let nextthumb = thumbs.eq(index);
			thumb.removeClass('current');
			nextthumb.addClass('current').trigger('click');
		});

		$('.products-slider-detail a').click(function () {
			let thumb = $(this).find('.thumb-img');
			let owlStage = $(this).parent().parent();
			owlStage.find('a').removeClass('current');
			$(this).addClass('current');

			let src = thumb.attr('data-src');
			let srcZ = thumb.attr('data-zoom-image');
			image.css('background-image', 'url("' + src + '")');

			image.find('.play-btn').remove();

			let EZP = image.data('ezPlus');

			if ($(this).data('video-url')) {
				if (fancyBox !== 1) clickableVid($(this).data('video-url'))
				image.append('<img class="play-btn" src="' + playBtn + '">');
				if (EZP) {
					EZP.swaptheimage(src, srcZ);
					EZP.changeState('disable');
					$('.zoomContainer').css('pointer-events', 'none')
				}
			}
			else {
				if (fancyBox !== 1) removeClickEvent();
				if (EZP) {
					EZP.changeState('enable');
					EZP.swaptheimage(src, srcZ);
					$('.zoomContainer').css('pointer-events', 'auto')
				}
			}

			return false;
		});

		//Enable Gallery in Fancy Box Popup
		if (fancyBox === 1) {
			image.on('click', function (e) {
				let ez = image.data('ezPlus');
				let galleryList = [];
				if (ez.options.gallery) {
					$('#' + ez.options.gallery + ' a').each(function () {
						let imgSrc = '';
						if ($(this).data(ez.options.attrImageZoomSrc)) {
							imgSrc = $(this).data(ez.options.attrImageZoomSrc);
						}
						else if ($(this).data('image')) {
							imgSrc = $(this).data('image');
						}

						//put the current image at the start
						if (imgSrc === ez.zoomImage) {
							if($(this).data('fbplus-type') === 'iframe'){
								imgSrc = $(this).data('video-url');
							}

							galleryList.unshift({
								href: '' + imgSrc + '',
								title: $(this).find('img').attr('title'),
								type: $(this).data('fbplus-type')
							});
						}
						else {
							if ($(this).data('fbplus-type') === 'iframe') {
								imgSrc = $(this).data('video-url');
							}

							galleryList.push({
								href: '' + imgSrc + '',
								title: $(this).find('img').attr('title'),
								type: $(this).data('fbplus-type')
							});
						}
					});
				}
				//if no gallery - return current image
				else {
					galleryList.push({
						href: '' + ez.zoomImage + '',
						title: $(this).find('img').attr('title'),
						type: $(this).data('fbplus-type')
					});
				}

				if (galleryList.length === 0) return;

				if (galleryList.length === 1) galleryList[0].showNavArrows = false;

				$.fancyboxPlus(galleryList);
				return false;
			});
		}

		const clickableVid = src => {
			let gallery = [{
				href: '' + src + '',
				type: 'iframe',
				showNavArrows: false
			}];

			image.on('click', function () {
				$.fancyboxPlus(gallery);
				return false;
			});
		}

		const removeClickEvent = () => {
			image.off('click');
		}

		let opts = $.extend({}, ezOptions, {});

		function initEZPlus() {
			image.ezPlus(opts);
		}

		//Init elevateZoom
		initEZPlus();

		//Triggered when window width is changed.
		$( window ).on( "resize", function() {
			//ReInit elevateZoom
			let obj = image.data('ezPlus');
			if (obj) obj.destroy();

			image.ezPlus(opts);
		});
	});
});
