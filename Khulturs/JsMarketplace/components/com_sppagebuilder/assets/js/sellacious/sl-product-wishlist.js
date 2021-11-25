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
	$('.btn-wishlist').not('.disabled').click(function () {
		var $this = $(this);
		var code = $this.data('item');
		var paths = Joomla.getOptions('system.paths', {});
		var base = paths.base || paths.root || '';

		if (!code) {
			var guest = $this.data('guest');
			var href = $this.data('href');
			if (guest) {
				if (confirm('You need to login to access your wishlist. Do you want to login?'))
					window.location.href = href || 'index.php?option=com_users&view=login';
			} else if (href) window.location.href = href;

			return;
		}

		$.ajax({
			url     : base + '/index.php?option=com_sellacious&task=wishlist.addAjax',
			type    : 'POST',
			data    : {p: code},
			cache   : false,
			dataType: 'json',
			success : function (response) {
				if (response.state == 1) {
					$this.addClass('fa-heart').removeClass('fa-heart-o')
						.html('<h5>Added to Wishlist</h5>')
						.data('href', response.data['redirect'])
						.data('item', null);
				} else {
					Joomla.renderMessages({error: [response.message]});
				}
			},
			error   : function (jqXHR) {
				Joomla.renderMessages({error: ['There was an error while processing your request. Please try later.']});
				console.log(jqXHR.responseText);
			}
		});
	});
});
