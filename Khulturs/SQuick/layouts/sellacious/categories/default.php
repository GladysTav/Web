<?php
/**
 * @version     2.0.0
 * @package     Sellacious.Category
 * @subpackage  Layout
 *
 * @copyright   Copyright (C) 2005 - 2019 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Saurabh Sabharwal <info@bhartiy.com> - http://www.bhartiy.com
 */

use Joomla\Registry\Registry;
use Joomla\Utilities\ArrayHelper;
use Sellacious\Config\ConfigHelper;

defined('_JEXEC') or die('Restricted access');

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
$context   = isset($context) ? (array) $context : array('categories');
$layout    = isset($layout) ? $layout : 'grid';

$defaults  = (object) array(
	'categories'          => array(),
	'show_sub_categories' => '1',
	'order_by'            => 'a.title ASC',
	'layout'              => 'default',
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
$blockStyle  = isset($blockLayout) ? $blockLayout : $config->get('category-block-layout', 'default');

JHtml::_('jquery.framework');
JHtml::_('ctech.bootstrap');

JHtml::_('script', 'sellacious/vue.min.js', array('relative' => true, 'version' => S_VERSION_CORE));
JHtml::_('script', 'sellacious/vue.category.block.js', array('relative' => true, 'version' => S_VERSION_CORE));

JHtml::_('script', 'sellacious/owl.carousel.js', array('relative' => true, 'version' => S_VERSION_CORE));
JHtml::_('stylesheet', 'sellacious/owl.carousel.min.css', array('relative' => true, 'version' => S_VERSION_CORE));
JHtml::_('stylesheet', 'sellacious/owl-custom.css', array('relative' => true, 'version' => S_VERSION_CORE));

JHtml::_('stylesheet', "sellacious/category.block-{$blockStyle}.css", array('relative' => true, 'version' => S_VERSION_CORE));

JText::script('COM_SELLACIOUS_CATEGORY_PRODUCTS');
JText::script('COM_SELLACIOUS_CATEGORY_CATEGORIES');

$colWidths = array(
	'2' => 'ctech-col-sm-6 ctech-col-6',
	'3' => 'ctech-col-sm-4 ctech-col-6',
	'4' => 'ctech-col-md-3 ctech-col-sm-4 ctech-col-6',
	'5' => 'ctech-col-c5 ctech-col-md-3 ctech-col-sm-4 ctech-col-6',
);

$configurations = array();

$configurations['blockStyle']                  = $blockStyle;
$configurations['context']                     = $context;
$configurations['module_id']                   = $contextId;
$configurations['mod_params']                  = $params;
$configurations['widthClasses']                = $layout === 'grid' ? ArrayHelper::getValue($colWidths, $config->get('category_cols', 4)) : '';
$configurations['show_subcat_count']           = $config->get('show_category_child_count', '1');
$configurations['show_category_product_count'] = $config->get('show_category_product_count', '1');
$configurations['category_image_size_adjust']  = $config->get('category_image_size_adjust', 'contain');


JHtml::_('ctech.vueTemplate', "vue-category-block-{$blockStyle}", __DIR__ . "/designs/{$blockStyle}/block-{$blockStyle}.vue");

$categories = $helper->category->parseCategoriesForLayout($items);

$doc->addScriptOptions('sellacious.category.data.module-' . $contextId, $categories);
$doc->addScriptOptions('sellacious.category.params.module-' . $contextId, $configurations);

?>
<div class="ctech-wrapper">
	<div class="category-blocks-container" data-id="<?php echo $id ?>">
		<?php include 'blocks.vue'; ?>
	</div>
</div>
