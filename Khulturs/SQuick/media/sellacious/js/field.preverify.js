/**
 * @version     2.0.0
 * @package     sellacious
 *
 * @copyright   Copyright (C) 2012-2020 Bhartiy Web Technologies. All rights reserved.
 * @license     SPL Sellacious Private License; see http://www.sellacious.com/spl.html
 * @author      Izhar Aazmi <info@bhartiy.com> - http://www.bhartiy.com
 */

/** @var  Vue */
const inputpreverify = {
	template: '#inputpreverify',
	props: ['formname', 'type', 'id', 'name', 'value', 'classname', 'placeholder', 'required', 'verified', 'pv_token', 'unique', 'userid', 'otplength'],
	data() {
		return {
			disabled: false,
			askOtp: false,
			otp: '',
			token: '',
			message: {type: 'success', text: ''},
			jquery: window.jQuery,
			timeout: 0,
		}
	},
	methods: {
		setMessage(type, text = null) {
			if (this.timeout) clearTimeout(this.timeout);
			this.message = {type, text};
		},
		send(e) {
			e.preventDefault();

			let valid;

			if (this.type === 'tel') {
				valid = /^(\+\d{1,3}[- ]?)?\d{10,12}$/.test(this.value);
			} else {
				let value = punycode.toASCII(this.value);
				valid = /^[a-zA-Z0-9.!#$%&'*+/=?^_`{|}~-]+@[a-zA-Z0-9](?:[a-zA-Z0-9-]{0,61}[a-zA-Z0-9])?(?:\.[a-zA-Z0-9](?:[a-zA-Z0-9-]{0,61}[a-zA-Z0-9])?)*$/.test(value);
			}

			if (!valid) {
				this.setMessage('danger', 'Invalid input');
				return;
			}

			this.otp = null;
			this.disabled = true;
			this.verified = false;
			window.jQuery(this.$refs.input).data('verified', false);
			this.setMessage('success', null);

			let token = Joomla.getOptions('csrf.token');
			let paths = Joomla.getOptions('system.paths', {});
			let baseUrl = (paths.base || paths.root || '');

			let data = {field: `${this.formname}.${this.id}`, type: this.type, value: this.value, unique: this.unique, userid: this.userid};
			data[token] = 1;

			this.jquery.ajax({
				url: `${baseUrl}/index.php?option=com_sellacious&task=otpauth.requestFieldOtp&format=json`,
				type: 'POST',
				dataType: 'json',
				cache: false,
				data: data,
			})
				.done(r => {
					if (r.success) {
						this.otp = null;
						this.askOtp = true;
						this.setMessage('success', r.message);
					} else {
						this.otp = null;
						this.askOtp = false;
						this.disabled = false;
						this.setMessage('danger', r.message);
					}
				})
				.fail(xhr => {
					this.otp = null;
					this.askOtp = false;
					this.disabled = false;
					this.setMessage('danger', 'Error sending OTP');
				});
		},
		change(e) {
			e.preventDefault();

			this.otp = null;
			this.askOtp = false;
			this.disabled = false;
			this.verified = false;
			window.jQuery(this.$refs.input).data('verified', false);
			this.setMessage();
		},
		verify(e) {
			e.preventDefault();

			if (this.otp === null || this.otp.length < this.otplength) {
				this.setMessage('danger', 'Enter OTP first');
				return
			}

			let token = Joomla.getOptions('csrf.token');
			let paths = Joomla.getOptions('system.paths', {});
			let baseUrl = (paths.base || paths.root || '');

			let data = {field: `${this.formname}.${this.id}`, type: this.type, value: this.value, otp: this.otp, unique: this.unique, userid: this.userid};
			data[token] = 1;

			this.jquery.ajax({
				url: `${baseUrl}/index.php?option=com_sellacious&task=otpauth.checkFieldOtp&format=json`,
				type: 'POST',
				dataType: 'json',
				cache: false,
				data: data,
			})
				.done(r => {
					if (r.success) {
						this.otp = null;
						this.askOtp = false;
						this.verified = true;
						window.jQuery(this.$refs.input).data('verified', true);
						this.token = r.data.token;
						this.setMessage('success', r.message);
					} else {
						this.otp = null;
						this.setMessage('danger', r.message);
					}
				})
				.fail(xhr => {
					this.otp = null;
					this.askOtp = true;
					this.setMessage('danger', 'Error verifying OTP');
				});
		}
	},
	mounted() {
		const self = this;
		if (self.verified) {
			self.token = this.pv_token;
			self.disabled = true;

			// Should not use jQuery
			window.jQuery(self.$refs.input).data('oldValue', self.value);
		}
	}
};

Vue.component('inputpreverify', inputpreverify);

jQuery(document).ready($ => {
	$('.preverify-wrapper').each((i, el) => new Vue({el, data: {}}));

	document.formvalidator.setHandler('preverify', (value, element) => $(element).data('verified') || $(element).data('oldValue') === value);
});
