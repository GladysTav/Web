/**
 * @version     2.0.0
 * @package     sellacious
 *
 * @copyright   Copyright (C) 2012-2020 Bhartiy Web Technologies. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Aditya Chakraborty <info@bhartiy.com> - http://www.bhartiy.com
 */

var ShopRuleClassGroup = function () {
	return this;
};

(function ($) {
	ShopRuleClassGroup.prototype = {
		init: function (selector, token, allowNew, selectionSize) {
			var $that = this;
			$that.element = $(selector);
			$that.token = token;
			$that.allowNew = allowNew != null ? allowNew : true;
			$that.selectionSize = selectionSize != null ? selectionSize : 0;

			var tags = $that.element.data('tags');

			$that.element.select2({
				tags: tags,
				maximumSelectionSize: selectionSize,
				multiple: true,
				createSearchChoice: function (term) {
					if (!$that.allowNew) {
						return false;
					}

					var choice = {
						id: $.trim(term),
						text: $.trim(term)
					};
					// First match from tags available
					$.each(tags, function (i, tag) {
						if (tag.text.toUpperCase() === $.trim(term).toUpperCase()) {
							choice.id = tag.id;
							choice.text = tag.text;
						}
					});
					// next match from current selection, select2 will be available at the time of calling this
					$.each($that.element.select2('data'), function (i, tag) {
						if (tag.text.toUpperCase() === $.trim(term).toUpperCase()) {
							choice.id = tag.id;
							choice.text = tag.text;
						}
					});
					// return finally
					return choice;
				}
			});

			$that.element.on('select2-selecting', function (e) {
				if (typeof e.choice === 'object' && typeof e.choice.text !== 'undefined') {
					if (/^\s*$/.test(e.choice.text)) {
						return false;
					} else if (/,/.test(e.choice.text)) {
						alert('Commas are not allowed.');
						return false;
					}
				}
			});

			// Preload existing, not included in form-field html to retain design consistency
			$that.preloadExisting();
		},

		preloadExisting: function () {
			var $that = this;
			var items = $that.element.data('value');
			var data = [];

			$.each(items, function (i, item) {
				data.push(item);
			});
			$that.element.select2('data', data);
		},
	}
})(jQuery);
