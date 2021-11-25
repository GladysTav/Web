/**
 * @version     2.0.0
 * @package     sellacious
 *
 * @copyright   Copyright (C) 2012-2020 Bhartiy Web Technologies. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Aditya Chakraborty <info@bhartiy.com> - http://www.bhartiy.com
 */

jQuery(document).ready(function ($) {
	new ClipboardJS('.active-code', {
		text: function(trigger) {
			return $(trigger).data('text');
		}
	}).on('success', function (e) {
		var $element = $(e.trigger);
		$element.tooltip('destroy').attr('title', 'Code copied to clipboard!').tooltip().tooltip('show');
		setTimeout(function () {
			$element.tooltip('hide').tooltip('destroy').attr('title', '');
		}, 1000);
	}).on('error', $.noop);

	$(document).on('click', '.toggle-description', function (e) {
		e.preventDefault();

		$(this).find('.coupon-description-text').toggle();

		$(this).toggleClass('ctech-border-bottom ctech-border-primary ctech-text-dark ctech-text-primary active');
		$(this).closest('.coupon-description-container').find('.coupon-description').slideToggle('fast');
	})
});
