<?php
/**
 * @version     2.0.0
 * @package     Sellacious Seller Stores Module
 *
 * @copyright   Copyright (C) 2012-2020 Bhartiy Web Technologies. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Mohd Kareemuddin <info@bhartiy.com> - http://www.bhartiy.com
 */

// no direct access
defined('_JEXEC') or die('Restricted access');

use Joomla\Registry\Registry;

JHtml::_('stylesheet', 'com_sellacious/util.rating.css', null, true);
JHtml::_('stylesheet', 'com_sellacious/font-awesome.min.css', null, true);
JHtml::_('stylesheet', 'mod_sellacious_stores/style.css', null, true);

?>
<div class="mod-sellacious-stores stores-grid-layout <?php echo $class_sfx; ?>">
	<?php if ($title || $subtitle): ?>
		<div class="module-header">
			<?php if ($title): ?>
				<h2 class="sellacious-stores-title"><?php echo $title; ?></h2>
			<?php endif; ?>
			<?php if ($subtitle): ?>
				<p class="sellacious-stores-subtitle"><?php echo $subtitle; ?></p>
			<?php endif; ?>
		</div>
	<?php endif; ?>
	<?php
	$args = array('id' => $module->id, 'items' => $stores, 'params' => $params, 'context' => array('stores'), 'layout' => $layout);
	echo JLayoutHelper::render('sellacious.stores.grid', $args);
	?>
	<div class="clearfix"></div>
</div>
