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

$start   = JFactory::getDate($item->publish_up);
$expiry  = JFactory::getDate($item->publish_down);
$expired = ($item->publish_down != $nullDate && $expiry < $now);

$amountClass = '';

if ($start > $now)
{
	$amountClass = 'ctech-text-warning';
}
elseif ($expired)
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
				$startDate = JText::sprintf('COM_SELLACIOUS_SHOPRULE_ITEM_DISCOUNT_AVAILABLE_DATE', $this->helper->core->relativeDateTime($item->publish_up)); ?>
				<div class="time-badge ctech-text-warning">
					<i class="fa fa-hourglass-start"></i>
					<span><?php echo $startDate; ?></span>
				</div>
			<?php endif; ?>
			<div class="amount <?php echo $amountClass; ?>">
				<span><?php echo '+' . $item->amount; ?></span>
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
