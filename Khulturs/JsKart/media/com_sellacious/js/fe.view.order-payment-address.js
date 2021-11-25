/**
 * @version     2.0.0
 * @package     sellacious
 *
 * @copyright   Copyright (C) 2012-2020 Bhartiy Web Technologies. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Izhar Aazmi <info@bhartiy.com> - http://www.bhartiy.com
 */
jQuery(function ($) {

	function StepAddress() {
		const paths = Joomla.getOptions('system.paths', {});
		this.baseUrl = (paths.base || paths.root || '') + '/index.php';
		this.token = Joomla.getOptions('csrf.token');
		this.user = Joomla.getOptions('sellacious.user', {});
		this.setup();
	}

	StepAddress.prototype = {

		formFields: {},

		elements: {
			container: '#address-container',
			editor: '#address-editor',
			viewer: '#address-viewer',
			viewer_billing: '#address-billing-text',
			viewer_shipping: '#address-shipping-text',
			boxes_container: '#address-items',
			modals_container: '#address-modals',
			input_billing: '#address-billing',
			input_shipping: '#address-shipping',
			box_single_class: '.address-item',
			btn_ship_here: '.btn-ship-here',
			btn_bill_here: '.btn-bill-here',
			modal_form: '.address-form-content',
			btn_add_new: '.btn-add-address'
		},

		element(name, selectorOnly) {
			return selectorOnly ? this.elements[name] : this.container.find(this.elements[name]);
		},

		setup() {
			const self = this;
			self.container = $(self.elements.container);
			self.container.find('select').select2();
			self.orderId = self.container.find('#order-id').val();
			self.loadEditor();

			self.container
				.on('click', '.btn-ship-here', function (e) {
					self.shipSelect(e, this);
				})
				.on('click', '.btn-bill-here', function (e) {
					self.billSelect(e, this);
				})
				.on('click', '.btn-save', function (e) {
					self.submitSelect();
				})
				.on('click', '.remove-address', function () {
					let address_id = $(this).data('id');
					if (address_id && confirm(Joomla.JText._('COM_SELLACIOUS_USER_CONFIRM_ADDRESS_REMOVE_MESSAGE'))) self.remove(address_id);
				});

			if ($('#ctech-modal-wrapper').length === 0) {
				$('body').append('<div class="ctech-wrapper" id="ctech-modal-wrapper"></div>');
			}

			$('#address-modals,#address-form-0').appendTo('#ctech-modal-wrapper');

			$(document).on('click', '.btn-save-address', function () {
				let el = this;
				let formKey = self.element('modal_form', true);
				let $form = $(el).closest('.ctech-modal').find(formKey);
				let data = self.validate($form);
				if (data) {
					self.save(data)
						.done(function (response) {
							if (response.status === 1) {
								$('#address-form-' + data.id).ctechmodal('hide');

								$('.ctech-modal-backdrop').remove();

								// Reset the form filled values in add new address
								if (parseInt(data.id) === 0) {
									let fields = self.getFormFields();
									$.each(fields, function (fieldKey) {
										if (fieldKey !== 'id') {
											let $field = $form.find('.address-' + fieldKey).filter(':input');
											$field.data('select2') ? $field.select2('val', '') : $field.val('');
										}
									});
								}
							}
						});
				}
			})
		},

		save(address) {
			const self = this;
			if (self.ajax) self.ajax.abort();
			let data = {address};
			data[self.token] = 1;
			return self.ajax = $.ajax({
				url: self.baseUrl + '?option=com_sellacious&task=order.saveAddressAjax&format=json&id=' + address.id,
				type: 'POST',
				dataType: 'json',
				cache: false,
				data: data,
				beforeSend() {
					self.overlay();
				}
			})
				.done(r => {
					if (r.status === 1) {
						self.container.find('#address-form-' + address.id).ctechmodal('hide');
						Joomla.renderMessages({success: [r.message]});
						self.loadEditor();
					} else {
						alert(r.message);
					}
				})
				.fail(() => {
					Joomla.renderMessages({warning: ['Request failed due to unknown error.']});
				})
				.always(() => self.overlay(true));
		},

		remove(addressId) {
			const self = this;
			if (self.ajax) self.ajax.abort();
			let data = {};
			data[self.token] = 1;

			self.ajax = $.ajax({
				url: self.baseUrl + '?option=com_sellacious&task=order.removeAddressAjax&format=json&id=' + addressId,
				type: 'POST',
				dataType: 'json',
				cache: false,
				data: data,
				beforeSend() {
					self.overlay();
				}
			})
				.done(function (response) {
					if (response.status === 1) {
						Joomla.renderMessages({success: [response.message]});
						self.loadEditor();
					} else {
						Joomla.renderMessages({warning: [response.message]})
					}
				})
				.fail(function () {
					Joomla.renderMessages({warning: ['Request failed due to unknown error.']});
				})
				.always(function () {
					self.overlay(true);
				});
		},

		loadEditor() {
			const self = this;
			if (self.ajax) self.ajax.abort();

			self.element('editor').removeClass('hidden');
			self.element('boxes_container').html('<br><h3>' + Joomla.JText._("COM_SELLACIOUS_UI_PROGRESS_WAIT_MESSAGE") + '</h3>').removeClass('hidden');
			self.element('modals_container').html('');

			let data = {};
			data[self.token] = 1;
			self.ajax = $.ajax({
				url: self.baseUrl + '?option=com_sellacious&task=order.getAddressesHtmlAjax&format=json',
				type: 'POST',
				dataType: 'json',
				cache: false,
				data: data,
				beforeSend() {
					self.overlay();
				}
			})
				.done(r => {
					if (r.status === 1) {
						self.element('boxes_container').html(r.data[0]).data('shippable', r.data[2] || false);
						$(self.element('modals_container', true)).html(r.data[1]);

						self.element('boxes_container').find('.hasTooltip').tooltip();
						self.element('boxes_container').find('.hasSelect2').select2();
						self.element('boxes_container').popover({trigger: 'hover'});

						/*
						This is changed because the address modals are now appended into
						body but self.element searches for them in address container only
						*/
						$(self.element('modals_container', true)).find('.hasTooltip').tooltip();
						$(self.element('modals_container', true)).find('.hasSelect2').select2();
						$(self.element('modals_container', true)).popover({trigger: 'hover'});

						let shipTo = self.element('input_shipping').val();
						let billTo = self.element('input_billing').val();

						self.element('editor').removeClass('has-shipping');
						self.element('editor').removeClass('has-billing');

						if (shipTo) self.element('btn_ship_here').each(function () {
							if ($(this).data('id') === shipTo) {
								$(this).addClass('ctech-btn-info active').removeClass('ctech-btn-light');
								self.element('editor').addClass('has-shipping');
							}
						});

						if (billTo) self.element('btn_bill_here').each(function () {
							if ($(this).data('id') === billTo) {
								$(this).addClass('ctech-btn-info active').removeClass('ctech-btn-light');
								self.element('editor').addClass('has-billing');
							}
						});

						// Open first edit form automatically
						const $boxes = self.element('box_single_class') || [];
						if ($boxes.length === 0) self.element('btn_add_new').click();
					} else {
						self.element('boxes_container').html(
							'<a class="btn btn-small pull-right btn-refresh btn-default margin-5">' +
							'<i class="fa fa-refresh"></i> </a><div class="clearfix"></div>'
						);
						Joomla.renderMessages({warning: [r.message]})
					}
				})
				.fail(() => {
					self.element('boxes_container').html(
						'<a class="btn btn-small pull-right btn-refresh btn-default margin-5">' +
						'<i class="fa fa-refresh"></i> </a><div class="clearfix"></div>'
					);
					Joomla.renderMessages({warning: ['Request failed due to unknown error.']});
				})
				.always(() => {
					if (self.guest) {
						const $boxes = self.element('box_single_class') || [];
						$boxes.find('.remove-address').addClass('hidden');
						self.element('btn_add_new').toggleClass('hidden', $boxes.length >= 2);
					}
					self.overlay(true);
					// Fix for t3 framework's radio buttons override
					$('.address-residential').removeClass('t3onoff');
				});
		},

		overlay(hide) {
			const self = this;
			let overlay = self.container.find('.ajax-overlay');
			if (!overlay.length) {
				overlay = $('<div>', {'class': 'ajax-overlay'});
				self.container.append(overlay);
			}
			self.container.toggleClass('ajax-running', !hide);
		},

		getFormFields() {
			const self = this;
			// Optimised to calculate only once
			if (typeof self.formFields === 'undefined' || Object.keys(self.formFields).length === 0) {
				self.formFields = {};
				const forms = self.element('modal_form', true);
				if ($(forms).length) {
					$(forms).eq(0).find('[class*=" address-"],[class^="address-"]').each(function () {
						const field = $(this).attr('class').match(/address-([\w]+)/i);
						if (field) self.formFields[field[1]] = $(this).is('.required') ? 1 : 0;
					});
				}
			}
			return self.formFields;
		},

		validate($form) {
			let self = this;
			let data = {};
			let invalid = {};
			let valid = true;
			let fields = self.getFormFields();
			$.each(fields, function (fieldKey, required) {
				let field = $form.find('.address-' + fieldKey);
				let field_input = field.is('fieldset') ? field.find('input:checked') : field.filter(':input');
				let labels = $(field).closest('tr').find('label');
				let value = $.trim(field_input.val());
				if (required && value === '') {
					field_input.addClass('invalid');
					labels.addClass('invalid');
					valid = false;
					invalid[fieldKey] = value;
				} else {
					field_input.removeClass('invalid');
					labels.removeClass('invalid');
					data[fieldKey] = value;
				}
			});
			if (valid) return data;
			Joomla.renderMessages({warning: ['Invalid or incomplete address form.']});
			console.log('Invalid form:', data, invalid);
			return false;
		},

		shipSelect(event, el) {
			const self = this;
			let address_id = $(el).data('id');
			self.element('input_shipping').val(address_id);
			self.element('btn_ship_here').removeClass('active ctech-btn-info').addClass('ctech-btn-light');
			self.element('editor').addClass('has-shipping');
			$(el).addClass('ctech-btn-info active').removeClass('ctech-btn-light');
		},

		billSelect(event, el) {
			const self = this;
			let address_id = $(el).data('id');
			self.element('input_billing').val(address_id);
			self.element('btn_bill_here').removeClass('active ctech-btn-info').addClass('ctech-btn-light');
			self.element('editor').addClass('has-billing');
			$(el).addClass('ctech-btn-info active').removeClass('ctech-btn-light');
		},

		submitSelect() {
			const self = this;

			let billTo = self.element('input_billing').val();
			let shipTo = self.element('input_shipping').val();

			billTo = parseInt(billTo);
			shipTo = parseInt(shipTo);
			billTo = isNaN(billTo) ? 0 : billTo;
			shipTo = isNaN(shipTo) ? 0 : shipTo;

			let hasBT = self.element('editor').is('.has-billing');
			let hasST = self.element('editor').is('.has-shipping');

			let isShippable = self.element('boxes_container').data('shippable');

			let btSet = billTo && hasBT;
			let stSet = (shipTo && hasST) || !isShippable;

			if (btSet && stSet) {
				Joomla.removeMessages();
				self.saveSelected(billTo, shipTo)
					.done(function () {
						if (response.status === 1) {
							self.element('editor').addClass('hidden');
							self.element('viewer').removeClass('hidden');
						}
					});
			} else if (btSet) {
				Joomla.renderMessages({warning: [Joomla.JText._('COM_SELLACIOUS_ORDER_ADDRESS_SHIPPING_EMPTY_MESSAGE')]});
			} else if (stSet) {
				Joomla.renderMessages({warning: [Joomla.JText._('COM_SELLACIOUS_ORDER_ADDRESS_BILLING_EMPTY_MESSAGE')]});
			} else {
				Joomla.renderMessages({warning: [Joomla.JText._('COM_SELLACIOUS_ORDER_ADDRESSES_EMPTY_MESSAGE')]});
			}
		},

		saveSelected(billTo, shipTo) {
			const self = this;
			if (self.ajax) self.ajax.abort();
			let data = {
				order_id: self.orderId,
				bt: billTo,
				st: shipTo
			};
			data[self.token] = 1;

			return self.ajax = $.ajax({
				url: self.baseUrl + '?option=com_sellacious&task=order.setAddressesAjax&format=json',
				type: 'POST',
				dataType: 'json',
				cache: false,
				data: data,
				beforeSend() {
					self.overlay();
				}
			})
				.done(function (response) {
					if (response.status === 1) {
						window.location.href = `${window.location.href}`;
					} else {
						Joomla.renderMessages({warning: [response.message]})
					}
				})
				.fail(function () {
					Joomla.renderMessages({warning: ['Request failed due to unknown error.']});
				})
				.always(function () {
					self.overlay(true);
				});
		}
	};

	$(document).ready(function () {
		const o = new StepAddress();
	});
});

