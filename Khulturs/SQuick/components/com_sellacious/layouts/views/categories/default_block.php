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

/** @var  stdClass $tplData */
$item = $tplData;

/** @var  SellaciousViewCategories $this */

$paths              = $this->helper->category->getImages($item->id, true);
$subcat_zero        = $this->helper->config->get('show_subcat_with_zero_products');
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
<?php if ($subcat_zero == '1' || isset($item->product_count)): ?>
	<div class="category-cols <?php echo $categoryClassName; ?>">
		<div class="category-box" data-rollover="container">
			<a href="<?php echo $url ?>">
				<h6><?php echo $item->title; ?></h6>
				<div class="image-box">
				<span class="product-img bgrollover" style="background-image:url('<?php echo reset($paths) ?>');"
					  data-rollover="<?php echo htmlspecialchars(json_encode($paths)); ?>"></span>
				</div>
			</a>
			<?php if ($item->product_count > 0 || (isset($item->subcat_count)) && $item->subcat_count > 0) : ?>
				<div class="item-counts-strip">
					<div class="tip-left">
						<?php
						if ($item->product_count > 0) :
							echo JText::plural('COM_SELLACIOUS_CATEGORY_PRODUCT_COUNT_N', $item->product_count);
						endif;
						?>
					</div>
					<div class="tip-right">
						<?php
						if (isset($item->subcat_count)) :
							echo JText::plural('COM_SELLACIOUS_CATEGORY_SUBCATEGORIES_COUNT_N', $item->subcat_count);
						endif;
						?>
					</div>
				</div>
			<?php endif; ?>
		</div>
	</div>
<?php endif; ?>
