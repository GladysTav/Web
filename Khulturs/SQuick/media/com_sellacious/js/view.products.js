/**
 * @version     2.0.0
 * @package     sellacious
 *
 * @copyright   Copyright (C) 2012-2020 Bhartiy Web Technologies. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Izhar Aazmi <info@bhartiy.com> - http://www.bhartiy.com
 */
function listItemTask2(id, task, prefix, form) {
	var f = form || document.adminForm,
		i = 0, cbx,
		cb = f[prefix + id];

	if (!cb) return false;

	while (true) {
		cbx = f[prefix + i];
		if (!cbx) break;
		cbx.checked = false;
		i++;
	}

	cb.checked = true;
	Joomla.submitform(task);

	return false;
}

(function ($) {
	// Bg rollover
	$.fn.extend({
		rollover: function () {
			var interval = [];
			$(this).each(function (i) {
				var $roller         = $(this);
				var $img            = $roller.find('img[data-rollover]');
				var $bgrollover     = $roller.find('.bgrollover');

				if ($img.length) {
					$img.each(function (i, img) {
						var images = $(img).data('rollover');
						$(img).removeAttr('data-rollover');
						interval[i] = 0;
						var count;
						if (count = images.length) {
							$roller.hover(function () {
								interval[i] = setInterval(function () {
									var ci = $(img).data('rolloverIndex') || 0;
									$(img).attr('src', images[ci]);
									$(img).data('rolloverIndex', ci + 1 < count ? ci + 1 : 0);
								}, 1500);
							}, function () {
								interval[i] && clearInterval(interval[i]);
							});
						}
					})
				} else if($bgrollover.length){
					$bgrollover.each(function (i, bgro) {
						var bgimages = $(bgro).data('rollover');
						$(bgro).removeAttr('data-rollover');
						interval[i] = 0;
						var bgcount;
						if (bgcount = bgimages.length) {
							$roller.hover(function () {
								interval[i] = setInterval(function () {
									var ci = $(bgro).data('rolloverIndex') || 0;
									$(bgro).css('background-image', 'url("'+bgimages[ci]+'")');
									$(bgro).data('rolloverIndex', ci + 1 < bgcount ? ci + 1 : 0);

								}, 1500);
							}, function () {
								interval[i] && clearInterval(interval[i]);
							});
						}
					})
				} else {
					return;
				}
			});
		}
	});

	$(document).ready(function () {
		// Initiate bg rollover
		$('[data-rollover="container"]').rollover();

		var $token = '';

		$('input[type="hidden"]').each(function () {
			if ($(this).attr('name').length === 32 && parseInt($(this).val()) === 1) {
				$token = $(this).attr('name');
				return false;
			}
		});

		$('.btn-spl-listing').on('click', '.btn', function (e) {
			e.preventDefault();
			var $this = $(e.target);

			var id = $this.data('id');
			var catid = $this.data('catid');
			var seller_uid = $this.data('seller_uid');

			if (catid === null || !seller_uid || !id) return;

			var data = {};
			data['option'] = 'com_sellacious';
			data['task'] = 'products.sellerListingAjax';
			data['cid'] = [id];
			data['remove'] = $this.is('.active') ? 1 : 0;
			data['catid'] = catid;
			data['seller_uid'] = seller_uid;
			data[$token] = 1;

			$.ajax({
				url: 'index.php',
				type: 'post',
				dataType: 'json',
				data: data
			}).done(function (response) {
				if (response.status === 1) {
					if (response['redirect']) {
						window.location.href = response['redirect'];
					} else {
						response.message.length && Joomla.renderMessages({success: [response.message]});
						if (data.remove) {
							$this.removeClass('active btn-danger btn-primary').addClass('btn-default').blur();
						} else {
							$this.removeClass('btn-primary btn-default').addClass('active btn-danger');
						}
					}
				} else {
					Joomla.renderMessages({error: [response.message]});
				}
			}).fail(function (response) {
				Joomla.renderMessages({warning: ['Failed to process your request due to some server error.']});
				console.log(response.responseText);
			});
		});

		new ClipboardJS('.btn-copy-code', {
			text: function(trigger) {
				return $(trigger).data('text');
			}
		}).on('success', function (e) {
			var $element = $(e.trigger);
			var oText = $element.attr('data-original-title');
			$element.tooltip('dispose').attr('title', 'Code copied to clipboard!').tooltip().tooltip('show');
			setTimeout(function () {
				$element.tooltip('hide').tooltip('dispose').attr('title', oText).tooltip();
			}, 1000);
		}).on('error', $.noop);
	});
})(jQuery);
