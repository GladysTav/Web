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

/** @var array $displayData */
$uid          = $displayData['uid'];
$checkoutData = $displayData['checkout_data'];
?>
<table class="w100p table table-noborder coq_data" id="coq_data_<?php echo $uid; ?>"><?php
	foreach ($checkoutData as $coqData):
		if (is_object($coqData) && $coqData->value): ?>
			<tr>
				<td>
					<?php echo $coqData->label; ?>
				</td>
				<td><?php echo $coqData->value; ?></td>
			</tr><?php
		endif;
	endforeach;
	?>
</table>
