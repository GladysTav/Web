/**
 * @version     1.7.3
 * @package     sellacious
 *
 * @copyright   Copyright (C) 2012-2019 Bhartiy Web Technologies. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Izhar Aazmi <info@bhartiy.com> - http://www.bhartiy.com
 */
jQuery(document).ready(function ($) {
	$('.btn-toggle').each(function() {
		this.onselectstart = function () {
			return false;
		}
	}).click(function (e) {
		if (e.shiftKey) {
			// Check current state
			var frame = $(this).closest('.toggle-frame');
			var changed = $(this).closest('.toggle-element').is('.visibility-changed');

			// Reset All
			frame.find('.visibility-changed').filter('.hidden').removeClass('hidden').removeClass('visibility-changed');
			frame.find('.visibility-changed').not('.hidden').addClass('hidden').removeClass('visibility-changed');

			// Set all to reverse of current state
			if (!changed) frame.find('.toggle-element').toggleClass('hidden').toggleClass('visibility-changed');
			// document.getSelection().removeAllRanges();
		} else {
			var box = $(this).closest('.toggle-box');
			$('.toggle-element').removeClass('visibility-changed');
			$('.tile-body').addClass('hidden');
			box.find('.toggle-element').toggleClass('hidden').toggleClass('visibility-changed');
		}
		return false;
	});

	// Make first visible
	var frame = $('.toggle-frame');
	var box = frame.find('.toggle-box').eq(0);
	box.find('.toggle-element').toggleClass('hidden').toggleClass('visibility-changed');


	//show Paid order by default
    $("#selct-order-type").change(function() {
        var filter = $(this).val();
        if(filter == 'all') {
            $('.toggle-frame').find('.' + filter).show();


    } else {
            $('.toggle-frame').find('.' + filter).show();
            $('.toggle-frame').find('.toggle-box').not('.' + filter).hide();
        }
    });
});


