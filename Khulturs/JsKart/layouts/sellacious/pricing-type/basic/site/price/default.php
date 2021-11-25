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
	if ($reqLogin && $me->guest)
	{
		$currentUrl = JUri::getInstance()->toString();
		$loginUrl   = JRoute::_('index.php?option=com_users&view=login&return=' . base64_encode($currentUrl), false);

		?><a class="login-title-text" href="<?php echo $loginUrl ?>"><?php
		echo JText::_('COM_SELLACIOUS_PRODUCT_PRICING_LOGIN_TO_VIEW'); ?></a><?php
	}
	else
	{
		$price_display = $helper->config->get('product_price_display');
		?>
		<span class="product-price ctech-text-primary">
			<?php
			if (round($registry->get('price.sales_price'), 2) >= 0.01)
			{
				$c_currency = $helper->currency->current('code_3');
				$s_currency = $helper->currency->forSeller($registry->get('seller_uid'), 'code_3');

				echo $helper->currency->display($registry->get('price.sales_price'), $s_currency, $c_currency, true);
			}
			else
			{
				echo JText::_('COM_SELLACIOUS_PRODUCT_PRICE_FREE');
			}
			?>
		</span>
		<span class="product-price-sellingPrice">
			<?php if ($price_display == 2 && $registry->get('price.list_price') > 0): ?>
				<?php echo JText::_('COM_SELLACIOUS_PRODUCT_SELLING_PRICE_LABEL'); ?>
				<strong><del><?php echo $helper->currency->display($registry->get('price.list_price'), $s_currency, $c_currency, true) ?></del></strong>
			<?php endif; ?>
		</span>
		<?php
	}
}
