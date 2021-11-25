<?php
/**
 * @version     1.7.4
 * @package     sellacious
 *
 * @copyright   Copyright (C) 2012-2019 Bhartiy Web Technologies. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Aditya Chakraborty <info@bhartiy.com> - http://www.bhartiy.com
 */
// no direct access
defined('_JEXEC') or die;

/** @var  \Sellacious\Cart\Item  $item */
$options       = $displayData['options'];
$params        = $displayData['params'];
$deliveryDate  = null;
$deliverySlot  = array();
$timeSelection = $params->get('delivery_time_selection', 0);

if (isset($options->delivery_date) && !empty($options->delivery_date))
{
	if ($timeSelection == 3)
	{
		$deliveryDate = str_replace(array('AM', 'PM'), '', $options->delivery_date);
		$deliveryDate = JFactory::getDate($deliveryDate)->format('d M, Y g:i A');
	}
	else
	{
		$deliveryDate = JFactory::getDate($options->delivery_date)->format('d M, Y');
	}
}

if (isset($options->delivery_slot) && !empty($options->delivery_slot))
{
	$deliverySlot = explode(' - ', $options->delivery_slot);

	foreach ($deliverySlot as &$item)
	{
		$time = JFactory::getDate($item);
		$item = $time->format('g:i A');
	}
}
?>
<?php if (!empty($deliveryDate)): ?>
	<?php echo JText::_('PLG_SYSTEM_SELLACIOUSHYPERLOCAL_DELIVERY_DATE'); ?>: <strong><?php echo $deliveryDate; ?></strong>
<?php endif;

if (!empty($deliverySlot)): ?>
	<br>
	<?php echo JText::_('PLG_SYSTEM_SELLACIOUSHYPERLOCAL_DELIVERY_SLOT'); ?>: <strong><?php echo implode(' - ' , array_unique($deliverySlot)); ?></strong>
<?php endif; ?>
