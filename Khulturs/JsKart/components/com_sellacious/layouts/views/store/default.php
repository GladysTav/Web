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
JHtml::_('script', 'com_sellacious/fe.view.store.js', false, true);

JHtml::_('stylesheet', 'com_sellacious/fe.component.css', null, true);
JHtml::_('stylesheet', 'com_sellacious/fe.view.store.css', null, true);
JHtml::_('stylesheet', 'com_sellacious/fe.view.products.list.css', null, true);

JText::script('COM_SELLACIOUS_USER_FAVORITE_STORE_LOGIN_FIRST');
?>
<div class="ctech-wrapper">
	<div class="store-wrapper">
		<?php
		echo $this->loadTemplate('header');

		if ($this->helper->config->get('show_store_reviews') == '1')
		{
			echo $this->loadTemplate('reviews');
		}

		echo $this->loadTemplate('applied_filters'); ?>

		<div class="ctech-container-fluid">
			<div class="ctech-row">
				<?php if ($this->helper->config->get('show_store_filters', 1) && $this->helper->config->get('filters_position_store_page', 'left') == 'left'):
					echo $this->loadTemplate('filters');
				endif; ?>

				<div class="<?php echo !$this->helper->config->get('show_store_filters', 1) ? 'ctech-col-lg-12' : 'ctech-col-lg-9'; ?>">
					<form action="<?php echo JUri::getInstance()->toString(); ?>" method="post" name="adminForm" id="adminForm">
						<?php
						echo $this->loadTemplate('bar');
						echo $this->loadTemplate('switcher');
						?>
						<div class="clearfix"></div>

						<div id="products-page" class="w100p">
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
						<div class="clearfix"></div>

						<div class="left ctech-pagination">
							<?php echo $this->pagination->getPaginationLinks('joomla.pagination.links', array('showLimitBox' => false)); ?>
						</div>

						<?php echo JHtml::_('form.token'); ?>
					</form>
				</div>

				<?php if ($this->helper->config->get('show_store_filters', 1) && $this->helper->config->get('filters_position_store_page', 'left') == 'right'):
					echo $this->loadTemplate('filters');
				endif; ?>
			</div>
		</div>
	</div>
</div>
