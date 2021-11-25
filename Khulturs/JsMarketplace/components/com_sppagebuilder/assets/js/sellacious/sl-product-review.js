/**
 * @version     2.2.0
 * @package     SP Page Builder Addons for Sellacious
 *
 * @copyright   Copyright (C) 2016. Bhartiy Web Technologies. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Bhavika Matariya <info@bhartiy.com> - http://www.bhartiy.com
 */

jQuery(function ($) {
	// For compare bar position override just create a div#compare-bar on the page anywhere, it will be used instead.
	$(document).ready(function () {
		$('.btn-review').click(function () {
			var $reviewBox = $('#reviewBox');
			$reviewBox.addClass('focused').find('input[type="text"]').focus();
			setTimeout(function () {
				$('#reviewBox').removeClass('focused');
			}, 1500);
		});
	});
});
