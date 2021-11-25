<?php
/**
 * @version     2.0.0
 * @package     sellacious
 *
 * @copyright   Copyright (C) 2012-2020 Bhartiy Web Technologies. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Saurabh Sabharwal <info@bhartiy.com> - http://www.bhartiy.com
 */
// no direct access
defined('_JEXEC') or die;

/** @var SellaciousViewProduct $this */

$me           = JFactory::getUser();
$code         = $this->state->get('product.code');

if ($this->helper->config->get('product_wishlist')):
	?>
	<div class="product-wishlist-container ctech-float-right <?php echo $this->helper->wishlist->check($code, null) ? 'ctech-text-danger ctech-border-danger' : 'ctech-text-primary ctech-border-primary' ?> ctech-rounded-circle">
		<?php
		if ($me->guest):
			$url   = JRoute::_('index.php?option=com_sellacious&view=product&p=' . $code, false);
			$login = JRoute::_('index.php?option=com_users&view=login&return=' . base64_encode($url), false); ?>
		<a  class="btn-wishlist btn-wishlist-notLoggedIn" data-guest="true" data-href="<?php echo $this->escape($login) ?>">
				<i class="far fa-heart"></i>
			</a><?php
		elseif ($this->helper->wishlist->check($code, null)):
			$url = JRoute::_('index.php?option=com_sellacious&view=wishlist', false); ?>
		<a class="btn-wishlist btn-wishlist-loggedIn" data-href="<?php echo $this->escape($url) ?>">
				<i class="fa fa-heart"></i>
			</a><?php
		else: ?>
		<a class="btn-wishlist" data-item="<?php echo $code ?>">
				<i class="far fa-heart"></i>
			</a><?php
		endif;
		?>
	</div>
<?php endif; ?>
