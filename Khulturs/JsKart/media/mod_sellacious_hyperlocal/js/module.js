/**
 * @version     2.0.0
 * @package     Sellacious Hyperlocal Module
 *
 * @copyright   Copyright (C) 2012-2020 Bhartiy Web Technologies. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Izhar Aazmi <info@bhartiy.com> - http://www.bhartiy.com
 */
jQuery(document).ready($ => {
	$('.btn-filter_shippable').prop('onclick', null).off('click').on('click', e => {
	    e.preventDefault();
		o.setShippableFilter($('#filter_shippable_text').val()).done(r => r.success ? () => $(e.target).closest('form').submit() : Joomla.renderMessages({warning: [r.message]}));
	});

	$('.btn-filter_shop_location').prop('onclick', null).off('click').on('click', e => {
	    e.preventDefault();
		o.setLocationFilter($('#filter_store_location_custom_text').val()).done(r => r.success ? $(e.target).closest('form').submit() : Joomla.renderMessages({warning: [r.message]}));
	});

	//  STYLE1/2

	// Hyperlocal Address
	$(".select-yourloc").on('click', function () {
		$(".hyperlocal-countries").addClass("hidden");
		$(".select-zone").removeClass('hidden');
	});

	$('.back-loc').on('click', function () {
		$(".hyperlocal-countries").removeClass("hidden");
		$(".select-zone").addClass('hidden');
	});

	$('.country-detail-li a').on('click', function (e) {
		e.preventDefault();

		var location = $(this).data('location');
		var lat = $(this).data('lat');
		var lng = $(this).data('lng');

		if (o.options.params.address_matching === '2') {
			o.setCustomAddress(location, lat, lng, () => window.location.reload());
		} else {
			o.setCustomAddress(location, lat, lng, o.setBounds);
		}
	});

});
