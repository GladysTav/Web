/**
 * @version     1.7.4
 * @package     sellacious
 *
 * @copyright   Copyright (C) 2012-2019 Bhartiy Web Technologies. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Aditya Chakraborty <info@bhartiy.com> - http://www.bhartiy.com
 */
var JFormFieldShopruleSlabs = function () {
	this.options = {
		id : 'jform',
		rowTemplate : {
			html : '',
			replacement : ''
		},
		rowIndex : 0
	};
};

(function ($) {
	JFormFieldShopruleSlabs.prototype = {
		setup : function (options) {
			$.extend(this.options, options);
			var that = this;
			that.wrapper = $('#' + that.options.id + '_wrapper');
			that.wrapper.on('click', '.sfssrow-add', function () {
				that.addRow();
			});
			that.wrapper.on('click', '.sfssrow-remove', function () {
				var index = this.id.match(/\d+$/);
				var $this = $(this);
				if ($this.data('confirm')) {
					$this.data('confirm', false);
					$this.html('<i class="fa fa-lg fa-times"></i> ');
					that.removeRow(index);
				} else {
					$this.data('confirm', true);
					$this.html('<i class="fa fa-lg fa-question-circle"></i> ');
					setTimeout(function () {
						$this.data('confirm', false);
						$this.html('<i class="fa fa-lg fa-times"></i> ');
					}, 5000);
				}
			});
			that.wrapper.on('change', 'input[data-input-name]', function () {
				that.evaluate();
			});
			that.wrapper.on('click', '.btn-clear-slabs', function () {
				var $this = $(this);
				if ($this.data('confirm')) {
					$this.data('confirm', false);
					$this.find('i.fa').replaceWith('<i class="fa fa-times"></i>');
					$('#' + that.options.id).val('[]');
					var rows = that.wrapper.find('.sfssrow');
					console.log(rows.length);
					rows.remove();
				} else {
					$this.data('confirm', true);
					$this.find('i.fa').replaceWith('<i class="fa fa-question-circle"></i> ');
					setTimeout(function () {
						$this.data('confirm', false);
						$this.find('i.fa').replaceWith('<i class="fa fa-times"></i> ');
					}, 5000);
				}
			});
		},

		addRow : function () {
			var that = this;
			var index = ++this.options.rowIndex;
			var template = this.options.rowTemplate.html;
			var replacement = this.options.rowTemplate.replacement;
			var html = template.replace(new RegExp(replacement, "ig"), index + "");
			$(html).insertBefore(that.wrapper.find('.sfss-blankrow'));
			this.evaluate();
		},

		removeRow : function (index) {
			$('#' + this.options.id + '_sfssrow_' + index).remove();
			this.evaluate();
		},

		evaluate: function () {
			var that = this;
			var records = [];
			var rows = that.wrapper.find('.sfssrow');
			rows.each(function () {
				var record = {};
				var row = $(this);
				$(this).find('input[data-input-name]').each(function () {
					var k = $(this).data('input-name');
					var v = $(this).val();

					if ($(this).is('[type="radio"]')) {
						record[k] = row.find('input[data-input-name="'+k+'"]:checked').length ? row.find('input[data-input-name="'+k+'"]:checked').val() : '';
					} else {
						record[k] = $(this).is('[type="checkbox"]') ? ($(this).prop('checked') ? v : 0) : v;
					}
				});
				records.push(record);
			});
			$('#' + this.options.id).val(JSON.stringify(records));
		},
	}
})(jQuery);
