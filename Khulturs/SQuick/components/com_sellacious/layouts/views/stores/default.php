<?php
/**
 * @version     2.0.0
 * @package     sellacious
 *
 * @copyright   Copyright (C) 2012-2020 Bhartiy Web Technologies. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Izhar Aazmi <info@bhartiy.com> - http://www.bhartiy.com
 */
// no direct access
defined('_JEXEC') or die;

/** @var \SellaciousViewStores $this */
$app = JFactory::getApplication();

// Load the behaviors.
JHtml::_('behavior.framework');
JHtml::_('jquery.framework');
JHtml::_('ctech.bootstrap');

JHtml::_('stylesheet', 'com_sellacious/util.rating.css', null, true);
JHtml::_('stylesheet', 'com_sellacious/fe.component.css', null, true);
JHtml::_('stylesheet', 'com_sellacious/font-awesome.min.css', null, true);
JHtml::_('stylesheet', 'com_sellacious/fe.view.stores.css', null, true);
?>
<form action="<?php echo JRoute::_('index.php?option=com_sellacious&view=stores'); ?>" method="post" name="adminForm"
	  id="adminForm">
	<?php
	if (count($this->items) == 0)
	{
		?><h4><?php echo JText::_('COM_SELLACIOUS_STORES_NO_MATCH') ?></h4><?php
	}

	$args = array('id' => 'storesList', 'items' => $this->items, 'context' => array('stores'), 'layout' => 'grid');

	echo JLayoutHelper::render('sellacious.stores.grid', $args);
	?>
	<?php
	/** @var JPagination $pagination */
	$pagination = $this->pagination;
	?>
	<div class="left pagination"><?php echo $this->pagination->getPagesLinks(); ?></div>
	<div class="center"><br><?php echo $pagination->getPagesCounter(); ?></div>

	<?php echo JHtml::_('form.token'); ?>
</form>
