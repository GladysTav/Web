/**
 * @version     2.0.0
 * @package     sellacious
 *
 * @copyright   Copyright (C) 2012-2020 Bhartiy Web Technologies. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Izhar Aazmi <info@bhartiy.com> - http://www.bhartiy.com
 */
class MapAddressField {

	constructor(el, options, jquery) {
		const self = this;

		self.container = el;
		self.options = options;
		self.jquery = jquery;
		self.el = jquery(el);

		self.map = null;
		self.marker = null;

		self.element('.fma-trigger').on('click', () => self.element('.fma-frame').addClass('in'));
		self.element('.fma-backdrop').on('click', () => self.element('.fma-frame').removeClass('in'));

		const coords = self.getFieldValue('coordinates').split(',');
		let lat, lng;

		if (coords.length === 2 && !isNaN(lat = parseFloat(coords[0])) && !isNaN(lng = parseFloat(coords[1]))) {
			self.setup(lat, lng);
		} else if (navigator.geolocation) {
			navigator.geolocation.getCurrentPosition(pos => self.setup(pos.coords.latitude, pos.coords.longitude));
		}

		// Fake the search-box value
		if (self.element('.fma-search-box').length) self.element('.fma-search-box').val(self.makeAddress());
	}

	element(selector) {
		return this.el.find(selector);
	}

	getFieldValue(key) {
		const self = this;
		return self.options.map[key] ? self.jquery('#' + self.options.map[key]).val() : '';
	}

	setFieldValue(key, value) {
		const self = this;
		if (self.options.map[key]) {
			self.jquery('#' + self.options.map[key]).val(value);
		}
	}

	setup(lat, long) {
		const self = this;
		const latLng = new google.maps.LatLng(lat, long);

		self.map = new google.maps.Map(self.element('.fma-frame').get(0), {
			center: latLng,
			zoom: self.options.zoom,
			styles: [
				{
					"featureType": "administrative",
					"stylers": [
						{
							"visibility": "off"
						}
					]
				},
				{
					"featureType": "poi",
					"stylers": [
						{
							"visibility": "simplified"
						}
					]
				},
				{
					"featureType": "road",
					"stylers": [
						{
							"visibility": "simplified"
						}
					]
				},
				{
					"featureType": "water",
					"stylers": [
						{
							"visibility": "simplified"
						}
					]
				},
				{
					"featureType": "transit",
					"stylers": [
						{
							"visibility": "simplified"
						}
					]
				},
				{
					"featureType": "landscape",
					"stylers": [
						{
							"visibility": "simplified"
						}
					]
				},
				{
					"featureType": "road.highway",
					"stylers": [
						{
							"visibility": "off"
						}
					]
				},
				{
					"featureType": "road.local",
					"stylers": [
						{
							"visibility": "on"
						}
					]
				},
				{
					"featureType": "road.highway",
					"elementType": "geometry",
					"stylers": [
						{
							"visibility": "on"
						}
					]
				},
				{
					"featureType": "water",
					"stylers": [
						{
							"color": "#84afa3"
						},
						{
							"lightness": 52
						}
					]
				},
				{
					"stylers": [
						{
							"saturation": -77
						}
					]
				},
				{
					"featureType": "road"
				}
			],
			streetViewControl: false,
			fullscreenControl: true,
			mapTypeControl: false,
			zoomControl: true
		});
		self.map.controls[google.maps.ControlPosition.TOP_LEFT].push(self.getCloseControl(self.map));
		google.maps.event.trigger(self.map, 'resize');

		self.map.setCenter(latLng);
		self.deleteMarkers();
		self.addMarker(latLng);

		new google.maps.places.Autocomplete(self.element('.fma-input').get(0))
			.addListener('place_changed', function () {
				const place = this.getPlace();
				const latLng = place.geometry.location;

				self.map.setCenter(latLng);
				self.deleteMarkers();
				self.addMarker(latLng);
				self.updateFields(latLng);
			});
	}

	addMarker(latLng) {
		const self = this;
		/** @namespace google.maps.Marker */
		self.marker = new google.maps.Marker({position: latLng, map: self.map, draggable: true});
		google.maps.event.addListener(self.marker, 'dragend', event => {
			self.map.setCenter(event.latLng);
			self.updateFields(event.latLng);
		});
	}

	updateFields(latLng) {
		const self = this;
		const geocoder = new google.maps.Geocoder();

		const lat = Math.round(latLng.lat() * 1000000) / 1000000;
		const lng = Math.round(latLng.lng() * 1000000) / 1000000;

		self.setFieldValue('coordinates', `${lat},${lng}`);

		const callback = (results, status) => {
			if (status === google.maps.GeocoderStatus.OK && typeof results[1] !== 'undefined') {
				const place = results[1];
				const parts = self.parseComponents(place);

				// Coincidentally, product and user form has same field id, we need to changed below code if those changes.
				self.setFieldValue('sublocality', parts.sublocality);
				self.setFieldValue('locality', parts.locality);
				self.setFieldValue('city', parts.city);
				self.setFieldValue('district', parts.district);
				self.setFieldValue('state', parts.state);
				self.setFieldValue('zip', parts.zip);
				self.setFieldValue('country', parts.country);
				self.setFieldValue('address', self.makeAddress());
			}
		};

		geocoder.geocode({latLng}, callback);
	}

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
		return parts;
	}

	makeAddress() {
		const self = this;
		let parts = [];

		parts.push(self.getFieldValue('sublocality'));
		parts.push(self.getFieldValue('locality'));
		parts.push(self.getFieldValue('city'));
		parts.push(self.getFieldValue('district'));
		parts.push(self.getFieldValue('state'));
		parts.push(self.getFieldValue('zip'));
		parts.push(self.getFieldValue('country'));

		return parts.join(', ').replace(/(, )+/g, ', ').replace(/(^, |, $)/g, '');
	}

	getCloseControl() {
		const self = this;
		const box = self.jquery('<div/>', {title: 'Click to close the map'}).css({
			backgroundColor: '#ffffff',
			border: '2px solid #ffffff',
			borderRadius: '3px',
			boxShadow: '0 2px 6px rgba(0,0,0,.3)',
			cursor: 'pointer',
			marginBottom: '22px',
			textAlign: 'center'
		});
		const txt = self.jquery('<div/>').text('Close').css({
			color: '#191919',
			fontFamily: 'Roboto, Arial, sans-serif',
			fontSize: '16px',
			lineHeight: '38px',
			paddingLeft: '5px',
			paddingRight: '5px'
		});
		box.append(txt).on('click', () => self.element('.fma-frame').removeClass('in'));
		const close = document.createElement('div');
		close.index = 1;
		close.appendChild(box.get(0));
		return close;
	}

	deleteMarkers() {
		const self = this;
		if (self.marker) {
			self.marker.setMap(null);
			self.marker = null;
		}
	}
}

/** Add magic binder */
jQuery(jquery => jquery('[data-field_mapaddress]').each(function () {
	jquery(this).data('mapaddress-instance', new MapAddressField(this, jquery(this).data('field_mapaddress'), jquery))
		.removeAttr('data-field_mapaddress');
}));
