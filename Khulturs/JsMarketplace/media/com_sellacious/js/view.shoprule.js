/**
 * @version     1.7.4
 * @package     sellacious
 *
 * @copyright   Copyright (C) 2012-2019 Bhartiy Web Technologies. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Aditya Chakraborty <info@bhartiy.com> - http://www.bhartiy.com
 */
jQuery(document).ready(function ($) {
	$('#jform_apply_rule_on_price_display input[type="radio"]').on('change', function () {
		if ($('#jform_apply_rule_on_price_display0').is(':checked')) {
			$('#jform_apply_rule_on_list_price').closest('.input-row').show();
		} else {
			$('#jform_apply_rule_on_list_price').closest('.input-row').hide();
		}
	}).triggerHandler('change');
});
