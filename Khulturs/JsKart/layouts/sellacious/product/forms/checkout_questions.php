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

/** @var array $displayData */
/** @var JForm $form */
$form      = $displayData['form'];
$fieldsets = $form->getFieldsets();

foreach ($fieldsets as $fieldset)
{
	$fields = $form->getFieldset($fieldset->name);

	if (count($fields))
	{
		?><fieldset><?php
		foreach ($fields as $field)
		{
			if (strtolower($field->type) == 'fieldgroup')
			{
				?>
				</fieldset>
				<fieldset><?php
				echo $field->input;
			}
			elseif ($field->hidden)
			{
				echo $field->input;
			}
			elseif ($field->getAttribute('label'))
			{
				?>
				<div class="ctech-form-group">
					<div class="ctech-col-form-label"><?php echo $field->label ?></div>
					<div class="ctech-clearfix"></div>
					<div class="ctech-col-form-input"><?php echo $field->input ?></div>
				</div>
				<?php
			}
			else
			{
				?>
				<div class="ctech-form-group">
					<div class="ctech-col-form-input"><?php echo $field->input; ?></div>
				</div>
				<?php
			}
		}
		?></fieldset><?php
	}
}
