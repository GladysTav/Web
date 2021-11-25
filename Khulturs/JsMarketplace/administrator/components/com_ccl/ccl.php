<?php
/**
 * @version    CVS: 1.0.0
 * @package    Com_Ccl
 * @author     Component Creator <info@component-creator.com>
 * @copyright  2016 Component Creator
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access
defined('_JEXEC') or die;

// Access check.
if (!JFactory::getUser()->authorise('core.manage', 'com_ccl'))
{
	throw new Exception(JText::_('JERROR_ALERTNOAUTHOR'));
}

// Include dependancies
jimport('joomla.application.component.controller');

JLoader::registerPrefix('Ccl', JPATH_COMPONENT_ADMINISTRATOR);

$controller = JControllerLegacy::getInstance('Ccl');
$controller->execute(JFactory::getApplication()->input->get('task'));
$controller->redirect();
