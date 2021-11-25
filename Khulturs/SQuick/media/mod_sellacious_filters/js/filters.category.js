/**
 * @version     2.0.0
 * @package     sellacious
 *
 * @copyright   Copyright (C) 2012-2020 Bhartiy Web Technologies. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Izhar Aazmi <info@bhartiy.com> - http://www.bhartiy.com
 */
jQuery(document).ready($ => {

	// Filter Category List Accordion
	function catTreeView() {
		$('#filter-list-group').treeview({
			collapsed: true,
			animated: 'normal',
			unique: true,
			persist: 'location'
		})
			.find('li>a.active')
			.parentsUntil('.treeview', 'ul')
			.css('display', 'block');
	}

	catTreeView();

	$('[data-show-all="category"]').on('click', function (e) {
		const self = $(this);
		e.preventDefault();
		$.ajax({
			url: 'index.php?option=com_ajax&module=sellacious_filters&method=getFilter&format=json',
			type: 'POST',
			data: {
				filter: 'category',
				args: {
					scope: 'html',
					parent_id: self.data('parent_id') || 1,
					category_id: self.data('categoryId'),
					store_id: self.data('storeId')
				},
			},
			dataType: 'json',
			cache: false
		}).done(r => {
			if (r.success) {
				$('#filter-list-group').html(r.data);
				catTreeView();
				self.hide();
			} else {
				Joomla.renderMessages({error: [r.message]});
			}
		}).fail(() => {
			Joomla.renderMessages({error: ['There was an error while processing your request. Please try later.']});
		});
	});
});
