<?php
/**
 * @version     1.7.4
 * @package     com_sellacioustemplates
 *
 * @copyright   Copyright (C) 2012-2019 Bhartiy Web Technologies. All rights reserved.
 * @license     SPL Sellacious Private License; see http://www.sellacious.com/spl.html
 * @author      Aditya Chakraborty <info@bhartiy.com> - http://www.bhartiy.com
 */

defined('_JEXEC') or die;

if (!class_exists('SellaciousHelper'))
{
	throw new Exception(JText::_('COM_SELLACIOUSREPORTING_SELLACIOUS_LIBRARY_MISSING'));
}

JTable::addIncludePath(__DIR__ . '/tables');
JForm::addFormPath(__DIR__ . '/models/forms');
JForm::addFieldPath(__DIR__ . '/models/fields');

$controller = JControllerLegacy::getInstance('Sellacioustemplates');
$controller->execute(JFactory::getApplication()->input->get('task'));
$controller->redirect();
