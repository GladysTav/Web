<?php
/**
 * @version     2.0.0
 * @package     Sellacious Filters Module
 *
 * @copyright   Copyright (C) 2012-2020 Bhartiy Web Technologies. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Bhavika Matariya <info@bhartiy.com> - http://www.bhartiy.com
 */
// no direct access
defined('_JEXEC') or die;

/** @var  array  $displayData */
extract($displayData);

/** @var  stdClass[]  $fields */
?>
<?php foreach ($fields as $field): ?>
	<?php if (count($field->choices)): ?>
		<div class="filter-snap-in">
			<div class="filter-title">
				<div title="<?php echo htmlspecialchars($field->title) ?>">
					<span><i class="fa fa-caret-right fa-lg unfold"></i>
						<i class="fa fa-caret-down fa-lg fold"></i>
					</span>
					<?php echo $field->title ?>
				</div>
				<span class="pull-right clear-filter hasTooltip" title="Reset">&times;</span>
				<div class="clearfix"></div>
			</div>
			<div class="search-filter">
				<input type="text" title="filter" placeholder="<?php
					echo JText::_('MOD_SELLACIOUS_FILTERS_SEARCH_PLACEHOLDER'); ?>"/>
			</div>
			<ul class="filter-choices unstyled">
				<?php foreach ($field->choices as $ck => $choice): ?>
					<li class="filter-choice">
						<label class="<?php echo $choice->disabled ? 'disabled' : '' ?>">
							<input type="checkbox"
								   name="filter[fields][f<?php echo $field->id ?>][]"
								   id="filter_fields_f<?php echo $field->id ?>_<?php echo (int) $ck ?>"
								   value="<?php echo htmlspecialchars($choice->value) ?>"
								   <?php echo $this->autoSubmit ? 'onclick="this.form.submit();"' : ''; ?>
								<?php echo $choice->selected ? 'checked' : ''; ?>
								<?php echo $choice->disabled ? ' disabled' : '' ?>
							/>
							<?php echo $this->helper->field->renderValue($choice->value, $field->type); ?>
						</label>
					</li>
				<?php endforeach; ?>
			</ul>
		</div>
	<?php endif; ?>
<?php endforeach; ?>
<input type="checkbox" name="filter[fields][f0][]" value="" style="display: none;" checked title=""/>
