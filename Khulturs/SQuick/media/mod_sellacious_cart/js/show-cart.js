/**
 * @version     2.0.0
 * @package     Sellacious Cart Module
 *
 * @copyright   Copyright (C) 2012-2020 Bhartiy Web Technologies. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Bhavika Matariya <info@bhartiy.com> - http://www.bhartiy.com
 */

jQuery(function ($) {
	window.addEventListener('message', function (e) {
		let message = $.parseJSON(e.data);

		if (typeof message =='object' && message.triggerEvent == 'cartUpdate') {
			$(document).trigger(message.triggerEvent, [message.method, message.args]);
		}
	});

	$(document).ready(function () {
		var loadModuleCart = function () {
			var data = {
				option: 'com_sellacious',
				task: 'cart.getCartAjax',
				format: 'json'
			};
			var paths = Joomla.getOptions('system.paths', {});
			var base = paths.base || paths.root || '';
			var token = Joomla.getOptions('csrf.token');
			data[token] = 1;
			$.ajax({
				url: base + '/index.php',
				type: 'POST',
				dataType: 'json',
				cache: false,
				data: data
			}).done(function (response) {
				if (response.status == 1) {
					setHTML(response.data, response.total);
				} else {
					$('.mod-products-list').html(response.message);
				}
			}).fail(function (jqXHR) {
				console.log(jqXHR.responseText);
			});
		};

		var setHTML = function (items, total) {
			$('.mod-grand-total').html(total);
			$('.mod-total-products').html(items.length);
			var cartItems = $('.mod-products-list');
			cartItems.html('');

			$.each(items, function(k, v){
				var html = '';

				html += '<div class="container">';
				html += '<a href="'+ v.link +'"><div class="product_row">';
				html += '<div class="image-cart"><img src="'+v.image+'" width="50px" class="product-thumb" /></div> ';
				html += '<div class="pro-title"> <span class="quantity">';
				html +=  v.quantity;
				html += '</span>&nbsp;x&nbsp;<span class="product_name">';
				html +=  v.title;
				html += '</span></div>';
				html += '<div class="prices ctech-text-primary" style="float: right;">';
				html +=  v.total;
				html += '</div>';
				html += '</a>';
				html += '</div>';
				html += '<div class="ctech-clearfix"></div>'

				cartItems.append(html);
			});

			var cartAppend = '';
			cartAppend += '<div class="container"><div style="float: right">';
			if (items.length) {
				cartAppend += Joomla.JText._('MOD_SELLACIOUS_CART_PLUS_TAXES');
			}
			else {
				cartAppend += Joomla.JText._('MOD_SELLACIOUS_CART_EMPTY_CART_NOTICE');
			}
			cartItems.append('</div> </div>');

			cartItems.append(cartAppend);

			return true;
		};

		$(document).on('cartUpdate', function (event, method, params) {
			loadModuleCart();
		});

		loadModuleCart();

		// Initialize cart modal
		var $cartModal = $('#modal-cart');

		if ($cartModal.length) {
			var oo = new SellaciousViewCartAIO;
			oo.token = Joomla.getOptions('csrf.token');
			oo.initCart('#modal-cart .ctech-modal-body', true);
			$cartModal.find('.ctech-modal-body').html('<div id="cart-items"></div>');
			$cartModal.data('CartModal', oo);
		}

		$('#btn-modal-cart').on('click', function () {
			var o = $cartModal.data('CartModal');
			o.token = $(this).data('token');
			o.navStep('cart');
			$cartModal.ctechmodal('show');
		});

		$(".mod-sellacious-cart").hover(function(){
			$('.mod-cart-ul').removeClass('ctech-d-none');
		},function(){
			$('.mod-cart-ul').addClass('ctech-d-none');
		});
	});
});
