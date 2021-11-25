/**
 * @version     2.0.0
 * @package     sellacious
 *
 * @copyright   Copyright (C) 2012-2020 Bhartiy Web Technologies. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Aditya Chakraborty <info@bhartiy.com> - http://www.bhartiy.com
 */
jQuery(document).ready(function ($) {
	$('.btn-coq-submit').on('click', function () {
		let form = $(this).closest('form');
		$(this).attr('disabled', true);
		$(this).text(Joomla.JText._('COM_SELLACIOUS_PRODUCT_CHECKOUT_QUESTIONS_FORM_SUBMIT_PROCESSING'));

		if (document.formvalidator.isValid(form[0])) {
			let formData = jQuery(form).find('.coq_form_wrapper').find('input, select, textarea').serializeArray();
			let data = {};
			let code = form.find('#product_code').val();

			$(formData).each(function(i, field){
				data[field.name] = field.value;
			});

			window.parent.postMessage(JSON.stringify({triggerEvent: "addToCart",  args : {uid: code, data: data}}), '*');
		} else {
			alert(Joomla.JText._('COM_SELLACIOUS_VALIDATION_FORM_FAILED'));
		}

		$(this).attr('disabled', false);
		$(this).text(Joomla.JText._('COM_SELLACIOUS_PRODUCT_CHECKOUT_QUESTIONS_FORM_SUBMIT'));
	});
});
