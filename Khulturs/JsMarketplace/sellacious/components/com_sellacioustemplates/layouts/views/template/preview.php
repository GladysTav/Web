<?php
/**
 * @version     1.7.4
 * @package     sellacious
 *
 * @copyright   Copyright (C) 2012-2019 Bhartiy Web Technologies. All rights reserved.
 * @license     SPL Sellacious Private License; see http://www.sellacious.com/spl.html
 * @author      Aditya Chakraborty <info@bhartiy.com> - http://www.bhartiy.com
 */
// no direct access
defined('_JEXEC') or die;

echo $this->app->getUserState('com_sellacioustemplates.preview');

// Remove from session as already used
$this->app->setUserState($this->_option . '.preview', null);
$this->app->setUserState($this->_option . '.preview_context', null);
