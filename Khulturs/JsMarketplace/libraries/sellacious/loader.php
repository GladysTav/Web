<?php
/**
 * @version     1.7.4
 * @package     sellacious
 *
 * @copyright   Copyright (C) 2012-2019 Bhartiy Web Technologies. All rights reserved.
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
