/**
 * @version     1.7.4
 * @package     sellacious
 *
 * @copyright   Copyright (C) 2012-2019 Bhartiy Web Technologies. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Izhar Aazmi <info@bhartiy.com> - http://www.bhartiy.com
 */
jQuery(document).ready(function ($) {
	$('.jff-checkmatrix').each(function () {
		var $matrix = $(this);
		var $input = $matrix.find('.jff-checkmatrix-input');
		var value = $.trim($input.val());
		try {
			var $values = value === '' ? {} : JSON.parse(value);
		} catch (e) {
			$values = {};
		}
		$matrix.on('change', 'input[type="checkbox"]', function () {
			var values = {};

			// Check all
			if ($(this).data('column') == 'product_fields') {
				$matrix.find('input[type="checkbox"]:not([data-column="product_fields"])').prop('checked', $(this).is(':checked')).triggerHandler('change');
			}

			// Check single row
			if ($(this).data('row') == 'row_checked') {
				$(this).closest('tr').find('input[type="checkbox"]:not([data-column="row_checked"])').prop('checked', $(this).is(':checked')).triggerHandler('change');

				$matrix.find('input[type="checkbox"][data-row="col_checked"]').each(function () {
					var $column = $(this).data('column');
					var $col_fields_checked = ($matrix.find('input[type="checkbox"][data-column="' + $column + '"]:not([data-row="col_checked"])').length == $matrix.find('input[type="checkbox"][data-column="' + $column + '"]:not([data-row="col_checked"]):checked').length);
					$matrix.find('input[type="checkbox"][data-column="' + $column + '"][data-row="col_checked"]').prop('checked', $col_fields_checked).triggerHandler('change');
				});
			}

			// Check single column
			if ($(this).data('row') == 'col_checked') {
				var $column = $(this).data('column');
				$matrix.find('input[type="checkbox"][data-column="' + $column + '"]:not([data-row="col_checked"])').prop('checked', $(this).is(':checked')).triggerHandler('change');

				$matrix.find('input[type="checkbox"][data-row="row_checked"]').each(function () {
					var $row_fields_checked = ($(this).closest('tr').find('input[type="checkbox"]:not([data-row="row_checked"])').length == $(this).closest('tr').find('input[type="checkbox"]:not([data-row="row_checked"]):checked').length);
					$(this).closest('tr').find('input[type="checkbox"][data-row="row_checked"]').prop('checked', $row_fields_checked).triggerHandler('change');
				});
			}

			// Make sure check all, check single rows and check single columns are synchronised
			if ($(this).data('row') != 'row_checked' && $(this).data('row') != 'col_checked') {
				var $row_fields_checked = ($(this).closest('tr').find('input[type="checkbox"]:not([data-row="row_checked"])').length == $(this).closest('tr').find('input[type="checkbox"]:not([data-row="row_checked"]):checked').length);
				$(this).closest('tr').find('input[type="checkbox"][data-row="row_checked"]').prop('checked', $row_fields_checked).triggerHandler('change');

				var $column = $(this).data('column');
				var $col_fields_checked = ($matrix.find('input[type="checkbox"][data-column="' + $column + '"]:not([data-row="col_checked"])').length == $matrix.find('input[type="checkbox"][data-column="' + $column + '"]:not([data-row="col_checked"]):checked').length);
				$matrix.find('input[type="checkbox"][data-column="' + $column + '"][data-row="col_checked"]').prop('checked', $col_fields_checked).triggerHandler('change');
			}

			var $all_fields_checked = ($matrix.find('input[type="checkbox"]:not([data-column="product_fields"])').length == $matrix.find('input[type="checkbox"]:not([data-column="product_fields"]):checked').length);
			$matrix.find('input[type="checkbox"][data-column="product_fields"]').prop('checked', $all_fields_checked).triggerHandler('change');

			$matrix.find('input[type="checkbox"]').each(function () {
				var x = $(this).data('column');
				var y = $(this).data('row');
				var v = $(this).prop('checked');
				if (v) {
					typeof values[x] === 'undefined' && (values[x] = {});
					values[x][y] = 1;
				}
			});

			$input.val(JSON.stringify(values));
		}).find('input[type="checkbox"]').each(function () {
			var x = $(this).data('column');
			var y = $(this).data('row');
			if (typeof $values[x] === 'object' && typeof $values[x][y] !== 'undefined' && $values[x][y] === 1) {
				$(this).prop('checked', true);
			}
		});
	})
});
