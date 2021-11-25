<?php
/**
 * @version     2.0.0
 * @package     sellacious
 *
 * @copyright   Copyright (C) 2012-2020 Bhartiy Web Technologies. All rights reserved.
 * @license     SPL Sellacious Private License; see http://www.sellacious.com/spl.html
 * @author      Izhar Aazmi <info@bhartiy.com> - http://www.bhartiy.com
 */
// no direct access
defined('_JEXEC') or die;

/** @var  array  $displayData */
extract($displayData);
?>
<div class="sff-sort-options-container row" id="<?php echo $id ?>">

	<?php foreach ($columns as $column): ?>

		<div data-column="<?php echo $column->name ?>" class="col-md-4">

			<p class="strong text-center"><?php echo JText::_($column->label); ?></p>

			<ul id="<?php echo $id ?>-<?php echo $column->name ?>"
			    class="sff-sort-options-sortable" data-sort-options-group="<?php echo $id ?>">
				<?php foreach ($column->options as $option): ?>
					<li rel="<?php echo $option->value ?>">
						<i class="fa fa-arrows"></i> &nbsp;|&nbsp; <?php echo JText::_($option->text); ?>
					</li>
				<?php endforeach; ?>
			</ul>

			<input type="hidden" name="<?php echo $name ?>[<?php echo $column->name ?>]"
			       class="sff-sort-options-input" value="<?php ?>">

		</div>

	<?php endforeach; ?>

</div>
