/**
 * @version     2.0.0
 * @package     sellacious
 *
 * @copyright   Copyright (C) 2012-2020 Bhartiy Web Technologies. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Izhar Aazmi <info@bhartiy.com> - http://www.bhartiy.com
 */
jQuery(document).ready(function ($) {
	const $filters = $('.mod-sellacious-filters');

	$filters.each(function () {
		const $filter = $(this);

		// Narrow choices on typing
		$filter.find('.search-filter').on('keyup', 'input[type="text"]', e => {
			const $this = $(e.target);
			const val = $this.val();
			const choices = $this.closest('.filter-snap-in').find('.filter-choice');
			const regex = new RegExp(val);

			$.each(choices, (i, choice) => {
				const val = $(choice).find('input').val();
				regex.test(val) ? $(choice).show('fast') : $(choice).hide('fast');
			});
		});

		// Clear individual filter
		$filter.on('click', '.clear-filter', function (e) {
			const $this = $(e.target);
			$filter.find('.search-filter').find('input[type="text"]').val('').trigger('keyup');
			$filter.find('.filter-price-area').find('input[type="number"]').val('').trigger('keyup');
			const choices = $this.closest('.filter-snap-in').find('input[type="checkbox"]');
			choices.not(':disabled').prop('checked', false);

			$(this).closest('form').submit();
		});

		// Collapse filter box
		$filter.find('.filter-title').click(function (e) {
			$(e.target).is('.clear-filter') ||
			$(this).closest('.filter-snap-in').toggleClass('filter-collapse');
		});

		// Hide filters on phone
		$filter.find('.filter-head').click(function () {
			$filter.toggleClass('closed-on-phone');
		});

		// Move this to store location filter element
		$filter.on('click change', '.store-location-options', function (e) {
			const $this = $(e.target);

			if ($this.val() === '2') {
				$('.s-l-custom-block').removeClass('hidden');
			} else {
				$('.s-l-custom-text').val('');
				$('.s-l-custom-block').addClass('hidden');
				$(this).closest('form').submit();
			}
		});
	});

	$('.btn-clear-filter').on('click', function (e) {
		e.preventDefault();
		$.ajax({
			url: 'index.php?option=com_ajax&module=sellacious_filters&method=clearFilters&format=json',
			type: 'POST',
			dataType: 'json',
			cache: false
		}).done(r => {
			if (r.success) {
				window.location.href = `${window.location.href}`;
			} else {
				Joomla.renderMessages({warning: [r.message]});
			}
		});
	});
});
