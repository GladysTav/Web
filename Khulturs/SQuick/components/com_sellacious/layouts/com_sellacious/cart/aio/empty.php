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

/** @var stdClass $displayData */
$helper = SellaciousHelper::getInstance();

if (!($url = $helper->config->get('shop_more_redirect')))
{
	$url = JRoute::_('index.php?option=com_sellacious&view=products');
}
?>
<fieldset>
	<div class="text-center">
		<h1><?php echo JText::_('COM_SELLACIOUS_CART_EMPTY_CART_NOTICE') ?></h1><br/>
		<a class="ctech-btn ctech-btn-primary ctech-btn-lg strong no-underline strong cart-empty-continue-btn" href="<?php echo $url ?>">
			<?php echo JText::_('COM_SELLACIOUS_CART_CONTINUE_SHOPPING') ?></a>
	</div>
</fieldset>
