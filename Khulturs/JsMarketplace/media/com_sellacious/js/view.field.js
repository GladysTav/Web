/**
 * @version     1.7.4
 * @package     sellacious
 *
 * @copyright   Copyright (C) 2012-2019 Bhartiy Web Technologies. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Saurabh Sabharwal <info@bhartiy.com> - http://www.bhartiy.com
 */

jQuery(document).ready(function ($) {
	var disabledText = '<span id="filterable-disabled" class="alert adjusted alert-warning fade in">' +
		Joomla.JText._('COM_SELLACIOUS_FIELD_FIELD_FILTERABLE_NOT_ALLOWED') + '</span>';
	
	if ($('input[name="jform[params][multiple]"]:checked').val() == 'true') {
		disableField();
	}

	$('input[name="jform[params][multiple]"]').on('change', function () {
		if($(this).val() == 'true') {
			disableField();
		} else {
			$('#jform_filterable').removeClass('disabled');
			$('#filterable-disabled').remove();
		}
	});

	function disableField() {
		$('#jform_filterable').addClass('disabled');
		$(disabledText).insertAfter($('#jform_filterable.disabled'));
		$('#jform_filterable1').trigger('click');
	}
});
