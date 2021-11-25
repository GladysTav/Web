<?php
/**
 * @version     2.0.0
 * @package     sellacious
 *
 * @copyright   Copyright (C) 2012-2020 Bhartiy Web Technologies. All rights reserved.
 * @license     SPL Sellacious Private License; see http://www.sellacious.com/spl.html
 * @author      Aditya Chakraborty <info@bhartiy.com> - http://www.bhartiy.com
 */

defined('_JEXEC') or die;

$controller = JControllerLegacy::getInstance('Sellaciousdelivery');
$controller->execute(JFactory::getApplication()->input->get('task'));
$controller->redirect();
