/**
 * @version     2.0.0
 * @package     sellacious
 *
 * @copyright   Copyright (C) 2012-2020 Bhartiy Web Technologies. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Aditya Chakraborty <info@bhartiy.com> - http://www.bhartiy.com
 */
var SellaciousFieldProduct = {
	Variant: function () {
		return this;
	}
};

(function ($) {
	SellaciousFieldProduct.Variant.prototype = {
		init: function (selector, token, baseDir) {
			var $that = this;
			$that.token = token;
			$that.baseDir = baseDir;
			$that.element = $(selector);

			$that.element.on('click', '.change-state-variant', function () {
				var id = $(this).data('id');
				var state = $(this).data('state') == 0 ? 1 : 0;

				$that.changeStateVariant(id, state, function () {
					$that.getVariant(id, function (data) {
						$that.addRow(data);
					});

					$(document).trigger('changeStateVariant', id, state);
				});
			});

			$that.element.on('click', '.delete-variant', function (e) {
				var $target = $(e.target).is('.delete-variant') ? $(e.target) : $(e.target).closest('.delete-variant');
				if ($target.data('confirm')) {
					$target.data('confirm', false);
					$target.html('<i class="fa fa-times"></i> Delete');
					Joomla.renderMessages({info: ['Please wait while we attempt to remove the selected variant&hellip;']});
					var id = $(this).data('id');
					$that.removeVariant(id, function () {
						$that.element.find('#variant-row-' + id).fadeOut('slow').remove();
						$(document).trigger('removeVariant', id);
					});
				} else {
					$target.data('confirm', true);
					$target.html('<i class="fa fa-question-circle"></i> Sure');
					setTimeout(function () {
						$target.data('confirm', false);
						$target.html('<i class="fa fa-times"></i> Delete');
					}, 5000);
				}
			});
		},

		// This method is retained as it's needed to the variant row whenever a variant is added/updated
		getVariant: function (id, callback) {
			var $that = this;
			var data = {};
			data['option'] = 'com_sellacious';
			data['task'] = 'variant.getItemAjax';
			data[$that.token] = 1;
			data['id'] = id;

			$.ajax({
				url: 'index.php',
				type: 'post',
				dataType: 'json',
				data: data
			}).done(function (response) {
				if (response.state == 1) {
					Joomla.removeMessages();
					if (typeof callback === 'function')
						callback(response.data);
				} else {
					Joomla.renderMessages({error: [response.message]});
				}
			}).fail(function (response) {
				Joomla.renderMessages({warning: ['Failed to load the details for selected variant due to some server error.']});
				console.log(response.responseText);
			});
		},

		// Ajax method to publish/unpublish variant from variant row itself
		changeStateVariant: function(id, state, callback) {
			var $that = this;
			var data = {};
			data['option'] = 'com_sellacious';
			data['task'] = 'variant.changeStateAjax';
			data[$that.token] = 1;
			data['id'] = id;
			data['state'] = state;

			$.ajax({
				url: 'index.php',
				type: 'post',
				dataType: 'json',
				data: data
			}).done(function (response) {
				if (response.state == 1) {
					Joomla.renderMessages({success: [response.message]});
					if (typeof callback === 'function')
						callback(response.data);
				} else {
					Joomla.renderMessages({error: [response.message]});
				}
			}).fail(function (response) {
				Joomla.renderMessages({warning: ['Failed to remove selected variant due to some server error.']});
				console.log(response.responseText);
			});
		},

		// Remove variant from the list
		removeVariant: function (id, callback) {
			var $that = this;
			var data = {};
			data['option'] = 'com_sellacious';
			data['task'] = 'variant.deleteAjax';
			data[$that.token] = 1;
			data['id'] = id;

			$.ajax({
				url: 'index.php',
				type: 'post',
				dataType: 'json',
				data: data
			}).done(function (response) {
				if (response.state == 1) {
					Joomla.renderMessages({success: [response.message]});
					if (typeof callback === 'function')
						callback(response.data);
				} else {
					Joomla.renderMessages({error: [response.message]});
				}
			}).fail(function (response) {
				Joomla.renderMessages({warning: ['Failed to remove selected variant due to some server error.']});
				console.log(response.responseText);
			});
		},

		addRow: function (data) {
			var $that = this;

			var $row = $that.element.find('#variant-row-' + data.id);
			var $new_row = $(data.html);

			if ($row.length) {
				$row.replaceWith($new_row);
			} else {
				$that.element.find('#variants-list').find('tbody').append($new_row);
			}

			$(document).trigger('addVariant', $.extend({}, data, {html: null}));
		}
	};
})(jQuery);
