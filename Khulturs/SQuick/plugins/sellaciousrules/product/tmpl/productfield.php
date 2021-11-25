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
$form  = $displayData['form'];
$class = $displayData['class'];
$alias = $displayData['alias'];
$field = $form->getField($alias, 'product');

if ($field->hidden):
	echo $field->input;
else:
	?>
	<div class="row <?php echo $field->label ? 'input-row' : '' ?>">
		<?php
		if ($field->label && (!isset($fieldset->width) || $fieldset->width < 12))
		{
			echo '<div class="form-label col-sm-3 col-md-3 col-lg-2">' . $field->label . '</div>';
			echo '<div class="controls col-sm-9 col-md-9 col-lg-10">' . $field->input . '</div>';
		}
		else
		{
			echo '<div class="controls col-md-12">' . $field->input . '</div>';
		}
		?>
	</div>
<?php
endif;
