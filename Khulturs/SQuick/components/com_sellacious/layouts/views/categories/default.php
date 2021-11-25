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

// @var  SellaciousViewCategories  $this */
JHtml::_('jquery.framework');
JHtml::_('ctech.bootstrap');

JHtml::_('script', 'com_sellacious/fe.view.sellacious.js', false, true);

JHtml::_('stylesheet', 'com_sellacious/fe.component.css', null, true);
JHtml::_('stylesheet', 'com_sellacious/fe.view.categories.css', null, true);

$catLayout = $this->helper->config->get('select_category_layout');
?>
<div class="ctech-wrapper">
	<?php
	echo $this->loadTemplate('head');

	if (!empty($this->current->id) && !empty($this->items)): ?>
		<div class="categories_innerheading">
			<h3 id="subcat-heading"><?php echo JText::_('COM_SELLACIOUS_CATEGORY_SUB_CATEGORIES'); ?></h3>
			<div class="clearfix"></div>
		</div>
	<?php endif; ?>

	<div class="sell-cols-row">
		<?php
		$opts = array(
			'items'   => $this->items,
			'id'      => 'categoriesList',
			'context' => array('categories'),
			'layout'  => 'grid'
		);

		echo JLayoutHelper::render('sellacious.categories.default', $opts);
		?>
	</div>
	<div class="clearfix"></div>

	<?php
	/** @var JPagination $pagination */
	$pagination = $this->pagination;
	if ($pagination->total > $pagination->limit):
		?>
		<div class="pagination-footer ctech-row">
			<div class="ctech-col-12 ctech-col-sm-6 center-xs">
				<div class="ctech-pagination sell-pagination"><?php echo $pagination->getPagesLinks(); ?></div>
			</div>
			<div class="ctech-col-12 ctech-col-sm-6 xs-right center-xs">
				<div class="pagecounter"><?php echo $pagination->getPagesCounter(); ?></div>
			</div>
		</div>
	<?php
	endif;

	if (count($this->products))
	{
		?>
		<div class="categories_innerheading">
			<h3 id="products-heading">
				<?php echo JText::_('COM_SELLACIOUS_CATEGORY_PRODUCTS'); ?>
				<?php
				$catId      = $this->state->get('categories.id', 1);
				$showAll    = $this->helper->config->get('category_page_view_all_products', 1);
				$viewAllUrl = JRoute::_('index.php?option=com_sellacious&view=products&category_id=' . $catId);
				?>
				<?php if ($showAll): ?>
					<div class="view-button-area ctech-text-right">
						<a href="<?php echo $viewAllUrl ?>" class="ctech-btn ctech-btn-primary" class="view-all-products-link">
							<?php echo JText::_('COM_SELLACIOUS_PRODUCT_VIEW_ALL_PRODUCTS'); ?> &nbsp;
							<?php echo ($this->document->direction === 'ltr') ? '<i class="fa fa-chevron-right"></i>' : '<i class="fa fa-chevron-left"></i>'; ?>
						</a>
					</div>
				<?php endif; ?>
			</h3>
		</div>
		<?php echo $this->loadTemplate('products');
	}
	?>
</div>
