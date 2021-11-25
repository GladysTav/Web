<?php
/**
 * @version     1.7.4
 * @package     sellacious
 *
 * @copyright   Copyright (C) 2012-2019 Bhartiy Web Technologies. All rights reserved.
 * @license     SPL Sellacious Private License; see http://www.sellacious.com/spl.html
 * @author      Izhar Aazmi <info@bhartiy.com> - http://www.bhartiy.com
 */
// no direct access
defined('_JEXEC') or die;

$data     = $this->getProperties();
$tOptions = array('view' => $this, 'options' => array('filtersHidden' => true));
$html     = array(
	'toolbar' => JLayoutHelper::render('joomla.searchtools.default', $tOptions),
	'head'    => $this->loadTemplate('head'),
	'body'    => $this->loadTemplate('body'),
);

$data['name']      = $this->getName();
$data['view']      = &$this;
$data['html']      = &$html;
$data['view_item'] = 'menu';

$options = array('client' => 2, 'debug' => 0);

echo JLayoutHelper::render('com_sellacious.view.list', $data, '', $options);