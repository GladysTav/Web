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
<div class="<?php echo $class ?>" id="<?php echo $id ?>">
	<div>
		<?php
		if ($form):

			$fieldsets = $form->getFieldsets();

			foreach ($fieldsets as $fs_key => $fieldset):

				$fields = $form->getFieldset($fieldset->name);
				?>
				<div class="shipment-table">
					<div>
						<?php
						foreach ($fields as $field):
							if (strtolower($field->getAttribute('type')) == 'fieldgroup'): ?>
								<div colspan="2"><legend><?php echo $field->label; ?></legend>
								</div><?php
							elseif ($field->hidden):
								echo $field->input;
							else:
								?>
								<div class="ctech-form-group">
									<div class="ctech-col-form-label"><?php echo $field->label; ?></div>
									<div class="ctech-col-form-input"><?php echo $field->input; ?></div>
									<div class="ctech-clearfix"></div>
								</div>
							<?php
							endif;

						endforeach;
						?>
					</div>
				</div>
			<?php

			endforeach;

		endif;

		?>
	</div>
</div>

