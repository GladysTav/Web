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

JHtml::addIncludePath(JPATH_COMPONENT . '/helpers/html');

$tOptions = array('view' => $this, 'options' => array('filtersHidden' => true));
$html     = array(
	'toolbar' => JLayoutHelper::render('joomla.searchtools.default', $tOptions),
	'head'    => $this->loadTemplate('head'),
//	'batch'   => $this->loadTemplate('batch'),
	'body'    => array(),
);

foreach ($this->items as $i => $item)
{
	$this->current_item = $i;
	$html['body'][$i]   = $this->loadTemplate('body');
}

unset($this->current_item);

$data = $this->getProperties();

$data['name']      = $this->getName();
$data['view']      = &$this;
$data['html']      = &$html;
$data['view_item'] = 'item';
$data['component'] = 'com_menus';

$options = array('client' => 2, 'debug' => 0);

echo JLayoutHelper::render('com_sellacious.view.nestedlist', $data, '', $options);