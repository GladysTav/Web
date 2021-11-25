/**
 * @version     2.0.0
 * @package     sellacious
 *
 * @copyright   Copyright (C) 2012-2020 Bhartiy Web Technologies. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Izhar Aazmi <info@bhartiy.com> - http://www.bhartiy.com
 */
jQuery(document).ready(function ($) {
	// Initialize cart modal
	var $cartModal = $('#modal-cart');

	if ($cartModal.length) {
		var oo = new SellaciousViewCartAIO;
		oo.token = Joomla.getOptions('csrf.token');
		oo.initCart('#modal-cart .ctech-modal-body', true);
		$cartModal.find('.ctech-modal-body').html('<div id="cart-items"></div>');
		$cartModal.data('CartModal', oo);
	}

	var addToCart = function (btn, empty_cart) {
		if (typeof empty_cart === "undefined") {
			empty_cart = false;
		}

		var code = btn.data('item');
		var checkout = btn.data('checkout');
		var paths = Joomla.getOptions('system.paths', {});
		var base = paths.base || paths.root || '';

		var data = {p: code};
		data.options = {};

		$(document).trigger('onAddCartOptions', [data.options, btn]);

		if (empty_cart) {
			data.options.empty_cart = 1;
		}

		$.ajax({
			url: base + '/index.php?option=com_sellacious&task=cart.addAjax&format=json',
			type: 'POST',
			data: data,
			cache: false,
			dataType: 'json'
		}).done(function (response) {
			if (response.state == 1) {
				$(document).trigger('cartUpdate', ['add', {uid: response.data.uid}]);
				Joomla.renderMessages({success: [response.message]});
				if (checkout && response.data['redirect']) {
					window.location.href = response.data['redirect'];
				} else {
					// Open cart in modal
					if ($('#modal-cart').length > 0) {
						$cartModal = $('#modal-cart');
						var o = $cartModal.data('CartModal');
						o.navStep('cart');
						$cartModal.ctechmodal('show');
					}
				}
			} else if (response.state == 1000) {
				if (confirm(response.message)) {
					addToCart(btn, true);
				} else {
					return false;
				}
			} else {
				Joomla.renderMessages({error: [response.message]});
			}
		}).fail(function (jqXHR) {
			Joomla.renderMessages({error: ['There was an error while processing your request. Please try later.']});
			console.log(jqXHR.responseText);
		});
	};
	$('.btn-add-cart').click(function (){
		addToCart($(this));
	});

	$('.btn-toggle').click(function () {
		$(this).find('[data-toggle="true"]').toggleClass('hidden');
	});

	$(".btn-group > .btn").click(function () {
		$(".btn-group > .btn").removeClass("active");
		$(this).addClass("active");
	});

	$('.switch-style').click(function () {
		const productsPage = $('.product-blocks-container[data-module="productsList"]');
		const switchedLayout = $(this).data('style');
		const $productsBox = $('.product-blocks-container[data-module="productsList"]');

		productsPage.removeClass('product-list')
			.removeClass('product-grid').removeClass('product-masonry').addClass(switchedLayout);

		if (switchedLayout === 'product-list') {
			$productsBox.isotope().isotope('destroy');
		} else if (switchedLayout === 'product-grid') {
			$productsBox.isotope().isotope('destroy');
			if (typeof setGridHeight === 'function') {
				setGridHeight();
				setGridHeight = null;
			}
		} else if (switchedLayout === 'product-masonry') {
			setTimeout(function () {
				$productsBox.isotope({
					itemSelector: '.product-list-block',
					layoutMode: 'masonry'
				});
			}, 400);
		}
	}).filter('.active').triggerHandler('click');

	if (!$('.mod-sellacious-filters').length) {
		$('.filter-icon').remove();
	}

	$('#filters-toggle').on('click', function (e) {
		e.preventDefault();
		$('.mod-sellacious-filters').addClass('slide');

		if (!$('.filter-backdrop').length) {
			let backdrop = '<div class="filter-backdrop hide-backdrop"></div>';
			$('body').append(backdrop);
			$('.filter-backdrop').removeClass('hide-backdrop');
		} else {
			$('.filter-backdrop').removeClass('hide-backdrop');
		}
	})

	$('#filters-close').on('click', function (e) {
		e.preventDefault();
		$('.filter-backdrop').addClass('hide-backdrop');
		$('.mod-sellacious-filters').removeClass('slide');
	})
});

