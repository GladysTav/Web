<?php
/**
 * @version     2.0.0
 * @package     Sellacious.Product
 * @subpackage  Layout
 *
 * @copyright   Copyright (C) 2005 - 2019 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Saurabh Sabharwal <info@bhartiy.com> - http://www.bhartiy.com
 */
defined('_JEXEC') or die('Restricted access');

use Joomla\Registry\Registry;
use Joomla\Utilities\ArrayHelper;
use Sellacious\Config\ConfigHelper;

/**
 * @var   array     $displayData
 * @var   int       $id
 * @var   stdClass  $items
 * @var   Registry  $params
 * @var   array     $context
 * @var   string    $layout
 * @var   string    $blockLayout
 */
extract($displayData);

$contextId = isset($id) ? $id : 0;
$items     = isset($items) ? $items : array();
$params    = isset($params) ? new Registry($params) : new Registry;
$context   = isset($context) ? (array) $context : array('products');
$layout    = isset($layout) ? $layout : 'grid';

$defaults = (object) array(
	'categories'                => array(),
	'class_sfx'                 => '',
	'display_delivery_slots'    => '1',
	'displayaddtocartbtn'       => '1',
	'displaybuynowbtn'          => '1',
	'displaycomparebtn'         => '1',
	'displayquickviewbtn'       => '1',
	'displayratings'            => '1',
	'displayproductprice'       => '1',
	'featurelist'               => '1',
	'header_class'              => '',
	'header_tag'                => 'h3',
	'module_tag'                => 'div',
	'section_desc'              => '',
	'section_title'             => '',
	'splcategory'               => '',
	'standout_special_category' => '1',
	'style'                     => '0',
	'total_products'            => '50',
	'layout'                    => 'grid',
);

foreach ($defaults as $key => $value)
{
	$params->def($key, $value);
}

try
{
	$app    = JFactory::getApplication();
	$doc    = JFactory::getDocument();
	$helper = SellaciousHelper::getInstance();
	$config = ConfigHelper::getInstance('com_sellacious');
}
catch (Exception $e)
{
}

/** @var  JDocumentHtml $doc */
$blockStyle = isset($blockLayout) ? $blockLayout : $config->get('product-block-layout', 'default');

$splStyleCss = $helper->splCategory->getCss('.product-block-wrap');

$doc->addStyleDeclaration($splStyleCss);

JHtml::_('jquery.framework');
JHtml::_('ctech.bootstrap');

JHtml::_('script', 'sellacious/vue.min.js', array('relative' => true, 'version' => S_VERSION_CORE));
JHtml::_('script', 'sellacious/vue.product.block.js', array('relative' => true, 'version' => S_VERSION_CORE));
JHtml::_('script', 'com_sellacious/util.product-block.height.js', array('relative' => true, 'version' => S_VERSION_CORE));
JHtml::_('script', 'sellacious/owl.carousel.js', array('relative' => true, 'version' => S_VERSION_CORE));
JHtml::_('stylesheet', 'sellacious/owl.carousel.min.css', array('relative' => true, 'version' => S_VERSION_CORE));
JHtml::_('stylesheet', 'sellacious/owl-custom.css', array('relative' => true, 'version' => S_VERSION_CORE));

JHtml::_('script', 'com_sellacious/util.cart.aio.js', array('relative' => true, 'version' => S_VERSION_CORE));

JHtml::_('script', 'com_sellacious/util.modal.js', array('relative' => true, 'version' => S_VERSION_CORE));
JHtml::_('stylesheet', 'com_sellacious/util.modal.css', array('relative' => true, 'version' => S_VERSION_CORE));

JHtml::_('stylesheet', 'com_sellacious/font-awesome.min.css', array('relative' => true, 'version' => S_VERSION_CORE));

JHtml::_('script', 'com_sellacious/fe.view.sellacious.js', array('relative' => true, 'version' => S_VERSION_CORE));
JHtml::_('stylesheet', 'com_sellacious/fe.view.cart.aio.css', array('relative' => true, 'version' => S_VERSION_CORE));
JHtml::_('stylesheet', 'com_sellacious/fe.view.cart.css', array('relative' => true, 'version' => S_VERSION_CORE));
JHtml::_('stylesheet', 'com_sellacious/util.rating.css', array('relative' => true, 'version' => S_VERSION_CORE));

JHtml::_('stylesheet', "sellacious/product.block-{$blockStyle}.css", array('relative' => true, 'version' => S_VERSION_CORE));
JHtml::_('stylesheet', 'sellacious/product.quickview.css', array('relative' => true, 'version' => S_VERSION_CORE));

JText::script('COM_SELLACIOUS_PRODUCTS_ADD_TO_CART');
JText::script('COM_SELLACIOUS_PRODUCTS_BUY_NOW');
JText::script('COM_SELLACIOUS_PRODUCTS_COMPARE');
JText::script('COM_SELLACIOUS_PRODUCTS_QUICK_VIEW');
JText::script('COM_SELLACIOUS_PRODUCTS_DELIVERY_SLOTS');
JText::script('COM_SELLACIOUS_PRODUCTS_OFFER');
JText::script('COM_SELLACIOUS_PRODUCT_PRICING_LOGIN_TO_VIEW');
JText::script('COM_SELLACIOUS_PRODUCT_PRICING_TYPE_CALL_CALL_US');
JText::script('COM_SELLACIOUS_PRODUCT_PRICING_TYPE_EMAIL_EMAIL_US');
JText::script('COM_SELLACIOUS_PRODUCT_PRICING_TYPE_QUERYFORM_OPEN_QUERY_FORM');
JText::script('COM_SELLACIOUS_PRODUCTS_IN_STOCK');
JText::script('COM_SELLACIOUS_PRODUCTS_OUT_OF_STOCK');
JText::script('COM_SELLACIOUS_PRODUCTS_GO_TO_PRODUCT');
JText::script('COM_SELLACIOUS_PRODUCTS_QUANTITY');
JText::script('COM_SELLACIOUS_PRODUCTS_CATEGORIES');
JText::script('COM_SELLACIOUS_PRODUCT_PRICE_FREE');
JText::script('COM_SELLACIOUS_PRODUCT_CHECKOUT_QUESTIONS_FORM_TITLE');
JText::script('COM_SELLACIOUS_PRODUCT_STOCK_IN_CART');

$uri       = JUri::getInstance();
$colWidths = array(
	'2' => 'ctech-col-sm-6 ctech-col-6',
	'3' => 'ctech-col-sm-4 ctech-col-6',
	'4' => 'ctech-col-md-3 ctech-col-sm-4 ctech-col-6',
	'5' => 'ctech-col-c5 ctech-col-md-3 ctech-col-sm-4 ctech-col-6',
);

$configurations = array();
$configurations['blockStyle']            = $blockStyle;
$configurations['context']               = $context;
$configurations['module_id']             = $contextId;
$configurations['mod_params']            = $params;
$configurations['login_url']             = JRoute::_('index.php?option=com_users&view=login&return=' . base64_encode($uri), false);
$configurations['product_detail_page']   = $config->get('product_detail_page');
$configurations['allow_rating']          = $config->get('product_rating');
$configurations['show_zero_rating']      = $config->get('show_zero_rating');
$configurations['login_to_see_price']    = $config->get('login_to_see_price', 0);
$configurations['disable_checkout']      = $config->get('disable_checkout');
$configurations['display_stock']         = $config->get('frontend_display_stock');
$configurations['security']              = $config->get('contact_spam_protection');
$configurations['price_display']         = $config->get('product_price_display');
$configurations['image_height']          = $config->get('products_image_height', 220);
$configurations['products_image_size']   = $config->get('products_image_size_adjust', 'contain');
$configurations['show_thumbs']           = $config->get('products_block_thumbs');
$configurations['rating_pages']          = (array) $config->get('product_rating_display');
$configurations['features_pages']        = (array) $config->get('product_features_list');
$configurations['compare_pages']         = (array) $config->get('product_compare_display');
$configurations['cart_pages']            = (array) $config->get('product_add_to_cart_display');
$configurations['buynow_pages']          = (array) $config->get('product_buy_now_display');
$configurations['show_modal']            = (array) $config->get('product_quick_detail_pages');
$configurations['allowed_price_display'] = (array) $config->get('allowed_price_display');
$configurations['price_d_pages']         = (array) $config->get('product_price_display_pages');
$configurations['c_currency']            = $helper->currency->current('code_3');
$configurations['widthClasses']          = $layout === 'grid' ? ArrayHelper::getValue($colWidths, $config->get('products_cols', 4)) : '';


$view = $app->input->getString('view');

if (in_array('module', $context))
{
	$configurations['show_price'] = true;
}
elseif ($view == 'store' || $view == 'wishlist')
{
	$configurations['show_price'] = in_array('products', $configurations['price_d_pages']);
}
elseif ($view === 'categories' || $view === 'products')
{
	$configurations['show_price'] = in_array($view, $configurations['price_d_pages']);
}
else
{
	$configurations['show_price'] = true;
}

JHtml::_('ctech.vueTemplate', "ctech-modal-layout", __DIR__ . "/modal.vue");

// To be moved to a separate function when ready
include __DIR__ . '/products.php';

// This should be overridable
JHtml::_('ctech.vueTemplate', "vue-product-block-{$blockStyle}", __DIR__ . "/designs/{$blockStyle}/block-{$blockStyle}.vue");
JHtml::_('ctech.vueTemplate', 'vue-product-quick-view', __DIR__ . '/QuickView.vue');

$doc->addScriptOptions('sellacious.product.data.module-' . $contextId, $products);
$doc->addScriptOptions('sellacious.product.params.module-' . $contextId, $configurations);

$blockClasses = '';

if ($layout === 'carousel')
{
	$blockClasses .= 'product-carousel owl-carousel';
	$opts         = array(
		'autoplay'   => (bool) $params->get('autoplay'),
		'pause'      => (bool) $params->get('hover_pause'),
		'speed'      => (int) $params->get('autoplayspeed', 3000),
		'margin'     => (int) $params->get('gutter', 8),
		'responsive' => array(
			0    => $params->get('responsive0to500', 1),
			500  => $params->get('responsive500', 2),
			992  => $params->get('responsive992', 3),
			1200 => $params->get('responsive1200', 3),
			1400 => $params->get('responsive1400', 3),
		),
		'rtl'        => $doc->direction === 'rtl' ? true : false,
	);
	JHtml::_('script', 'com_sellacious/fe.owl.carousel.js', null, true);

}
elseif ($layout === 'grid')
{
	$blockClasses .= 'product-grid';
}
elseif ($layout === 'list')
{
	$blockClasses .= 'product-list';
}

?>
<div class="ctech-wrapper">
	<div class="ctech-container-fluid">
		<div class="product-blocks-container <?php echo $blockClasses; ?> ctech-row" data-module="<?php echo $contextId; ?>"
			<?php if ($layout === 'carousel'): ?> data-owl-carousel="<?php echo htmlspecialchars(json_encode($opts)); ?>"<?php endif; ?>>
			<?php include __DIR__ . '/blocks.vue'; ?>
		</div>
	</div>
</div>
