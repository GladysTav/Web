/**
 * @version     2.0.0
 * @package     sellacious
 *
 * @copyright   Copyright (C) 2012-2020 Bhartiy Web Technologies. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Izhar Aazmi <info@bhartiy.com> - http://www.bhartiy.com
 */
Joomla = window.Joomla || {};
Joomla.submitbutton = function (task, form) {
	Joomla.submitform(task, form);
};

jQuery(document).ready(function ($) {

	$('.btn-toggle').click(function () {
		$(this).find('[data-toggle="true"]').toggleClass('hidden');
	});

	// Initialize cart modal
	var $cartModal = $('#modal-cart');
	if ($cartModal.length) {
		var oo = new SellaciousViewCartAIO;
		oo.token = Joomla.getOptions('csrf.token');
		oo.initCart('#modal-cart .modal-body', true);
		$cartModal.find('.modal-body').html('<div id="cart-items"></div>');
		$cartModal.data('CartModal', oo);
	}
});

