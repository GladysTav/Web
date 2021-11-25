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
		$code    = $registry->get('code');
		$options = array(
			'backdrop' => 'static',
			'keyboard' => true,
			'url'      => 'index.php?option=com_sellacious&view=product&p=' . $code . '&layout=query&tmpl=component',
		);

		echo JHtml::_('ctechBootstrap.modal', 'query-form-' . strtolower($code), JText::sprintf('COM_SELLACIOUS_PRODUCT_PRICING_TYPE_QUERYFORM_OPEN_QUERY_FORM_FOR', $registry->get('title'), $registry->get('variant_title')), '', '', $options);
		?>
			<div class="ctech-wrapper">
				<div class="querysend">
					<a href="#query-form-<?php echo strtolower($code) ?>"
						role="button" data-toggle="ctech-modal" class="ctech-btn ctech-btn-primary ctech-btn-sm"><i class="fa fa-file-text"></i>
							<?php echo JText::_('COM_SELLACIOUS_PRODUCT_PRICING_TYPE_QUERYFORM_OPEN_QUERY_FORM') ?></a>
				</div>
			</div>
		<?php
	}
	else
	{
		$currentUrl = JUri::getInstance()->toString();
		$loginUrl   = JRoute::_('index.php?option=com_users&view=login&return=' . base64_encode($currentUrl), false);

		?><a class="login-title-text" href="<?php echo $loginUrl ?>"><?php
			echo JText::_('COM_SELLACIOUS_PRODUCT_PRICING_LOGIN_TO_VIEW'); ?></a><?php
	}
}
