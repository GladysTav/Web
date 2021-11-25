<?php
/**
 * @version     2.0.0
 * @package     sellacious
 *
 * @copyright   Copyright (C) 2012-2020 Bhartiy Web Technologies. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Aditya Chakraborty <info@bhartiy.com> - http://www.bhartiy.com
 */

// no direct access
defined('_JEXEC') or die;

/** @var  JLayoutFile $this */
/** @var  stdClass $displayData */
/** @var  JForm $form */
$form  = $displayData->form;
$class = $displayData->class;
$id    = $displayData->id;
?>
<tr class="<?php echo $class ?>" id="<?php echo $id ?>">
	<td colspan="3">
		<?php
		if ($form):
			$fieldsets = $form->getFieldsets();

			foreach ($fieldsets as $fs_key => $fieldset):
				$fields = $form->getFieldset($fieldset->name);
				?>
				<table class="shipment-table">
				<tbody>
				<?php
				foreach ($fields as $field):

					if ($field->hidden):
						echo $field->input;
					else:
						?>
						<tr>
							<td style="width: 160px;"
							    class="v-top">
								<?php if ($field->getAttribute('type') == 'fieldgroup'): ?>
									<fieldset><legend><?php echo $field->label; ?></legend></fieldset>
								<?php else: ?>
									<?php echo $field->label; ?>
								<?php endif; ?>
							</td>
							<td><?php echo ($field->getAttribute('type') != 'fieldgroup') ? $field->input : ''; ?></td>
						</tr>
					<?php
					endif;

				endforeach;
				?>
				</tbody>
				</table><?php
			endforeach;
		endif;
		?>
	</td>
</tr>

