jQuery(function ($) {

	function sendOtp(container, identity, callback, failCallback) {
		let token = Joomla.getOptions('csrf.token');
		let paths = Joomla.getOptions('system.paths', {});
		let baseUrl = (paths.base || paths.root || '');

		let data = {identity};
		data[token] = 1;

		return $.ajax({
			url: `${baseUrl}/index.php?option=com_ajax&module=sellacious_login&method=requestOtp&format=json&ignoreMessages=0`,
			type: 'post',
			dataType: 'json',
			cache: false,
			data: data,
			beforeSend: () => {
				container.find('.msl-message').addClass('ctech-alert-warning').removeClass('ctech-alert-danger ctech-d-none').text(Joomla.JText._('MOD_SELLACIOUS_LOGIN_SENDING_OTP', 'Sending OTP...'));
			}
		})
			.done(r => {
				if (r.success) {
					container.find('.msl-message').addClass('ctech-alert-success').removeClass('ctech-alert-danger ctech-alert-warning ctech-d-none').text(r.message);
				} else {
					container.find('.msl-message').removeClass('ctech-alert-success ctech-alert-warning ctech-d-none').addClass('ctech-alert-danger').text(r.message);
				}
			})
			.fail(() => {
				container.find('.msl-message').removeClass('ctech-alert-success ctech-d-none').addClass('ctech-alert-danger').text(Joomla.JText._('MOD_SELLACIOUS_LOGIN_OTP_NOT_SENT', 'Error sending OTP'));
			});
	}

	function doLogin(container, identity, passkey) {
		let token = Joomla.getOptions('csrf.token');
		let paths = Joomla.getOptions('system.paths', {});
		let baseUrl = (paths.base || paths.root || '');

		let data = {identity, passkey};
		data[token] = 1;

		$.ajax({
			url: `${baseUrl}/index.php?option=com_ajax&module=sellacious_login&method=login&format=json&ignoreMessages=0`,
			type: 'post',
			dataType: 'json',
			cache: false,
			data: data,
		})
			.done(r => {
				if (r.success) {
					window.location.href = window.location.href + '';
				} else {
					container.find('.msl-message').removeClass('ctech-alert-success ctech-d-none').addClass('ctech-alert-danger').text(r.message);
				}
			})
			.fail(() => {
				container.find('.msl-message').removeClass('ctech-alert-success ctech-d-none').addClass('ctech-alert-danger').text(Joomla.JText._('MOD_SELLACIOUS_LOGIN_LOGIN_FAILED', 'login failed'));
			});
	}

	$('.msl-use').click(function (e) {
		e.preventDefault();
		let container = $(this).closest('.msl-container');
		container.find('.msl-message').addClass('ctech-d-none');
		container.toggleClass('msl-use-pw msl-use-otp');

		if (container.is('.msl-use-pw')) {
			container.find('.msl-username').removeAttr('readonly')
			container.find('.msl-password-label').text(Joomla.JText._('JGLOBAL_PASSWORD', 'Password'))
			container.find('.msl-password').val('')
				.attr('placeholder', Joomla.JText._('MOD_SELLACIOUS_LOGIN_PLACEHOLDER_PW', 'Enter Password'))
				.attr('type', 'password').attr('maxlength', '');
		} else {
			let identity = container.find('.msl-username').val();
			if (identity.length) {
				sendOtp(container, identity)
					.done((r) => {
						if (r.success) {
							container.find('.msl-password-label').text(Joomla.JText._('MOD_SELLACIOUS_LOGIN_OTP', 'OTP'))
							container.find('.msl-username').attr('readonly', true)
							container.find('.msl-password').val('')
								.attr('placeholder', Joomla.JText._('MOD_SELLACIOUS_LOGIN_PLACEHOLDER_OTP', 'Enter OTP'))
								.attr('type', 'text').attr('maxlength', '6');
						} else {
							container.find('.msl-password-label').text(Joomla.JText._('JGLOBAL_PASSWORD', 'Password'))
							container.toggleClass('msl-use-pw msl-use-otp');
						}
					})
					.fail(() => {
						container.find('.msl-password-label').text(Joomla.JText._('JGLOBAL_PASSWORD', 'Password'))
						container.toggleClass('msl-use-pw msl-use-otp');
					})
			} else {
				container.toggleClass('msl-use-pw msl-use-otp')
					.find('.msl-message').addClass('ctech-alert-danger').removeClass('ctech-alert-success ctech-alert-warning ctech-d-none').text(Joomla.JText._('MOD_SELLACIOUS_LOGIN_INVALID_INPUT', 'Invalid input'));
			}
		}
	});

	$('.msl-otp-resend').click(function (e) {
		e.preventDefault();
		let container = $(this).closest('.msl-container');
		container.find('.msl-message').addClass('ctech-d-none');
		let identity = container.find('.msl-username').val();
		container.find('.msl-password').val('');
		if (identity.length) {
			sendOtp(container, identity);
		}
	});

	$('.msl-login-button').click(function (e) {
		e.preventDefault();
		let container = $(this).closest('.msl-container');
		container.find('.msl-message').addClass('ctech-d-none');
		let identity = container.find('.msl-username').val();
		let passkey = container.find('.msl-password').val();

		if (identity.length < 1 || passkey.length < 1) {
			container.find('.msl-message').addClass('ctech-alert-danger').removeClass('ctech-alert-success ctech-d-none').text(Joomla.JText._('MOD_SELLACIOUS_LOGIN_INVALID_INPUT', 'Invalid input'));
		}

		doLogin(container, identity, passkey);
	});
});
