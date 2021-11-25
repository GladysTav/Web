<?php
/**
 * @version     1.0.1
 * @package     mod_sellacious_offeredproducts
 *
 * @copyright   Copyright (C) 2017. Bhartiy Web Technologies. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Mohd Kareemuddin <info@bhartiy.com> - http://www.bhartiy.com
 */

// no direct access
defined('_JEXEC') or die('Restricted access');

jimport('sellacious.loader');

$helper = SellaciousHelper::getInstance();

/** @var $params */
$class_sfx   		= $params->get('class_sfx', '');
$modtitle           = $params->get('modtitle', '');
$useCatsOrProds     = $params->get('usecatsorprods', '1');
$style      	 	= $params->get('layout_style', '0');
$productList 		= $params->get('products', '');
$catList 			= $params->get('categories', '');
$ordering 			= $params->get('ordering', '4');
$orderBy 			= $params->get('orderby', 'DESC');

if ($style == 1 || $style == 2)
{
	$mainProductId = $params->get('mainproduct', '0');
}

if (!$style)
{
	return;
}
$mainProduct = new stdClass();

if ($useCatsOrProds > 0 && $style < 3) {
    if (isset($mainProductId)) {
        $filters = array();
        $filters['list.select'][] = ' a.id, a.title, p.product_price, p.list_price, p.sdate, p.edate, p.seller_uid';

        $filters['list.join'] = array(
            array('inner', '#__sellacious_product_prices AS p ON p.product_id = a.id'),
        );

        $filters['list.where'][] = 'a.id = ' . (int)$mainProductId;
	    $filters['list.where'][] = 'a.state = 1';
	    $filters['list.where'][] = 'p.edate > NOW()';
	    $filters['list.where'][] = 'p.qty_min <= 1';
        $mainProduct = $helper->product->loadObject($filters);

    }
}

$filters    = array();

if ($useCatsOrProds > 0 ) {
	$filters['list.select'][] = ' a.id, a.title, p.product_price, p.list_price, p.sdate, p.edate, p.seller_uid';
}else{
	$filters['list.select'][] = ' a.id, a.title, min(p.product_price) as product_price, p.list_price, p.sdate, p.edate, p.seller_uid';

}

$filters['list.join'][] = array('inner', '#__sellacious_product_prices AS p ON p.product_id = a.id');

if ($useCatsOrProds == 0 )
{
	$filters['list.join'][] = array('inner', '#__sellacious_product_categories AS pc ON pc.product_id = a.id');
}
if ($useCatsOrProds > 0 ){

	if ($productList)
	{
		$filters['list.where'][] = 'a.id IN (' . implode(",", $productList) . ')';
	}
}
else
{
	if ($catList)
	{
		$filters['list.where'][] = 'pc.category_id = ' . (int) $catList;
	}
}
$filters['list.group'][] = 'p.product_id ';
$filters['list.where'][] = 'a.state = 1';
$filters['list.where'][] = 'p.edate >= NOW()';
$filters['list.where'][] = 'p.qty_min <= 1';

switch ($ordering) {
    case "1":
        $ord = 'a.title '. $orderBy;
        break;
    case "2":
        $ord = 'p.product_price '. $orderBy;
        break;
    case "3":
        $ord = 'a.created '. $orderBy;
        break;
    case "4":
        $ord = 'rand() ';
        break;
    default:
        $ord = 'rand() ';
}

if ($style == 1){
$limit = '4';
}
elseif ($style == 2){
$limit = '6';
}
elseif ($style == 3){
$limit = '16';
}


$filters['list.order'][] = $ord;
$filters['list.start']   = 0;
$filters['list.limit']   = $useCatsOrProds ? $limit :  ($style<3) ? $limit+1 : $limit;

if ($useCatsOrProds == 1 && !empty($productList) ) {
	$products = $helper->product->loadObjectList($filters);
}elseif($useCatsOrProds == 0){
	$products = $helper->product->loadObjectList($filters);
}else{
	$products    = array();
}


if (($style == 3) && empty($products))
{
	return;
}
elseif($style < 3 && empty($mainProduct))
{
	return;
}
if ($useCatsOrProds == 0 ) {
	if ($style < 3 )
	{
		$mainProduct = array_shift($products);
	}
}

$g_currency = $helper->currency->getGlobal('code_3');
$c_currency = $helper->currency->current('code_3');

$layout = $style ? 'style' . $style : 'default';

require(JModuleHelper::getLayoutPath('mod_sellacious_offeredproducts', $layout));
