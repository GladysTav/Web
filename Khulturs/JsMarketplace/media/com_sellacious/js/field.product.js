/**
 * @version     1.7.4
 * @package     sellacious
 *
 * @copyright   Copyright (C) 2012-2019 Bhartiy Web Technologies. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Izhar Aazmi <info@bhartiy.com> - http://www.bhartiy.com
 */

var JFormFieldProduct = function () {
	this.options = {
		id: 'jform',
		name: 'jform'
	};
};

(function ($) {
	JFormFieldProduct.prototype = {
		setup: function (options) {
			$.extend(this.options, options);
			this.select2();
			return this;
		},

		select2: function () {
			var $this = this;
			var $id = '#' + this.options.id;

			$($id).select2({
				allowClear: true,
				multiple: $this.options.multiple,
				minimumInputLength: 1,
				placeholder: $this.options.hint || ' ',
				ajax: {
					url: 'index.php',
					dataType: 'json',
					data: function (term, page) {
						return {
							option: 'com_sellacious',
							task: 'product.autocomplete',
							query: term,
							type: $this.options.type,
							context: $this.options.context,
							separate: $this.options.separate ? 1 : 0,
							seller_uid: $this.options.seller_uid ? parseInt($this.options.seller_uid) : 0,
							list_start: 10 * (page - 1),
							list_limit: 10
						};
					},
					results: function (response, page) {
						// Parse the results into the format expected by Select2.
						// If we are using custom formatting functions we do not need to alter remote JSON data
						var results = [];
						var o_results = [];
						var pattern = /P([\d]+)V([\d]*)S(-1|[\d]+)/i;
						if (response.status === 1) {
							$.each(response.data, function (i, v) {
								var p_id = v.id;
								var p_code = (p_id != '' && p_id !== null && isNaN(p_id)) ? ' (' + p_id.replace('V0', ' ') + ')' : '';
								var match = pattern.exec(v.id);

								if (match && $this.options.seller_uid <= 0) {
									var productId = match[1];

									if (o_results[productId] === undefined) {
										results.push({id: productId, text: (v.title)});
										o_results[productId] = {id: productId, text: (v.title)};
									}
								}

								results.push({id: v.id, text: (v.full_title || v.title), code: p_code});
								o_results[v.id] = {id: v.id, text: (v.full_title || v.title)};
							});
						}

						return {results: results};
					}
				},
				initSelection: function (element, callback) {
					// The input tag has a value attribute preloaded that points to a preselected items id
					// This function resolves that id attribute to an object that select2 can render
					// using its formatResult renderer - that way the item title is shown preselected
					var values = $(element).val();

					if (values === '' || values === '0') return;

					$.ajax({
						url: 'index.php',
						dataType: 'json',
						data: {
							option: 'com_sellacious',
							task: 'product.getInfoAjax',
							id: values.split(','),
							type: $this.options.type,
							context: $this.options.context,
							separate: $this.options.separate ? 1 : 0,
						},
					}).done(function (response) {
						var results = [];
						var new_values = [];
						var old_values = values.split(',');
						var pattern = /P([\d]+)V([\d]*)S(-1|[\d]+)/i;
						if (response.status === 1) {
							$.each(response.data, function (i, v) {
								var p_id = v.id;
								var p_code = (p_id != '' && p_id !== null && isNaN(p_id)) ? ' (' + p_id.replace('V0', ' ') + ')' : '';
								var match = pattern.exec(v.id);

								if (match && $this.options.seller_uid <= 0) {
									var productId = match[1];

									if ($.inArray(productId, old_values) != -1) {
										if ($.inArray(productId, new_values) == -1) {
											new_values.push(productId);
											results.push({id: productId, text: (v.title)});
										}

										return true;
									}
								}

								new_values.push(v.id);
								results.push({id: v.id, text: (v.full_title || v.title) + '<strong>' + p_code + '</strong>'});
							});
						}
						$(element).val(new_values.join(','));
						// {id, text} for single-select, [{id, text},{id, text}] for multi-select
						callback($this.options.multiple ? results : results[0]);
					}).fail(function (response) {
						console.log(response.responseText);
					});
				},
				formatResult: function (result) {
					if (result.code) {
						return result.text + '<strong>' + result.code + '</strong>';
					} else {
						return result.text;
					}
				},
				formatSelection: function (result) {
					if (result.code) {
						return result.text + '<strong>' + result.code + '</strong>';
					} else {
						return result.text;
					}
				}
			});
		}
	}
})(jQuery);
