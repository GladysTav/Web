<?php
/**
 * @version     1.7.3
 * @package     sellacious
 *
 * @copyright   Copyright (C) 2012-2019 Bhartiy Web Technologies. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Izhar Aazmi <info@bhartiy.com> - http://www.bhartiy.com
 */
// no direct access
defined('_JEXEC') or die;

/** @var  SellaciousViewProduct  $this */
$item           = $this->item;
JHtml::_('stylesheet', 'com_sellacious/fe.component.css', null, true);
JHtml::_('stylesheet', 'com_sellacious/fe.view.product.css', null, true);
?>

    <div class="dropdown" id="share-buttons">

        <a data-toggle="dropdown">
            <i class="fa fa-share-alt"></i>
        </a>
        <div class="dropdown-menu dropdown-menu-left sharebtns">

            <span class="share_via"><?php echo JText::_('SELLA_SELLACIOUS_SHARE_VIA'); ?></span>
            <!-- Facebook -->
            <a class="fb" href="http://www.facebook.com/sharer.php?u=<?php echo JUri::getInstance(); ?>" target="_blank"><i class="fa fa-facebook"></i></a>

            <!-- Google+ -->
            <a class="gplus" href="https://plus.google.com/share?url=<?php echo JUri::getInstance(); ?>" target="_blank"><i class="fa fa-google-plus"></i></a>

            <!-- Twitter -->
            <a class="twt" href="https://twitter.com/share?url=<?php echo JUri::getInstance(); ?>&amp;text=<?php echo $item->get('title'); ?>&amp;hashtags=<?php echo $item->get('title'); ?>" target="_blank"><i class="fa fa-twitter"></i></a>

            <!-- LinkedIn -->
            <a  class="linkdn" href="http://www.linkedin.com/shareArticle?mini=true&amp;url=<?php echo JUri::getInstance(); ?>" target="_blank"><i class="fa fa-linkedin"></i></a>

            <!-- Pinterest -->
            <a class="pin" href="javascript:void((function()%7Bvar%20e=document.createElement('script');e.setAttribute('type','text/javascript');e.setAttribute('charset','UTF-8');e.setAttribute('src','http://assets.pinterest.com/js/pinmarklet.js?r='+Math.random()*99999999);document.body.appendChild(e)%7D)());"> <i class="fa fa-pinterest"></i></a>

            <!-- Reddit -->
            <a class="redit" href="http://reddit.com/submit?url=<?php echo JUri::getInstance(); ?>&amp;title=<?php echo $item->get('title'); ?>" target="_blank"><i class="fa fa-reddit"></i></a>

            <!-- Tumblr-->
            <a class="tumblr" href="http://www.tumblr.com/share/link?url=<?php echo JUri::getInstance(); ?>" target="_blank"><i class="fa fa-tumblr"></i></a>

            <!-- Email -->
            <a  class="mail" href="mailto:?Subject=<?php echo $item->get('title'); ?><?php echo JUri::getInstance(); ?>"> <i class="fa fa-envelope-o"></i></a>

        </div>
    </div>




    <!--  Visible at 1199px screen size  -->
    <div class="dropdown-sm" id="share-buttons-sm">

        <a class="drop-btn-share">
            <i class="fa fa fa-share"></i>
        </a>


        <div class="dropdown-menu-sm sharebtns tab-box-slideup">
            <div class="top-share">

                <p class="share-title"><?php echo JText::_('COM_SELLACIOUS_PRODUCT_SHARE'); ?></p>
            <span class="pull-right share-close">
                <i class="fa fa-close"></i>
            </span>
            </div>


          <div class="share-cont">
            <!-- Facebook -->
            <a class="fb soc-btn" href="http://www.facebook.com/sharer.php?u=<?php echo JUri::getInstance(); ?>" target="_blank"><i class="fa fa-facebook"></i>
            <p><?php echo JText::_('SELLA_SELLACIOUS_SHARE_VIA_FB'); ?></p>
            </a>

            <!-- Google+ -->
            <a class="gplus soc-btn" href="https://plus.google.com/share?url=<?php echo JUri::getInstance(); ?>" target="_blank"><i class="fa fa-google-plus"></i>
              <p><?php echo JText::_('SELLA_SELLACIOUS_SHARE_VIA_GOOGLE_P'); ?></p>
            </a>

            <!-- Twitter -->
            <a class="twt soc-btn" href="https://twitter.com/share?url=<?php echo JUri::getInstance(); ?>&amp;text=<?php echo $item->get('title'); ?>&amp;hashtags=<?php echo $item->get('title'); ?>" target="_blank"><i class="fa fa-twitter"></i>
              <p><?php echo JText::_('SELLA_SELLACIOUS_SHARE_VIA_TWITTER'); ?></p>
            </a>

            <!-- LinkedIn -->
            <a class="linkdn soc-btn" href="http://www.linkedin.com/shareArticle?mini=true&amp;url=<?php echo JUri::getInstance(); ?>" target="_blank"><i class="fa fa-linkedin"></i>
              <p><?php echo JText::_('SELLA_SELLACIOUS_SHARE_VIA_LINKDIN'); ?></p>
            </a>

            <!-- Pinterest -->
            <a class="pin soc-btn" href="javascript:void((function()%7Bvar%20e=document.createElement('script');e.setAttribute('type','text/javascript');e.setAttribute('charset','UTF-8');e.setAttribute('src','http://assets.pinterest.com/js/pinmarklet.js?r='+Math.random()*99999999);document.body.appendChild(e)%7D)());"> <i class="fa fa-pinterest"></i>
              <p><?php echo JText::_('SELLA_SELLACIOUS_SHARE_VIA_PINTREST'); ?></p>
            </a>

            <!-- Reddit -->
            <a class="redit soc-btn" href="http://reddit.com/submit?url=<?php echo JUri::getInstance(); ?>&amp;title=<?php echo $item->get('title'); ?>" target="_blank"><i class="fa fa-reddit"></i>
              <p><?php echo JText::_('SELLA_SELLACIOUS_SHARE_VIA_REDDIT'); ?></p>
            </a>


            <!-- Tumblr-->
            <a class="tumblr soc-btn" href="http://www.tumblr.com/share/link?url=<?php echo JUri::getInstance(); ?>" target="_blank"><i class="fa fa-tumblr"></i>
              <p><?php echo JText::_('SELLA_SELLACIOUS_SHARE_VIA_TUMBLR'); ?></p>
            </a>

            <!-- Email -->
            <a  class="mail soc-btn" href="mailto:?Subject=<?php echo $item->get('title'); ?><?php echo JUri::getInstance(); ?>"> <i class="fa fa-envelope-o"></i>
              <p><?php echo JText::_('SELLA_SELLACIOUS_SHARE_VIA_MAIL'); ?></p>
            </a>

        </div>
        </div>
    </div>



<script>
    jQuery(function($){
        $(".drop-btn-share").click(function(){
            $(".dropdown-menu-sm").slideToggle("slow");
            // $(".dropdown-menu-sm").show()
        });
        $(".share-close").click(function(){
            $(".dropdown-menu-sm").hide("slow")
        });

    });
</script>
