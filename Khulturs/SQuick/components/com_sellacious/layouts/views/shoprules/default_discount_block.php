<?php
/**
 * @version     2.0.0
 * @package     sellacious
 *
 * @copyright   Copyright (C) 2012-2020 Bhartiy Web Technologies. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Aditya Chakraborty <info@bhartiy.com> - http://www.bhartiy.com
 */
// no direct access
defined('_JEXEC') or die;

/** @var SellaciousViewShoprules $this */
$db       = JFactory::getDbo();
$now      = JFactory::getDate();
$nullDate = $db->getNullDate();

/** @var  stdClass $tplData */
$item = $tplData;

$start    = JFactory::getDate($item->publish_up);
$expiry   = JFactory::getDate($item->publish_down);
$expired  = ($item->publish_down != $nullDate && $expiry < $now);
$expiring = $expiry->diff($now);

$amountClass = '';

if ($start > $now)
{
	$amountClass = 'ctech-text-warning';
}
elseif ($expired || $expiring->d == 0)
{
	$amountClass = 'ctech-text-danger';
}
?>
<div class="shoprule-wrap <?php echo $expired ? 'expired' : ''; ?>">
	<?php echo $this->loadTemplate('modal', $item); ?>
	<div class="shoprule-box <?php echo $expired ? 'ctech-border-danger' : ''; ?>">
		<div class="amount-box">
			<?php if ($expired):
				$expiredDate = JText::sprintf('COM_SELLACIOUS_SHOPRULE_ITEM_EXPIRED_DATE', $this->helper->core->relativeDateTime($item->publish_down)); ?>
				<div class="expired_badge ctech-badge-danger hasTooltip" title="<?php echo $expiredDate; ?>">
					<?php echo JText::_('COM_SELLACIOUS_SHOPRULE_ITEM_EXPIRED'); ?>
				</div>
			<?php elseif ($start > $now):
				// Not available yet
				$startDate = JText::sprintf('COM_SELLACIOUS_SHOPRULE_ITEM_AVAILABLE_DATE', $this->helper->core->relativeDateTime($item->publish_up)); ?>
				<div class="time-badge ctech-text-warning">
					<i class="fa fa-hourglass-start"></i>
					<span><?php echo $startDate; ?></span>
				</div>
			<?php elseif ($expiring->d > 0 && $item->publish_down != $nullDate):
				// Expiring date
				$expiringDate = JText::sprintf('COM_SELLACIOUS_SHOPRULE_ITEM_EXPIRING_DATE', $this->helper->core->relativeDateTime($item->publish_down)); ?>
				<div class="time-badge">
					<i class="fa fa-hourglass-half"></i>
					<span><?php echo $expiringDate; ?></span>
				</div>
			<?php elseif ($expiring->d == 0):
				// Expiring today
				$expiringToday = $expiring->h > 0 ? sprintf("%02s hours", $expiring->h) : sprintf("%02s minutes", $expiring->i); ?>
				<div class="time-badge ctech-text-danger">
					<i class="fa fa-hourglass-end"></i>
					<span><?php echo JText::sprintf('COM_SELLACIOUS_SHOPRULE_ITEM_EXPIRING_TODAY', $expiringToday); ?></span>
				</div>
			<?php endif; ?>
			<div class="amount <?php echo $amountClass; ?>">
				<span><?php echo $item->amount; ?></span>
				<div class="disc-off">
					<small><?php echo JText::_('COM_SELLACIOUS_SHOPRULE_ITEM_DISCOUNT_OFF'); ?></small>
				</div>
			</div>
		</div>
		<div class="shoprule-info-box">
			<div class="shoprule-title">
				<?php echo $item->title; ?>
			</div>
			<div class="shoprule-info-footer">
				<?php if ($item->terms): ?>
					<a href="#" data-shoprule-id="<?php echo $item->id; ?>" class="ctech-text-warning terms-modal">
						<sup>*</sup>
						<?php echo JText::_('COM_SELLACIOUS_SHOPRULE_ITEM_CONDITIONS_APPLY'); ?>
					</a>
				<?php else: ?>
					<span class="ctech-text-warning">
						<sup>*</sup>
						<?php echo JText::_('COM_SELLACIOUS_SHOPRULE_ITEM_CONDITIONS_APPLY'); ?>
					</span>
				<?php endif; ?>
			</div>
			<div class="clearfix"></div>
		</div>
		<div class="rule_applied ctech-badge-info"><?php echo $item->rule_applied; ?></div>
	</div>
</div>
