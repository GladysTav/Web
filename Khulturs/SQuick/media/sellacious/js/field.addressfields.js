/**
 * @version     2.0.0
 * @package     sellacious
 *
 * @copyright   Copyright (C) 2012-2020 Bhartiy Web Technologies. All rights reserved.
 * @license     SPL Sellacious Private License; see http://www.sellacious.com/spl.html
 * @author      Saurabh Sabharwal <info@bhartiy.com> - http://www.bhartiy.com
 */

/** @var  Vue */

Vue.filter('t', str => Joomla.JText._(str, str));

Vue.use('draggable')

let addressFieldMixins = {
	props: ['field', 'id'],
}

Vue.component('field-text', {
	template: '#vue-address-field-text',
	mixins: [addressFieldMixins],
})

Vue.component('field-address', {
	template: '#vue-address-field-address',
	mixins: [addressFieldMixins],
	data: function () {
		return {
			showEditor: false,
		}
	},
	methods: {

	}
})

Vue.component('field-location', {
	template: '#vue-address-field-location',
	mixins: [addressFieldMixins],
	data: function () {
		return {
			showEditor: false,
		}
	},
})

Vue.component('field-context', {
	template: '#vue-address-field-context',
	mixins: [addressFieldMixins],
	data: function () {
		return {
			showEditor: false,
		}
	},
})

Vue.component('field-radio', {
	template: '#vue-address-field-radio',
	mixins: [addressFieldMixins],
	data: function () {
		return {

		}
	}
})

const initAddressFields = (el, info, fields) => new Vue({
	el,
	data: function () {
		return {
			fields,
			id: info.id,
			name: info.name
		}
	},
	mounted: function () {

	},
	computed: {
		output: function () {
			let thisOutput = [];
			this.fields.forEach(field => {
				thisOutput.push({
					label: field.label,
					labelValue: field.labelValue,
					name: field.name,
					lines: field.lines,
					show: field.show,
					textOnly: field.textOnly,
					options: field.options ? field.options : []
				})
			})
			return JSON.stringify(thisOutput)
		}
	},
	methods: {
		t: function (text) {
			const translate = Vue.filter('t');
			return (translate(text));
		}
	}
})

jQuery(document).ready(function ($) {
	const elements = $('.addressfields')
	elements.each((i, el) => {
		const fields   = $(el).data('fields')
		const info     = $(el).data('info')
		initAddressFields(el, info, fields)
	})
})
