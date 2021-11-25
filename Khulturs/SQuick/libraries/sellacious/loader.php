<?php
/**
 * @version     2.0.0
 * @package     sellacious
 *
 * @copyright   Copyright (C) 2012-2020 Bhartiy Web Technologies. All rights reserved.
 * @license     SPL Sellacious Private License; see http://www.sellacious.com/spl.html
 * @author      Izhar Aazmi <info@bhartiy.com> - http://www.bhartiy.com
 */
// No direct access
defined('_JEXEC') or die;

/**
 * Class loader initialization for sellacious library
 */
JLoader::registerPrefix('Sellacious', __DIR__);
JLoader::register('SUtils', __DIR__ . '/utilities/utils.php');

JLoader::registerNamespace('Sellacious', __DIR__ . '/objects');
JLoader::registerNamespace('Psr', __DIR__ . '/objects');
JLoader::registerNamespace('TeamTNT\\TNTSearch\\', __DIR__ . '/objects/teamtnt/tntsearch/src', false, false, 'psr4');

if (!class_exists('finfo'))
{
	// Typo3 Phar stream wrapper unnecessarily uses finfo
	JLoader::register('finfo', __DIR__ . '/utilities/finfo.php');
}

JLoader::register('TCPDF', __DIR__ . '/objects/tcpdf/tcpdf.php');

if (class_exists('SellaciousHelper'))
{
	$helper = SellaciousHelper::getInstance();

	$helper->core->registerPharPsr4('PhpOffice', 'phar://' . __DIR__ . '/objects/PhpOffice/PhpSpreadsheet.phar');
}

/* Register ctech bootstrap Modal*/
JHtml::register('ctechBootstrap.modal', array('Sellacious\Html\Bootstrap\Modal', 'modal'));

/* Register ctech bootstrap Accordion*/
JHtml::register('ctechBootstrap.startAccordion', array('Sellacious\Html\Bootstrap\Accordion', 'startAccordion'));
JHtml::register('ctechBootstrap.addAccordionSlide', array('Sellacious\Html\Bootstrap\Accordion', 'addAccordionSlide'));
JHtml::register('ctechBootstrap.endAccordionSlide', array('Sellacious\Html\Bootstrap\Accordion', 'endAccordionSlide'));
JHtml::register('ctechBootstrap.endAccordion', array('Sellacious\Html\Bootstrap\Accordion', 'endAccordion'));

/* Register ctech bootstrap Tabs*/
JHtml::register('ctechBootstrap.startTabs', array('Sellacious\Html\Bootstrap\Tabs', 'startTabs'));
JHtml::register('ctechBootstrap.addTab', array('Sellacious\Html\Bootstrap\Tabs', 'addTab'));
JHtml::register('ctechBootstrap.endTab', array('Sellacious\Html\Bootstrap\Tabs', 'endTab'));
JHtml::register('ctechBootstrap.endTabs', array('Sellacious\Html\Bootstrap\Tabs', 'endTabs'));

/* Register ctech scripts */
JHtml::register('ctech.select2', array('Sellacious\Html\Html', 'select2'));
JHtml::register('ctech.bootstrap', array('Sellacious\Html\Html', 'bootstrap'));
JHtml::register('ctech.vueTemplate', array('Sellacious\Html\Html', 'vueTemplate'));
