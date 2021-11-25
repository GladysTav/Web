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

/** @var SellaciousViewCoupons $this */

// Load the behaviors.
JHtml::_('behavior.framework');
JHtml::_('jquery.framework');
JHtml::_('ctech.bootstrap');

JHtml::_('stylesheet', 'com_sellacious/util.rating.css', null, true);
JHtml::_('stylesheet', 'com_sellacious/font-awesome.min.css', null, true);
JHtml::_('stylesheet', 'sellacious/card.css', null, true);
JHtml::_('stylesheet', 'com_sellacious/fe.view.coupons.css', null, true);

JHtml::_('script', 'com_sellacious/plugin/clipboardjs/clipboard.min.js', array('version' => S_VERSION_CORE, 'relative' => true));
JHtml::_('script', 'com_sellacious/fe.view.coupons.js', null, true);
?>
<div class="ctech-wrapper">
	<form action="<?php echo JUri::getInstance()->toString(); ?>" method="post" name="adminForm" id="adminForm">
		<?php echo $this->loadTemplate('bar'); ?>
		<div class="ctech-clearfix"></div>
		<div id="stores-page" class="w100p">
			<?php
			if (count($this->items) == 0)
			{
				?><h4><?php echo JText::_('COM_SELLACIOUS_COUPONS_NO_MATCH') ?></h4><?php
			}

			foreach ($this->items as $item)
			{
				echo $this->loadTemplate('block', $item);
			}
			?>
			<div class="clearfix"></div>
			<?php
			/** @var JPagination $pagination */
			$pagination = $this->pagination;
			?>

		</div>
		<div class="clearfix"></div>
		<div class="left pagination"><?php echo $pagination->getPagesLinks(); ?></div>
		<div class="center"><br><?php echo $pagination->getPagesCounter(); ?></div>

		<?php echo JHtml::_('form.token'); ?>
	</form>
</div>
