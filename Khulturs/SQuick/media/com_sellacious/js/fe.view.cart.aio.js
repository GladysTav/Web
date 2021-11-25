/**
 * @version     2.0.0
 * @package     sellacious
 *
 * @copyright   Copyright (C) 2012-2020 Bhartiy Web Technologies. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Izhar Aazmi <info@bhartiy.com> - http://www.bhartiy.com
 */
jQuery(document).ready(function ($) {
	var $cartAOI = $('#cart-aio-container');

	if ($cartAOI.length) {
		var o = new SellaciousViewCartAIO;
		o.token = Joomla.getOptions('csrf.token');
		o.init('#cart-aio-container');
		$cartAOI.data('CartAIO', o);
	}

	var $cartModal = $('#modal-cart');

	if ($cartModal.length) {
		var oo = new SellaciousViewCartAIO;
		oo.token = Joomla.getOptions('csrf.token');
		oo.initCart('#modal-cart .ctech-modal-body', true);
		$cartModal.find('.ctech-modal-body').html('<div id="cart-items"></div>');
		$cartModal.data('CartModal', oo);
		$(document).on('click', '.btn-cart-modal', function () {
			var o = $cartModal.data('CartModal');
			o.navStep('cart');
			$cartModal.ctechmodal('show')
		});
	} else {
		$('.btn-cart-modal').addClass('hidden');
	}

	window.addEventListener('message', function (e) {
		let message = $.parseJSON(e.data);

		if (typeof message =='object' && message.triggerEvent == 'addToCart' && message.args.uid !== undefined) {
			let uid = message.args.uid;
			let checkoutQuestionData = message.args.data;

			$('#checkout-questions-' + uid).ctechmodal('hide');

			let data = new FormData();
			data.append('p', uid);

			let paths = Joomla.getOptions('system.paths', {});
			let base = paths.base || paths.root || '';

			jQuery(document).trigger('onAddCartOptions');

			if (checkoutQuestionData) {
				for (key in checkoutQuestionData) {
					data.append(key, checkoutQuestionData[key]);
				}
			}

			fetch(base + '/index.php?option=com_sellacious&task=cart.saveItemCheckoutFormAjax&format=json', {
				method: 'post',
				body: data,
				cache: 'no-cache',
				redirect: 'follow',
				referrer: 'no-referrer'
			})
				.then((response) => response.json())
				.then((response) => {

					if (response.success) {
						Joomla.renderMessages({success: [response.message]});

						if (response.data.html) {
							$('.cart-summary-section').find('#coq_data_' + uid).replaceWith(response.data.html);
						}
					} else {
						Joomla.renderMessages({error: [response.message]});
					}
				})
				.catch(error => {
					Joomla.renderMessages({error: [error]});
				})
		}
	});
});
