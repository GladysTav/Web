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

$switcher_display    = (array) $this->helper->config->get('list_switcher_display');
$active_layout       = $this->helper->config->get('list_style', 'grid');
$list_style_switcher = $this->helper->config->get('list_style_switcher', 1);
?>
<?php if ($list_style_switcher): ?>

	<div class="layout-switcher ctech-btn-group ctech-btn-group-toggle" data-toggle="ctech-buttons">

		<?php if (count($switcher_display)):

			$active_layout = in_array($active_layout, $switcher_display) ? $active_layout : $switcher_display[0];

			if (count($switcher_display) > 1): ?>

				<?php if (in_array('masonry', $switcher_display)): ?>
					<label data-style="masonry-layout" class="ctech-btn ctech-btn-primary ctech-btn-sm switch-style <?php
						echo $active_layout == 'masonry' ? 'ctech-active' : '' ?>">
						<input type="radio" name="layout" value="masonry-layout"
							   autocomplete="off" <?php echo $active_layout == 'masonry' ? 'checked' : '' ?>>
						<i class="fa fa-indent"></i>
					</label>
				<?php endif; ?>

				<?php if (in_array('grid', $switcher_display)): ?>
					<label data-style="product-grid" class="ctech-btn ctech-btn-primary ctech-btn-sm switch-style <?php
						echo $active_layout == 'grid' ? 'ctech-active' : '' ?>">
						<input type="radio" name="layout" value="product-grid"
							   autocomplete="off" <?php echo $active_layout == 'grid' ? 'checked' : '' ?>>
						<i class="fa fa-th"></i>
					</label>
				<?php endif; ?>

				<?php if (in_array('list', $switcher_display)): ?>
					<label data-style="product-list" class="ctech-btn ctech-btn-primary ctech-btn-sm switch-style <?php
						echo $active_layout == 'list' ? 'ctech-active' : '' ?>">
						<input type="radio" name="layout" value="product-list"
							   autocomplete="off" <?php echo $active_layout == 'list' ? 'checked' : '' ?>>
						<i class="fa fa-list"></i>
					</label>
				<?php endif; ?>

			<?php else: ?>

				<?php if ($active_layout === 'masonry'): ?>
					<button data-style="masonry-layout" class="hidden switch-style active"></button>
				<?php else: ?>
					<button data-style="product-<?php echo $active_layout ?>" class="hidden switch-style active"></button>
				<?php endif; ?>

			<?php endif; ?>

		<?php else: ?>

			<label data-style="product-masonry" class="ctech-btn ctech-btn-primary ctech-btn-sm switch-style <?php
				echo $active_layout == 'masonry' ? 'ctech-active' : '' ?>">
				<input type="radio" name="layout" value="masonry-layout"
					   autocomplete="off" <?php echo $active_layout == 'masonry' ? 'checked' : '' ?>>
				<i class="fa fa-indent"></i>
			</label>

			<label data-style="product-grid" class="ctech-btn ctech-btn-primary ctech-btn-sm switch-style <?php
				echo $active_layout == 'grid' ? 'ctech-active' : '' ?>">
				<input type="radio" name="layout" value="grid-layout"
					   autocomplete="off" <?php echo $active_layout == 'grid' ? 'checked' : '' ?>>
				<i class="fa fa-th"></i>
			</label>

			<label data-style="product-list" class="ctech-btn ctech-btn-primary ctech-btn-sm switch-style <?php
				echo $active_layout == 'list' ? 'ctech-active' : '' ?>">
				<input type="radio" name="layout" value="list-layout"
					   autocomplete="off" <?php echo $active_layout == 'list' ? 'checked' : '' ?>>
				<i class="fa fa-list"></i>
			</label>

		<?php endif; ?>

	</div>

<?php else: ?>

	<?php if ($active_layout === 'masonry'): ?>
		<button data-style="masonry-layout" class="hidden switch-style active"></button>
	<?php else: ?>
		<button data-style="product-<?php echo $active_layout ?>" class="hidden switch-style active"></button>
	<?php endif; ?>

<?php endif; ?>

