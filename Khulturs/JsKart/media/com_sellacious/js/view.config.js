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
		var token = Joomla.getOptions('csrf.token');
		$('.checkboxes.btn-group').each(function () {
			let checkboxGrp = $(this);
			$(this).find('input[type="checkbox"]').on('change', function () {
				let name = $(this).attr('name');
				let values = $('input[name="' + name + '"]:checked').map(function () {
					return $(this).val();
				}).get();

				if (!values.length) {
					$('<input>').attr({
						type: 'hidden',
						name: name,
						value: ''
					}).appendTo(checkboxGrp);
				}
			}).triggerHandler('change')
		});

		// Todo: Optimize to be called on specific clicks and not global ones
		$(':input').on('change click', function () {
			// Layout Switcher
			var lsdChk = $('#jform_com_sellacious_list_switcher_display');
			var lsdRdo = $('#jform_com_sellacious_list_style');
			var lsdOpts = lsdChk.find('input[type="checkbox"]');

			lsdOpts.each(function () {
				var cbVal = $(this).val();
				var targetOption = lsdRdo.find('input[value="' + cbVal + '"]');
				if ($(this).is(':checked')) {
					targetOption.attr('disabled', null);
					targetOption.closest('label').removeClass('hidden');
				} else {
					targetOption.attr('disabled', 'disabled').attr('checked', null).removeAttr('active');
					targetOption.closest('label').removeClass('active').addClass('hidden');

					var lsdRdoAct = lsdRdo.find('input[type="radio"]:not([disabled])');
					if (lsdRdoAct.length > 0) {
						lsdRdoAct.each(function () {
							$(this).attr('checked', null);
							$(this).closest('label').removeClass('active');
						});
						lsdRdoAct.first().attr('checked', 'checked');
						lsdRdoAct.first().closest('label').addClass('active');
					}
				}
			});
		})
		// This is too costly for the browser if called for each element, hence call for just the first one
			.eq(0).triggerHandler('change');

		$('#jform_com_sellacious_category_menu_menutype').change(function () {
			var menutype = $(this).val();
			var $categoryMenuParent = $('#jform_com_sellacious_category_menu_parent');
			var oldVal = $categoryMenuParent.val();
			$categoryMenuParent.val('1').trigger('change');
			$categoryMenuParent.find('option').each(function () {
				$(this).val() === '1' || $(this).remove();
			});
			if (menutype === '-') {
				$categoryMenuParent.closest('.input-row').hide();
			} else {
				$categoryMenuParent.closest('.input-row').show();
				$.ajax({
					url: 'index.php?option=com_menus&task=item.getParentItem&menutype=' + menutype,
					dataType: 'json'
				}).done(function (data) {
					$.each(data, function (i, val) {
						var option = $('<option>');
						option.text(val.title).val(val.id);
						$categoryMenuParent.append(option);
					});
					$categoryMenuParent.val(oldVal).trigger('change');
				});
			}
		}).triggerHandler('change');

		var orgForm = $("form#adminForm").serialize();

		$("form#adminForm").on("change", function (event) {
			var changedForm = $("form#adminForm").serialize();

			if (changedForm != orgForm) {
				$("form#adminForm").attr("data-form-changed", true);
			}
		});

		$('.btn-refresh-rating-cache').on('click', function (e) {
			e.preventDefault();

			var btn = $(this);
			var data = {
				option: 'com_sellacious',
				task: 'products.refreshCacheAjax',
				key: 'productratings'
			};

			data[token] = 1;

			$.ajax({
				url: 'index.php',
				type: 'POST',
				dataType: 'json',
				cache: false,
				data: data,
				beforeSend: function () {
					btn.attr("disabled", true);
					btn.text(Joomla.JText._('COM_SELLACIOUS_CONFIG_RATING_CACHE_REFRESHING'));
				},
				complete: function () {
					btn.attr("disabled", false);
					btn.text(Joomla.JText._('COM_SELLACIOUS_CONFIG_REFRESH_RATING_CACHE'));
				}
			}).done(function (response) {
				if (response.success === true) {
					Joomla.renderMessages({success: [Joomla.JText._('COM_SELLACIOUS_CONFIG_RATING_REFRESH_SUCCESS')]});
				} else {
					Joomla.renderMessages({warning: [response.message]});
				}
			}).fail(function (jqXHR) {
				Joomla.renderMessages({warning: ['Request failed due to unknown error.']});
				console.log(jqXHR.responseText);
			});
		});

		$(document).on('subform-row-add', function (event, row) {
			$(row).find('select').select2();
		});
	});
})(jQuery);
