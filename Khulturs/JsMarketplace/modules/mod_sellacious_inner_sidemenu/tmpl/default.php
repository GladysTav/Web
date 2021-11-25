<?php
/**
 * @version     1.6.0
 * @package     sellacious
 *
 * @copyright   Copyright (C) 2012-2018 Bhartiy Web Technologies. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Chandni <info@bhartiy.com> - http://www.bhartiy.com
 */
// no direct access
defined('_JEXEC') or die;

/** @var  SellaciousViewWishlist $this */
JHtml::_('behavior.framework');
JHtml::_('jquery.framework');
JHtml::_('script', 'com_sellacious/util.noframes.js', false, true);
JHtml::_('script', 'com_sellacious/fe.view.sellacious.js', false, true);
JHtml::_('script', 'com_sellacious/util.cart.aio.js', false, true);
JHtml::_('script', 'com_sellacious/fe.view.wishlist.js', false, true);

JHtml::_('stylesheet', 'com_sellacious/font-awesome.min.css', null, true);
JHtml::_('stylesheet', 'com_sellacious/fe.component.css', null, true);
JHtml::_('stylesheet', 'com_sellacious/util.rating.css', null, true);
JHtml::_('stylesheet', 'mod_sellacious_inner_sidemenu/style.css', null, true);

$doc = JFactory::getDocument();
$me         = JFactory::getUser();

$user_id = $me->get('id');

$avatar  = $helper->media->getImage('user.avatar', $me->id, true);


?>

<div class="profile-box">
    <div class="profileImg-container">
<!--        <i class="fa fa-user"></i>-->
        <img src="<?php echo $avatar; ?>">


    </div>
    <div class="userDetails-container">
        <h2><?php echo $me->get('name'); ?></h2>
        <p> <?php echo $me->get('email'); ?></p>
    </div>

</div>
<a href="<?php echo JRoute::_('index.php?option=com_sellacious&view=wishlist') ?>" class="myAccWishlist inner-side-menu-top"><span><i class="fa fa-heart"></i> <?php echo JText::_('COM_SELLACIOUS_PRODUCT_WISHLIST') ?></span></a>
<a href="<?php echo JRoute::_('index.php?option=com_sellacious&view=seller')?>" class="sell-with-us inner-side-menu-top"><i class="fa fa-dollar"></i> <?php echo JText::_('COM_SELLACIOUS_SELL_WITH_US')?></a>

<div class="my-menu">
    <ul>
        <li><?php echo JText::_('COM_SELLACIOUS_STATUS_CONTEXT_ORDER')?>
            <ul>
                <li><a href="<?php echo JRoute::_('index.php?option=com_sellacious&view=orders')?>"><?php echo JText::_('COM_SELLACIOUS_MY_ORDER')?></a></li>
            </ul>
        </li>

        <li><?php echo JText::_('COM_SELLACIOUS_MANAGE_ACCOUNT')?>
            <ul>
                <li><a href="<?php echo JRoute::_('index.php?option=com_sellacious&view=profile')?>"><?php echo JText::_('COM_SELLACIOUS_MY_ACCOUNT')?></a></li>
                <li><a href="<?php echo JRoute::_('index.php?option=com_sellacious&view=profile&layout=edit')?>"><?php echo JText::_('COM_SELLACIOUS_PROFILE')?></a> </li>
                <li><a href="<?php echo JRoute::_('index.php?option=com_sellacious&view=addresses')?>"><?php echo JText::_('COM_SELLACIOUS_ADDRESS_BOOK')?></a></li>
            </ul>
        </li>




    </ul>
</div>
