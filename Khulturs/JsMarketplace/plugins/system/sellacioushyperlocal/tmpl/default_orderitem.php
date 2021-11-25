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

$fromDateTime = JFactory::getDate($displayData['slot_from_time']);
$toDateTime   = JFactory::getDate($displayData['slot_to_time']);
$fromTime     = $fromDateTime->format('g:i A');
$toTime       = $toDateTime->format('g:i A');
?>
<br>
<?php echo JText::_('PLG_SYSTEM_SELLACIOUSHYPERLOCAL_DELIVERY_DATE'); ?>: <strong><?php echo $fromDateTime->format('d M, Y'); ?></strong>
<?php if (strtotime($fromTime) < strtotime($toTime)): ?>
	<br>
	<?php echo JText::_('PLG_SYSTEM_SELLACIOUSHYPERLOCAL_DELIVERY_SLOT'); ?>: <strong><?php echo $fromTime . ' - ' . $toTime; ?></strong>
<?php elseif (strtotime($fromTime) == strtotime($toTime)): ?>
	<br>
	<?php echo JText::_('PLG_SYSTEM_SELLACIOUSHYPERLOCAL_DELIVERY_SLOT'); ?>: <strong><?php echo $fromTime; ?></strong>
<?php endif; ?>
