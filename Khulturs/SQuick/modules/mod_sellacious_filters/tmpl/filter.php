<?php
/**
 * @version     2.0.0
 * @package     Sellacious Filters Module
 *
 * @copyright   Copyright (C) 2012-2020 Bhartiy Web Technologies. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Izhar Aazmi <info@bhartiy.com> - http://www.bhartiy.com
 */
// no direct access
defined('_JEXEC') or die;

JHtml::_('script', 'mod_sellacious_filters/filters.js', false, true);
JHtml::_('stylesheet', 'mod_sellacious_filters/filters.css', null, true);

/** @var  JApplicationCms   $app */
/** @var  string            $class_sfx */
/** @var  bool              $submitBtn */
/** @var  string            $html */

$view = $app->input->get('view');
$id   = $app->input->getInt('id');
$url  = JUri::getInstance()->toString(array('path', 'query', 'fragment'));
?>
<div class="ctech-wrapper">
	<div class="mod-sellacious-filters w100p closed-on-phone toolbar-push-down <?php echo $class_sfx; ?>">
		<div class="sellacious-filters-wrapper">
			<div class="filter-head">
				<div class="close-filters">
					<a id="filters-close" class="ctech-text-dark" href="#"><i class="fa fa-times"></i></a>
				</div>
			</div>
			<form method="post" action="<?php echo $url; ?>">

				<div class="btn-main text-right">
					<button type="button" class="btn-clear-filter ctech-btn ctech-btn-dark ctech-btn-sm" data-redirect="<?php echo $url ?>">
						<?php echo JText::_('MOD_SELLACIOUS_FILTERS_BTN_CLEAR_FILTER'); ?>
						<i class='fa fa-times'></i></button>
					<?php if ($submitBtn): ?>
						<button class="ctech-btn ctech-btn-primary ctech-btn-sm" type="submit">
							<?php echo JText::_('MOD_SELLACIOUS_FILTERS_BTN_APPLY_FILTER'); ?></button>
					<?php endif; ?>
				</div>

				<?php echo $html; ?>

				<?php if ($view == 'stores' && $id): ?>

					<input type="hidden" name="id" value="<?php echo $id ?>"/>

				<?php endif; ?>

				<input type="hidden" name="layout" value="<?php echo $app->input->get('layout'); ?>"/>
				<input type="hidden" name="tmpl" value="<?php echo $app->input->get('tmpl', 'index'); ?>"/>

				<?php echo JHtml::_('form.token'); ?>
				<div class="filter-action-buttons ctech-w-100">
					<button class="btn-clear-filter ctech-btn ctech-btn-dark"
							data-redirect="<?php echo JRoute::_('index.php?option=com_sellacious&view=products', false) ?>">
						<?php echo JText::_('MOD_SELLACIOUS_FILTERS_BTN_CLEAR_FILTER'); ?> <i class='fa fa-times'></i></button>
					<button class="ctech-btn ctech-btn-primary" onclick="this.form.submit()"><?php echo JText::_('GO') ?></button>
				</div>
			</form>
		</div>
	</div>
</div>
