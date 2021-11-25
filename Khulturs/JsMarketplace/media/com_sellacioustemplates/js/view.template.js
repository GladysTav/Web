/**
 * @version     1.7.4
 * @package     sellacious
 *
 * @copyright   Copyright (C) 2012-2019 Bhartiy Web Technologies. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Aditya Chakraborty <info@bhartiy.com> - http://www.bhartiy.com
 */
jQuery(document).ready(function ($) {
	$('.accordion-heading').click(function (e) {
		if (!$(e.target).is('a')) $(this).find('a').click();
	});

	Joomla.submitbutton = function (task, form) {
		form = form || document.getElementById('adminForm');
		var task2 = task.split('.')[1] || '';

		if (task2 != 'cancel') {
			$('#jform_body').val($('#jform_body').html());
			Joomla.submitform(task, form, true);
		} else {
			Joomla.submitform(task, form, false);
		}
	};

	$('.btn-restore').on('click', function () {
		var context = $('#jform_context').val();

		var $data = {
			option: 'com_sellacioustemplates',
			task: 'template.getTemplateDefaultAjax',
			context: context,
		};

		$.ajax({
			url: 'index.php',
			type: 'POST',
			dataType: 'json',
			cache: false,
			data: $data
		}).done(function (response) {
			if (response.success) {
				var defaultTemplate = response.data.default_template;
				CKEDITOR.instances['jform_body'].setData(defaultTemplate);
				document.getElementById('jform_preview').contentWindow.location.reload(true);
			} else {
				Joomla.renderMessages({warning: [response.message]});
			}
		}).fail(function ($xhr) {
			console.log($xhr.responseText);
		});
	});

	$(window).load(function () {
		CKEDITOR.instances['jform_body'].on('change', function() {
			$('.btn-restore').removeClass('hidden');

			var html = CKEDITOR.instances['jform_body'].getData();
			var context = $('#jform_context').val();

			var $data = {
				option: 'com_sellacioustemplates',
				task: 'template.getPreviewAjax',
				html: html,
				context: context,
			};

			$.ajax({
				url: 'index.php',
				type: 'POST',
				dataType: 'json',
				cache: false,
				data: $data
			}).done(function (response) {
				if (response.success) {
					document.getElementById('jform_preview').contentWindow.location.reload(true);
				} else {
					Joomla.renderMessages({warning: [response.message]});
				}
			}).fail(function ($xhr) {
				console.log($xhr.responseText);
			});
		});
	});
});
