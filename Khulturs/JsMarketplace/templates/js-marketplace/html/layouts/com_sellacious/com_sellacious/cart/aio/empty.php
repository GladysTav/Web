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

/** @var stdClass $displayData */
$helper = SellaciousHelper::getInstance();

if (!($url = $helper->config->get('shop_more_redirect')))
{
	$url = JRoute::_('index.php?option=com_sellacious&view=products');
}
?>


<style>
    .not-found h1  {
        font-size: 40px;
    }
    .not-found {
        margin-top: 50px;
    }

</style>


<fieldset>
	<div class="text-center not-found">
		<h1><?php echo JText::_('COM_SELLACIOUS_CART_EMPTY_CART_NOTICE') ?></h1><br/>
		<a class="btn btn-primary btn-large strong no-underline strong" href="<?php echo $url ?>">
			<?php echo JText::_('COM_SELLACIOUS_CART_CONTINUE_SHOPPING') ?></a>
	</div>
</fieldset>
