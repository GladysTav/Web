<?php
/**
 * @version     2.0.0
 * @package     sellacious
 *
 * @copyright   Copyright (C) 2012-2020 Bhartiy Web Technologies. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Versha Verma <info@bhartiy.com> - http://www.bhartiy.com
 */
// no direct access
defined('_JEXEC') or die;
?>

<div class="ctech-col-lg-3">
	<?php
	jimport('joomla.application.module.helper');
	$module = JModuleHelper::getModule('mod_sellacious_filters');
    $renMod = JModuleHelper::renderModule($module);

    if (!empty($renMod) && ($module->module == "mod_sellacious_filters")): ?>
        <?php if ($module->showtitle == 1): ?>
            <h6><?php echo $module->title ?></h6>
        <?php endif; ?>

		<?php echo trim($renMod); ?>
    <?php else: ?>
        <div class="product-bottom <?php echo (isset($module->class_sfx)) ? $module->class_sfx : ''; ?>">
            <?php echo trim($renMod); ?>
        </div>
    <?php endif; ?>
</div>
