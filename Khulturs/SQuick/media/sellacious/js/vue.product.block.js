/**
 * @version     2.0.0
 * @package     sellacious
 *
 * @copyright   Copyright (C) 2012-2020 Bhartiy Web Technologies. All rights reserved.
 * @license     SPL Sellacious Private License; see http://www.sellacious.com/spl.html
 * @author      Saurabh Sabharwal <info@bhartiy.com> - http://www.bhartiy.com
 */

/** @var  Vue */

Vue.filter('t', str => Joomla.JText._(str, str));

let addToCart = function(product_code, checkout, checkoutQuestionData) {
	if (checkout === undefined) {
		checkout = false;
	}

	if (checkoutQuestionData === undefined) {
		checkoutQuestionData = null;
	}

	let data = new FormData();
	data.append('p', product_code);

	let paths = Joomla.getOptions('system.paths', {});
	let base = paths.base || paths.root || '';

	jQuery(document).trigger('onAddCartOptions');

	if (checkoutQuestionData) {
		for (key in checkoutQuestionData) {
			data.append(key, checkoutQuestionData[key]);
		}
	}

	fetch(base + '/index.php?option=com_sellacious&task=cart.addAjax&format=json', {
		method: 'post',
		body: data,
		cache: 'no-cache',
		redirect: 'follow',
		referrer: 'no-referrer'
	})
		.then((response) => response.json())
		.then((response) => {

			if (parseInt(response.state) === 1) {
				Joomla.renderMessages({success: [response.message]});

				if (checkout === true && response.data['redirect']) {
					window.location.href = response.data['redirect'];
				} else if (jQuery('#modal-cart').length) {
					const $cartModal = jQuery('#modal-cart');
					let o = $cartModal.data('CartModal');
					o.navStep('cart');
					$cartModal.ctechmodal('show');
				}

				jQuery(document).trigger('cartUpdate', ['add', {uid: response.data.uid}]);

				// There's no way to trigger parent window's event so we will send a message to parent as an alternative
				window.parent.postMessage(JSON.stringify({triggerEvent: "cartUpdate", method: "add", args : {uid: response.data.uid}}), '*');
			} else {
				Joomla.renderMessages({error: [response.message]});
			}
		})
		.catch(error => {
			Joomla.renderMessages({error: [error]});
		})
};

let productBlockMixins = {
	props: ['product', 'params', 'user', 'context'],
	data() {
		return {
			productImage: this.product.images[0],
			mod_params: this.params.mod_params,
		}
	},
	methods: {
		changeImage: function (image) {
			this.productImage = image;
		},
		addToCart: function (checkout = false) {
			if (!this.product.code) return;

			if (this.product.checkout_form) {
				jQuery('.ctech-show').ctechmodal('hide');
				jQuery('.checkout-questions-form').find('iframe').attr('src', 'index.php?option=com_sellacious&view=product&p=' + this.product.code + '&layout=coq&tmpl=component');
				jQuery('.checkout-questions-form').ctechmodal('show');
				return;
			}

			addToCart(this.product.code, checkout);
		},
		removeFromWishlist: function (e) {
			e.preventDefault();
			const code = this.product.code;
			if (!code) return;

			let data = new FormData();
			data.append('p', code);

			const paths = Joomla.getOptions('system.paths', {});
			const base = paths.base || paths.root || '';

			fetch(base + '/index.php?option=com_sellacious&task=wishlist.removeAjax', {
				method: 'post',
				body: data,
				cache: 'no-cache',
				redirect: 'follow',
				referrer: 'no-referrer'
			})
				.then((response) => response.json())
				.then((response) => {
					if (response.state === 1) {
						(function ($) {
							$('[data-code="' + code + '"]').closest('.product-list-block').fadeOut('fast', function () {
								$(this).remove();
								$('.wishlist-products-container').find('.product-list-block').length || $('#empty-wishlist').removeClass('hidden');
							});
						})(jQuery);
					} else {
						Joomla.renderMessages({error: [response.message]});
					}
				})
		},
		quickView(product, modal_context) {
			this.$emit('open-modal', product, modal_context);
		},
		t(text) {
			const translate = Vue.filter('t');
			return (translate(text));
		}
	},
	computed: {
		productImageCss() {
			return `background-image: url('${this.productImage}'); height: ${this.params.image_height}px`;
		},
		productImageThumbsCss() {
			let css = [];

			this.product.images.forEach(image => css.push(`background-image: url('${image}')`) );

			return css;
		}
	}
}

let productPricingMixins = {
	props: ['product', 'params'],
	data: function() {
		return {
			showSellerDetails: false
		}
	}
};

let productCheckoutButtonsMixins = {
	props: ['product', 'params', 'mod_params', 'user', 'label', 'variant', 'classes', 'show_nostock'],
	data: function() {
		return {

		}
	},
	methods: {
		t(text) {
			const translate = Vue.filter('t');
			return (translate(text));
		},
		addToCart: function (checkout = false) {
			if (!this.product.code) return;

			if (this.product.checkout_form) {
				jQuery('.ctech-show').ctechmodal('hide');
				jQuery('.checkout-questions-form').find('iframe').attr('src', 'index.php?option=com_sellacious&view=product&p=' + this.product.code + '&layout=coq&tmpl=component');
				jQuery('.checkout-questions-form').ctechmodal('show');
				return;
			}

			addToCart(this.product.code, checkout);
		},
	}
};

let productStockMixins = {
	props: ['product', 'params']
}

Vue.component('product-quick-view', {
	template: '#vue-product-quick-view',
	props: ['url', 'content', 'attributes', 'product', 'params', 'user', 'context'],
	data() {
		return {
			mod_params: this.params.mod_params,
			quantity: 1
		}
	},
	methods: {
		addToCart() {
			const $this = this;

			if (!$this.product.code) return;

			let data = new FormData();
			data.append('p', $this.product.code);
			data.append('quantity', $this.quantity || 1);

			let paths = Joomla.getOptions('system.paths', {});
			let base = paths.base || paths.root || '';

			jQuery(document).trigger('onAddCartOptions');

			fetch(base + '/index.php?option=com_sellacious&task=cart.addAjax&format=json', {
				method: 'post',
				body: data,
				cache: 'no-cache',
				redirect: 'follow',
				referrer: 'no-referrer'
			})
				.then((response) => response.json())
				.then((response) => {

					if (parseInt(response.state) === 1) {
						Joomla.renderMessages({success: [response.message]});

						let modal = jQuery('#modal-cart');
						if (modal.length) {
							jQuery('#modal-quick-view-' + $this.context).ctechmodal('hide');

							let o = modal.data('CartModal');
							o.navStep('cart');
							modal.ctechmodal('show');
						}

						jQuery(document).trigger('cartUpdate', ['add', {uid: response.data.uid}]);

						// There's no way to trigger parent window's event so we will send a message to parent as an alternative
						window.parent.postMessage(JSON.stringify({triggerEvent: "cartUpdate", method: "add", args : {uid: response.data.uid}}), '*');
					} else {
						Joomla.renderMessages({error: [response.message]});
					}
				})
				.catch(error => {
					Joomla.renderMessages({error: [error]});
				})
		},
		t(text) {
			const translate = Vue.filter('t');
			return (translate(text));
		}
	},
	mounted() {
		const $this = this;
		(function ($) {
			if ($('#ctech-modal-wrapper').length) {
				$('.modal-quick-view').appendTo('#ctech-modal-wrapper');
			} else {
				const wrap = '<div class="ctech-wrapper" id="ctech-modal-wrapper"></div>';
				$('body').append(wrap);
				$('.modal-quick-view').appendTo('#ctech-modal-wrapper');
			}
			$('#modal-quick-view-' + $this.context).on('hide.ctech-bs.modal', function () {
				$('#modal-quick-view-' + $this.context).find('.quick-carousel').owlCarousel('destroy');
				$this.quantity = 1;
			});
		})(jQuery)
	}
});

/*Product block components*/
Vue.component('product-block-default', {
	template: '#vue-product-block-default',
	mixins: [productBlockMixins]
});

Vue.component('product-block-elegant', {
	template: '#vue-product-block-elegant',
	mixins: [productBlockMixins]
});

Vue.component('product-block-jskart', {
	template: '#vue-product-block-jskart',
	mixins: [productBlockMixins]
});

Vue.component('product-block-minimal', {
	template: '#vue-product-block-minimal',
	mixins: [productBlockMixins]
});

Vue.component('product-block-travelkit', {
	template: '#vue-product-block-travelkit',
	mixins: [productBlockMixins]
});

/*Product price components*/
Vue.component('product-price-flat', {
	template: '#vue-product-price-flat',
	mixins: [productPricingMixins]
});

Vue.component('product-price-basic', {
	template: '#vue-product-price-basic',
	mixins: [productPricingMixins]
});

Vue.component('product-price-dynamic', {
	template: '#vue-product-price-dynamic',
	mixins: [productPricingMixins]
});

Vue.component('product-price-call', {
	template: '#vue-product-price-call',
	mixins: [productPricingMixins]
});

Vue.component('product-price-email', {
	template: '#vue-product-price-email',
	mixins: [productPricingMixins]
});

Vue.component('product-price-queryform', {
	template: '#vue-product-price-queryform',
	mixins: [productPricingMixins]
});

Vue.component('product-price-hidden', {
	template: '#vue-product-price-hidden',
	mixins: [productPricingMixins]
});

Vue.component('product-price-free', {
	template: '#vue-product-price-free',
	mixins: [productPricingMixins]
});

/*Product add to cart button components*/
Vue.component('product-addtocart-flat', {
	template: '#vue-product-addtocart-flat',
	mixins: [productCheckoutButtonsMixins]
});

Vue.component('product-addtocart-basic', {
	template: '#vue-product-addtocart-basic',
	mixins: [productCheckoutButtonsMixins]
});

Vue.component('product-addtocart-dynamic', {
	template: '#vue-product-addtocart-dynamic',
	mixins: [productCheckoutButtonsMixins]
});

Vue.component('product-addtocart-call', {
	template: '#vue-product-addtocart-call',
	mixins: [productCheckoutButtonsMixins]
});

Vue.component('product-addtocart-email', {
	template: '#vue-product-addtocart-email',
	mixins: [productCheckoutButtonsMixins]
});

Vue.component('product-addtocart-queryform', {
	template: '#vue-product-addtocart-queryform',
	mixins: [productCheckoutButtonsMixins]
});

Vue.component('product-addtocart-hidden', {
	template: '#vue-product-addtocart-hidden',
	mixins: [productCheckoutButtonsMixins]
});

Vue.component('product-addtocart-free', {
	template: '#vue-product-addtocart-free',
	mixins: [productCheckoutButtonsMixins]
});

/*Product buy now button components*/
Vue.component('product-buynow-flat', {
	template: '#vue-product-buynow-flat',
	mixins: [productCheckoutButtonsMixins]
});

Vue.component('product-buynow-basic', {
	template: '#vue-product-buynow-basic',
	mixins: [productCheckoutButtonsMixins]
});

Vue.component('product-buynow-dynamic', {
	template: '#vue-product-buynow-dynamic',
	mixins: [productCheckoutButtonsMixins]
});

Vue.component('product-buynow-call', {
	template: '#vue-product-buynow-call',
	mixins: [productCheckoutButtonsMixins]
});

Vue.component('product-buynow-email', {
	template: '#vue-product-buynow-email',
	mixins: [productCheckoutButtonsMixins]
});

Vue.component('product-buynow-queryform', {
	template: '#vue-product-buynow-queryform',
	mixins: [productCheckoutButtonsMixins]
});

Vue.component('product-buynow-hidden', {
	template: '#vue-product-buynow-hidden',
	mixins: [productCheckoutButtonsMixins]
});

Vue.component('product-buynow-free', {
	template: '#vue-product-buynow-free',
	mixins: [productCheckoutButtonsMixins]
});

/*Product checkout button components*/
Vue.component('product-checkoutbuttons-flat', {
	template: '#vue-product-checkoutbuttons-flat',
	mixins: [productCheckoutButtonsMixins]
});

Vue.component('product-checkoutbuttons-basic', {
	template: '#vue-product-checkoutbuttons-basic',
	mixins: [productCheckoutButtonsMixins]
});

Vue.component('product-checkoutbuttons-dynamic', {
	template: '#vue-product-checkoutbuttons-dynamic',
	mixins: [productCheckoutButtonsMixins]
});

Vue.component('product-checkoutbuttons-call', {
	template: '#vue-product-checkoutbuttons-call',
	mixins: [productCheckoutButtonsMixins]
});

Vue.component('product-checkoutbuttons-email', {
	template: '#vue-product-checkoutbuttons-email',
	mixins: [productCheckoutButtonsMixins]
});

Vue.component('product-checkoutbuttons-queryform', {
	template: '#vue-product-checkoutbuttons-queryform',
	mixins: [productCheckoutButtonsMixins]
});

Vue.component('product-checkoutbuttons-hidden', {
	template: '#vue-product-checkoutbuttons-hidden',
	mixins: [productCheckoutButtonsMixins]
});

Vue.component('product-checkoutbuttons-free', {
	template: '#vue-product-checkoutbuttons-free',
	mixins: [productCheckoutButtonsMixins]
});

/*Product stock components*/
Vue.component('product-stock-basic', {
	template: '#vue-product-stock-basic',
	mixins: [productStockMixins]
});

Vue.component('product-stock-dynamic', {
	template: '#vue-product-stock-dynamic',
	mixins: [productStockMixins]
});

Vue.component('product-stock-call', {
	template: '#vue-product-stock-call',
	mixins: [productStockMixins]
});

Vue.component('product-stock-email', {
	template: '#vue-product-stock-email',
	mixins: [productStockMixins]
});

Vue.component('product-stock-queryform', {
	template: '#vue-product-stock-queryform',
	mixins: [productStockMixins]
});

Vue.component('product-stock-hidden', {
	template: '#vue-product-stock-hidden',
	mixins: [productStockMixins]
});

Vue.component('product-stock-free', {
	template: '#vue-product-stock-free',
	mixins: [productStockMixins]
});

Vue.component('product-stock-flat', {
	template: '#vue-product-stock-flat',
	mixins: [productStockMixins]
});

/*Other components*/
Vue.component('ctech-modal', {
	template: '#ctech-modal-layout',
	props: ['id', 'url', 'classes', 'size', 'header', 'footer', 'backdrop'],
});

const initProductBlock = (el, products, params, user, context) => new Vue({
	el,
	data() {
		return {
			params: params,
			user: user,
			products: products,
			product: {},
			blockStyle: params.blockStyle,
			context
		}
	},
	methods: {
		openModal: function (product, modal_context) {
			const $this = this;
			$this.product = product;
			(function ($) {
				setTimeout(function () {
					$('#modal-quick-view-' + modal_context).find('.quick-carousel').owlCarousel({
						autoplay: true,
						autoplayTimeout: 2000,
						autoplayHoverPause: true,
						loop: $this.product.images.length > 1,
						nav: true,
						navText: [
							"<i class='fa fa-angle-left'></i>",
							"<i class='fa fa-angle-right'></i>"
						],
						rewind: true,
						responsive: {
							0: {
								items: 1
							}
						}
					});
				}, 400);
				$('#modal-quick-view-' + modal_context).ctechmodal('show');
			})(jQuery);
		}
	},
	computed: {
		modal() {
			return {
				attributes: {
					id: 'modal-quick-view-' + this.context,
					size: 'lg',
					width: '800',
					height: '600'
				},
				content: {
					title: 'COM_SELLACIOUS_PRODUCTS_QUICK_VIEW'
				},
				url: '',
				product: {name: ''},
			}
		},
		mod_params() {
			return this.params.mod_params;
		}
	},
	mounted() {
		(function ($) {
			if ($('#ctech-modal-wrapper').length) {
				$('#products-box').find('.ctech-modal').appendTo('#ctech-modal-wrapper');
			} else {
				const wrapper = '<div class="ctech-wrapper" id="ctech-modal-wrapper"></div>';
				$('body').append(wrapper);
				$('#products-box').find('.ctech-modal').appendTo('#ctech-modal-wrapper');
			}

			if ($('.product-list-block').length === 0) {
				$('#empty-wishlist').removeClass('hidden');
			}
		})(jQuery);
	}
});

jQuery(document).ready(function ($) {
	const user = Joomla.getOptions('sellacious.user');
	Vue.config.devtools = true;
	$('.product-blocks-container').each((i, block) => {
		const id       = $(block).data('module');
		const products = Joomla.getOptions('sellacious.product.data.module-' + id);
		const params   = Joomla.getOptions('sellacious.product.params.module-' + id);

		initProductBlock(block, products, params, user, id);
	});

	window.addEventListener('message', function (e) {
		let message = $.parseJSON(e.data);

		if (typeof message =='object' && message.triggerEvent == 'addToCart' && message.args.uid !== undefined) {
			let uid = message.args.uid;
			let data = message.args.data;

			$('.checkout-questions-form').ctechmodal('hide');
			addToCart(uid, false, data);
		}
	});
});
