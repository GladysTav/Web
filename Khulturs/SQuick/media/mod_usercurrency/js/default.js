/**
 * @version     2.0.0
 * @package     sellacious
 *
 * @copyright   Copyright (C) 2012-2020 Bhartiy Web Technologies. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Izhar Aazmi <info@bhartiy.com> - http://www.bhartiy.com
 */
(function ($) {
	$(document).ready(function () {

		// Skip already converted select2
		$('select#mod_usercurrency_list').not('.select2-offscreen').select2();

		const element = $("#mod_usercurrency_list");

		element.on('change', () => {

			const value = element.val();
			const $this = $(this);

			$.ajax({
				url: 'index.php?option=com_sellacious&task=setCurrencyAjax',
				type: 'POST',
				dataType: 'json',
				cache: false,
				data: { c: value }
			}).done((response) => {
				if (response.state == 1) {
					// Reload so that the prices are reflected accordingly
					window.location.href = window.location.href.split('#')[0];
				} else {
					$this.val('');
					Joomla.renderMessages({warning: [response.message]});
				}
			}).fail((jqXHR) => {
				Joomla.renderMessages({warning: 'Failed to change your currency display preference.'});
				console.log(jqXHR.responseText);
			})
		});
	});
})(jQuery);
