/**
 * @version     2.0.0
 * @package     sellacious
 *
 * @copyright   Copyright (C) 2012-2020 Bhartiy Web Technologies. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Aditya Chakraborty <info@bhartiy.com> - http://www.bhartiy.com
 */

jQuery(document).ready(function ($) {
    $('.terms-modal').on('click', function (e) {
        e.preventDefault();
        let $id = $(this).data('shoprule-id');

        $('#shoprule-terms-modal-' + $id).ctechmodal('show');
    });
});
