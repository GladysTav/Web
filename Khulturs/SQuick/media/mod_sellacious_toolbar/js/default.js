/**
 * @version     2.0.0
 * @package     Sellacious Cart Module
 *
 * @copyright   Copyright (C) 2012-2020 Bhartiy Web Technologies. All rights reserved.
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

	// Get unread messages count
	let getUnreadCount = () => {
		const userId   = $('.mod-sellacious-toolbar').data('user');

		const paths = Joomla.getOptions('system.paths', {});
		const base  = paths.root || '';
		fetch(`${base}/index.php?option=com_ajax&module=sellacious_toolbar&method=getUnreadCount&userId=${userId}&format=json`, {
			cache: 'no-cache',
			redirect: 'follow',
			referrer: 'no-referrer'
		})
			.then((response) => response.json())
			.then((response) => {
				$('.mod-sellacious-toolbar .unread-message-count').html(response.data.messages);
				$('.mod-sellacious-toolbar .unanswered-questions-count').html(response.data.questions);
			})
	};

	let interval = $('.mod-sellacious-toolbar').data('interval');
	interval     = interval * 1000;
	setInterval(getUnreadCount, interval);
});
