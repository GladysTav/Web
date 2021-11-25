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

// Include dependencies
JLoader::import('sellacious.loader');

if (!class_exists('SellaciousHelper'))
{
	JLog::add(JText::_('COM_SELLACIOUS_LIBRARY_NOT_FOUND'));

	return false;
}

$controller = JControllerLegacy::getInstance('Sellaciouscoq');
$controller->execute(JFactory::getApplication()->input->get('task'));
$controller->redirect();
