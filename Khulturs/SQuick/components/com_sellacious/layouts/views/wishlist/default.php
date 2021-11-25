<?php
/**
 * @version     2.0.0
 * @package     sellacious
 *
 * @copyright   Copyright (C) 2012-2020 Bhartiy Web Technologies. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Izhar Aazmi <info@bhartiy.com> - http://www.bhartiy.com
 */
// no direct access
defined('_JEXEC') or die;

/** @var  SellaciousViewWishlist $this */
JHtml::_('behavior.framework');
JHtml::_('jquery.framework');
JHtml::_('ctech.bootstrap');

JHtml::_('script', 'com_sellacious/fe.view.sellacious.js', false, true);
JHtml::_('script', 'com_sellacious/util.cart.aio.js', false, true);
JHtml::_('script', 'com_sellacious/fe.view.wishlist.js', false, true);

JHtml::_('stylesheet', 'com_sellacious/font-awesome.min.css', null, true);
JHtml::_('stylesheet', 'com_sellacious/fe.component.css', null, true);
JHtml::_('stylesheet', 'com_sellacious/util.rating.css', null, true);
JHtml::_('stylesheet', 'com_sellacious/fe.view.wishlist.css', null, true);

$url   = JRoute::_('index.php?option=com_sellacious&view=products');
$url   = $this->helper->config->get('shop_more_redirect', $url);
?>
<div class="ctech-wrapper">
	<div class="wishlist-heading">
		<h2><span class="wishlist"><?php echo JText::_('COM_SELLACIOUS_PRODUCT_WISHLIST') ?></span>
			<span class="product-number">(<?php echo $this->get('Total'); ?>&nbsp;<?php
				echo JText::_('COM_SELLACIOUS_PRODUCT_WISHLIST_ITEMS') ?>)</span></h2>
	</div>

	<div class="wishlist-products-container">
		<?php
		$args = array('id' => 'wishlistPage', 'items' => $this->items, 'context' => array('wishlist', 'products'), 'layout' => 'grid');

		echo JLayoutHelper::render('sellacious.product.grid', $args);
		?>
	</div>
	<fieldset class="hidden" id="empty-wishlist">
		<h1><?php echo JText::_('COM_SELLACIOUS_CART_EMPTY_WISHLIST_NOTICE') ?></h1>
		<h4><?php echo JText::_('COM_SELLACIOUS_CART_EMPTY_WISHLIST_MESSAGE') ?></h4><br/>
		<a class="ctech-btn ctech-btn-primary strong no-underline" href="<?php echo $url ?>">
			<?php echo JText::_('COM_SELLACIOUS_WISHLIST_CONTINUE_SHOPPING') ?></a>
	</fieldset>

	<?php echo JHtml::_('form.token'); ?>
</div>
