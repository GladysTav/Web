/**
 * @version     2.0.0
 * @package     sellacious
 *
 * @copyright   Copyright (C) 2012-2020 Bhartiy Web Technologies. All rights reserved.
 * @license     SPL Sellacious Private License; see http://www.sellacious.com/spl.html
 * @author      Saurabh Sabharwal <info@bhartiy.com> - http://www.bhartiy.com
 */

(function ($) {
	$(document).ready(() => {
		$('.view-option').on('click', (e) => {
			const $this  = $(e.target)
			const design = $this.closest('.view-option').data('design');
			$this.closest('.view-selector').find('input[type="hidden"]').val(design);
			$this.closest('.view-selector').find('.view-option').removeClass('selected')
			$this.closest('.view-option').addClass('selected')
		})
	})
})(jQuery)
