<?php
/**
 * @version     2.0.0
 * @package     sellacious
 *
 * @copyright   Copyright (C) 2012-2020 Bhartiy Web Technologies. All rights reserved.
 * @license     SPL Sellacious Private License; see http://www.sellacious.com/spl.html
 * @author      Izhar Aazmi <info@bhartiy.com> - http://www.bhartiy.com
 */
// no direct access
use Joomla\Utilities\ArrayHelper;

defined('_JEXEC') or die;

/** @var  SellaciousView  $this */
/** @var  stdClass[]  $tplData */
$slabs = $tplData;

$currency = $this->helper->currency->getGlobal('code_3');
$filename = $this->state->get('shippingrule.title', 'shipping-slabs');

if (!headers_sent($file, $line))
{
	header('content-type: text/csv');
	header('content-disposition: attachment; filename="' . htmlspecialchars($filename) . '.csv"');
}
else
{
	echo 'Headers already sent at ' . $file . ':' . $line . '.';

	return;
}

$rows   = array();
$rows[] = array('Min', 'Max', 'Origin_Country', 'Origin_State', 'Origin_Zip', 'Delivery_Country', 'Delivery_State', 'Delivery_Zip', 'Shipping');

foreach ($slabs as $i => $record)
{
	$record    = (array)$record;
	$min       = ArrayHelper::getValue($record, 'min', 0, 'float');
	$max       = ArrayHelper::getValue($record, 'max', 0, 'float');
	$o_country = ArrayHelper::getValue($record, 'origin_country', 0, 'int');
	$o_state   = ArrayHelper::getValue($record, 'origin_state', 0, 'int');
	$o_zip     = ArrayHelper::getValue($record, 'origin_zip', 0, 'string');
	$country   = ArrayHelper::getValue($record, 'country', 0, 'int');
	$state     = ArrayHelper::getValue($record, 'state', 0, 'int');
	$zip       = ArrayHelper::getValue($record, 'zip', 0, 'string');
	$price     = ArrayHelper::getValue($record, 'price', 0, 'float');

	try
	{
		$country   = $this->helper->location->loadResult(array('id' => $country, 'list.select' => 'a.iso_code'));
		$o_country = $this->helper->location->loadResult(array('id' => $o_country, 'list.select' => 'a.iso_code'));
	}
	catch (Exception $e)
	{
		$country   = '';
		$o_country = '';
	}

	try
	{
		$state   = $this->helper->location->loadResult(array('id' => $state, 'list.select' => 'a.iso_code'));
		$o_state = $this->helper->location->loadResult(array('id' => $o_state, 'list.select' => 'a.iso_code'));
	}
	catch (Exception $e)
	{
		$state   = '';
		$o_state = '';
	}

	try
	{
		$zip   = $this->helper->location->getTitle($zip);
		$o_zip = $this->helper->location->getTitle($o_zip);
	}
	catch (Exception $e)
	{
		$zip   = '';
		$o_zip = '';
	}

	$rows[] = array($min, $max, $o_country, $o_state, $o_zip, $country, $state, $zip, $price);
}


echo $this->helper->core->array2csv($rows);
