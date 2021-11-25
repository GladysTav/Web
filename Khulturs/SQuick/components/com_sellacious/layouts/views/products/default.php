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

/** @var  SellaciousViewProducts  $this */
JHtml::_('behavior.framework');
JHtml::_('jquery.framework');
JHtml::_('ctech.bootstrap');

JHtml::_('stylesheet', 'com_sellacious/font-awesome.min.css', null, true);

JHtml::_('script', 'com_sellacious/util.cart.aio.js', false, true);
JHtml::_('script', 'com_sellacious/fe.view.sellacious.js', false, true);
JHtml::_('script', 'com_sellacious/isotope.pkgd.min.js', false, true);
JHtml::_('script', 'com_sellacious/fe.view.products.js', false, true);

JHtml::_('stylesheet', 'com_sellacious/fe.component.css', null, true);
JHtml::_('stylesheet', 'com_sellacious/fe.view.products.css', null, true);
JHtml::_('stylesheet', 'com_sellacious/fe.view.products.list.css', null, true);

$imgH = (int) $this->helper->config->get('products_image_height', 220);
$imgS = $this->helper->config->get('products_image_size_adjust') ?: 'contain';

$this->document->addStyleDeclaration(<<<CSS
	.product-box .image-box .product-img {
	    height: {$imgH}px;
	    background-size: {$imgS};
	}
	@media (max-width: 767px) {
		.list-layout .product-box .image-box .product-img {
		    height: {$imgH}px;
		}
	}
CSS
);
?>
<div class="ctech-wrapper">

	<?php
	echo $this->loadTemplate('banner');

	echo $this->loadTemplate('filters');

	echo $this->loadTemplate('switcher');
	?>

	<form action="<?php echo JUri::getInstance()->toString(); ?>" method="post" name="adminForm" id="adminForm">

		<?php echo $this->loadTemplate('bar'); ?>

		<div class="clearfix"></div>

		<div id="products-page" class="w100p">

			<div id="products-box" class="sell-cols-row">
				<?php
				if (count($this->items) == 0)
				{
					?><h4><?php echo JText::_('COM_SELLACIOUS_PRODUCT_NO_MATCH_FILTER') ?></h4><?php
				}

				$args = array('id' => 'productsList', 'items' => $this->items, 'context' => array('products'), 'layout' => 'grid');

				echo JLayoutHelper::render('sellacious.product.grid', $args);
				?>
				<div class="clearfix"></div>
			</div>

		</div>

		<div class="clearfix"></div>

		<div class="left ctech-pagination">
			<?php echo $this->pagination->getPaginationLinks('joomla.pagination.links', array('showLimitBox' => false)); ?>
		</div>

		<?php echo JHtml::_('form.token'); ?>

	</form>

</div>
