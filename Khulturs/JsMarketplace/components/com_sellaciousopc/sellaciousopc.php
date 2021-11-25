<?php
/**
 * @version     1.7.4
 * @package     sellacious
 *
 * @copyright   Copyright (C) 2012-2019 Bhartiy Web Technologies. All rights reserved.
 * @license     SPL Sellacious Private License; see http://www.sellacious.com/spl.html
 * @author      Aditya Chakraborty <info@bhartiy.com> - http://www.bhartiy.com
 */
// No direct access
defined('_JEXEC') or die;

// Include dependencies
JLoader::import('sellacious.loader');

if (!class_exists('SellaciousHelper'))
{
	JLog::add('COM_SELLACIOUSOPC_LIBRARY_NOT_FOUND');

	return false;
}

$lang = JFactory::getLanguage();
$tag  = $lang->getTag();
$lang->load('com_sellacious', JPATH_BASE, $tag, true, false);
$lang->load('com_sellacious', JPATH_BASE . '/components/com_sellacious', $tag, true, false);

$controller = JControllerLegacy::getInstance('Sellaciousopc');
$controller->execute(JFactory::getApplication()->input->get('task'));
$controller->redirect();
