<?php
/**
 * @version     2.0.0
 * @package     sellacious
 *
 * @copyright   Copyright (C) 2012-2020 Bhartiy Web Technologies. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Izhar Aazmi <info@bhartiy.com> - http://www.bhartiy.com
 */
// no direct access.
defined('_JEXEC') or die;

/** @var  array  $displayData */
/** @var  JFormFieldCheckMatrix  $field */
$field   = $displayData['field'];
$rows    = $displayData['rows'];
$columns = $displayData['columns'];

if (count($columns) == 0)
{
	return;
}

JHtml::_('script', 'com_sellacious/field.checkmatrix.js', array('version' => S_VERSION_CORE, 'relative' => true));
JHtml::_('stylesheet', 'com_sellacious/field.checkmatrix.css', array('version' => S_VERSION_CORE, 'relative' => true));
?>
<div class="w100p bg-color-white">
	<table class="<?php echo $field->class ?> table-striped table-bordered jff-checkmatrix">
		<thead>
		<th>
			<input type="hidden" name="<?php echo $field->name ?>"
		           id="<?php echo $field->id ?>" value="<?php echo htmlspecialchars($field->value) ?>" class="jff-checkmatrix-input">
			<label class="checkbox style-0">
				<input type="checkbox" class="checkbox style-0"
					   data-checkall="true">
				<span class="heading"><?php echo JText::_('JALL') ?></span>
			</label>
		</th>
		<?php foreach ($columns as $ci => $column): ?>
			<th class="center" data-column="<?php echo $column->value ?>">
				<label class="checkbox style-0">
					<input type="checkbox" class="checkbox style-0"
						   data-checkall-col="<?php echo $column->value ?>">
					<span class="heading"><?php echo $column->text ?></span>
				</label>
			</th>
		<?php endforeach; ?>
		</thead>
		<tbody>
		<?php foreach ($rows as $ri => $row): ?>
			<tr>
				<th data-row="<?php echo $row->value ?>">
					<label class="checkbox style-0">
						<input type="checkbox" class="checkbox style-0"
							   data-checkall-row="<?php echo $row->value ?>">
						<span><?php echo $row->text ?></span>
					</label>
				</th>
				<?php foreach ($columns as $column): ?>
					<td class="center" data-column="<?php echo $column->value ?>">

						<label class="checkbox style-0">
							<input type="checkbox" class="checkbox style-0"
							       data-column="<?php echo $column->value ?>" data-row="<?php echo $row->value ?>">
							<span> </span>
						</label>

					</td>
				<?php endforeach; ?>
			</tr>
		<?php endforeach; ?>
		</tbody>
	</table>
</div>
