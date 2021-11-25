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

/** @var  \stdClass  $tplData */
$fieldset = $tplData[0];

$custom = isset($tplData[1]) && $tplData[1] === 'custom';

/** @var SellaciousViewUser $this */
if ($fieldset->name === 'address')
{
	$fields = $this->helper->user->sortFields($this->form, 'register', 'address', 'address');
}
else
{
	$fields = $this->form->getFieldset($fieldset->name);
}

if (array_filter($fields, function ($field) { return !$field->hidden; })):
	echo JHtml::_('ctechBootstrap.addTab', 'profile_tabs_' . $fieldset->name, JText::_($fieldset->label), 'profile_tabs'); ?>
	<div class="ctech-wrapper <?php echo $custom ? 'custom-attributes-container' : ''; ?>">
	<?php
	foreach ($fields as $field):
		if ($field->hidden):
			echo $field->input;
		else:
			?>
			<div class="ctech-form-group">
				<?php if ($field->label && (!isset($fieldset->width) || $fieldset->width < 12)): ?>
					<div class="ctech-col-form-label"><?php echo $field->label ?></div>
					<div class="ctech-col-form-input"><?php echo $field->input ?></div>
				<?php else: ?>
					<div class="ctech-col-form-input"><?php echo $field->input ?></div>
				<?php endif; ?>
			</div>
		<?php
		endif;
	endforeach;
	?>
</div>
	<div class="clearfix"></div><?php
	echo JHtml::_('ctechBootstrap.endTab');
else:
	foreach ($fields as $field):
		echo $field->input;
	endforeach;
endif;
