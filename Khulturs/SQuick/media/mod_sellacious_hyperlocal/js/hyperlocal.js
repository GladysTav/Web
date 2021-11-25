/**
 * @version     2.0.0
 * @package     Sellacious Hyperlocal Module
 *
 * @copyright   Copyright (C) 2012-2020 Bhartiy Web Technologies. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Izhar Aazmi <info@bhartiy.com> - http://www.bhartiy.com
 */
class SellaciousHyperLocal {

	/**
	 * Constructor
	 *
	 * @param   {HTMLElement}  element
	 * @param   {object}       options
	 * @param   {jQuery}       jquery
	 *
	 * @returns {void}
	 *
	 * @since    2.0.0
	 */
	constructor(element, options, jquery) {
		const self = this;

		self.jquery = jquery || window.jQuery;
		self.options = options || {};
		self.el = element;
		self.$el = self.jquery(element);

		self.range = [0, self.options['distance_default']];
		self.position = [self.options['latitude'], self.options['longitude']];

		/** @namespace  google.maps.Geocoder */
		self.geocoder = new google.maps.Geocoder();

		if (self.options['autocomplete'] === 'google') {
			const el = self.element('.address-input').get(0);

			/** @namespace  google.maps.places */
			new google.maps.places.Autocomplete(el).addListener('place_changed', function () {
				self.placeChange(this.getPlace());
			});
		} else {
			self.element('.address-input').autocomplete({
				minLength: 3,
				source(request, response) {
					self.jquery.ajax({
						url: 'index.php?option=com_ajax&module=sellacious_hyperlocal&method=getAutoCompleteSearch&format=json',
						type: 'POST',
						dataType: 'json',
						cache: false,
						data: {
							term: request.term,
							list_start: 0,
							list_limit: 5
						}
					}).done(data => response(data));
				},
				select(event, ui) {
					self.element('.address-input').val(ui.item.value);
					self.element('.address-id').val(ui.item.id);
					self.setAddress(ui.item.id, ui.item.value).done(r => {
						r.success ? self.setBounds() : Joomla.renderMessages({warning: [r.message]});
					});
					return false;
				}
			});
		}

		self.element('.btn-detect-location').on('click', () => self.geoLocate());

		if (self.element('.btn-detect-location').data('detect')) self.geoLocate();

		self.element('.btn-reset-location').on('click', e => {
			e.preventDefault();
			self.element('.address-input,.address-id').val('');
			self.resetAddress().done(r => {
				r.success ? window.location.reload() : Joomla.renderMessages({warning: [r.message]});
			});
		});

		const $slider = self.element('.distance_filter_slider');

		if ($slider.length) {

			$slider.slider({
				range: true,
				min: 0,
				max: self.options['distance_limit'],
				values: [self.options['distance_min'], self.options['distance_max']],
				slide(event, ui) {
					const [min, max] = ui.values;
					self.range[0] = min;
					self.range[1] = max;
					self.element('.distance-display').text(`${min} - ${max} ${self.options['unit_symbol']}`);
				},
				change() {
					self.setBounds();
				}
			});

			const min = $slider.slider('values', 0);
			const max = $slider.slider('values', 1);

			self.range[0] = min;
			self.range[1] = max;
			self.element('.distance-display').text(`${min} - ${max} ${self.options['unit_symbol']}`);
		}
	}

	/**
	 * Get the element within the wrapper element for this module object instance
	 *
	 * @param   {string}  selector
	 *
	 * @returns {jQuery}  jQuery object for matching element(s)
	 *
	 * @since    2.0.0
	 */
	element(selector) {
		return this.$el.find(selector);
	}

	/**
	 * Get the boundary assuming given distance from a coordinate
	 *
	 * @param   {object}  latLng
	 * @param   {number}  distance
	 *
	 * @returns {object}  jQuery object for matching element(s)
	 *
	 * @since    2.0.0
	 */
	static getBoundary(latLng, distance) {
		/** @namespace  google.maps.Circle */
		const circle = new google.maps.Circle({center: latLng, radius: distance});
		const bounds = circle.getBounds();

		const northEast = bounds.getNorthEast();
		const southWest = bounds.getSouthWest();

		return {
			north: Math.round(northEast.lat() * 10000) / 10000,
			east: Math.round(northEast.lng() * 10000) / 10000,
			south: Math.round(southWest.lat() * 10000) / 10000,
			west: Math.round(southWest.lng() * 10000) / 10000,
		};
	}

	/**
	 * Detect the location coordinates from the browser, (if the given argument is empty)
	 *
	 * @returns {void}
	 *
	 * @since    2.0.0
	 */
	geoLocate() {
		const self = this;

		if (!navigator.geolocation) return;

		self.element('.btn-detect-location').html('<i class="fa fa-spin fa-spinner"></i>').prop('disabled', true);

		navigator.geolocation.getCurrentPosition(
			pt => self.doGeocode(pt.coords.latitude, pt.coords.longitude),
			() => self.element('.btn-detect-location').html('<i class="fa fa-location-arrow"></i>').prop('disabled', false)
		);
	}

	/**
	 * Reset the address data stored in the session using ajax call
	 *
	 * @returns  {Promise}
	 *
	 * @since    2.0.0
	 */
	resetAddress() {
		const self = this;

		return self.jquery.ajax({
			url: 'index.php?option=com_ajax&module=sellacious_hyperlocal&method=resetAddress&format=json',
			type: 'POST',
			dataType: 'json',
			cache: false
		}).fail(() => {
			Joomla.renderMessages({warning: ['Failed to clear address due to unknown error.']});
		});
	}

	/**
	 * Set selected address in the session
	 *
	 * @param   {number}  id
	 * @param   {string}  address
	 *
	 * @returns {Promise}
	 *
	 * @since    2.0.0
	 */
	setAddress(id, address) {
		const self = this;

		return self.jquery.ajax({
			url: 'index.php?option=com_ajax&module=sellacious_hyperlocal&method=setAddress&format=json',
			type: 'POST',
			dataType: 'json',
			cache: false,
			data: {id, address},
		})
			.done(r => {
				if (r.success) self.position = [r.data.lat, r.data.long];
			})
			.fail(() => Joomla.renderMessages({warning: [Joomla.JText._('MOD_SELLACIOUS_HYPERLOCAL_SET_ADDRESS_FAILED')]}));
	}

	/**
	 * Set N/E/W/S boundary for set radius range assuming the centre coordinate
	 *
	 * @returns {Promise}
	 *
	 * @since    2.0.0
	 */
	setBounds() {
		const self = this;
		const latLng = new google.maps.LatLng(self.position[0], self.position[1]);
		const [min, max] = self.range;

		const minMt = min * self.options['unit_rate'];
		const maxMt = max * self.options['unit_rate'];

		const boundsMin = SellaciousHyperLocal.getBoundary(latLng, minMt);
		const boundsMax = SellaciousHyperLocal.getBoundary(latLng, maxMt);

		function getGeoAjax(timezone = undefined) {
			return self.jquery.ajax({
				url: 'index.php?option=com_ajax&module=sellacious_hyperlocal&method=setBounds&format=json',
				type: 'POST',
				dataType: 'json',
				cache: false,
				data: {
					bounds_min: boundsMin,
					bounds_max: boundsMax,
					min_radius: min,
					max_radius: max,
					timezone: timezone
				}
			})
				.done(r => {
					r.success ? window.location.reload() : Joomla.renderMessages({warning: [r.message]});
				})
				.fail(() => {
					Joomla.renderMessages({warning: [Joomla.JText._('MOD_SELLACIOUS_HYPERLOCAL_FETCH_ADDRESS_FAILED')]});
				});
		}

		return self.getTimezone(self.position[0], self.position[1]).then(r => getGeoAjax(r['timeZoneId']));
	}

	/**
	 * Handler for google maps autocomplete instance's place change event
	 *
	 * @param   {object}  place
	 *
	 * @returns {void}
	 *
	 * @since    2.0.0
	 */
	placeChange(place) {
		const self = this;

		self.parseComponents(place);

		if (place.display_name === '') {
			const msg = Joomla.JText._('MOD_SELLACIOUS_HYPERLOCAL_MESSAGE_DETECTED_EMPTY', '');
			Joomla.renderMessages({warning: [msg.replace('%s', self.options.components.join(', '))]});
			return;
		}

		self.element('.address-id').val('');
		self.element('.address-input').val(place.display_name);

		self.setGeoLocation(place, true).done(r => {
			if (r.success) {
				if (self.options['hyperlocal_type'] === 'distance') {
					self.setBounds();
				} else {
					window.location.reload();
				}
			} else {
				Joomla.renderMessages({warning: [r.message]});
			}
		})
	}

	/**
	 * Set selected geolocation in the session
	 *
	 * @param   {object}   place
	 * @param   {boolean}  tz
	 *
	 * @returns {Promise}
	 *
	 * @since    2.0.0
	 */
	setGeoLocation(place, tz = false) {
		const self = this;

		let lat = place.geometry.location.lat();
		let long = place.geometry.location.lng();
		self.position = [lat, long];

		const getAjax = timezone => self.jquery.ajax({
			url: 'index.php?option=com_ajax&module=sellacious_hyperlocal&method=setGeoLocation&format=json',
			type: 'POST',
			dataType: 'json',
			cache: false,
			data: {lat, long, address: place.display_name, components: place.components, timezone}
		});

		return tz ? self.getTimezone(lat, long).then(r => getAjax(r['timeZoneId'])) : getAjax();
	}

	/**
	 * Parse the address components from the given place object
	 *
	 * @param   {object}   place
	 *
	 * @returns {object}
	 *
	 * @since    2.0.0
	 */
	parseComponents(place) {
		const self = this;
		let parts = {country: null, state: null, district: null, city: null, locality: null, sublocality: null, zip: null};

		self.jquery.each(place['address_components'], (key, component) => {
			const value = component['long_name'];
			if (component.types.indexOf('sublocality_level_1') >= 0) {
				parts.locality = value;
			} else if (component.types.indexOf('sublocality_level_2') >= 0) {
				parts.sublocality = value;
			} else if (component.types.indexOf('locality') >= 0) {
				parts.city = value;
			} else if (component.types.indexOf('administrative_area_level_2') >= 0) {
				parts.district = value;
			} else if (component.types.indexOf('administrative_area_level_1') >= 0) {
				parts.state = value;
			} else if (component.types.indexOf('country') >= 0) {
				parts.country = value;
			} else if (component.types.indexOf('postal_code') >= 0) {
				parts.zip = value;
			}
		});

		let segments = [], t;
		const components = self.options.components;

		if (components.indexOf('sublocality') >= 0 && (t = parts.sublocality || parts.locality)) segments.push(t);
		if (components.indexOf('locality') >= 0 && (t = parts.locality || parts.sublocality)) segments.push(t);
		if (components.indexOf('city') >= 0 && (t = parts.city || parts.district)) segments.push(t);
		if (components.indexOf('district') >= 0 && (t = parts.district || parts.city)) segments.push(t);
		if (components.indexOf('state') >= 0 && parts.state) segments.push(parts.state);
		if (components.indexOf('country') >= 0 && parts.country) segments.push(parts.country);

		let displayName = self.jquery.grep(segments, (e, i) => i === segments.indexOf(e)).join(', ');

		if (components.indexOf('zip') >= 0 && parts.zip) {
			displayName = displayName.length ? `${displayName} - ${parts.zip}` : parts.zip;
		}

		place.display_name = displayName;
		place.components = parts;
	}

	/**
	 * Parse the geolocation coordinates to retrieve the address
	 *
	 * @param   {number}  latitude
	 * @param   {number}  longitude
	 *
	 * @returns {void}
	 *
	 * @since    2.0.0
	 */
	doGeocode(latitude, longitude) {
		const self = this;

		/** @namespace  google.maps.LatLng */
		const latLng = new google.maps.LatLng(latitude, longitude);

		self.geocoder.geocode({latLng}, (results, status) => {

			/** @namespace  google.maps.GeocoderStatus */
			if (status !== google.maps.GeocoderStatus.OK) {
				Joomla.renderMessages({error: [Joomla.JText._('MOD_SELLACIOUS_HYPERLOCAL_GEOCODE_FAILED') + status]});
				return;
			}

			if (!results[1]) {
				Joomla.renderMessages({error: [Joomla.JText._('MOD_SELLACIOUS_HYPERLOCAL_GEOCODE_NO_RESULTS_FOUND')]});
				return;
			}

			const place = results[2];

			if (self.options['autocomplete'] === 'google') {
				self.placeChange(place);
			} else {
				/** @namespace  place.geometry.location */
				const lat = place.geometry.location.lat();
				const lng = place.geometry.location.lng();

				self.getAddress(lat, lng, place['address_components']).done(() => {
					if (r.success) self.setBounds();
				});
			}
		});
	}

	/**
	 * Get the timezone identifier for given coordinates
	 *
	 * @returns  {Promise}
	 *
	 * @since    2.0.0
	 */
	getTimezone(lat, long) {
		const self = this;
		const ts = (Math.round((new Date().getTime()) / 1000)).toString();
		const key = self.options['google_api_key'];
		const url = `https://maps.googleapis.com/maps/api/timezone/json?location=${lat},${long}&timestamp=${ts}&key=${key}`;

		return self.jquery.ajax({url});
	}

	/**
	 * Get the location info from internal db for auto-detected location, if available
	 *
	 * @returns  {Promise}
	 *
	 * @since    2.0.0
	 */
	getAddress(lat, lng, components) {
		const self = this;

		return self.jquery.ajax({
			url: 'index.php?option=com_ajax&module=sellacious_hyperlocal&method=getAddress&format=json',
			type: 'POST',
			dataType: 'json',
			cache: false,
			data: {lat, lng, components}
		})
			.done(r => {
				if (r.success) {
					self.position = [r.data.lat, r.data.lng];
					self.element('.address-input').val(r.data.address);
					self.element('.address-id').val(r.data.id);
				} else {
					Joomla.renderMessages({warning: [r.message]});
				}
			})
			.fail(() => {
				Joomla.renderMessages({warning: [Joomla.JText._('MOD_SELLACIOUS_HYPERLOCAL_FETCH_ADDRESS_FAILED')]});
			})
			.always(() => {
				self.element('.btn-detect-location').prop('disabled', false).html('<i class="fa fa-location-arrow"></i>');
			});
	}
}

/** Add magic binder */
jQuery(jquery => jquery('[data-mod_sellacious_hyperlocal]').each(function () {
	jquery(this).data('hyperlocal', new SellaciousHyperLocal(this, jquery(this).data('mod_sellacious_hyperlocal'), jquery))
		.removeAttr('data-mod_sellacious_hyperlocal');
}));

