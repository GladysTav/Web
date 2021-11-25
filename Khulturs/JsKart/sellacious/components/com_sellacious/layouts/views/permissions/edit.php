<?php
/**
 * @version     2.0.0
 * @package     sellacious
 *
 * @copyright   Copyright (C) 2012-2020 Bhartiy Web Technologies. All rights reserved.
 * @license     SPL Sellacious Private License; see http://www.sellacious.com/spl.html
 * @author      Izhar Aazmi <info@bhartiy.com> - http://www.bhartiy.com
 */

defined('_JEXEC') or die;

JHtml::_('jquery.framework');
JHtml::_('script', 'com_sellacious/plugin/cookie/jquery.cookie.js', array('version' => S_VERSION_CORE, 'relative' => true));
JHtml::_('script', 'com_sellacious/view.permissions.js', array('version' => S_VERSION_CORE, 'relative' => true));
JHtml::_('stylesheet', 'com_sellacious/view.component.css', array('version' => S_VERSION_CORE, 'relative' => true));
JHtml::_('stylesheet', 'com_sellacious/view.permissions.css', array('version' => S_VERSION_CORE, 'relative' => true));

// If we were passed catid in the URL, its consumed now. Do not carry it over!
JUri::getInstance()->delVar('catid');

JFactory::getDocument()->addStyleDeclaration('.select-small { width:100%; max-width:350px; }');

echo $this->loadTemplate('permissions');
