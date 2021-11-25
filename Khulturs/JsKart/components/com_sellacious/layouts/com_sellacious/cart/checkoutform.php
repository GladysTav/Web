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

/** @var   stdClass $displayData */
/** @var   JForm $form */
$form   = $displayData->form;
$fields = $form->getFieldset();

if (count($fields) > 0)
{
	?>
	<div class="ctech-wrapper custom-attributes-container"
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
			<div class="ctech-form-group ">
			<?php if ($field->label): ?>
			<div class="ctech-col-form-label"><?php echo $field->label ?></div>
			<div class="ctech-col-form-input"><?php echo $field->input ?></div>
			<?php else: ?>
				<?php echo $field->input ?>
			<?php endif; ?>
			<div class="ctech-clearfix"></div>
			</div><?php
		}
		?>
		</fieldset>
	</div>
	<?php
}
