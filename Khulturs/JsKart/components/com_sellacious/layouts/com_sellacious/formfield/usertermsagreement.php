<?php
/**
 * @version     2.0.0
 * @package     sellacious
 *
 * @copyright   Copyright (C) 2012-2020 Bhartiy Web Technologies. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Izhar Aazmi <info@bhartiy.com> - http://www.bhartiy.com
 */
// no direct access.
defined('_JEXEC') or die;


/** @var  array  $displayData */
$field = (object) $displayData;

$options = array(
	'backdrop' => 'static',
	'keyboard' => true,
	'scrollable' => true
);

$content = '<div style="padding:20px">' . $field->content . '</div>';
echo JHtml::_('ctechBootstrap.modal', 'register-tnc-modal', JText::_('COM_SELLACIOUS_REGISTER_TERMS_AGREEMENT'), $content, '', $options);
?>
<div>
	<input type="checkbox" name="<?php echo $field->name ?>" id="<?php echo $field->id ?>" value="1" style="margin-top: -2px"
		<?php echo $field->class ?> <?php echo $field->checked ?> <?php echo $field->disabled ?>
		<?php echo  $field->required ?> title=""/>

	<a href="#" data-target="#register-tnc-modal" data-toggle="ctech-modal" style="font-weight: bold; margin-top: 10px">
		&nbsp;<?php echo JText::_('COM_SELLACIOUS_REGISTER_TERMS_AGREEMENT_AGREE') ?></a>
</div>
