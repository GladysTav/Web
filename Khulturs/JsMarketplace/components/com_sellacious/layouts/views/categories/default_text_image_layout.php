<?php
/**
 * @version     1.7.4
 * @package     sellacious
 *
 * @copyright   Copyright (C) 2012-2019 Bhartiy Web Technologies. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Izhar Aazmi <info@bhartiy.com> - http://www.bhartiy.com
 */
// no direct access
defined('_JEXEC') or die;

/** @var  stdClass $tplData */
$item = $tplData;

/** @var  SellaciousViewCategories $this */

$paths              = $this->helper->category->getImages($item->id, true);
$suffixStore        = $this->state->get('stores.id') ? '&store_id=' . $this->state->get('stores.id') : '';
$suffixManufacturer = $this->state->get('manufacturers.id') ? '&manufacturer_id=' . $this->state->get('manufacturers.id') : '';
$url                = JRoute::_('index.php?option=com_sellacious&view=categories&parent_id=' . $item->id . $suffixStore . $suffixManufacturer);

$catCols  = $this->helper->config->get('category_cols', 4);
$categoryClassName = '';
if ($catCols == '4')
{
	$categoryClassName = 'sell-col-md-3 sell-col-sm-4 sell-col-xs-6';
}
elseif ($catCols == '3')
{
	$categoryClassName = 'sell-col-sm-4 sell-col-xs-6';
}
elseif ($catCols == '2')
{
	$categoryClassName = 'sell-col-xs-6';
}
elseif ($catCols == 'auto')
{
	$categoryClassName = 'auto-adjust';
}

?>
<div class="layout-one category-cols <?php echo $categoryClassName; ?>">
	<div data-rollover="container">
		<div class="image align-left-image">
			<a href="<?php echo $url ?>">
				<span class="cat-img bgrollover" style="background-image:url('<?php echo reset($paths) ?>');"
					  data-rollover="<?php echo htmlspecialchars(json_encode($paths)); ?>"></span>
			</a>
		</div>

		<div class="desc-product">
			<a href="<?php echo $url ?>">
				<h6 class="title"><?php echo $item->title; ?></h6>

				<?php if (isset($item->product_count) || isset($item->subcat_count)): ?>
					<div class="item-counts-strip">
						<?php if ($item->product_count > 0): ?>
							<div class="tip-product">
								<?php echo JText::plural('COM_SELLACIOUS_CATEGORY_PRODUCT_COUNT', $item->product_count); ?>
							</div>
						<?php endif; ?>
						<?php if ($item->subcat_count > 0): ?>
							<div class="tip-category">
								<?php echo JText::plural('TEMP_COM_SELLACIOUS_CATEGORY_SUBCATEGORIES_COUNT_N', $item->subcat_count); ?>
							</div>
						<?php endif; ?>
					</div>
				<?php endif; ?>
			</a>
		</div>


	</div>
</div>
