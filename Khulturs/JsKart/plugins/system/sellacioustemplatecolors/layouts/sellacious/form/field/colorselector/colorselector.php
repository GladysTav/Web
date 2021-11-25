<?php
/**
 * @version     2.0.0
 * @package     Sellacious
 *
 * @copyright   Copyright (C) 2012-2020 Bhartiy Web Technologies. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Saurabh Sabharwal <info@bhartiy.com> - http://www.bhartiy.com
 */
// no direct access
defined('_JEXEC') or die('Restricted access');

/** @var  array   $displayData */
/** @var  array   $isPremium */
/** @var  string  $fieldName */
/** @var  string  $id */
/** @var  string  $selector */
/** @var  string  $hover */
/** @var  string  $name */
/** @var  string  $pseudo_selector */
/** @var  string  $pseudo_attribute */
/** @var  array   $attributes */
/** @var  string  $template */
extract($displayData);

JHtml::_('ctech.bootstrap', array('js' => false));

JHtml::_('script', 'sellacious/vue.js', array('relative' => true, 'version' => S_VERSION_CORE));
JHtml::_('script', 'sellacious/vue-color.min.js', array('relative' => true, 'version' => S_VERSION_CORE));
JHtml::_('script', 'plg_system_sellacioustemplatecolors/field.colorselector.js', array('relative' => true, 'version' => S_VERSION_CORE));

JHtml::_('stylesheet', 'plg_system_sellacioustemplatecolors/field.colorselector.css', array('relative' => true, 'version' => S_VERSION_CORE));

$args = array();

$args['isPremium']        = $isPremium;
$args['fieldName']        = $fieldName;
$args['id']               = $id;
$args['selector']         = $selector;
$args['hover']            = $hover;
$args['name']             = $name;
$args['pseudo_selector']  = $pseudo_selector;
$args['pseudo_attribute'] = $pseudo_attribute;
$args['attributes']       = $attributes;
$args['template']         = $template;
$args['classes']          = $class;

?>

<div class="colorselector" id="colorselector-<?php echo $id; ?>" data-id="<?php echo $id; ?>" data-options="<?php echo htmlspecialchars(json_encode($args)); ?>">
	<?php include __DIR__ . '/colorselector.vue'; ?>
</div>
