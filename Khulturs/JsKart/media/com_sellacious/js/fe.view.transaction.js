/**
 * @version     2.0.0
 * @package     sellacious
 *
 * @copyright   Copyright (C) 2012-2020 Bhartiy Web Technologies. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Aditya Chakraborty <info@bhartiy.com> - http://www.bhartiy.com
 */

(function ($) {
	$(document).ready(function () {
		$('select').select2();

		var paths = Joomla.getOptions('system.paths', {});
		var base = paths.root || '';
		var token = Joomla.getOptions('csrf.token');

		$('.btn-pay-now').on('click', function () {
			var adminForm = $('#adminForm');
			var paymentForm = $(this).closest('form');

			if (!document.formvalidator.isValid(adminForm[0]) || !document.formvalidator.isValid(paymentForm[0]) ) {
			   return false;
			}

			var paymentMethodId = paymentForm.find('input[name="jform[method_id]"]').val();
			adminForm.find('#jform_payment_method_id').val(paymentMethodId);

			var transactionData = adminForm.serializeArray();
			var paymentData = paymentForm.serializeArray();

			var data = transactionData;
			data.push({name: 'option', value: 'com_sellacious'});
			data.push({name: 'task', value: 'transaction.saveAjax'});

			$.ajax({
				url: base + '/index.php',
				type: 'POST',
				dataType: 'json',
				cache: false,
				data: data,
				beforeSend: function () {
					$('.transaction-add').addClass('ajax-running');
				}
			}).done(function (response) {
				if (response.success == true && response.data.txn_id) {
					setPayment(response.data.txn_id, paymentData);
				} else {
					Joomla.renderMessages({warning: [response.message]});
				}
			}).fail(function (jqXHR) {
				Joomla.renderMessages({warning: ['Request failed due to unknown error.']});
				console.log(jqXHR.responseText);
			});
		});

		var setPayment = function (txn_id, payment_data) {
			var data = payment_data;
			data.push({name: 'option', value: 'com_sellacious'});
			data.push({name: 'task', value: 'transaction.setPaymentAjax'});
			data.push({name: 'txn_id', value: txn_id});
			data.push({name: token, value: '1'});

			$.ajax({
				url: base + '/index.php',
				type: 'POST',
				dataType: 'json',
				cache: false,
				data: data,
				complete: function() {
					$('.transaction-add').removeClass('ajax-running');
				}
			}).done(function (response) {
				if (response.success == true) {
					window.location.href = response.data.redirect;
				} else {
					Joomla.renderMessages({warning: [response.message]});
				}
			}).fail(function (jqXHR) {
				Joomla.renderMessages({warning: ['Request failed due to unknown error.']});
				console.log(jqXHR.responseText);
			});
		};
	});
})(jQuery);
