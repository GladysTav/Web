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

/** @var SellaciousViewShoprules $this */
/** @var  stdClass $tplData */
$item = $tplData;

if ($item->terms): ?>
<div class="ctech-modal shoprule-modal" id="shoprule-terms-modal-<?php echo $item->id ?>" role="dialog" aria-hidden="true" data-backdrop="true">
	<div class="ctech-modal-dialog" role="document">
		<div class="ctech-modal-content">
			<div class="ctech-modal-header">
				<h5 class="ctech-modal-title"><?php echo JText::_('COM_SELLACIOUS_SHOPRULE_ITEM_TERMS'); ?></h5>
				<button type="button" class="ctech-close" data-dismiss="ctech-modal" aria-label="close">
					<span aria-hidden="true">&times;</span>
				</button>
			</div>
			<div class="ctech-modal-body">
				<?php echo $item->terms; ?>
			</div>
		</div>
	</div>
	</div><?php
endif;
