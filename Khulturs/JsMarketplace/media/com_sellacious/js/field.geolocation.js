/**
 * @version     1.7.4
 * @package     sellacious
 *
 * @copyright   Copyright (C) 2012-2019 Bhartiy Web Technologies. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Aditya Chakraborty <info@bhartiy.com> - http://www.bhartiy.com
 */

var JFormFieldMapAddress = function () {
	this.options = {
	};

	this.map = null;
	this.latLng = null;
	this.geocoder = null;
	this.autocomplete = null;
	this.markers = [];

	return this;
};

(function ($) {
	JFormFieldMapAddress.prototype = {
		init: function(options) {
			var thisobj = this;

			$.extend(thisobj.options, options);

			if (thisobj.options.type == 'google') {
				if (thisobj.options.lat != "" || thisobj.options.lng != "") {
					thisobj.latLng = new google.maps.LatLng(thisobj.options.lat, thisobj.options.lng);
					thisobj.setup();
				} else {
					if (navigator.geolocation) {
						navigator.geolocation.getCurrentPosition(function (position) {
							thisobj.latLng = new google.maps.LatLng(position.coords.latitude, position.coords.longitude);

							thisobj.setup();
						});
					} else {
						return false;
					}
				}
			} else if (thisobj.options.type == 'database') {
				var paths = Joomla.getOptions('system.paths', {});
				var baseUrl = (paths.root || '') + '/index.php';

				$('#' + thisobj.options.id).autocomplete({
					source: function( request, response ) {
						var pData = {
							format: 'json',
							option: 'com_sellacious',
							task: 'geolocation.getSearch',
							term: request.term,
							parent_id: 1,
							types: ['zip', 'city', 'district', 'state', 'country'],
							list_start: 0,
							list_limit: 5
						};
						$.ajax({
							url: baseUrl,
							type: 'POST',
							dataType: "json",
							data: pData,
							cache: false,
							success: function(data) {
								response(data);
							}
						});
					},
					select: function(event, ui) {
						$(document).trigger('onAutoCompleteSelect', [thisobj.options.id]);
					},
					minLength: 3
				});
			}
		},
		setup: function() {
			var thisobj = this;

			if (thisobj.options.type == 'google') {
				thisobj.map = new google.maps.Map(document.getElementById(thisobj.options.id + "_map"), {
					center: thisobj.latLng,
					zoom: thisobj.options.zoom,
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
					streetViewControl:false,
					fullscreenControl: true,
					mapTypeControl: false,
					zoomControl: true
				});
				google.maps.event.trigger(thisobj.map, 'resize');

				thisobj.geocoder = new google.maps.Geocoder();

				thisobj.addMarker(thisobj.latLng);
				thisobj.geocodeMap(thisobj.latLng);

				var closeControlElement = document.createElement('div');
				var closeControl = new thisobj.mapCloseControl(closeControlElement, thisobj.map);
				closeControlElement.index = 1;
				thisobj.map.controls[google.maps.ControlPosition.TOP_LEFT].push(closeControlElement);

				thisobj.autocomplete = new google.maps.places.Autocomplete(
					/** @type {!HTMLInputElement} */(document.getElementById(thisobj.options.id)),
					{types: ['geocode']});

				thisobj.autocomplete.addListener('place_changed', function () {
					var place = this.getPlace();
					var latLng = place.geometry.location;

					thisobj.deleteMarkers();
					thisobj.addMarker(latLng);
					thisobj.geocodeMap(latLng);
					$(document).trigger('onMapChangeLocation', [latLng.lat(), latLng.lng(), thisobj.options.id]);
				});
			}
		},
		addMarker: function(latLng)
		{
			var thisobj = this;

			if (thisobj.options.type == 'google') {
				thisobj.deleteMarkers();

				var marker = new google.maps.Marker({
					position: latLng,
					map: thisobj.map,
					draggable: true,
				});

				thisobj.geocodeMap(latLng);

				google.maps.event.addListener(marker, "dragend", function (event) {
					thisobj.geocodeMap(event.latLng);

					$(document).trigger('onMapChangeLocation', [event.latLng.lat(), event.latLng.lng(), thisobj.options.id]);
				});

				thisobj.markers.push(marker);
				thisobj.map.setCenter(latLng);

				$(document).trigger('onMapChangeLocation', [latLng.lat(), latLng.lng(), thisobj.options.id]);
			}
		},
		geocodeMap: function(latLng) {
			var thisobj = this;

			thisobj.geocoder.geocode({
				'latLng': latLng
			}, function(results, status) {
				if (status == google.maps.GeocoderStatus.OK) {
					if (results[1]) {
						$(document).trigger('OnMapGeoCode', [results[1].address_components, thisobj.options.id])
					}
				}
			});
		},
		mapCloseControl: function(controlDiv, map) {
			// Set CSS for the control border.
			var controlUI = document.createElement('div');
			controlUI.style.backgroundColor = '#fff';
			controlUI.style.border = '2px solid #fff';
			controlUI.style.borderRadius = '3px';
			controlUI.style.boxShadow = '0 2px 6px rgba(0,0,0,.3)';
			controlUI.style.cursor = 'pointer';
			controlUI.style.marginBottom = '22px';
			controlUI.style.textAlign = 'center';
			controlUI.title = 'Click to close the map';
			controlDiv.appendChild(controlUI);

			// Set CSS for the control interior.
			var controlText = document.createElement('div');
			controlText.style.color = 'rgb(25,25,25)';
			controlText.style.fontFamily = 'Roboto,Arial,sans-serif';
			controlText.style.fontSize = '16px';
			controlText.style.lineHeight = '38px';
			controlText.style.paddingLeft = '5px';
			controlText.style.paddingRight = '5px';
			controlText.innerHTML = 'Close';
			controlUI.appendChild(controlText);

			controlUI.addEventListener('click', function() {
				$('.mapaddress').removeClass('in');
			});
		},
		deleteMarkers: function() {
			this.clearMarkers();
			this.markers = [];
		},
		clearMarkers: function() {
			this.setMapOnAll(null);
		},
		showMarkers: function() {
			this.setMapOnAll(this.map);
		},
		setMapOnAll: function(map) {
			for (var i = 0; i < this.markers.length; i++) {
				this.markers[i].setMap(map);
			}
		}
	}
})(jQuery);
