/**
 * @version     2.0.0
 * @package     sellacious
 *
 * @copyright   Copyright (C) 2012-2020 Bhartiy Web Technologies. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Izhar Aazmi <info@bhartiy.com> - http://www.bhartiy.com
 */
(function ($) {
	// Fixed version of jQuery.clone function
	(function (original) {
		$.fn.clone = function () {
			var result = original.apply(this, arguments),
				o_textarea = this.find('textarea').add(this.filter('textarea')),
				r_textarea = result.find('textarea').add(result.filter('textarea')),
				o_select = this.find('select').add(this.filter('select')),
				r_select = result.find('select').add(result.filter('select'));

			var i, l;
			for (i = 0, l = o_textarea.length; i < l; ++i) $(r_textarea[i]).val($(o_textarea[i]).val());
			for (i = 0, l = o_select.length; i < l; ++i) r_select[i].selectedIndex = o_select[i].selectedIndex;

			return result;
		};
	})($.fn.clone);

	$(document).ready(function () {
		// Predefine functions
		var rebuildItemRows = function (status, row) {
			var $st_list = row.find('select.oi-status-list');
			var selected = $st_list.find('option').filter(':selected');
			var new_status = status['s_title'] || (selected.val() ? selected.text() : 'NA');

			var idx = row.attr('id').match(/\d+-\d+/);
			var oi_row = $('#oi-row-' + idx);

			oi_row.find('.oi-status').text(new_status);
			$st_list.find('option').not(':first-child').remove();

			if (status['next_status']) {
				$.each(status['next_status'], function (i, o) {
					var option = $('<option>').val(o.id).text(o.title);
					$st_list.append(option);
				});
			}
			$st_list.select2('destroy').select2().trigger('change');
		};

		var rebuildOrderRows = function (data, row) {
			var status = data.order_status;
			var items = data.items;
			var $st_list = row.find('select.order-status-list');
			var new_status = status['s_title'];

			var idx = row.attr('id').match(/\d+/);
			var order_row = $('#order-' + idx);
			var order_items_row = $('#order-items-' + idx);

			order_row.find('.order-status').html(new_status);

			items.forEach(function (item, key) {
				var item_status = item.status['s_title'];
				order_items_row.find('tr[data-uid="' + item.item_uid + '"] .oi-status').text(item_status);
			});

			$st_list.find('option').not(':first-child').remove();

			if (status['next_status']) {
				$.each(status['next_status'], function (i, o) {
					var option = $('<option>').val(o.id).text(o.title);
					$st_list.append(option);
				});
			}
			$st_list.select2('destroy').select2().trigger('change');
		};

		var updateLog = function (status, row) {
			row.find('.status-log-container').empty();
			var token = Joomla.getOptions('csrf.token');

			var data = {
				option: 'com_sellacious',
				task: 'orders.getItemStatusLogAjax',
				jform: {
					order_id: status.order_id,
				}
			};
			data[token] = 1;

			if (status.seller_exclusive == 1 && status.seller_uid) {
				data.jform.seller_uid = status.seller_uid;
			}

			if (status.item_uid) {
				data.jform.item_uid = status.item_uid;
			}

			$.ajax({
				url: 'index.php',
				type: 'POST',
				dataType: 'json',
				cache: false,
				data: data,
				success: function (response) {
					if (response.status == 1) {
						row.find('.status-log-container').html(response.data);
					} else {
						Joomla.renderMessages({warning: [response.message]});
					}
				},
				error: function ($xhr) {
					console.log($xhr.responseText);
				}
			});
		};

		// Set handlers
		$('.btn-toggle').click(function (e) {
			e.preventDefault();
			var cell = $(this).closest('td');
			var rec = $(cell).data('row');
			$(rec).toggleClass('hidden');
			cell.find('.btn-toggle').toggleClass('hidden');

			// If collapsed implicitly close form
			if ($(rec).is('.hidden')) {
				$(rec).find('.btn-oi-status-close').triggerHandler('click');
			}
		});

		$('.btn-oi-status-edit').click(function (e) {
			e.preventDefault();

			var row_index = $(this).closest('tr').attr('id').match(/(\d+)-(\d+)/);
			if (!row_index) return;

			// Hide all other rows for clear view of current one
			var node = '#order-' + row_index[1] + ',' + '#order-items-' + row_index[1];
			var infoRow = $('#oi-info-row-' + row_index); // row_index[0]

			var isOpen = !infoRow.is('.hidden');
			// Now show the form container
			infoRow.toggleClass('hidden', isOpen).find('.status-form-container').toggleClass('hidden', isOpen);

			var $rows = $('.order-row,.order-items-row').not(node).toggleClass('hide-edit', !isOpen);
			isOpen ? $rows.fadeIn('slow') : $rows.fadeOut('slow');
		});

		$('.btn-oi-status-close').click(function (e) {
			e.preventDefault();
			// closest 'tr' may match an internal table
			var infoRow = $(this).closest('[id^="oi-info-row-"]');
			var row_index = infoRow.attr('id').match(/\d+-\d+/);
			var oiRow = $('#oi-row-' + row_index);

			infoRow.addClass('hidden').find('.status-form-container').addClass('hidden');
			// oiRow.find('.oi-show-info').removeClass('hidden');

			// Show rows that were hidden for clear view of current one
			// $('.order-items-row,.order-row').not(oiRow).not(infoRow).addClass('hide-edit').hide();
			$('.hide-edit').removeClass('hide-edit').show();
		});

		$('.btn-order-status-edit').click(function (e) {
			e.preventDefault();

			var row_index = $(this).closest('tr').attr('id').match(/(\d+)/);
			if (!row_index) return;

			// Hide all other rows for clear view of current one
			var node = '#order-' + row_index[1];
			var itemnode = '#order-items-' + row_index[1];
			var infoRow = $('#order-info-row-' + row_index); // row_index[0]

			var isOpen = !infoRow.is('.hidden');

			infoRow.toggleClass('hidden');

			// Now show the form container
			infoRow.toggleClass('hidden', isOpen).find('.status-form-container').toggleClass('hidden', isOpen);

			var $rows = $('.order-row,.order-items-row').not(node + ',' + itemnode).toggleClass('hide-edit', !isOpen);
			isOpen ? $rows.fadeIn('slow') : $rows.fadeOut('slow');
		});

		$('.btn-order-status-close').click(function (e) {
			e.preventDefault();
			// closest 'tr' may match an internal table
			var infoRow = $(this).closest('[id^="order-info-row-"]');
			var row_index = infoRow.attr('id').match(/\d+/);
			var oRow = $('#order-row-' + row_index);

			oRow.addClass('hidden');

			infoRow.addClass('hidden').find('.status-form-container').addClass('hidden');
			$('.hide-edit').removeClass('hide-edit').show();
		});

		$('select.oi-status-list').change(function () {
			var $formTable = $(this).closest('.status-form-table');

			var token = Joomla.getOptions('csrf.token');
			var head = $formTable.find('thead');
			var foot = $formTable.find('tfoot');

			var $table = $('<table>').append(head.clone()).append(foot.clone());
			var values = $('<form>').append($table).serializeObject();
			var $data = $.extend({}, values, {
				option: 'com_sellacious',
				task: 'orders.getItemStatusFormAjax'
			});
			$data[token] = 1;

			$formTable.find('tbody').empty().append('<tr><td colspan="2"><h5 class="red">'+ Joomla.JText._("COM_SELLACIOUS_UI_PROGRESS_WAIT_MESSAGE") + '</h5></td></tr>');

			$.ajax({
				url: 'index.php',
				type: 'POST',
				dataType: 'json',
				cache: false,
				data: $data
			}).done(function (response) {
				if (response.status == 1) {
					$formTable.find('tbody').empty();
					var html = $(response.data);
					html.find('.textarea,.inputbox').addClass('form-control').attr('form', 'order-status-form');
					$formTable.find('tbody').append(html);
				} else {
					Joomla.renderMessages({warning: [response.message]});
				}
			}).fail(function ($xhr) {
				console.log($xhr.responseText);
			});
		}).trigger('change');

		$(document).on('change', 'select.order-status-list', function () {
			var $formTable = $(this).closest('.status-form-table');

			var token = Joomla.getOptions('csrf.token');
			var head = $formTable.find('thead');
			var foot = $formTable.find('tfoot');

			var $table = $('<table>').append(head.clone()).append(foot.clone());
			var values = $('<form>').append($table).serializeObject();
			var $data = $.extend({}, values, {
				option: 'com_sellacious',
				task: 'orders.getItemStatusFormAjax'
			});
			$data[token] = 1;

			$formTable.find('tbody').empty().append('<tr><td colspan="2"><h5 class="red">Please wait&hellip;</h5></td></tr>');

			$.ajax({
				url: 'index.php',
				type: 'POST',
				dataType: 'json',
				cache: false,
				data: $data
			}).done(function (response) {
				if (response.status == 1) {
					$formTable.find('tbody').empty();
					var html = $(response.data);
					html.find('.textarea,.inputbox').addClass('form-control').attr('form', 'order-status-form');
					$formTable.find('tbody').append(html);
				} else {
					Joomla.renderMessages({warning: [response.message]});
				}
			}).fail(function ($xhr) {
				Joomla.renderMessages({warning: ['Request failed due to unknown error.']});
			});
		});

		$('select.order-status-list').trigger('change');

		$('select.order-sellers-list').change(function () {
			let $this = $(this);
			var $formTable = $this.closest('.status-form-table');

			var infoRow = $this.closest('.order-info-row');
			var infoHead = $this.closest('.status-form-table').find('thead');
			var args = $('<form>').append(infoHead.clone()).serializeObject();

			updateLog(args['jform'], infoRow);

			var token = Joomla.getOptions('csrf.token');
			var head = $formTable.find('thead');
			var foot = $formTable.find('tfoot');

			var $table = $('<table>').append(head.clone()).append(foot.clone());
			var values = $('<form>').append($table).serializeObject();
			var $data = $.extend({}, values, {
				option: 'com_sellacious',
				task: 'orders.getSellerStatusesAjax'
			});
			$data[token] = 1;

			$formTable.find(".btn-order-status-save").attr('disabled', true);

			$.ajax({
				url: 'index.php',
				type: 'POST',
				dataType: 'json',
				cache: false,
				data: $data
			}).done(function (response) {
				if (response.success) {
					$formTable.find(".btn-order-status-save").attr('disabled', false);
					$formTable.find('.order-status-list').select2('destroy');
					$formTable.find('.order-status-list').replaceWith(response.data);
					$formTable.find('.order-status-list').select2();
					$formTable.find('.order-status-list').trigger('change');
				} else {
					Joomla.renderMessages({warning: [response.message]});
				}
			}).fail(function ($xhr) {
				Joomla.renderMessages({warning: ['Request failed due to unknown error.']});
			});
		});

		$('.btn-order-status-save').click(function () {
			var infoRow = $(this).closest('[id^="order-info-row-"]');

			try {
				var $form = $(this).closest('.status-form-table');
				var $form_clone = $form.clone();

				var inputs = $form_clone.find(':input');
				var values = $('<form>').append($form_clone).serializeObject();

				if ($form.find('#jform_notes').attr('required') && (values.jform.notes == undefined || values.jform.notes == ''))
				{
					Joomla.renderMessages({warning: [Joomla.JText._('COM_SELLACIOUS_ORDERS_STATUS_NOTES_MISSING')]});
					return false;
				}

				// Tweak to include array indexes which otherwise gets ignored due to numeric fist character.
				$.each(inputs, function () {
					var s = $(this).attr('name');
					// /^\d/.test(s) && (values[s] = 1);
					// Why hardcoded (=1) @20160326@ ?
					/^\d/.test(s) && (values[s] = $(this).val());
				});

				var $data = $.extend({}, values, {
					option: 'com_sellacious',
					task: 'orders.setOrderStatusAjax'
				});

				$form.find('tbody').find(':input').not(':disabled').addClass('ajax-wait').attr('disabled', 'disabled');

				// todo: We should run validation against the selected form
				if ($data['jform']) $.ajax({
					url: 'index.php',
					type: 'POST',
					dataType: 'json',
					cache: false,
					data: $data
				}).done(function (response) {
					if (response.success) {
						$form.find(':input').addClass('ajax-success');
						setTimeout(function () {
							$form.find(':input').removeClass('ajax-success');
						}, 3000);
						updateLog(response.data.order_status, infoRow);
						rebuildOrderRows(response.data, infoRow);
						Joomla.renderMessages({success: [response.message]});
					} else {
						$form.find(':input').addClass('ajax-failed');
						setTimeout(function () {
							$form.find(':input').removeClass('ajax-failed');
						}, 3000);
						Joomla.renderMessages({warning: [response.message]});
					}
				}).fail(function ($xhr) {
					$form.find(':input').addClass('ajax-failed');
					setTimeout(function () {
						$form.find(':input').removeClass('ajax-failed');
					}, 3000);
					Joomla.renderMessages({warning: ['Request failed due to unknown error.']});
				}).always(function () {
					$form.find('.ajax-wait').removeClass('ajax-wait').removeAttr('disabled');
				});
			} catch (e) {
				console.log(e);
			}
		});

		$('.btn-oi-status-save').click(function () {
			var infoRow = $(this).closest('[id^="oi-info-row-"]');

			try {
				var $form = $(this).closest('.status-form-table');
				var $form_clone = $form.clone();

				var inputs = $form_clone.find(':input');
				var values = $('<form>').append($form_clone).serializeObject();

				if ($form.find('#jform_notes').attr('required') && (values.jform.notes == undefined || values.jform.notes == ''))
				{
					Joomla.renderMessages({warning: [Joomla.JText._('COM_SELLACIOUS_ORDERS_STATUS_NOTES_MISSING')]});
					return false;
				}

				// Tweak to include array indexes which otherwise gets ignored due to numeric fist character.
				$.each(inputs, function () {
					var s = $(this).attr('name');
					// /^\d/.test(s) && (values[s] = 1);
					// Why hardcoded (=1) @20160326@ ?
					/^\d/.test(s) && (values[s] = $(this).val());
				});

				var $data = $.extend({}, values, {
					option: 'com_sellacious',
					task: 'orders.setItemStatusAjax'
				});

				$form.find('tbody').find(':input').not(':disabled').addClass('ajax-wait').attr('disabled', 'disabled');

				// todo: We should run validation against the selected form
				if ($data['jform']) $.ajax({
					url: 'index.php',
					type: 'POST',
					dataType: 'json',
					cache: false,
					data: $data
				}).done(function (response) {
					if (response.status == 1) {
						$form.find(':input').addClass('ajax-success');
						setTimeout(function () {
							$form.find(':input').removeClass('ajax-success');
						}, 3000);
						updateLog(response.data, infoRow);
						rebuildItemRows(response.data, infoRow);
						Joomla.renderMessages({success: [response.message]});
					} else {
						$form.find(':input').addClass('ajax-failed');
						setTimeout(function () {
							$form.find(':input').removeClass('ajax-failed');
						}, 3000);
						Joomla.renderMessages({warning: [response.message]});
					}
				}).fail(function ($xhr) {
					$form.find(':input').addClass('ajax-failed');
					setTimeout(function () {
						$form.find(':input').removeClass('ajax-failed');
					}, 3000);
					console.log($xhr.responseText);
				}).always(function () {
					$form.find('.ajax-wait').removeClass('ajax-wait').removeAttr('disabled');
				});
			} catch (e) {
				console.log(e);
			}
		});

		$(document).on('mouseover', '.order-status-col', function () {
			$(this).find('.btn-order-status-edit').removeClass('hidden');
		});

		$(document).on('mouseout', '.order-status-col', function () {
			$(this).find('.btn-order-status-edit').addClass('hidden');
		});

		// Initial actions
		$('[id^="oi-info-row-"]').each(function () {
			var infoRow = $(this);
			var head = $(this).find('.status-form-table').find('thead');
			var args = $('<form>').append(head.clone()).serializeObject();

			updateLog(args['jform'], infoRow);
		});

		$('[id^="order-info-row-"]').each(function () {
			var infoRow = $(this);
			var head = $(this).find('.status-form-table').find('thead');
			var args = $('<form>').append(head.clone()).serializeObject();

			updateLog(args['jform'], infoRow);
		});
	});
})(jQuery);
