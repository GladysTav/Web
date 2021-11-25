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
$fieldset = $tplData;

/** @var SellaciousViewUser $this */

if ($fieldset->name === 'address')
{
	$fields = $this->helper->user->sortFields($this->form, 'register', 'address');
}
else
{
	$fields = $this->form->getFieldset($fieldset->name);
}

if (array_filter($fields, function ($field) { return !$field->hidden; })):
	echo JHtml::_('ctechBootstrap.addTab', 'profile_tabs_' . $fieldset->name, JText::_($fieldset->label), 'profile_tabs', 'accordion'); ?>
	<div class="ctech-wrapper custom-attributes-container">
		<?php
			foreach ($fields as $field):
				/** @var  \JFormField  $field  */
				if ($field->hidden):
					echo $field->input;
				else:
					$w = $field->getAttribute('fullwidth') == 'true';
					?>
					<div class="ctech-form-group">
						<?php if ($field->label && (!isset($fieldset->width) || $fieldset->width < 12)): ?>
							<?php if($w): ?>
								<div class="ctech-col-md-12"><?php echo $field->label ?></div>
								<div class="ctech-col-md-12"><?php echo $field->input ?></div>
							<?php else: ?>
								<div class="ctech-col-form-label"><?php echo $field->label ?></div>
								<div class="ctech-col-form-input"><?php echo $field->input ?></div>
								<div class="ctech-clearfix"></div>
							<?php endif; ?>
						<?php else: ?>
							<div class="ctech-col-form-input"><?php echo $field->input ?></div>
						<?php endif; ?>
						<div class="ctech-clearfix"></div>
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
endif; ?>
