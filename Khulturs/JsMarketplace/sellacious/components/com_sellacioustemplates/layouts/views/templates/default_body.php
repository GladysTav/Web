<?php
/**
 * @version     1.7.4
 * @package     sellacious
 *
 * @copyright   Copyright (C) 2012-2019 Bhartiy Web Technologies. All rights reserved.
 * @license     SPL Sellacious Private License; see http://www.sellacious.com/spl.html
 * @author      Aditya Chakraborty <info@bhartiy.com> - http://www.bhartiy.com
 */
// no direct access
defined('_JEXEC') or die;

/** @var  \SellaciousTemplatesViewTemplates $this */
$context   = '';
$active    = '';

$document = JFactory::getDocument();
$document->addStyleDeclaration('.group-row > td { background: #f0efd0 !important; font-size: 105%; }');
$document->addStyleDeclaration('.active0 > td { background: #474544 !important; font-size: 105%; color: #fff; }');
$document->addStyleDeclaration('.active1 > td { background: #474544 !important; font-size: 105%; color: #fff; }');

foreach ($this->items as $i => $item)
{
	$canEdit   = $this->helper->access->check('template.edit', $item->id);
	$canChange = 0;

	$tempContext = explode('.', $item->context);

	if ($active != $item->active) :
		$title = $item->active ? JText::_('COM_SELLACIOUSTEMPLATES_TEMPLATES_ACTIVE') : JText::_('COM_SELLACIOUSTEMPLATES_TEMPLATES_INACTIVE'); ?>
		<tr class="active<?php echo $item->active; ?>">
			<td colspan="7" align="center"><strong><?php echo $title; ?></strong></td>
		</tr>
		<?php
		$active = $item->active;
	endif;

	if ($context != $tempContext[0]) : ?>
		<tr class="group-row">
			<td class="center">&raquo;</td>
			<td colspan="6"><?php echo strtoupper(str_replace('_', ' ', $tempContext[0])); ?></td>
		</tr>
	<?php
		$context = $tempContext[0];
	endif;
	?>
	<tr role="row">
		<td class="nowrap center hidden-phone">
			<label>
				<input type="checkbox" name="cid[]" id="cb<?php echo $i ?>" class="checkbox style-0"
					   value="<?php echo $item->id ?>" onclick="Joomla.isChecked(this.checked);"
					<?php echo ($canEdit || $canChange) ? '' : ' disabled="disabled"' ?> />
				<span></span>
			</label>
			<input type="hidden" name="jform[<?php echo $i ?>][id]"
				   id="jform_<?php echo $i ?>_id" value="<?php echo $item->id; ?>"/>
		</td>
		<td class="nowrap center">
			<span class="btn-round">
				<?php echo JHtml::_('jgrid.published', $item->state, $i, 'templates.', $canChange); ?>
			</span>
		</td>
		<td class="nowrap">
			<?php echo str_repeat('<span class="gi">|&mdash;</span>', 1) ?>
			<?php if ($canEdit): ?>
			<a href="#" onclick="listItemTask('cb<?php echo $i ?>', 'template.edit');return false;"><?php echo
				$this->escape($tempContext[1]); ?></a>
			<?php else:
				echo $this->escape($tempContext[1]);
			endif; ?>
		</td>
		<td class="nowrap">
			<?php echo $this->escape($item->title); ?>
		</td>
		<td class="nowrap">
			<button type="button" class="btn btn-default btn-small btn-preview hasTooltip" data-context="<?php echo $item->context ?>" title="Show Template Preview">
				<i class="icon-eye"></i>
				<span class="hidden-xs">Preview</span>
			</button>
		</td>
		<td class="center hidden-phone">
			<?php echo (int) $item->id; ?>
		</td>
	</tr>
<?php
}
