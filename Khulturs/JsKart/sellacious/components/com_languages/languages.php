<?php
/**
 * @version     2.0.0
 * @package     sellacious
 *
 * @copyright   Copyright (C) 2012-2020 Bhartiy Web Technologies. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Izhar Aazmi <info@bhartiy.com> - http://www.bhartiy.com
 */
// No direct access.
defined('_JEXEC') or die;

$user = JFactory::getUser();

// Access to Joomla language extension is required to use this feature as of now.
if (!$user->authorise('core.manage', 'com_languages'))
{
	throw new JAccessExceptionNotallowed(JText::_('JERROR_ALERTNOAUTHOR'), 403);
}

$controller = JControllerLegacy::getInstance('Languages');
$app        = JFactory::getApplication();
$task       = $app->input->get('task');

$controller->execute($task);
$controller->redirect();
