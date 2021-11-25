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
        $('li.deeper').on('hover', function(){
            if($('li.deeper:not(:first-child)')) {
                $('li.deeper:first-child').css('background', 'transparent');
            } else
            {
                $('li.deeper:first-child').css('background', 'white');
            }

        })

    });
})(jQuery);

