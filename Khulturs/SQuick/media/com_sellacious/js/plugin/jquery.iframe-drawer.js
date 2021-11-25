/**
 * @version     2.0.0
 * @package     sellacious
 *
 * @copyright   Copyright (C) 2012-2020 Bhartiy Web Technologies. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Izhar Aazmi <info@bhartiy.com> - http://www.bhartiy.com
 */
(function ($) {
	$.fn.iframeDrawer = function () {
		$(this).on('click', '[data-drawer-url]', function () {
			let src = $(this).data('drawerUrl');
			let btn = $('<a/>', {
				'class': 'btn btn-danger iframe-drawer iframe-drawer-close pull-right hidden'
			}).css({position: 'fixed', right: '20px', top: '8px', left: 'unset', zIndex: 1021})
				.html('<i class="fa fa-times"></i>').one('click', function () {
					const drw = $('body').find('.iframe-drawer');
					drw.remove();
				});
			let iframe = $('<iframe/>', {src}).css({width: '100%', height: '100%', border: 0});
			let overlay = $('<div/>', {'class': 'iframe-drawer iframe-drawer-overlay'}).css({
				position: 'fixed',
				top: 0,
				right: 0,
				bottom: 0,
				left: 0,
				background: '#000000',
				opacity: '0.75',
				zIndex: '1019'
			});
			let container = $('<div/>', {'class': 'iframe-drawer iframe-drawer-container'}).css({
				position: 'fixed',
				top: 0,
				right: 0,
				bottom: 0,
				width: '90%',
				maxWidth: '867px',
				zIndex: '1020',
				background: '#ffffff'
			}).append(btn).append(iframe);
			$('body').append(container).append(overlay);
		});
	};

	$(document).ready(() => $(document).iframeDrawer());
})(jQuery);
