<?php
/**
 * @version     1.7.3
 * @package     sellacious
 *
 * @copyright   Copyright (C) 2012-2019 Bhartiy Web Technologies. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Izhar Aazmi <info@bhartiy.com> - http://www.bhartiy.com
 */
// no direct access.
defined('_JEXEC') or die;


/** @var  array  $displayData */
$field = (object) $displayData;

$options = array(
	'title'    => JText::_('COM_SELLACIOUS_REGISTER_TERMS_AGREEMENT'),
	'backdrop' => 'static',
	'height'   => '520',
	'keyboard' => true,
);

$content = '<div style="padding:20px">' . $field->content . '</div>';
echo JHtml::_('bootstrap.renderModal', 'register-tnc-modal', $options, $content);
?>
<style>
    .tnc-ul .tnc-ul-li {
        display: inline-flex;
        margin-left: 5px;
    }
    .tnc-ul {
        margin-top: 0; text-align: left;
        padding-left: 0;
    }
</style>

<ul  class="tnc-ul">


    <li class="tnc-ul-li"> <input type="checkbox" name="<?php echo $field->name ?>" id="<?php echo $field->id ?>" value="1" style="margin-top: -2px"
            <?php echo $field->class ?> <?php echo $field->checked ?> <?php echo $field->disabled ?>
            <?php echo  $field->required ?> title=""/>
    </li>

<li class="tnc-ul-li"><a href="#register-tnc-modal" data-toggle="modal" style="font-weight: bold; margin-top: 10px">
	&nbsp;<?php echo JText::_('COM_SELLACIOUS_REGISTER_TERMS_AGREEMENT_AGREE') ?></a>
</li>





</ul>
