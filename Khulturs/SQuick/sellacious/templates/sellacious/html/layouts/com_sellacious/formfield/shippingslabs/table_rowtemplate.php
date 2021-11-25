<?php
/**
 * @version     2.0.0
 * @package     sellacious
 *
 * @copyright   Copyright (C) 2012-2020 Bhartiy Web Technologies. All rights reserved.
 * @license     SPL Sellacious Private License; see http://www.sellacious.com/spl.html
 * @author      Izhar Aazmi <info@bhartiy.com> - http://www.bhartiy.com
 */
// no direct access
use Joomla\Utilities\ArrayHelper;

defined('_JEXEC') or die;

$helper = SellaciousHelper::getInstance();

/** @var stdClass $displayData */
$field      = $displayData;
$precision  = $field->precision;
$rowIndex   = $field->row_index;
$percentage = $field->percentage;
$value      = isset($field->value[$rowIndex]) ? (array) $field->value[$rowIndex] : array();

$min       = ArrayHelper::getValue($value, 'min', 0, 'float');
$max       = ArrayHelper::getValue($value, 'max', 0, 'float');
$unit      = ArrayHelper::getValue($value, 'u', 0, 'int');
$o_country = ArrayHelper::getValue($value, 'origin_country', 0, 'int');
$o_state   = ArrayHelper::getValue($value, 'origin_state', 0, 'int');
$o_zip     = ArrayHelper::getValue($value, 'origin_zip', '', 'string');
$country   = ArrayHelper::getValue($value, 'country', 0, 'int');
$state     = ArrayHelper::getValue($value, 'state', 0, 'int');
$zip       = ArrayHelper::getValue($value, 'zip', '', 'string');
$amntUnit  = ArrayHelper::getValue($value, 'unit', '', 'string');
$price     = ArrayHelper::getValue($value, 'price', 0, 'float');

try
{
	$o_country = $helper->location->getTitle($o_country);
	$country   = $helper->location->getTitle($country);
}
catch (Exception $e)
{
	$o_country = '';
	$country   = '';
}

try
{
	$o_state = $helper->location->getTitle($o_state);
	$state   = $helper->location->getTitle($state);
}
catch (Exception $e)
{
	$o_state = '';
	$state   = '';
}

try
{
	$zip   = $helper->location->getTitle($zip);
	$o_zip = $helper->location->getTitle($o_zip);
}
catch (Exception $e)
{
	$zip   = '';
	$o_zip = '';
}

?>
<tr role="row" id="<?php echo $field->id ?>_sfssrow_<?php echo $rowIndex ?>" class="sfssrow">
	<td class="nowrap text-center" data-float="<?php echo $precision ?>">
		<?php echo $min ?>
	</td>
	<td class="nowrap text-center" data-float="<?php echo $precision ?>">
		<?php echo $max ?>
	</td>
	<td class="nowrap text-center">
		<?php echo $o_country; ?>
	</td>
	<td class="nowrap text-center">
		<?php echo $o_state; ?>
	</td>
	<td class="nowrap text-center">
		<?php echo $o_zip; ?>
	</td>
	<td class="nowrap text-center">
		<?php echo $country; ?>
	</td>
	<td class="nowrap text-center">
		<?php echo $state; ?>
	</td>
	<td class="nowrap text-center">
		<?php echo $zip; ?>
	</td>
	<td class="nowrap text-center">
		<span data-float="2"><?php echo $price ?></span><?php echo ($percentage ? $amntUnit : ''); ?>
		<?php if ($field->unitToggle && $unit): ?>
			<?php echo JText::_('COM_SELLACIOUS_FIELD_SHIPPING_RATE_PER_UNIT_SUFFIX') ?>
		<?php endif; ?>
	</td>
</tr>
