<?php
/**
 * @version     2.0.0
 * @package     sellacious
 *
 * @copyright   Copyright (C) 2012-2020 Bhartiy Web Technologies. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Aditya Chakraborty <info@bhartiy.com> - http://www.bhartiy.com
 */
// no direct access
defined('_JEXEC') or die;

/** @var \SellaciousViewStores $this */
$app = JFactory::getApplication();

// Load the behaviors.
JHtml::_('behavior.framework');
JHtml::_('jquery.framework');

JHtml::_('stylesheet', 'com_sellacious/util.rating.css', array('version' => S_VERSION_CORE, 'relative' => true));
JHtml::_('stylesheet', 'com_sellacious/fe.component.css', array('version' => S_VERSION_CORE, 'relative' => true));
JHtml::_('stylesheet', 'com_sellacious/font-awesome.min.css', array('version' => S_VERSION_CORE, 'relative' => true));
JHtml::_('stylesheet', 'com_sellacious/fe.view.stores.css', array('version' => S_VERSION_CORE, 'relative' => true));

JHtml::_('script', 'com_sellacious/fe.view.favoritestores.js', array('version' => S_VERSION_CORE, 'relative' => true));
?>
<div class="ctech-wrapper">
	<div class="favorite-stores-heading">
		<h2><?php echo JText::_('COM_SELLACIOUS_USER_FAVORITE_STORES') ?>
			<span>(<?php echo $this->get('Total'); ?>&nbsp;<?php
				echo JText::_('COM_SELLACIOUS_USER_FAVORITE_STORE_COUNT') ?>)
			</span>
		</h2>
	</div>
	<form action="<?php echo JRoute::_('index.php?option=com_sellacious&view=stores'); ?>" method="post" name="adminForm" id="adminForm">
		<div id="stores-page" class="w100p">
			<h4 class="no-stores-found <?php echo count($this->items) == 0 ? '' : 'ctech-d-none' ?>"><?php echo JText::_('COM_SELLACIOUS_STORES_NO_MATCH') ?></h4>
			<?php
			$args = array('id' => 'storesList', 'items' => $this->items, 'context' => array('stores', 'favorite'), 'layout' => 'grid');

			echo JLayoutHelper::render('sellacious.stores.grid', $args);
			?>
			<div class="clearfix"></div>
			<?php
			/** @var JPagination $pagination */
			$pagination = $this->pagination;
			?>
		</div>
		<div class="clearfix"></div>
		<div class="left pagination"><?php echo $this->pagination->getPagesLinks(); ?></div>
		<div class="center"><br><?php echo $pagination->getPagesCounter(); ?></div>

		<?php echo JHtml::_('form.token'); ?>
	</form>
</div>
