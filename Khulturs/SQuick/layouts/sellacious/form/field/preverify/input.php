<?php
/**
 * @version     2.0.0
 * @package     Sellacious
 *
 * @copyright   Copyright (C) 2012-2020 Bhartiy Web Technologies. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Saurabh Sabharwal <info@bhartiy.com> - http://www.bhartiy.com
 */
defined('_JEXEC') or die;

/**
 * @var  array   $displayData
 * @var  string  $id
 * @var  string  $name
 * @var  string  $fieldType
 * @var  string  $formName
 * @var  string  $hint
 * @var  bool    $required
 * @var  string  $class
 * @var  bool    $validated
 * @var  string  $pv_token
 * @var  string  $unique
 * @var  JForm   $form
 * @var  int     $otp_length
 */
extract($displayData);

JHtml::_('ctech.vueTemplate', 'inputpreverify', __DIR__ . '/preverify.vue');

$val = htmlspecialchars($value, ENT_COMPAT, 'UTF-8');
?>
<div class="ctech-wrapper">
	<div class="preverify-wrapper">
		<span is="inputpreverify"
			  id="<?php echo $id ?>"
			  type="<?php echo $fieldType ?>"
			  name="<?php echo $name ?>"
			  value="<?php echo $val ?>"
			  formname="<?php echo $formName ?>"
			  classname="<?php echo $class ?>"
			  placeholder="<?php echo htmlspecialchars($hint, ENT_COMPAT, 'UTF-8') ?>"
			  pv_token="<?php echo htmlspecialchars($pv_token, ENT_COMPAT, 'UTF-8') ?>"
			  :required="<?php echo $required ? 'true' : 'false' ?>"
			  :verified="<?php echo $validated ? 'true' : 'false' ?>"
			  :unique="<?php echo $unique ? 'true' : 'false' ?>"
			  userid="<?php echo $form->getValue('id'); ?>"
			  :otplength="<?php echo (int) $otp_length; ?>"
		>
			<template #send><?php echo JText::_('COM_SELLACIOUS_FIELD_PREVERIFY_BTN_SEND_OTP') ?></template>
			<template #resend><?php echo JText::_('COM_SELLACIOUS_FIELD_PREVERIFY_BTN_RESEND_OTP') ?></template>
			<template #change><?php echo JText::_('COM_SELLACIOUS_FIELD_PREVERIFY_BTN_CHANGE_INPUT') ?></template>
			<template #verify><?php echo JText::_('COM_SELLACIOUS_FIELD_PREVERIFY_BTN_VERIFY_OTP') ?></template>
		</span>
	</div>
</div>
