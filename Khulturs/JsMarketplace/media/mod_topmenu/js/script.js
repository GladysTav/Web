/**
 * @version     1.6.1
 * @package     sellacious
 *
 * @copyright   Copyright (C) 2012-2019 Bhartiy Web Technologies. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Chandni <info@bhartiy.com> - http://www.bhartiy.com
 */

(function ($) {
    $(document).ready(function () {
        $('.mainmenu ul ul ~ li').on('hover', function(){

            $('.mainmenu').find('ul ul ul ~ li').each(function () {
                if($(this).css('opacity') == '1'){
                    console.log($(this));
                    $(this).closest('li').addClass('active-li-menu');
                }
            });
        })

    });
})(jQuery);

