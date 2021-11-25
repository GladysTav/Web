/**
 * @version     1.7.4
 * @package     sellacious
 *
 * @copyright   Copyright (C) 2012-2019 Bhartiy Web Technologies. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Izhar Aazmi <info@bhartiy.com> - http://www.bhartiy.com
 */
jQuery(document).ready(function () {
	document.formvalidator.setHandler('mobile', function (value) {
		return /^(\+\d{1,3}[- ]?)?\d{10,12}$/.test(value);
	});
});