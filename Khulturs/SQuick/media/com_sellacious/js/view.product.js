/**
 * @version     2.0.0
 * @package     sellacious
 *
 * @copyright   Copyright (C) 2012-2020 Bhartiy Web Technologies. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Izhar Aazmi <info@bhartiy.com> - http://www.bhartiy.com
 */
var SellaciousViewProduct = {
	Related: function () {
		return this;
	}
};

(function ($) {
	// Fixed version of jQuery.clone function
	(function (original) {
		$.fn.clone = function () {
			var result = original.apply(this, arguments),
				o_textarea = this.find('textarea').add(this.filter('textarea')),
				r_textarea = result.find('textarea').add(result.filter('textarea')),
				o_select = this.find('select').add(this.filter('select')),
				r_select = result.find('select').add(result.filter('select'));

			var i, l;
			for (i = 0, l = o_textarea.length; i < l; ++i) $(r_textarea[i]).val($(o_textarea[i]).val());
			for (i = 0, l = o_select.length; i < l; ++i) r_select[i].selectedIndex = o_select[i].selectedIndex;

			return result;
		};
	})($.fn.clone);

	SellaciousViewProduct.Related.prototype = {
		init: function (selector, token) {
			var $that = this;
			$that.element = $(selector);
			$that.token = token;
			$that.preview = $(selector + '_preview');

			if ($that.element.length === 0) return false;

			var tags = $that.element.data('tags');

			$that.element.select2({
				tags: tags,
				createSearchChoice: function (term) {
					var choice = {
						id: $.trim(term),
						text: $.trim(term)
					};
					// First match from tags available
					$.each(tags, function (i, tag) {
						if (tag.text.toUpperCase() === $.trim(term).toUpperCase()) {
							choice.id = tag.id;
							choice.text = tag.text;
						}
					});
					// next match from current selection, select2 will be available at the time of calling this
					$.each($that.element.select2('data'), function (i, tag) {
						if (tag.text.toUpperCase() === $.trim(term).toUpperCase()) {
							choice.id = tag.id;
							choice.text = tag.text;
						}
					});
					// return finally
					return choice;
				}
			});

			$that.element.on('select2-selecting', function (e) {
				if (typeof e.choice === 'object' && typeof e.choice.text !== 'undefined') {
					if (/^\s*$/.test(e.choice.text)) {
						return false;
					} else if (/,/.test(e.choice.text)) {
						alert('Commas are not allowed.');
						return false;
					} else if (e.choice['existing']) {
						// Existing item should be already available, may be faded
						var displayed = $.grep($('.del-related-group'), function (btn) {
							var b = $(btn).data('id') === e.choice['existing'];
							// Trigger will suffice the action required
							if (b && $(btn).data('deleted')) $(btn).trigger('click');
							return b;
						});
						// Load from ajax, if already displayed ones doesn't match, this should never happen ideally
						if (displayed.length === 0) $that.loadProducts(e.choice['existing'], e.choice.text);
						// Trigger above will add automatically, return false to prevent duplicate
						else return false;
					} else {
						$that.addGroupRow(e.choice.text);
					}
				}
			});

			// We do not allow removal from select2, use provided separate button for it in the preview section
			$that.element.on('select2-removing', function () {
				return false;
			});

			// Timer used by delete button handler
			// var timer = [];

			$that.preview.on("click", '.del-related-group', function (e) {
				var $target = $(e.target).is('.del-related-group') ? $(e.target) : $(e.target).closest('.del-related-group');

				var id = $target.data('id');
				var data = $that.element.select2('data');

				// Remove any pending timeout for this button, as user has clicked now
				// if (timer[id]) clearTimeout(timer[id]);

				// Delete/Restore behaviour, skip confirmation as we don't actually delete existing ones directly
				if ($target.data('deleted')) {
					// Update select2 value
					$.each(tags, function (i, value) {
						if (value['existing'] === id) {
							data.push(value);
							return false;
						}
					});
					$that.element.select2('data', data);

					$target.data('deleted', false);
					$target.addClass('btn-danger').removeClass('btn-success')
						.html('<i class="fa fa-times"></i> Remove');
					$target.closest('tr').find('.group-items').fadeTo('slow', 1.0);
				}
				else {
					// Update select2 value
					data = $.grep(data, function (value) {
						return value['existing'] ? value['existing'] !== id : value.text !== id;
					});
					$that.element.select2('data', data);

					// Allow restoration of already existing groups, remove new ones immediately
					if ($target.closest('tr').data('existing')) {
						$target.data('confirm', false);
						$target.data('deleted', true);
						$target.closest('tr').find('.group-items').fadeTo('slow', .3);
						$target.removeClass('btn-danger').addClass('btn-success')
							.html('<i class="fa fa-check"></i> Restore');
					} else {
						$target.closest('tr').fadeOut('slow').remove();
					}
				}
			});

			// Preload existing, not included in form-field html to retain design consistency
			$that.preloadExisting();
		},

		preloadExisting: function () {
			var $that = this;
			var items = $that.element.data('value');
			var data = [];

			$.each(items, function (i, item) {
				$that.loadProducts(item['existing'], item.text);
				data.push(item);
			});
			$that.element.select2('data', data);
		},

		loadProducts: function (group /*, label*/) {
			var $that = this;
			$.ajax({
				url: 'index.php?option=com_sellacious&view=relatedproducts&tmpl=raw&filter[group]=' + group,
				type: 'GET',
				cache: false,
				async: true,
				success: function (response) {
					if (response === '') {
						Joomla.renderMessages({warning: ['Unknown error encountered while trying to load existing products for selected related product group.']});
					} else {
						var $row = $('<tr/>').data('existing', true);
						$row.append(
							'<td class="group-items">' + response + '</td>\
							<td style="vertical-align: top !important; width: 50px; text-align: right;">\
								<button type="button" class="btn btn-xs btn-danger del-related-group" data-id="' + group + '">\
								<i class="fa fa-times"></i> Remove</button>\
							</td>'
						);
						$that.preview.first('tbody').append($row);
					}
				},
				error: function (jqXHR) {
					console.log(jqXHR.responseText);
				}
			});
		},

		addGroupRow: function (group) {
			var $that = this;
			var $row = $('<tr/>');
			$row.append(
				'<td>\
					<table class="table table-stripped table-noborder w100p">\
					<thead><tr style="background: #deefc9">\
						<td colspan="2">' + group + '</td><td style="width:15px;">new</td>\
					</tr></thead></table>\
				</td>\
				<td style="vertical-align: top !important; width: 50px; text-align: right;">\
					<button type="button" class="btn btn-xs btn-danger del-related-group" data-id="' + group + '">\
					<i class="fa fa-times"></i> Remove</button>\
				</td>'
			);
			$that.preview.first('tbody').append($row);
		}
	};

	$(document).ready(function () {
		var seller_uid = $('#jform_seller_uid').val();

		if (seller_uid > 0) {
			// Shipping config
			var $flatShip = $('#jform_seller_flat_shipping').find('input[type="radio"]');
			var flatFee = $('#jform_seller_shipping_flat_fee').closest('div.input-row');
			var shipRules = $('#jform_seller_rules_shipping_rules').closest('div.input-row');
			$flatShip.change(function () {
				var value = $flatShip.filter(':checked').val();
				if (parseInt(value) === 1) {
					flatFee.removeClass('hidden');
					shipRules.addClass('hidden');
				} else {
					flatFee.addClass('hidden');
					shipRules.removeClass('hidden');
				}
			}).triggerHandler('change');

			// Allow / disallow returns and exchange of products by customer
			$('#jform_seller_return_days').change(function () {
				var allow = parseInt($(this).val());
				allow = isNaN(allow) ? 0 : allow;
				var ret = $('#jform_seller_return_tnc').closest('div.input-row');
				allow ? ret.removeClass('hidden') : ret.addClass('hidden');
			}).triggerHandler('change');

			$('#jform_seller_exchange_days').change(function () {
				var allow = parseInt($(this).val());
				allow = isNaN(allow) ? 0 : allow;
				var ret = $('#jform_seller_exchange_tnc').closest('div.input-row');
				allow > 0 ? ret.removeClass('hidden') : ret.addClass('hidden');
			}).triggerHandler('change');

			// Listing type new / used / refurbished
			var $iType = $('#jform_seller_listing_type').find('input[type="radio"]');

			$iType.change(function () {
				var value = $iType.filter(':checked').val();
				var $iCond = $('#jform_seller_item_condition').closest('div.input-row');
				parseInt(value) === 1 ? $iCond.addClass('hidden') : $iCond.removeClass('hidden');
			}).triggerHandler('change');
		}

		// E-Product delivery mode
		var $deliveryMode = $('#jform_seller_delivery_mode');
		var $eproductDelivery = $deliveryMode.find('input[type="radio"]');
		var message = '<div class="enterprise-message text text-danger padding-5">Only available with <b>Sellacious Enterprise</b></div>';
		$deliveryMode.closest('div.input-row').find('.controls').append(message);

		$eproductDelivery.change(function () {
			var value = $eproductDelivery.filter(':checked').val();
			var $iRow = $deliveryMode.closest('.controls').find('.enterprise-message');
			value === 'download' || value === 'none' ? $iRow.addClass('hidden') : $iRow.removeClass('hidden');
		}).triggerHandler('change');

		document.formvalidator.setHandler('primary_video', function(value, element) {
			var regex = /^(http:|https:|)\/\/(player.|www.)?(vimeo\.com|youtu(be\.com|\.be|be\.googleapis\.com))\/(video\/|embed\/|watch\?v=|v\/)?([A-Za-z0-9._%-]*)(&\S+)?/;
			value.match(regex);
			return (RegExp.$3.indexOf('youtu') > -1 || RegExp.$3.indexOf('vimeo') > -1);
		});

		const pricingType = $('#jform_seller_pricing_type');
		const fallback = $('#jform_prices_fallback');

		let priceType = pricingType.find('input:checked').length ? pricingType.find('input:checked').val() : pricingType.val();
		fallback.find('.hide-on-flat-price').toggleClass('hidden', priceType === 'flat');

		pricingType.on('change', 'input[type=radio]', function () {
			$(this).closest('form').submit();
		});

		var validateUniquefield = function(element, value, callback) {
			var token = Joomla.getOptions('csrf.token');
			var data = element.closest('form').serializeArray();

			data.push({name: 'option', value: 'com_sellacious'});
			data.push({name: 'task', value: 'product.validateUniqueFieldAjax'});
			data.push({name: 'unique_field_value', value: value});
			data.push({name: token, value: 1});

			$.ajax({
				url: 'index.php',
				type: 'POST',
				dataType: 'json',
				cache: false,
				data: data
			}).done(function (response) {
				if (element.hasClass('validate-unique')) {
					if (response.success == true) {
						element.attr('data-unique', 'true');
					} else {
						element.attr('data-unique', 'false');
					}
				}

				if (response.message) {
					Joomla.renderMessages({warning: [response.message]});
				}

				if (typeof callback == "function") {
					callback(response);
				}
			}).fail(function ($xhr) {
				Joomla.renderMessages({warning: ['Request failed due to unknown error.']});
			});
		};

		$('.unique_field').on('change', function (e) {
			let form = $(this).closest('form');
			let $this = $(this);

			validateUniquefield($(this), $(this).val(), function (response) {
				if (document.formvalidator.isValid(form)) {
					if (!$this.hasClass('validate-unique') && response.data.redirect_url !== undefined) {
						window.location.href = response.data.redirect_url;
					} else {
						form.submit();
					}
				}
			});
		});

		$('.unique_field').on('keyup', function (e) {
			let $this = $(this);
			var value = $this.val();

			if (!value) {
				return;
			}

			e.preventDefault();
			validateUniquefield($this, value);
		});

		$('#jform_seller_seller_sku').on('keyup', function (e) {
			let $this = $(this);
			let $form = $this.closest('form');
			var value = $this.val();

			if (!value) {
				return;
			}

			var productId = $form.find('#jform_id').val();
			var sellerUid = $form.find('#jform_seller_uid').val();

			var token = Joomla.getOptions('csrf.token');

			var data = {
				option: 'com_sellacious',
				task: 'product.isSkuUniqueAjax',
				product_id: productId,
				seller_uid: sellerUid,
				sku: value,
			};

			data[token] = 1;

			$.ajax({
				url: 'index.php',
				type: 'POST',
				dataType: 'json',
				cache: false,
				data: data
			}).done(function (response) {
				if ($this.hasClass('validate-unique')) {
					if (response.success == true) {
						$this.attr('data-unique', 'true');
					} else {
						$this.attr('data-unique', 'false');
					}
				}

				if (response.message) {
					Joomla.renderMessages({warning: [response.message]});
				}
			}).fail(function ($xhr) {
				Joomla.renderMessages({warning: ['Request failed due to unknown error.']});
			});
		});

		document.formvalidator.setHandler('unique', function(value, element) {
			return $(element).hasClass('validate-unique') && $(element).attr('data-unique') != 'false';
		});
	});
})(jQuery);
