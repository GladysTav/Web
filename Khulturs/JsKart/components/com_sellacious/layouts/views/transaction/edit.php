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

/** @var SellaciousViewTransaction $this */

JHtml::_('jquery.framework');
JHtml::_('behavior.keepalive');
JHtml::_('bootstrap.tooltip');
JHtml::_('behavior.formvalidator');

JHtml::_('ctech.bootstrap');
JHtml::_('ctech.select2');

JHtml::_('script', 'com_sellacious/util.validator-mobile.js', array('relative' => true, 'version' => S_VERSION_CORE));
JHtml::_('script', 'com_sellacious/fe.view.transaction.js', array('relative' => true, 'version' => S_VERSION_CORE));

JHtml::_('stylesheet', 'com_sellacious/plugin/select2-3.5/select2.css', array('relative' => true, 'version' => S_VERSION_CORE));
JHtml::_('stylesheet', 'com_sellacious/font-awesome.min.css', array('relative' => true, 'version' => S_VERSION_CORE));
JHtml::_('stylesheet', 'com_sellacious/fe.component.css', array('relative' => true, 'version' => S_VERSION_CORE));
JHtml::_('stylesheet', 'com_sellacious/fe.view.transaction.css', array('relative' => true, 'version' => S_VERSION_CORE));

JText::script('COM_SELLACIOUS_VALIDATION_FORM_FAILED');

$app  = JFactory::getApplication();
$type = $app->getUserState('com_sellacious.edit.transaction.type');
?>
<div class="ctech-wrapper">
    <?php echo $this->loadTemplate($type); ?>
</div>

