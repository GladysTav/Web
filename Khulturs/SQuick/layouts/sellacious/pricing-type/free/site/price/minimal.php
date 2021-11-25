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

use Joomla\Registry\Registry;

/** @var  Registry $displayData */
$registry  = $displayData;
$helper    = SellaciousHelper::getInstance();
$me        = JFactory::getUser();
$reqLogin  = $helper->config->get('login_to_see_price');
$showPrice = $helper->config->inList('product', 'product_price_display_pages');

if ($showPrice)
{
    if (!$reqLogin || !$me->guest)
	{
		?>
		<span class="product-price ctech-text-primary">
			<?php echo JText::_('COM_SELLACIOUS_PRODUCT_PRICE_FREE'); ?>
		</span>
		<?php
	}
	else
	{
		$currentUrl = JUri::getInstance()->toString();
		$loginUrl   = JRoute::_('index.php?option=com_users&view=login&return=' . base64_encode($currentUrl), false);

		?><a class="login-to-view-price" href="<?php echo $loginUrl ?>"><?php
			echo JText::_('COM_SELLACIOUS_PRODUCT_PRICING_LOGIN_TO_VIEW'); ?></a><?php
	}
}
