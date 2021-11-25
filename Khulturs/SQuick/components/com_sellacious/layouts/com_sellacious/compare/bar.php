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

/** @var   JLayoutFile $this */
/** @var   \Sellacious\Product[] $displayData */
$items = $displayData;
$codes = array();
?>
<div class="ctech-wrapper">
<?php
if (is_array($items) && array_filter($items))
{
	$count = 0;

	foreach ($items as $item)
	{
		if (is_object($item))
		{
			$count++;
		}
	}
	?>
	<div class="w100p">
		<div id="compare-bar-toggle" class="ctech-bg-primary">
			<i class="fa fa-balance-scale"></i> <span class="ctech-badge ctech-badge-dark compare-count"><?php echo $count; ?></span>
		</div>
		<div class="compare-backdrop">
			<div id="compare-bar-items">
			<div class="compare-bar-header">
				<h4>
					<i class="fa fa-balance-scale"></i> <?php echo JText::_('COM_SELLACIOUS_PRODUCT_COMPARE_LABEL'); ?>
					<a href="#" id="close-compare-bar" class="ctech-text-danger ctech-float-right"><i class="fa fa-times"></i></a></h4>
			</div>
			<div class="clearfix"></div>
			<div class="tbl-compare">
				<?php foreach ($items as $item) : ?>
					<?php
					if (is_object($item))
					{
						$layoutId = 'bar_item';
						$codes[]  = $item->getCode();
					}
					else
					{
						$layoutId = 'bar_noitem';
					}
					?>
					<div class="<?php echo $layoutId ?>"><?php echo $this->sublayout($layoutId, $item); ?></div>
				<?php endforeach; ?>
				<div class="compare-submit"><?php
					if (count($items) >= 2):
						?><a class="ctech-btn ctech-btn-success ctech-btn-block" href="<?php
					echo JRoute::_('index.php?option=com_sellacious&view=compare&c=' . implode(',', $codes)); ?>"><?php echo strtoupper(JText::_('COM_SELLACIOUS_PRODUCT_COMPARE')); ?></a><?php
					else:
						?><a href="#" class="ctech-btn ctech-btn-success ctech-btn-block disabled"><?php echo strtoupper(JText::_('COM_SELLACIOUS_PRODUCT_COMPARE')); ?></a><?php
					endif;
					?>
				</div>
			</div>
			<div class="ctech-clearfix"></div>
		</div>
		</div>
	</div>
	<?php
}
else
{
	echo '<div class="hidden w100p"></div>';
}
?>
</div>
