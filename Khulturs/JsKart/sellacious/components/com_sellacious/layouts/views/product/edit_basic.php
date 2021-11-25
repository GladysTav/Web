<?php
/**
 * @version     2.0.0
 * @package     sellacious
 *
 * @copyright   Copyright (C) 2012-2020 Bhartiy Web Technologies. All rights reserved.
 * @license     SPL Sellacious Private License; see http://www.sellacious.com/spl.html
 * @author      Izhar Aazmi <info@bhartiy.com> - http://www.bhartiy.com
 */
// no direct access.
defined('_JEXEC') or die;

use Joomla\Utilities\ArrayHelper;

/** @var  SellaciousViewProduct $this */
$fieldsets = $this->form->getFieldsets();
$fields    = $this->form->getFieldset('basic');
$fieldset  = ArrayHelper::getValue($fieldsets, 'basic');

$groupFields = array();

/** @var array $tplData */
$visible  = array_filter($tplData['visible_tabs']);
$tabClass = array_key_first($visible) == 'basic' ? 'in active' : '';

$uniqueField = $this->state->get('product.unique_field');

$col1 = array(
	'jform_id',
	'jform_basic_type',
	'jform_categories',
	'jform_basic_title',
	'jform_basic_parent_id',
	'jform_basic_manufacturer_id',
	'jform_basic_local_sku',
	'jform_basic_manufacturer_sku',
	'jform_basic_features',
	'jform_basic_introtext',
	'jform_basic_primary_image',
	'jform_basic_primary_video_url',
	'jform_seller_product_address',
	'jform_seller_product_location',
);

$col2 = array(
	'jform_basic_description',
	'jform_basic_images',
	'jform_basic_attachments',
);

if (!$this->allow_duplicates && $uniqueField)
{
	if (is_numeric($uniqueField))
	{
		$customField = $this->helper->field->getItem($uniqueField);

		if ($customField)
		{
			$uField      = 'jform_specifications_' . $uniqueField;
			$groupFields = $this->form->getFieldset('specifications');
			array_unshift($col1, $uField);

			if ($customField->parent_id > 1)
			{
				array_unshift($col1, 'jform_specifications_' . $customField->parent_id);
			}
		}
	}
	else
	{
		$uFieldParts = explode('.', $uniqueField);

		if (isset($uFieldParts[1]))
		{
			$uField      = 'jform_' . $uFieldParts[0] . '_' . $uFieldParts[1];
			$groupFields = $this->form->getFieldset($uFieldParts[0]);
		}
		else
		{
			$uField = 'jform_basic_' . $uFieldParts[0];
		}

		if (($key1 = array_search($uField, $col1)) !== false)
		{
			unset($col1[$key1]);
		}

		if (($key2 = array_search($uField, $col2)) !== false)
		{
			unset($col2[$key2]);
		}

		array_unshift($col1, $uField);
	}
}

foreach ($fields as $fKey => $field)
{
	if (!$field->hidden && !in_array($fKey, $col1) && !in_array($fKey, $col2))
	{
		$col1[] = $fKey;
	}
}
?>
<div class="tab-pane fade <?php echo $tabClass; ?>" id="tab-basic">
	<div class="row padding-10">
		<div class="col-lg-6 col-md-12 col-sm-12">
			<fieldset>
				<?php
				foreach ($col1 as $fKey)
				{
					/** @var JFormField $field */
					$field = ArrayHelper::getValue($fields, $fKey);
					$field = $field ?: ArrayHelper::getValue($groupFields, $fKey);

					if (!$field)
					{
						continue;
					}
					elseif ($field->hidden)
					{
						echo $field->renderField(array('hiddenLabel' => true));
					}
					else
					{
						?>
						<div class="row <?php echo $field->label ? 'input-row' : '' ?>">
							<?php
							if ($field->label == '' || (isset($fieldset->width) && $fieldset->width == 12))
							{
								echo '<div class="controls col-md-12">' . $field->renderField(array('hiddenLabel' => true)) . '</div>';
							}
							else
							{
								echo '<div class="form-label col-sm-4 col-md-4 col-lg-3">' . $field->renderField(array('onlyLabel' => true, 'noWrapper' => true)) . '</div>';
								echo '<div class="controls col-sm-8 col-md-8 col-lg-9">' . $field->renderField(array('hiddenLabel' => true)) . '</div>';
							}
							?>
						</div>
						<?php
					}
				}
				?>
			</fieldset>
		</div>

		<div class="col-lg-6 col-md-12 col-sm-12">
			<fieldset>
				<?php
				foreach ($col2 as $fKey)
				{
					$field = ArrayHelper::getValue($fields, $fKey);

					if (!$field)
					{
						continue;
					}
					elseif ($field->hidden)
					{
						echo $field->renderField(array('hiddenLabel' => true));
					}
					else
					{
						?>
						<div class="row <?php echo $field->label ? 'input-row' : '' ?>">
							<?php
							if ($field->fieldname == 'description')
							{
								echo '<div class="form-label col-md-12">' . $field->renderField(array('onlyLabel' => true, 'noWrapper' => true)) . '</div>';
								echo '<div class="controls col-md-12">' . $field->renderField(array('hiddenLabel' => true)) . '</div>';
							}
							elseif ($field->label && (!isset($fieldset->width) || $fieldset->width < 12))
							{
								echo '<div class="form-label col-sm-4 col-md-4 col-lg-3">' . $field->renderField(array('onlyLabel' => true, 'noWrapper' => true)) . '</div>';
								echo '<div class="controls col-sm-8 col-md-8 col-lg-9">' . $field->renderField(array('hiddenLabel' => true)) . '</div>';
							}
							else
							{
								echo '<div class="controls col-md-12">' . $field->renderField(array('hiddenLabel' => true)) . '</div>';
							}
							?>
						</div>
						<?php
					}
				}
				?>
			</fieldset>
		</div>

		<!-- Additional fields free flow 100% -->
		<div class="col-lg-6 col-md-12 col-sm-12">
			<fieldset>
				<?php
				foreach ($fields as $fKey => $field)
				{
					if (in_array($fKey, $col1) || in_array($fKey, $col2) || $field->group == 'basic.params')
					{
						continue;
					}
					elseif ($field->hidden)
					{
						echo $field->renderField(array('hiddenLabel' => true));
					}
					else
					{
						?>
						<div class="row <?php echo $field->label ? 'input-row' : '' ?>">
							<?php
							if ($field->label == '' || (isset($fieldset->width) && $fieldset->width == 12))
							{
								echo '<div class="controls col-md-12">' . $field->renderField(array('hiddenLabel' => true)) . '</div>';
							}
							else
							{
								echo '<div class="form-label col-sm-4 col-md-4 col-lg-3">' . $field->renderField(array('onlyLabel' => true, 'noWrapper' => true)) . '</div>';
								echo '<div class="controls col-sm-8 col-md-8 col-lg-9">' . $field->renderField(array('hiddenLabel' => true)) . '</div>';
							}
							?>
						</div>
						<?php
					}
				}
				?>
			</fieldset>
		</div>
	</div>
</div>
