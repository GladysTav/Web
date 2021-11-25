/**
 * @version     1.7.3
 * @package     sellacious
 *
 * @copyright   Copyright (C) 2012-2019 Bhartiy Web Technologies. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Izhar Aazmi <info@bhartiy.com> - http://www.bhartiy.com
 */
jQuery(document).ready(function ($) {
	let element = $('#mod_usercurrency_list');
	element.on('change', e => {
		let value = $('#mod_usercurrency_list').val();
		$.ajax({
			url: 'index.php?option=com_sellacious&task=setCurrencyAjax',
			type: 'POST',
			dataType: 'json',
			cache: false,
			data: {c: value},
			success: function (response) {
				if (response.state === 1) {
					// Reload so that the prices are reflected accordingly
					window.location.href = window.location.href.split('#')[0];
				} else {
					element.val('');
					Joomla.renderMessages({warning: [response.message]});
				}
			},
			error: function (jqXHR) {
				Joomla.renderMessages({warning: 'Failed to change your currency display preference.'});
				console.log(jqXHR.responseText);
			}
		});
	});
});

