<?php
/**
 * @version     __DEPLOY_VERSION__
 * @package     Sellacious Navigator Module
 *
 * @copyright   Copyright (C) 2012-2019 Bhartiy Web Technologies. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Saurabh Sabharwal <info@bhartiy.com> - http://www.bhartiy.com
 */
// no direct access
defined('_JEXEC') or die('Restricted access');

JHtml::_('behavior.framework');
JHtml::_('jquery.framework');


JHtml::_('stylesheet', 'mod_sellacious_navigator/style.css', null, true);
JHtml::_('stylesheet', 'com_sellacious/fe.component.css', null, true);
JHtml::_('stylesheet', 'com_sellacious/font-awesome.min.css', null, true);

JHtml::_('script', 'mod_sellacious_navigator/default.js', false, true);


$app          = JFactory::getApplication();
$layout_class = '';
if ($style == 1) {
	$layout_class = 'full-width';
} else if ($style == 2) {
	$layout_class = 'fixed-top';
} else if ($style == 3) {
	$layout_class = 'fixed-top centered';
}
?>
<div class="sellacious-navigator <?php echo $layout_class; ?> <?php echo (isset($module->class_sfx)) ? $module->class_sfx : ''; ?>">
	<?php if ($style == 2 || $style == 3): echo '<div class="navigator-wrap">'; endif;?>
	<?php
	if(!empty($menu_items)):
		foreach($menu_items as $item) {
	?>
			<div class="scroll-to-element">
				<a <?php echo $item->menu_type == 0 ? 'href="' . $item->menu_url . '"' : 'scroll-target="#' . $item->menu_anchor . '"'; ?>>
					<?php if($item->menu_icon): ?>
					<i class="<?php echo $item->menu_icon ?>"></i>
					<?php endif; ?>
				</a>
				<span><?php echo $item->menu_label; ?></span>
			</div>
	<?php
		}
	endif;
	?>
	<?php if($style == 3): ?>
		<div class="active-line"></div>
	<?php endif; ?>
	<?php if ($style == 2 || $style == 3): echo '</div>'; endif;?>
</div>
