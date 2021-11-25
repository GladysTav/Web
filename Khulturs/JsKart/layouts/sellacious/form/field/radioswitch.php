<?php
/**
 * @version     2.0.0
 * @package     sellacious
 *
 * @copyright   Copyright (C) 2012-2020 Bhartiy Web Technologies. All rights reserved.
 * @license     SPL Sellacious Private License; see http://www.sellacious.com/spl.html
 * @author      Saurabh Sabharwal <info@bhartiy.com> - http://www.bhartiy.com
 */
// no direct access
defined('_JEXEC') or die;

/** @var array $displayData */
extract($displayData);

/**
 * Layout variables
 * -----------------
 * @var   string   $autocomplete    Autocomplete attribute for the field.
 * @var   boolean  $autofocus       Is autofocus enabled?
 * @var   string   $class           Classes for the input.
 * @var   string   $description     Description of the field.
 * @var   boolean  $disabled        Is this field disabled?
 * @var   string   $group           Group the field belongs to. <fields> section in form XML.
 * @var   boolean  $hidden          Is this field hidden in the form?
 * @var   string   $hint            Placeholder for the field.
 * @var   string   $id              DOM id of the field.
 * @var   string   $label           Label of the field.
 * @var   string   $labelclass      Classes to apply to the label.
 * @var   boolean  $multiple        Does this field support multiple values?
 * @var   string   $name            Name of the input field.
 * @var   string   $onchange        Onchange attribute for the field.
 * @var   string   $onclick         Onclick attribute for the field.
 * @var   string   $pattern         Pattern (Reg Ex) of value of the form field.
 * @var   boolean  $readonly        Is this field read only?
 * @var   boolean  $repeat          Allows extensions to duplicate elements.
 * @var   boolean  $required        Is this field required?
 * @var   integer  $size            Size attribute of the input.
 * @var   boolean  $spellcheck      Spellcheck state for the form field.
 * @var   string   $validate        Validation rules to apply.
 * @var   string   $value           Value attribute of the field.
 * @var   array    $options         Options available for this field.
 * @var   array    $element         XML element.
 */

// Including fallback code for HTML5 non supported browsers.
JHtml::_('jquery.framework');
JHtml::_('script', 'system/html5fallback.js', array('version' => 'auto', 'relative' => true, 'conditional' => 'lt IE 9'));
JHtml::_('stylesheet', 'com_sellacious/field.switch.css', false, true);

$true  = isset($element['trueValue']) ? $element['trueValue'] : 1;
$false = isset($element['falseValue']) ? $element['falseValue'] : 0;

$checked = $value == $true ? ' checked' : '';
$disable = ($readonly || $disabled) ? ' disabled' : '';
?>
<div id="<?php echo $id; ?>_wrap" class="jff-radioswitch <?php echo $class ?>">

	<label for="<?php echo $id; ?>_ui" class="radio-switch <?php echo $disable ?>">
		<input type="checkbox" id="<?php echo $id; ?>_ui" value="1" class="jff-radioswitch-ui"
			   data-true="<?php echo $true ?>" data-false="<?php echo $false ?>" <?php echo $disable . $checked ?>/>
		<span></span>
	</label>
	<input type="hidden" name="<?php echo $name ?>" id="<?php echo $id ?>" value="<?php echo $value ?>" <?php echo $readonly ? ' readonly' : '' ?>/>

</div>
