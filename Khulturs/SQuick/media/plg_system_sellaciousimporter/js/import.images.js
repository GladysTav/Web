/**
 * @version     2.0.0
 * @package     sellacious
 *
 * @copyright   Copyright (C) 2012-2020 Bhartiy Web Technologies. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Izhar Aazmi <info@bhartiy.com> - http://www.bhartiy.com
 */
jQuery(document).ready(function ($) {
	var $ajax;
	var interval = 0;

	var $import = $('#import-images');

	var pingImport = function ($importBlock) {
		var $log = $importBlock.find('.import-log');
		var $csrf = {};
		var token = Joomla.getToken();
		$csrf[token] = 1;
		var iOpts = Joomla.getOptions('com_importer.import') || {};

		if ($ajax) $ajax.abort();
		$ajax = $.ajax({
			url: 'index.php?option=com_importer&task=import.importAjax',
			data: $.extend({}, {id: iOpts.id || 0}, $csrf),
			type: 'post',
			dataType: 'json',
			timeout: 10000
		}).done(function (response) {
			// 0 = ERROR, 1 = NOTHING PENDING, 2 = RUNNING, 3 = DONE
			var log = '';
			var state = parseInt(response.state);
			if (response != null && response.data != null && response.data.log != null) log = response.data.log;
			var message = $('<div/>').html(log).text();
			message = '<p>' + response.message + '</p>' + $.trim(message).split(/[\r\n]+/g).reverse().join('<br/>');
			if (state === 0) {
				if (interval) clearInterval(interval);
				$log.html(message);
			} else if (state === 1) {
				if (interval) clearInterval(interval);
				$log.prepend(message + '<br>');
			} else if (state === 2) {
				$log.html(message);
			} else if (state === 3) {
				if (interval) clearInterval(interval);
				$log.html(message);
			} else {
				if (interval) clearInterval(interval);
				$log.prepend('<p>Unknown response from server</p>');
			}
		}).fail(function (xhr) {
			console.log(xhr.responseText);
		});
	};

	var triggerImport = function ($importBlock) {
		var $log = $importBlock.find('.import-log');
		var $form = $importBlock.find('.form-import');
		var $data = $form.serializeObject();
		var token = Joomla.getToken();
		$data[token] = 1;
		var iOpts = Joomla.getOptions('com_importer.import') || {};

		var settings = {
			url: 'index.php?option=com_importer',
			data: $.extend({}, $data, {task: 'import.setOptionsAjax', id: iOpts.id || 0}),
			type: 'post',
			dataType: 'json',
			timeout: 10000,
			beforeSend: function () {
				$importBlock.find('.status-viewer').removeClass('hidden');
				$log.html('<p>Working&hellip; Please wait&hellip;</p>');
			}
		};
		$.ajax(settings).done(function (response) {
			if (response.state === 1) {
				$form.slideUp('slow');
				if ('function' === typeof pingImport) {
					interval = setInterval(function () {
						pingImport($importBlock);
					}, 3000);
					pingImport($importBlock);
				}
			} else {
				$log.prepend(response.message);
			}
		}).fail(function (xhr) {
			console.log(xhr.responseText);
			$log.prepend('<p>Unknown response from server</p>');
		});
	};

	var triggerResume = function ($importBlock) {
		var $log = $importBlock.find('.import-log');
		var $form = $importBlock.find('.form-import');

		var $csrf = {};
		var token = Joomla.getToken();
		$csrf[token] = 1;
		var iOpts = Joomla.getOptions('com_importer.import') || {};

		var settings = {
			url: 'index.php?option=com_importer',
			data: $.extend({}, {task: 'import.resumeAjax', id: iOpts.id || 0}, $csrf),
			type: 'post',
			dataType: 'json',
			timeout: 10000,
			beforeSend: function () {
				$importBlock.find('.status-viewer').removeClass('hidden');
				$log.html('<p>Working&hellip; Please wait&hellip;</p>');
			}
		};
		$.ajax(settings).done(function (response) {
			// 0 = ERROR, 1 = NOTHING PENDING, 2 = RUNNING, 3 = DONE
			var log = '';
			var state = parseInt(response.state);
			if (response != null && response.data != null && response.data.log != null) log = response.data.log;
			var message = $('<div/>').html(log).text();
			message = '<p>' + response.message + '</p>' + $.trim(message).split(/[\r\n]+/g).reverse().join('<br/>');

			if (state === 0) {
				if (interval) clearInterval(interval);
				$log.html(message);
			} else if (state === 2) {
				$form.slideUp('slow');
				if ('function' === typeof pingImport) {
					interval = setInterval(function () { pingImport($importBlock); }, 4000);
					pingImport($importBlock);
				} else {
					$log.prepend(response.message);
				}
			} else if (state === 3) {
				if (interval) clearInterval(interval);
				$log.html(message);
			} else {
				if (interval) clearInterval(interval);
				$log.prepend('<p>Unknown response from server</p>');
			}
		}).fail(function (xhr) {
			console.log(xhr.responseText);
			$log.prepend('<p>Unknown response from server</p>');
		});
	};

	$import.find('.btn-import').click(function () {
		var $iBlock = $(this).closest('.importer-block');
		triggerImport($iBlock);
	});

	$import.find('.btn-resume').click(function () {
		var $iBlock = $(this).closest('.importer-block');
		triggerResume($iBlock);
	});
});
