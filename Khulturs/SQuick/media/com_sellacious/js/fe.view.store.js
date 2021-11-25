/**
 * @version     2.0.0
 * @package     sellacious
 *
 * @copyright   Copyright (C) 2012-2020 Bhartiy Web Technologies. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Aditya Chakraborty <info@bhartiy.com> - http://www.bhartiy.com
 */

jQuery(document).ready(function ($) {
    $(document).on('click', '#open-store-location', (e) => {
        e.preventDefault();
        let coords = $('#store-location').data('coords');

        $('.store-location-content').addClass('open');
        const latLng = new google.maps.LatLng(coords[0],coords[1]);
        let map      = new google.maps.Map(document.getElementById('store-location'), {
            center: latLng,
            zoom: 8
        });
        let marker  = new google.maps.Marker({
            position: latLng
        });

        marker.setMap(map);
    })

    $(document).on('click', '.store-location-backdrop, #close-store-location', (e) => {
        e.preventDefault();
        $('.store-location-content').removeClass('open');
    })

    $(document).on('click', '.btn-favorite:not(.disabled)', function () {
        var $this = $(this);
        var sellerUid = $this.attr('data-seller-id');
        var token = Joomla.getOptions('csrf.token');

        if (!sellerUid) {
            var guest = $this.attr('data-guest');
            var href = $this.attr('data-href');

            if (guest) {
                if (confirm(Joomla.JText._('COM_SELLACIOUS_USER_FAVORITE_STORE_LOGIN_FIRST')))
                    window.location.href = href || 'index.php?option=com_users&view=login';
            } else if (href) window.location.href = href;

            return;
        }

        var paths = Joomla.getOptions('system.paths', {});
        var baseUrl = (paths.base || paths.root || '');

        let data = {seller_uid: sellerUid};
        data[token] = 1;

        $.ajax({
            url: baseUrl + '/index.php?option=com_sellacious&task=stores.favoriteStoreAjax',
            type: 'POST',
            dataType: 'json',
            cache: false,
            data: data,
        }).done(function (response) {
            if (response.success) {
                $this.find('i.fa').addClass('fa-heart ctech-text-danger').removeClass('fa-heart-o');
                $this.closest('.user-favorite-container').removeClass('ctech-text-primary ctech-border-primary').addClass('ctech-text-danger ctech-border-danger');
                $this.closest('.user-favorite-container').find('a').addClass('btn-favorite-loggedIn').attr('data-href', response.data['url']).removeAttr('data-seller-id');
            } else {
                Joomla.renderMessages({error: [response.message]});
            }
        }).fail(function (jqXHR) {
            Joomla.renderMessages({error: ['There was an error while processing your request. Please try later.']});
        });
    });

    $('.hasTooltip').tooltip()
});
