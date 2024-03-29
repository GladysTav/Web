<?php
/**
 * @version     1.7.4
 * @package     Sellacious Latest Products Module
 *
 * @copyright   Copyright (C) 2012-2019 Bhartiy Web Technologies. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Bhavika Matariya <info@bhartiy.com> - http://www.bhartiy.com
 */

// no direct access
defined('_JEXEC') or die('Restricted access');

use Joomla\Registry\Registry;

// Include the helper functions only once
JLoader::register('ModSellaciousLatestProducts', __DIR__ . '/helper.php');
jimport('sellacious.loader');

$db     = JFactory::getDBO();
$me     = JFactory::getUser();
$helper = SellaciousHelper::getInstance();

$c_cat          = $helper->client->loadResult(array('list.select' => 'category_id', 'user_id' => $me->id));
$c_currency     = $helper->currency->current('code_3');
$default_seller = $helper->config->get('default_seller', -1);
$multi_seller   = $helper->config->get('multi_seller', 0);
$allowed        = $helper->config->get('allowed_product_type');
$allow_package  = $helper->config->get('allowed_product_package');

$login_to_see_price = $helper->config->get('login_to_see_price', 0);
$current_url        = JUri::getInstance()->toString();
$login_url          = JRoute::_('index.php?option=com_users&view=login&return=' . base64_encode($current_url), false);

$class_sfx           = $params->get('class_sfx', '');
$section_title       = $params->get('section_title', '');
$section_desc        = $params->get('section_desc', '');
$limit               = $params->get('total_products', '50');
$categories          = $params->get('categories', '');
$splCategory         = $params->get('splcategory', 0);
$splStandOut         = $params->get('standout_special_category', 0);
$prods               = $params->get('products', '');
$featurelist         = $params->get('featurelist', '1');
$displayratings      = $params->get('displayratings', '1');
$displaycomparebtn   = $params->get('displaycomparebtn', '1');
$displayaddtocartbtn = $params->get('displayaddtocartbtn', '1');
$displaybuynowbtn    = $params->get('displaybuynowbtn', '1');
$displayquickviewbtn = $params->get('displayquickviewbtn', '1');
$layout              = $params->get('layout', 'grid');
$autoplayopt         = $params->get('autoplay', '0');
$autoplayspeed       = $params->get('autoplayspeed', '3000');
$gutter              = $params->get('gutter', '8');
$responsive0to500    = $params->get('responsive0to500', '1');
$responsive500       = $params->get('responsive500', '2');
$responsive992       = $params->get('responsive992', '3');
$responsive1200      = $params->get('responsive1200', '4');
$responsive1400      = $params->get('responsive1400', '4');
$filters             = array();
$latestProductsSpl   = array();

$jInput     = JFactory::getApplication()->input;
$layoutview = $jInput->getString('view');

$products = $helper->product->getModProducts($params);

if (empty($products))
{
	return;
}

$styles  = array();
$splList = array();

if ($splCategory)
{
	$splList = $helper->splCategory->loadObjectList($splCategory);
}
elseif ($splStandOut)
{
	$splList = $helper->splCategory->loadObjectList(array('list.select' => 'a.id, a.params', 'list.where' => array('a.state = 1 AND a.level > 0')));
}

if (!empty($splList))
{
	foreach ($splList as $spl)
	{
		$style     = '';
		$splparams = new Registry($spl->params);

		// New or old format?
		$css = isset($splparams['styles']) ? (array) $splparams->get('styles') : $splparams;

		foreach ($css as $css_k => $css_v)
		{
			$style .= "$css_k: $css_v;";
		}

		$styles[$spl->id] = ".mod-sellacious-latestproducts .spl-cat-$spl->id { $style }";
	}

	$doc = JFactory::getDocument();
	$doc->addStyleDeclaration(implode("\n", $styles));
}

require JModuleHelper::getLayoutPath('mod_sellacious_latestproducts', $layout);
