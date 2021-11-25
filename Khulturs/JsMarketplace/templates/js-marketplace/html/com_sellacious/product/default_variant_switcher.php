<?php
/**
 * @version     1.7.3
 * @package     sellacious
 *
 * @copyright   Copyright (C) 2012-2019 Bhartiy Web Technologies. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Izhar Aazmi <info@bhartiy.com> - http://www.bhartiy.com
 */
// no direct access
defined('_JEXEC') or die;

/** @var  SellaciousViewProduct  $this */
$switcher = $this->getVariantSwitcher();

if (!$switcher)
{
	return;
}

$fields = $switcher->getVisibleFields();

if (!$fields)
{
	return;
}
?>
<hr class="isolate"/>
<form action="<?php echo JUri::getInstance()->toString() ?>" method="post" id="varForm" name="varForm">

	<div class="variant-picker">
		<?php foreach ($fields as $field): ?>
			<div class="variant-choice">
				<h5><?php echo $field->title ?></h5>
				<div class="radio">
					<?php foreach ($field->getOptions(false) as $option):
						$o_value  = htmlspecialchars($option->value);
						$o_text   = $this->helper->field->renderValue($option->value, $field->type);
						$selected = $switcher->matchValue($option->value, $field->id) ? ' selected' : '';

						$availability = $switcher->isAvailable($field, $option) ? ' available-option' : '';
						$avVariant    = $switcher->getAvailableVariant($field, $option);

						$href = $avVariant ? JRoute::_('index.php?option=com_sellacious&view=product&p=' . $avVariant->code) : '';
						?>
						<?php if($field->type == 'color'): ?>
							<label class="colors-option <?php echo $availability; ?>  <?php echo $selected; ?>">
								<input type="radio" class="variant_spec" data-href="<?php echo $href ?>"
									<?php echo $selected ? 'checked' : '' ?>>
								<span style="background: <?php echo $option->value ?>;"></span>
							</label>
						<?php else: ?>
							<label class="variant-options <?php echo $availability; echo $selected; ?>">
								<input type="radio" class="variant_spec" data-href="<?php echo $href ?>"
									<?php echo $selected ? 'checked' : '' ?>>
								<?php echo $o_text ?>
							</label>
						<?php endif; ?>
					<?php endforeach; ?>
				</div>
			</div>
		<?php endforeach; ?>
		<div class="clearfix"></div>
	</div>

	<input type="hidden" name="option" value="com_sellacious"/>
	<input type="hidden" name="view" value="product"/>
	<input type="hidden" name="task" value=""/>
	<input type="hidden" name="p" value="<?php echo $this->state->get('product.code') ?>"/>

	<?php echo JHtml::_('form.token'); ?>
</form>
<div class="clearfix"></div>

