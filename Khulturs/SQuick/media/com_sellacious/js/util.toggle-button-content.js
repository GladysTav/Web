/**
 * @version     2.0.0
 * @package     sellacious
 *
 * @copyright   Copyright (C) 2012-2020 Bhartiy Web Technologies. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Saurabh Sabharwal <info@bhartiy.com> - http://www.bhartiy.com
 */

(function ($) {
	$(document).ready(function () {
		$(document).on('click', '.toggle-button-content', (e) => {
			e.preventDefault();

			$(this).find('.toggle-title, .toggle-subtitle').toggleClass('ctech-d-none')
		})
	})
})(jQuery)
