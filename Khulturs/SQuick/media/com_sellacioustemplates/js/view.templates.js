/**
 * @version     2.0.0
 * @package     sellacious
 *
 * @copyright   Copyright (C) 2012-2020 Bhartiy Web Technologies. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Aditya Chakraborty <info@bhartiy.com> - http://www.bhartiy.com
 */
jQuery(document).ready(function ($) {
	$('.btn-preview').on('click', function () {
		var context = $(this).data('context');

		var $data = {
			option: 'com_sellacioustemplates',
			task: 'templates.getPreviewAjax',
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
				document.getElementById('template-preview-iframe').contentWindow.location.reload(true);
				$('#template-preview').addClass('in');
			} else {
				Joomla.renderMessages({warning: [response.message]});
			}
		}).fail(function ($xhr) {
			console.log($xhr.responseText);
		});
	});

	$('.preview-backdrop, .btn-close-template').on('click', function() {
		$('#template-preview').removeClass('in');
	});
});
