<?php
/**
 * @version    CVS: 1.0.0
 * @package    Com_Ccl
 * @author     Component Creator <info@component-creator.com>
 * @copyright  2016 Component Creator
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

// Include dependancies
jimport('joomla.application.component.controller');

JLoader::registerPrefix('Ccl', JPATH_COMPONENT);
JLoader::register('CclController', JPATH_COMPONENT . '/controller.php');


// Execute the task.
$controller = JControllerLegacy::getInstance('Ccl');
$controller->execute(JFactory::getApplication()->input->get('task'));
$controller->redirect();
