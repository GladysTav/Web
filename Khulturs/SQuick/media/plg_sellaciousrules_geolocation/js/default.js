/**
 * @version     2.0.0
 * @package     sellacious
 *
 * @copyright   Copyright (C) 2012-2020 Bhartiy Web Technologies. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Aditya Chakraborty <info@bhartiy.com> - http://www.bhartiy.com
 */

(function ($) {
	$(document).ready(function() {
		var checkAddressFields = function(matchField) {
			var values = $(matchField).map(function(){
				return $(this).val();
			}).get();

			$('#jform_params_geolocation_country').closest('.input-row').show();
			$('#jform_params_geolocation_state').closest('.input-row').show();
			$('#jform_params_geolocation_district').closest('.input-row').show();
			$('#jform_params_geolocation_zip').closest('.input-row').show();

			if (values.length) {
				$('#jform_params_geolocation_country').closest('.input-row').hide();
				$('#jform_params_geolocation_state').closest('.input-row').hide();
				$('#jform_params_geolocation_district').closest('.input-row').hide();
				$('#jform_params_geolocation_zip').closest('.input-row').hide();
			}
		};

		$("#jform_params_geolocation_seller_match input[type='checkbox']").change(function() {
			var name = $(this).attr('name');
			checkAddressFields('input[name="' + name + '"]:checked');
		});

		checkAddressFields('input[name="jform[params][geolocation][seller_match][]"]:checked');
	});
})(jQuery);
