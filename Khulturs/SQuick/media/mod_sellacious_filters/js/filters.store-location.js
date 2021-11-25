/**
 * @version     2.0.0
 * @package     sellacious
 *
 * @copyright   Copyright (C) 2012-2020 Bhartiy Web Technologies. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Izhar Aazmi <info@bhartiy.com> - http://www.bhartiy.com
 */
jQuery(function ($) {
	$('.filter-location-autocomplete').each(function () {
		let $wrapper = $(this);

		const $1 = $wrapper.find('.location_custom_text');
		const $2 = $wrapper.find('.location_custom');

		// Merge this into hyperlocal somehow?
		$1.autocomplete({
			minLength: 3,
			source(request, response) {
				$.ajax({
					url: 'index.php?option=com_ajax&module=sellacious_filters&method=getAutoCompleteSearch&format=json',
					dataType: 'json',
					data: {
						term: request.term,
						parent_id: 1,
						search_in: $1.data('searchIn'),
						list_start: 0,
						list_limit: 5,
						Itemid: $1.data('itemId')
					}
				}).done(data => response(data));
			},
			select(event, ui) {
				$1.val(ui.item.value);
				$2.val(ui.item.id);
				return false;
			}
		});
	})
});
