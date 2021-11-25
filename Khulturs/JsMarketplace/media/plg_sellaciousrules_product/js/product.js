/**
 * @version     1.7.4
 * @package     sellacious
 *
 * @copyright   Copyright (C) 2012-2019 Bhartiy Web Technologies. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Aditya Chakraborty <info@bhartiy.com> - http://www.bhartiy.com
 */
jQuery(document).ready(function ($) {
	$('#jform_discount_class_groups').on('select2-selecting', function (e) {
		var paths = Joomla.getOptions('system.paths', {});
		var base = paths.root || '';

		var data = {
			option: 'com_ajax',
			plugin: 'product',
			group : 'sellaciousrules',
			format: 'json',
			method: 'getProductField',
			class_group: e.choice.text
		};

		$.ajax({
			url: base + '/index.php',
			type: 'POST',
			dataType: 'json',
			cache: false,
			data: data,
		}).done(function (response) {
			if (response.success == true) {
				$(response.data.layout).appendTo($('#jform_product_products').closest('.input-row').closest('fieldset'));
			} else {
				Joomla.renderMessages({warning: [response.message]});
			}
		}).fail(function (jqXHR) {
			Joomla.renderMessages({warning: ['Request failed due to unknown error.']});
			console.log(jqXHR.responseText);
		});
	});

	$('#jform_discount_class_groups').on('select2-removing', function (e) {
		var class_group = e.choice.text;
		class_group = class_group.replace(/\s+/g,"_").toLowerCase();

		$('#jform_product_' + class_group).closest('.input-row').remove();
	});
});
