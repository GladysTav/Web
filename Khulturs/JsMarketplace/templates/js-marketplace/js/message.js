(function ($) {
	Joomla.renderMessages = messages => {
		let type, color, i;
		let timeout = 4000;
		for (type in messages) {
			if (!messages.hasOwnProperty(type)) continue;
			switch (type) {
				case 'success':
					color = '#599e51';
					break;
				case 'error':
					color = '#df481d';
					break;
				case 'warning':
					color = '#ffc107';
					break;
				case 'message':
				case 'notice':
				case 'info':
				default:
					color = '#17a2b8';
					break;
			}
			for (i = messages[type].length - 1; i >= 0; i--) {
				let title = Joomla.JText._(type) || (s => s.charAt(0).toUpperCase() + s.slice(1))(type);
				let content = messages[type][i];
				$.smallBox({title, content, color, timeout, iconSmall: 'fa fa-times bounce animated', sound: false});
			}
		}
	};
	Joomla.removeMessages = () => {};

	$(document).ready(() => {
		// Show initial messages on page load
		let j = $('#system-message-json').text(), ms = j === '' ? {} : JSON.parse(j);
		if (Object.keys(ms).length) Joomla.renderMessages(ms);
	})
})(jQuery);
