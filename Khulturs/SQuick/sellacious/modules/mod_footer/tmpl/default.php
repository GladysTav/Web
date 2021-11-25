<?php
/**
 * @version     2.0.0
 * @package     sellacious
 *
 * @copyright   Copyright (C) 2012-2020 Bhartiy Web Technologies. All rights reserved.
 * @license     SPL Sellacious Private License; see http://www.sellacious.com/spl.html
 * @author      Izhar Aazmi <info@bhartiy.com> - http://www.bhartiy.com
 */

use Joomla\Registry\Registry;
use Sellacious\Access\AccessHelper;

defined('_JEXEC') or die;

jimport('sellacious.loader');

$helper    = SellaciousHelper::getInstance();
$isAllowed = AccessHelper::allow('app.manage');
$license   = $helper->core->getLicense();

// Get site template
$tpl   = array('list.select' => 'a.template, a.title', 'list.from' => '#__template_styles', 'client_id' => '0', 'home' => 1);
$style = $helper->config->loadObject($tpl);
$style = is_object($style) ? sprintf('%s (%s)', $style->template, $style->title) : 'NA';

$license->set('template', $style);
unset($tpl, $style);

/** @var  Registry $params */
require JModuleHelper::getLayoutPath('mod_footer', $isAllowed ? 'default_auto' : 'default_basic');
