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
/** @var  array   $fields */

extract($displayData);

JHtml::_('ctech.bootstrap', array('js' => false));

JHtml::_('script', 'sellacious/vue.js', array('relative' => true, 'version' => S_VERSION_CORE));
JHtml::_('script', 'sellacious/plugins/Sortable/Sortable.min.js', array('relative' => true, 'version' => S_VERSION_CORE));
JHtml::_('script', 'sellacious/plugins/Draggable/vuedraggable.umd.min.js', array('relative' => true, 'version' => S_VERSION_CORE));

JHtml::_('script', 'sellacious/field.addressfields.js', array('relative' => true, 'version' => S_VERSION_CORE));
JHtml::_('stylesheet', 'sellacious/field.addressfields.css', array('relative' => true, 'version' => S_VERSION_CORE));

JText::script('JSHOW');
JText::script('JHIDE');
JText::script('JREQUIRED');

foreach ($fields as $field)
{
	JText::script($field['label']);

	foreach ($field['options'] as $option)
	{
		JText::script($option->label);
	}

	JHtml::_('ctech.vueTemplate', "vue-address-field-{$field['type']}", __DIR__ . "/fieldtypes/address-field-{$field['type']}.vue");
}

$helper = SellaciousHelper::getInstance();

$info = array(
	'id'   => $id,
	'name' => $name
);
?>

<div class="addressfields" id="addressfields-<?php echo $id; ?>" data-info="<?php echo htmlspecialchars(json_encode($info)); ?>" data-fields="<?php echo htmlspecialchars(json_encode($fields)); ?>">
	<?php include __DIR__ . '/addressfields.vue'; ?>
</div>
