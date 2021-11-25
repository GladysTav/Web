<?php
/**
 * @version     2.0.0
 * @package     Sellacious Hyperlocal Module
 *
 * @copyright   Copyright (C) 2012-2020 Bhartiy Web Technologies. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Izhar Aazmi <info@bhartiy.com> - http://www.bhartiy.com
 */
// no direct access
defined('_JEXEC') or die('Restricted access');
?>
<div class="mod_sellacious_hyperlocal">
	<?php if (isset($e) && $e instanceof Exception): ?>
		<div class="alert alert-error"><strong>Error</strong>: <?php echo $e->getMessage() ?></div>
	<?php else: ?>
		<div class="alert alert-error"><strong>Error</strong>: <?php echo $message ?></div>
	<?php endif; ?>
</div>
