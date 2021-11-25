<?php
/**
 * @version     2.0.0
 * @package     sellacious
 *
 * @copyright   Copyright (C) 2012-2020 Bhartiy Web Technologies. All rights reserved.
 * @license     SPL Sellacious Private License; see http://www.sellacious.com/spl.html
 * @author      Aditya Chakraborty <info@bhartiy.com> - http://www.bhartiy.com
 */
// No direct access
defined('_JEXEC') or die;

JHtml::_('behavior.framework');
JHtml::_('jquery.framework');
JHtml::_('behavior.formvalidator');
JHtml::_('formbehavior.chosen', 'select');
JHtml::_('bootstrap.tooltip');
JHtml::_('ctech.bootstrap');
JHtml::_('ctech.select2');

JHtml::_('stylesheet', 'com_sellacious/font-awesome.min.css', null, true);

JHtml::_('script', 'com_sellacious/plugin/serialize-object/jquery.serialize-object.min.js', false, true);

JHtml::_('script', 'com_sellaciousopc/fe.view.opc.js', false, true);
JHtml::_('stylesheet', 'com_sellacious/fe.view.opc.css', null, true);

JHtml::_('script', 'com_sellacious/plugin/datepicker/dcalendar.picker.js', false, true);
JHtml::_('stylesheet', 'com_sellacious/plugin/datepicker/dcalendar.picker.css', null, true);
?>
<h1><?php echo JText::_("COM_SELLACIOUSOPC");?></h1>
