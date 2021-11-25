<?php

/**
 * @package   JD Simple Contact Form
 * @author    JoomDev https://www.joomdev.com
 * @copyright Copyright (C) 2009 - 2018 JoomDev.
 * @license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 or Later
 */
// no direct access
defined('_JEXEC') or die;

require_once dirname(__FILE__) . '/helper.php';

$document = JFactory::getDocument();
$document->addScript('//parsleyjs.org/dist/parsley.min.js');
$document->addStylesheet(JURI::root() . 'media/mod_jdsimplecontactform/assets/css/style.css');

$layout = $params->get('layout', 'default');
require JModuleHelper::getLayoutPath('mod_jdsimplecontactform', $layout);
