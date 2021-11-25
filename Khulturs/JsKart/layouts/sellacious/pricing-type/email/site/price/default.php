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
$registry = $displayData;
$helper   = SellaciousHelper::getInstance();
$me       = JFactory::getUser();
$reqLogin = $helper->config->get('login_to_see_price');

JHtml::_('script', 'com_sellacious/util.toggle-button-content.js', array('relative' => true, 'version' => S_VERSION_CORE));

if ($reqLogin && $me->guest)
{
	$currentUrl = JUri::getInstance()->toString();
	$loginUrl   = JRoute::_('index.php?option=com_users&view=login&return=' . base64_encode($currentUrl), false);

	?><a class="login-title-text" href="<?php echo $loginUrl ?>"><?php
			echo JText::_('COM_SELLACIOUS_PRODUCT_PRICING_LOGIN_TO_VIEW'); ?></a><?php
}
else
{
	?>
	<div class="email-envelope">
	<a href="#" class="ctech-btn ctech-btn-sm ctech-btn-primary ctech-rounded toggle-button-content">
		<span class="toggle-title"><i class="fa fa-envelope"></i> <?php echo JText::_('COM_SELLACIOUS_PRODUCT_PRICING_TYPE_EMAIL_EMAIL_US'); ?></span>

		<?php
		$security = $helper->config->get('contact_spam_protection');
		$email    = $registry->get('seller_email') ?: JText::_('COM_SELLACIOUS_PRODUCT_PRICING_TYPE_EMAIL_NO_EMAIL');

		if ($security)
		{
			?><span class="toggle-subtitle ctech-d-none"><img src="data:image/png;base64,<?php
				echo $helper->media->writeText($email, 4, true); ?>" alt=""/></span><?php
		}
		else
		{
			?>
			<span class="toggle-subtitle ctech-d-none hasTooltip" title="<?php echo $email; ?>"><?php echo $email; ?></span>
			<?php
		}
		?>
	</a></div>
	<?php
}
