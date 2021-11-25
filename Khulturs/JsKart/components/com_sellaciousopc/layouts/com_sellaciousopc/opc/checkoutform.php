<?php
/**
 * @version     2.0.0
 * @package     sellacious
 *
 * @copyright   Copyright (C) 2012-2020 Bhartiy Web Technologies. All rights reserved.
 * @license     SPL Sellacious Private License; see http://www.sellacious.com/spl.html
 * @author      Aditya Chakraborty <info@bhartiy.com> - http://www.bhartiy.com
 */
// No direct access
defined('_JEXEC') or die;

/** @var   stdClass  $displayData */
/** @var   JForm     $form */
$form            = $displayData->form;
$fields          = $form->getFieldset();

if (count($fields) > 0)
{
	?>
	<fieldset class="form-horizontal checkoutform w100p"><?php
	foreach ($fields as $field)
	{
		if (strtolower($field->type) == 'fieldgroup')
		{
			?>
			</fieldset>
			<fieldset class="form-horizontal checkoutform w100p"><?php
		}
		?>
		<div class="control-group">
		<?php if ($field->label): ?>
		<div class="control-label"><?php echo $field->label ?></div>
		<div class="controls"><?php echo $field->input ?></div>
	<?php else: ?>
		<?php echo $field->input ?>
	<?php endif; ?>
		</div><?php
	}
	?>
	</fieldset><?php
}
