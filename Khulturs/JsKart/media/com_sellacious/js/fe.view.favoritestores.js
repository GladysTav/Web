/**
 * @version     2.0.0
 * @package     sellacious
 *
 * @copyright   Copyright (C) 2012-2020 Bhartiy Web Technologies. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Aditya Chakraborty <info@bhartiy.com> - http://www.bhartiy.com
 */

jQuery(document).ready(function ($) {
    $(document).on('click', '.favorite-remove-store', function (e) {
        e.preventDefault();
        var sellerUid = $(this).data('seller-uid');
        if (!sellerUid) return;

        var paths = Joomla.getOptions('system.paths', {});
        var baseUrl = (paths.base || paths.root || '');
        var token = Joomla.getOptions('csrf.token');

        let data = {seller_uid: sellerUid};
        data[token] = 1;

        $.ajax({
            url: baseUrl + '/index.php?option=com_sellacious&task=stores.removeFavoriteStoreAjax',
            type: 'POST',
            dataType: 'json',
            cache: false,
            data: data,
        }).done(function (response) {
            if (response.success) {
                $('[data-seller-uid="' + sellerUid + '"]').closest('.store-wrap').fadeOut('fast', function () {
                    $(this).remove();
                });

                $('.favorite-stores-heading span').html(response.data.total);
            } else {
                Joomla.renderMessages({error: [response.message]});
            }
        }).fail(function (jqXHR) {
            Joomla.renderMessages({error: ['There was an error while processing your request. Please try later.']});
        });
    });
});
