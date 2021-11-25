/**
 * @version     1.7.4
 * @package     sellacious
 *
 * @copyright   Copyright (C) 2012-2019 Bhartiy Web Technologies. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Aditya Chakraborty <info@bhartiy.com> - http://www.bhartiy.com
 */

(function ($) {
	$(document).ready(function () {
		$('#jform_basic_params_custom_product_url').closest('.input-row').hide();
		$('#jform_basic_params_custom_product_menu').closest('.input-row').hide();
		$('#jform_basic_params_custom_url_append_query').closest('.input-row').hide();

		$('#jform_basic_params_custom_url_type').on('change', function () {
			var value = $('#jform_basic_params_custom_url_type').val();

			if (value == 1) {
				$('#jform_basic_params_custom_product_url').closest('.input-row').show();
				$('#jform_basic_params_custom_product_menu').closest('.input-row').hide();
				$('#jform_basic_params_custom_url_append_query').closest('.input-row').show();
			} else if (value == 2) {
				$('#jform_basic_params_custom_product_url').closest('.input-row').hide();
				$('#jform_basic_params_custom_product_menu').closest('.input-row').show();
				$('#jform_basic_params_custom_url_append_query').closest('.input-row').show();
			} else {
				$('#jform_basic_params_custom_product_url').closest('.input-row').hide();
				$('#jform_basic_params_custom_product_menu').closest('.input-row').hide();
				$('#jform_basic_params_custom_url_append_query').closest('.input-row').hide();
			}
		}).triggerHandler('change');

		$('#tab-variants .add-variant-form').on('onVariantFillForm', function (event, data) {
			var $fieldset = $(this);

			$fieldset.find('#jform_variant_params_custom_product_url').closest('.input-row').hide();
			$fieldset.find('#jform_variant_params_custom_product_menu').closest('.input-row').hide();
			$fieldset.find('#jform_variant_params_custom_url_append_query').closest('.input-row').hide();

			if (data.params != undefined) {
				$fieldset.find('#jform_variant_params_custom_url_type').val(data.params.custom_url_type);
				$fieldset.find('#jform_variant_params_custom_product_url').val(data.params.custom_product_url);
				$fieldset.find('#jform_variant_params_custom_product_menu').val(data.params.custom_product_menu).triggerHandler('change');
				$fieldset.find('#jform_variant_params_custom_url_append_query').val(data.params.custom_url_append_query);
			}

			$fieldset.find('#jform_variant_params_custom_url_type').on('change', function () {
				var value = $fieldset.find('#jform_variant_params_custom_url_type').val();

				if (value == 1) {
					$fieldset.find('#jform_variant_params_custom_product_url').closest('.input-row').show();
					$fieldset.find('#jform_variant_params_custom_product_menu').closest('.input-row').hide();
					$fieldset.find('#jform_variant_params_custom_url_append_query').closest('.input-row').show();
				} else if (value == 2) {
					$fieldset.find('#jform_variant_params_custom_product_url').closest('.input-row').hide();
					$fieldset.find('#jform_variant_params_custom_product_menu').closest('.input-row').show();
					$fieldset.find('#jform_variant_params_custom_url_append_query').closest('.input-row').show();
				} else {
					$fieldset.find('#jform_variant_params_custom_product_url').closest('.input-row').hide();
					$fieldset.find('#jform_variant_params_custom_product_menu').closest('.input-row').hide();
					$fieldset.find('#jform_variant_params_custom_url_append_query').closest('.input-row').hide();
				}
			}).triggerHandler('change');
		});
	});
})(jQuery);
