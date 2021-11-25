/**
 * @version     1.7.4
 * @package     Sellacious Cart Module
 *
 * @copyright   Copyright (C) 2012-2019 Bhartiy Web Technologies. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Saurabh Sabharwal <info@bhartiy.com> - http://www.bhartiy.com
 */

jQuery(document).ready(function ($) {

	// Open login form on click
	$('.toolbar-login-button').on('click', function (e) {
		e.preventDefault();
		e.stopPropagation();
		$('.login-menu').toggle();
	});

	// Close login form when clicked anywhere on the page
	$(document).on('click', function () {
		$('.login-menu').hide();
	});

	// Prevent login form from closing when clicked on itself.
	$('.login-menu').on('click', function (e) {
		e.stopPropagation();
	});

	$('body').addClass('hasToolbar');

	if ($('.mod-sellacious-toolbar').hasClass('top')) {
		$('body').addClass('toolbarTop');
	} else if ($('.mod-sellacious-toolbar').hasClass('bottom')) {
		$('body').addClass('toolbarBottom');
	}

	$('.mod-sellacious-toolbar').appendTo('body');

	stackBelowToolbar();

	$(window).on('scroll', function() {
		stackBelowToolbar();
	});

	// Function to stack toolbar above/below fixed or sticky elements.
	function stackBelowToolbar () {
		if ($('body').hasClass('toolbarTop')) {
			$('.toolbar-push-down:not(.push-done)').each(function () {
				let top = $(this).css('top');
				$(this).css({'top': parseInt(top) + 30});
				$(this).addClass('push-done')
			})
		} else if ($('body').hasClass('toolbarBottom')) {
			$('.toolbar-push-up:not(pull-done)').each(function () {
				let bottom = $(this).css('bottom');
				$(this).css({'bottom': parseInt(bottom) + 30});
				$(this).addClass('pull-done');
			})
		}
	}
});
