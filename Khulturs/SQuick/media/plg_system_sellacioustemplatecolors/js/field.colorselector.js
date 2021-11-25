/**
 * @version     2.0.0
 * @package     sellacious
 *
 * @copyright   Copyright (C) 2012-2020 Bhartiy Web Technologies. All rights reserved.
 * @license     SPL Sellacious Private License; see http://www.sellacious.com/spl.html
 * @author      Saurabh Sabharwal <info@bhartiy.com> - http://www.bhartiy.com
 */

/** @var  Vue */
/** @var  VueColor */

var Chrome = VueColor.Chrome;

Vue.component('colorpicker', {
	components: {
		'chrome-picker': Chrome,
	},
	template: `
		<div class="input-group color-picker" ref="colorpicker">
			<input type="hidden" class="form-control color-picker" v-model="colorValue" @focus="showPicker()" @input="updateFromInput" />
			<span class="input-group-addon color-picker-container">
				<span class="current-color" :style="'background-color: ' + colorValue" @click="togglePicker()"></span>
				<chrome-picker :value="colors" @input="updateFromPicker" v-if="displayPicker" />
			</span>
		</div>`,
	props: ['color', 'classes'],
	data() {
		return {
			colors: {
				hex: '#000000',
			},
			colorValue: '',
			displayPicker: false,
		}
	},
	mounted() {
		this.setColor(this.color || '#000000');
	},
	methods: {
		setColor(color) {
			this.updateColors(color);
			this.colorValue = color;
		},
		updateColors(color) {
			if(color.slice(0, 1) == '#') {
				this.colors = {
					hex: color
				};
			}
			else if(color.slice(0, 4) == 'rgba') {
				var rgba = color.replace(/^rgba?\(|\s+|\)$/g,'').split(','),
					hex = '#' + ((1 << 24) + (parseInt(rgba[0]) << 16) + (parseInt(rgba[1]) << 8) + parseInt(rgba[2])).toString(16).slice(1);
				this.colors = {
					hex: hex,
					a: rgba[3],
				}
			}
		},
		showPicker() {
			document.addEventListener('click', this.documentClick);
			this.displayPicker = true;
		},
		hidePicker() {
			document.removeEventListener('click', this.documentClick);
			this.displayPicker = false;
		},
		togglePicker() {
			this.displayPicker ? this.hidePicker() : this.showPicker();
		},
		updateFromInput() {
			this.updateColors(this.colorValue);
		},
		updateFromPicker(color) {
			this.colors = color;
			if(color.rgba.a == 1) {
				this.colorValue = color.hex;
			}
			else {
				this.colorValue = 'rgba(' + color.rgba.r + ', ' + color.rgba.g + ', ' + color.rgba.b + ', ' + color.rgba.a + ')';
			}
		},
		documentClick(e) {
			var el = this.$refs.colorpicker,
				target = e.target;
			if(el !== target && !el.contains(target)) {
				this.hidePicker()
			}
		}
	},
	watch: {
		colorValue(val) {
			if(val) {
				this.updateColors(val);
				this.$emit('input', val);
			}
		}
	},
});

let colors = '#194d33'

const initPicker = (el, options) => new Vue({
	el,
	data () {
		return {
			options,
			defaultColor: '#000',
			showEditor: false
		}
	},
	mounted: function () {

	},
	methods: {
		closeEditor: function () {
			this.options.attributes.forEach(attr => {
				this.$set(attr, 'current', attr.value)
				this.$set(attr, 'hoverCurrent', attr.hoverValue)
			})
			this.showEditor = false
		},
		resetColors: function () {
			this.options.attributes.forEach((attr, i) => {
				this.$set(attr, 'current', attr.default)
				this.$set(attr, 'hoverCurrent', attr.hover)
			})
			this.showEditor = false
			this.$forceUpdate()
		}
	},
	computed: {
		css: function () {
			let pseudo_attribute_value = ''
			let style = `<style>`
			style += `.ctech-wrapper ${this.options.selector} {`
			this.options.attributes.forEach(attr => {
				style += `${attr.property}: ${attr.current} !important; `
				if (options.pseudo_selector) {
					pseudo_attribute_value = attr.current
				}
			})
			style += `}`

			if (options.hover == 'true') {
				style += `.ctech-wrapper ${this.options.selector}:hover {`

				this.options.attributes.forEach(attr => {
					style += `${attr.property}: ${attr.hoverCurrent} !important; `
				})

				style += `}`
			}

			if (options.pseudo_selector) {
				style += `.ctech-wrapper ${this.options.pseudo_selector} {`
				style += `${this.options.pseudo_attribute}: ${pseudo_attribute_value} !important`
				style += `}`
			}

			style += `</style>`

			return style
		},
		value: function () {
			let values = {};
			values[this.options.fieldName] = {}
			values[this.options.fieldName].css = this.css
			this.options.attributes.forEach(attr => {
				values[this.options.fieldName][attr.property] = {
					'value': attr.current,
					'hover': attr.hoverCurrent
				}
			})

			return JSON.stringify(values)
		}
	},
})

jQuery(document).ready($ => {
	$('.colorselector').each((i, selector) => {
		const id = $(selector).data('id');
		const options = $(selector).data('options')
		initPicker(selector, options)
	})
})
