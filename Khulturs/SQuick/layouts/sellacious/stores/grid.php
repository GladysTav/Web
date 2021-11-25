<?php
/**
 * @version     2.0.0
 * @package     Sellacious.Store
 * @subpackage  Layout
 *
 * @copyright   Copyright (C) 2005 - 2019 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Versha Verma <info@bhartiy.com> - http://www.bhartiy.com
 */
defined('_JEXEC') or die('Restricted access');

use Joomla\Registry\Registry;
use Joomla\Utilities\ArrayHelper;
use Sellacious\Config\ConfigHelper;

/**
 * @var  array    $displayData
 * @var  int      $id
 * @var  stdClass $items
 * @var  Registry $params
 * @var  array    $context
 * @var  string   $layout
 * @var  string   $blockLayout
 */
extract($displayData);

$contextId = isset($id) ? $id : 0;
$items     = isset($items) ? $items : array();
$params    = isset($params) ? new Registry($params) : new Registry;
$context   = isset($context) ? (array) $context : array('products');
$layout    = isset($layout) ? $layout : 'grid';

$defaults = (object) array(
	'categories'            => array(),
	'class_sfx'             => '',
	'section_desc'          => '1',
	'total_records'         => '50',
	'category_id'           => '1',
	'display_product_count' => '1',
	'display_ratings'       => '1',
	'layout'                => 'grid',
	'ordering'              => '3',
	'orderby'               => 'DESC',
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
$blockStyle = isset($blockLayout) ? $blockLayout : $config->get('stores-block-layout', 'default');

JHtml::_('jquery.framework');
JHtml::_('ctech.bootstrap');

JHtml::_('script', 'sellacious/vue.min.js', array('relative' => true, 'version' => S_VERSION_CORE));
JHtml::_('script', 'sellacious/vue.stores.block.js', array('relative' => true, 'version' => S_VERSION_CORE));
JHtml::_('script', 'sellacious/vue.stores.block.components.js', array('relative' => true, 'version' => S_VERSION_CORE));

JHtml::_('script', 'sellacious/owl.carousel.js', array('relative' => true, 'version' => S_VERSION_CORE));
JHtml::_('stylesheet', 'sellacious/owl.carousel.min.css', array('relative' => true, 'version' => S_VERSION_CORE));
JHtml::_('stylesheet', 'sellacious/owl-custom.css', array('relative' => true, 'version' => S_VERSION_CORE));

JHtml::_('script', 'com_sellacious/util.cart.aio.js', array('relative' => true, 'version' => S_VERSION_CORE));

JHtml::_('stylesheet', 'com_sellacious/font-awesome.min.css', array('relative' => true, 'version' => S_VERSION_CORE));

JHtml::_('stylesheet', "sellacious/stores.block-{$blockStyle}.css", array('relative' => true, 'version' => S_VERSION_CORE));

$uri       = JUri::getInstance();
$colWidths = array(
	'2' => 'ctech-col-sm-6 ctech-col-6',
	'3' => 'ctech-col-sm-4 ctech-col-6',
	'4' => 'ctech-col-md-3 ctech-col-sm-4 ctech-col-6',
	'5' => 'ctech-col-c5 ctech-col-md-3 ctech-col-sm-4 ctech-col-6',
);

JText::script('COM_SELLACIOUS_STORES_STORE_TITLE');
JText::script('COM_SELLACIOUS_STORES_STORE_PRODUCT_COUNT');
JText::script('COM_SELLACIOUS_STORES_RATING');
JText::script('COM_SELLACIOUS_STORES_PRODUCT');
JText::script('COM_SELLACIOUS_STORES_PRODUCT_REVIEWS');

$configurations = array();

$configurations['blockStyle']              = $blockStyle;
$configurations['context']                 = $context;
$configurations['module_id']               = $contextId;
$configurations['mod_params']              = $params;
$configurations['login_url']               = JRoute::_('index.php?option=com_users&view=login&return=' . base64_encode($uri), false);
$configurations['store_title']             = $config->get('store_title');
$configurations['store_product_count']     = $config->get('show_store_product_count');
$configurations['show_store_rating']       = $config->get('show_store_rating', 0);
$configurations['show_zero_rating']        = $config->get('show_zero_rating', 1);
$configurations['allow_ratings_for']       = $config->get('allow_ratings_for');
$configurations['store_logo_image_size']   = $config->get('store_logo_image_size', "cover");
$configurations['store_logo_image_height'] = $config->get('store_logo_image_height', "220");
$configurations['widthClasses']            = $layout === 'grid' ? ArrayHelper::getValue($colWidths, $config->get('stores_cols', 4)) : '';

$stores = $helper->seller->parseStoresForLayout($items);

// This should be overridable
JHtml::_('ctech.vueTemplate', "vue-stores-block-{$blockStyle}", __DIR__ . "/designs/{$blockStyle}/block-{$blockStyle}.vue");

$doc->addScriptOptions('sellacious.stores.data.module-' . $contextId, $stores);
$doc->addScriptOptions('sellacious.stores.params.module-' . $contextId, $configurations);

$blockClasses = '';

if ($layout === 'carousel')
{
	$blockClasses .= 'stores-carousel owl-carousel';
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
		'rtl'        => $doc->direction === 'rtl',
	);
	JHtml::_('script', 'com_sellacious/fe.owl.carousel.js', null, true);

}
elseif ($layout === 'grid')
{
	$blockClasses .= 'stores-grid';
}

?>
<div class="ctech-wrapper">
	<div class="ctech-container-fluid">
		<div class="store-blocks-container <?php echo $blockClasses; ?> ctech-row" data-module="<?php echo $contextId; ?>"
			<?php if ($layout === 'carousel'): ?> data-owl-carousel="<?php echo htmlspecialchars(json_encode($opts)); ?>"<?php endif; ?>>
			<?php include __DIR__ . '/storesblock.vue'; ?>
		</div>
	</div>
</div>
