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
		$('#jform_params_custom_product_url').closest('.input-row').hide();
		$('#jform_params_custom_product_menu').closest('.input-row').hide();
		$('#jform_params_custom_url_append_query').closest('.input-row').hide();

		$('#jform_params_custom_url_type').on('change', function () {
			var value = $('#jform_params_custom_url_type').val();

			if (value == 1) {
				$('#jform_params_custom_product_url').closest('.input-row').show();
				$('#jform_params_custom_product_menu').closest('.input-row').hide();
				$('#jform_params_custom_url_append_query').closest('.input-row').show();
			} else if (value == 2) {
				$('#jform_params_custom_product_url').closest('.input-row').hide();
				$('#jform_params_custom_product_menu').closest('.input-row').show();
				$('#jform_params_custom_url_append_query').closest('.input-row').show();
			} else {
				$('#jform_params_custom_product_url').closest('.input-row').hide();
				$('#jform_params_custom_product_menu').closest('.input-row').hide();
				$('#jform_params_custom_url_append_query').closest('.input-row').hide();
			}
		}).triggerHandler('change');
	});
})(jQuery);
