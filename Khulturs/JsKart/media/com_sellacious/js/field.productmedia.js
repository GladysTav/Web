/**
 * @version     2.0.0
 * @package     sellacious
 *
 * @copyright   Copyright (C) 2012-2020 Bhartiy Web Technologies. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Izhar Aazmi <info@bhartiy.com> - http://www.bhartiy.com
 */
class JFormFieldProductMedia {

	$;
	$element;
	timer = 0;

	constructor(element, $) {
		const self = this;

		self.$ = $;
		self.$element = self.$(element);
		self.initCopy2Clipboard(this);
		self.refresh();
		self.$element.on('click', '.jff-productmedia-remove', function () {
			self.removeHandler(self.$(this));
		});

		self.$element.on('click', '.jff-productmedia-state-publish', function (e) {
			e.preventDefault();
			self.publishAjax(self.$(this), 1);
		});
		self.$element.on('click', '.jff-productmedia-state-unpublish', function (e) {
			e.preventDefault();
			self.publishAjax(self.$(this), 0);
		})
	}

	removeHandler(btn) {
		const self = this;

		if (!btn.data('confirm')) {
			btn.data('confirm', true).html('<i class="fa fa-question-circle"></i> ');
			return setTimeout(() => btn.data('confirm', false).html('<i class="fa fa-times"></i> '), 6000);
		}

		const token = Joomla.getOptions('csrf.token');
		const $row = btn.closest('.jff-productmedia-row');
		const id = $row.data('media-id');

		btn.data('confirm', false).html('<i class="fa fa-times"></i> ');

		self.$.ajax({
			url: `index.php?option=com_sellacious&task=product.removeEProductAjax&${token}=1`,
			type: 'POST',
			data: {id},
			cache: false,
			dataType: 'json',
		}).done(response => {
			if (response.status === 1) {
				$row.find('.hasTooltip').tooltip('destroy');
				$row.fadeOut('slow').remove();
			} else {
				self.messageWait({error: [response.message]}, 6000);
			}
		}).fail(jqXHR => console.log(jqXHR.responseText));
	}

	publishAjax (element, value) {
		// const $id = '#' + this.options.wrapper;
		const self = this;
		const $id = element[0];
		const token = Joomla.getOptions('csrf.token') + '=1';
		const cid = self.$($id).closest('.jff-productmedia-row').data('media-id');

		self.$.ajax({
			url: 'index.php?option=com_sellacious&task=' + (value ? 'productmedia.publishAjax' : 'productmedia.unpublishAjax') + '&' + token,
			type: 'POST',
			data: {cid},
			cache: false,
			dataType: 'json'
		}).done(response => {
			if (response.status === 1) {
				// This id is globally unique so its ok to search in root node
				const a = self.$($id);

				a.toggleClass('jff-productmedia-state-unpublish hasTooltip', value)
					.toggleClass('jff-productmedia-state-publish', !value)
					.attr('title', value ? 'Published' : 'Unpublished')
					.find('i')
					.toggleClass('fa-eye', value).toggleClass('fa-eye-slash', !value);
				a.closest('.jff-productmedia-row')
					.toggleClass('unpublished-row', !value).toggleClass('published-row', value)
					.toggleClass('disabled-media', !value);

				self.$($id).tooltip('destroy').tooltip({
						html: true,
						container: 'body'
					});
			} else {
				self.messageWait({error: [response.message]}, 8000);
			}
		});
	}

	refresh() {
		const self = this;
		const code = self.$element.data('itemCode');
		return self.$.ajax({
			url: `index.php?option=com_sellacious&view=productmedia&layout=items&p=${code}`,
			type: 'get',
			cache: false,
			beforeSend() {
				Joomla.loadingLayer('show', self.$element[0]);
			}
		})
			.done(response => {
				self.$element.find('.jff-productmedia-items').html(response);
				self.$element.find('.hasTooltip').tooltip();
			})
			.fail(jqXHR => console.log(jqXHR.responseText))
			.always(() => Joomla.loadingLayer('hide'));
	}

	messageWait(messages, timeout) {
		const self = this;
		let type;

		// Use custom container with fallback to default system message container
		let $container = self.$element.find('.messages-container');

		if ($container.length === 0) {
			Joomla.renderMessages(messages);
			return;
		}

		$container.empty();

		for (type in messages) {
			if (messages.hasOwnProperty(type)) {
				let title = Joomla.JText._(type);
				let $box = self.$('<div/>', {class: 'alert alert-' + type + ' fade in'});
				$box.append('<button class="close" data-dismiss="alert">×</button>');
				if (typeof title != 'undefined') $box.append(self.$('<h4>', {class: 'alert-heading'}).html(title));
				for (let i = messages[type].length - 1; i >= 0; i--) $box.append(self.$('<p>').html(messages[type][i]));
				$container.append($box);
			}
		}

		if (typeof timeout === 'undefined' || timeout > 0) {
			// Clear any pending timeOut otherwise it may conflict
			if (self.timer) clearTimeout(self.timer);
			self.timer = setTimeout(() => $container.empty(), timeout || 6000);
		}
	}

	initCopy2Clipboard() {
		const self = this;

		/** @namespace  ClipboardJS */
		new ClipboardJS('.btn-copy-code', {
			text: trigger => self.$(trigger).data('text')
		}).on('success', e => {
			let $element = self.$(e.trigger);
			let oText = $element.attr('data-real-title') || $element.attr('data-original-title');
			$element.attr('data-real-title', oText);
			$element.tooltip('destroy').attr('title', '<i class="fa fa-check-square-o"></i> Copied!')
				.data('html', true).tooltip().tooltip('show');
			setTimeout(function () {
				$element.attr('title', oText).attr('data-original-title', oText).tooltip('hide').tooltip('destroy').tooltip();
			}, 1000);
		}).on('error', self.$.noop);
	}
}

jQuery($ => $('.jff-productmedia-wrapper').each(function () {
	$(this).data('productmedia-instance', new JFormFieldProductMedia(this, $));
}));
