<?php
/**
 *  
 * @package    Com_Jdprofiles
 * @author      Joomdev
 * @copyright  Copyright (C) 2018 Joomdev, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access
defined('_JEXEC') or die;

// Access check.
if (!JFactory::getUser()->authorise('core.manage', 'com_jdprofiles'))
{
	throw new Exception(JText::_('JERROR_ALERTNOAUTHOR'));
}

// Include dependancies
jimport('joomla.application.component.controller');

JLoader::registerPrefix('Jdprofiles', JPATH_COMPONENT_ADMINISTRATOR);
JLoader::register('JdprofilesHelper', JPATH_COMPONENT_ADMINISTRATOR . DIRECTORY_SEPARATOR . 'helpers' . DIRECTORY_SEPARATOR . 'jdprofiles.php');

$controller = JControllerLegacy::getInstance('Jdprofiles');
$controller->execute(JFactory::getApplication()->input->get('task'));
$controller->redirect();