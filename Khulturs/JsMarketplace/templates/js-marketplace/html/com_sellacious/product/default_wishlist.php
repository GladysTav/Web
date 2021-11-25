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
$user = JFactory::getUser();
$code = $this->state->get('product.code');

$page_id = $this->getLayout() == 'modal' ? 'product_modal' : 'product';
?>
<div class="product-toolbar-wishlist">

    <?php
    if ($this->helper->config->get('product_wishlist')):
        if ($user->guest):
            $url   = JRoute::_('index.php?option=com_sellacious&view=product&p=' . $code, false);
            $login = JRoute::_('index.php?option=com_users&view=login&return=' . base64_encode($url), false); ?>
        <a  title="<?php echo JText::_('COM_SELLACIOUS_PRODUCT_ADD_TO_WISHLIST'); ?>" class="hasTooltip btn-wishlist" data-guest="true" data-href="<?php echo $this->escape($login) ?>">
            <i class="fa fa-heart-o"></i><span></span>
            </a><?php
        elseif ($this->helper->wishlist->check($code, null)):
            $url = JRoute::_('index.php?option=com_sellacious&view=wishlist', false); ?>
        <a title="<?php echo JText::_('COM_SELLACIOUS_PRODUCT_ADDED_TO_WISHLIST'); ?>" class="hasTooltip btn-wishlist" data-href="<?php echo $this->escape($url) ?>">
            <i class="fa fa-heart"></i>
            </a><?php
        else: ?>
        <a title="<?php echo JText::_('COM_SELLACIOUS_PRODUCT_ADD_TO_WISHLIST'); ?>" class="hasTooltip btn-wishlist" data-item="<?php echo $code ?>">
            <i class="fa fa-heart-o"></i>
            </a><?php
        endif;
    endif; ?>
</div>
<div class="clearfix"></div>
