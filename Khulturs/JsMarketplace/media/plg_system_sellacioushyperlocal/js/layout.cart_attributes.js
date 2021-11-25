jQuery(document).ready(function ($) {
	$(document).on('onAddCartOptions', function (event, options, element) {
		var delivery_date_field = $(element).closest('.product-single').find('#product-info .delivery_date');
		var delivery_slot_field = $(element).closest('.product-single').find('#product-info select.delivery_slot');

		if (delivery_date_field.length) {
			options.delivery_date = delivery_date_field.val();
		}

		if (delivery_slot_field.length) {
			var value = delivery_slot_field.val();

			if (value == "") {
				// Select the first option by default if none is selection
				value = delivery_slot_field.find('option:eq(1)').val();
			}

			options.delivery_slot = value;
		}
	});

	$('button.btn-date').on('click', function () {
		var deliveryDate = $(this).data('delivery-date');
		$('#delivery_date').val(deliveryDate);
		$('#delivery_date_dtp').trigger('dp.change');
	});
});
