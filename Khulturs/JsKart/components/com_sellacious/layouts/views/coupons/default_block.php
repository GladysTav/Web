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

/** @var SellaciousViewCoupons $this */
$db       = JFactory::getDbo();
$now      = JFactory::getDate();
$nullDate = $db->getNullDate();

/** @var  stdClass $tplData */
$item       = $tplData;
$s_currency = $this->helper->currency->forSeller($item->seller_uid, 'code_3');

if (substr($item->discount_amount, -1) == '%')
{
	$discount = $item->discount_amount;
}
else
{
	$discount = $this->helper->currency->display($item->discount_amount, $s_currency, null, true);
}

$expiry    = JFactory::getDate($item->publish_down);
$expired   = ($item->publish_down != $nullDate && $expiry < $now);
$codeClass = $expired ? 'code' : 'code active-code';
$cardClass = $expired ? 'card ctech-mb-3 w100p expired' : 'card ctech-mb-3 w100p';

JText::script('COM_SELLACIOUS_COUPON_SHOW_COUPON_DESCRIPTION');
JText::script('COM_SELLACIOUS_COUPON_HIDE_COUPON_DESCRIPTION');
?>
<div class="<?php echo $cardClass; ?>">
	<div class="ctech-row ctech-no-gutters">
		<div class="ctech-col-md-4">
			<div class="coupon-code ctech-list-group-item-secondary">
				<div data-text="<?php echo $item->coupon_code; ?>" class="<?php echo $codeClass; ?>" id="coupon-code-<?php echo $item->id; ?>"><?php echo $item->coupon_code; ?></div>
			</div>
		</div>
		<div class="ctech-col-md-8">
			<div class="card-body">
				<div class="ctech-row coupon-detail">
					<div class="<?php echo $expired ? 'ctech-col-8' : 'ctech-col-12'; ?> card-detail">
						<h5 class="card-title"><?php echo $item->title; ?></h5>
						<div class="card-text">
							<?php echo JText::sprintf('COM_SELLACIOUS_COUPON_ITEM_DISCOUNT', $discount); ?>
						</div>
					</div>

					<?php if ($expired): ?>
						<div class="ctech-col-4 coupon-status">
							<?php echo JText::_('COM_SELLACIOUS_COUPON_ITEM_STATUS_EXPIRED'); ?>
						</div>
					<?php endif; ?>
				</div>
				<div class="ctech-clearfix"></div>
				<p class="card-text">
					<?php if ($item->publish_up != $nullDate): ?>
						<small class="text-muted ctech-float-left ctech-text-primary">
							<i class="fa fa-clock-o"></i>
							<?php echo JText::sprintf('COM_SELLACIOUS_COUPON_ITEM_AVAILABLE_DATE', $this->helper->core->relativeDateTime($item->publish_up)); ?>
						</small>
					<?php endif;?>
					<?php if ($item->publish_down != $nullDate): ?>
						<small class="text-muted ctech-float-right ctech-text-danger">
							<i class="fa fa-clock-o"></i>
							<?php if ($expiry < $now):
								echo JText::sprintf('COM_SELLACIOUS_COUPON_ITEM_EXPIRED_DATE', $this->helper->core->relativeDateTime($item->publish_down));
							else:
								echo JText::sprintf('COM_SELLACIOUS_COUPON_ITEM_EXPIRING_DATE', $this->helper->core->relativeDateTime($item->publish_down));
							endif; ?>
						</small>
					<?php endif;?>
				</p>
			</div>
		</div>
	</div>
	<?php if ($item->description): ?>
		<div class="coupon-description-container">
			<div class="coupon-desc-heading ctech-border-top ctech-border-secondary">
				<a class="toggle-description ctech-text-dark">
					<span class="coupon-description-text"><?php echo JText::_('COM_SELLACIOUS_COUPON_SHOW_COUPON_DESCRIPTION') ?></span>
					<span class="coupon-description-text" style="display: none"><?php echo JText::_('COM_SELLACIOUS_COUPON_HIDE_COUPON_DESCRIPTION') ?></span>
				</a>
			</div>
			<div class="coupon-description" style="display: none">
				<?php echo $item->description ?>
			</div>
		</div>
	<?php endif;?>
</div>
