/**
 * @version     2.0.0
 * @package     sellacious
 *
 * @copyright   Copyright (C) 2012-2020 Bhartiy Web Technologies. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Izhar Aazmi <info@bhartiy.com> - http://www.bhartiy.com
 */
class FieldCheckMatrix {

	constructor(el, jquery) {
		const self = this;

		self.jquery = jquery || window.jQuery;
		self.matrix = self.jquery(el);
		self.input = self.matrix.find('.jff-checkmatrix-input');

		self.initialise();
		self.recheck();

		self.matrix.on('change', 'input[data-column][data-row]', function () {
			self.cellChange(this);
			self.recheck();
			self.calculate();
		});

		self.matrix.on('change', 'input[data-checkall-col]', function () {
			self.colChange(this);
			self.recheck();
			self.calculate();
		});

		self.matrix.on('change', 'input[data-checkall-row]', function () {
			self.rowChange(this);
			self.recheck();
			self.calculate();
		});

		self.matrix.on('change', 'input[data-checkall]', function () {
			self.allChange(this);
			self.recheck();
			self.calculate();
		});

		self.matrix.data('checkmatrix', this);
	}

	initialise() {
		const self = this;
		const value = self.jquery.trim(self.input.val());
		let values = value === '' ? {} : JSON.parse(value);
		self.matrix.find('input[data-column][data-row]').each(function () {
			const col = self.jquery(this).data('column');
			const row = self.jquery(this).data('row');
			if (typeof values[col] === 'object' && typeof values[col][row] !== 'undefined' && values[col][row] === 1)
				self.jquery(this).prop('checked', true);
		});
	}

	cellChange(el) {
		let row = this.jquery(el).data('row');
		let col = this.jquery(el).data('column');
	};

	colChange(el) {
		let col = this.jquery(el).data('checkall-col');
		this.matrix.find('input[data-column="' + col + '"]').prop('checked', el.checked);
	};

	rowChange(el) {
		let row = this.jquery(el).data('checkall-row');
		this.matrix.find('input[data-row="' + row + '"]').prop('checked', el.checked);
	};

	allChange(el) {
		this.matrix.find('input[data-column][data-row]').prop('checked', el.checked);
	};

	recheck() {
		const self = this;
		self.matrix.find('input[data-checkall]').each(function () {
			self.jquery(this).prop('checked', self.matrix.find('input[data-column][data-row]').not(':checked').length === 0);
		});
		self.matrix.find('input[data-checkall-col]').each(function () {
			const col = self.jquery(this).data('checkall-col');
			self.jquery(this).prop('checked', self.matrix.find('input[data-column="' + col + '"]').not(':checked').length === 0);
		});
		self.matrix.find('input[data-checkall-row]').each(function () {
			const row = self.jquery(this).data('checkall-row');
			self.jquery(this).prop('checked', self.matrix.find('input[data-row="' + row + '"]').not(':checked').length === 0);
		});
	}

	calculate() {
		const self = this;
		const values = {};
		self.matrix.find('input[data-column][data-row]').each(function () {
			const col = self.jquery(this).data('column');
			const row = self.jquery(this).data('row');
			const checked = self.jquery(this).prop('checked');
			typeof values[col] === 'undefined' && (values[col] = {});
			if (checked) values[col][row] = 1;
		});
		self.input.val(JSON.stringify(values));
	}
}

jQuery(document).ready(jquery => {

	jquery('.jff-checkmatrix').each(function () {
		new FieldCheckMatrix(this, jquery);
	});

});
