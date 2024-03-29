<?php
/**
 * @version     2.0.0
 * @package     com_sellaciousreporting
 *
 * @copyright   Copyright (C) 2012-2020 Bhartiy Web Technologies. All rights reserved.
 * @license     SPL Sellacious Private License; see http://www.sellacious.com/spl.html
 * @author      Aditya Chakraborty <info@bhartiy.com> - http://www.bhartiy.com
 */

defined('_JEXEC') or die;

$html = array(
	'toolbar' => JLayoutHelper::render('joomla.searchtools.default', array('view' => $this)),
	'head'    => $this->loadTemplate('head'),
	'body'    => $this->loadTemplate('body'),
);

$data = $this->getProperties();

$data['name']      = $this->getName();
$data['view']      = &$this;
$data['html']      = &$html;
$data['view_item'] = 'reporting';

$options = array('client' => 2, 'debug' => 0);

echo JLayoutHelper::render('com_sellacious.view.list', $data, '', $options);
