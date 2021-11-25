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
defined('_JEXEC') or die('Restricted access');

/** @var array  $displayData */
?>
<table cellpadding="5" style="width: 100%;max-width: 100%;background-color: #fff;border-collapse: collapse;border-spacing: 0;">
	<?php
	$count = 0;

	foreach ($displayData as $si => $status)
	{
		$style = '';

		if ($count >= 1)
		{
			$style = 'style = "border-top: 1px solid #dfdfdf;"';
		}
		?>
		<tr <?php echo $style ?>>
			<td style="background-color: #f9f9f9; color: #000000; font-size: 11px; line-height: 1;padding: 5px; white-space: nowrap;"><?php
				echo JHtml::_('date', $status->created, 'M d, Y h:i A'); ?></td>
			<td style="background-color: #f9f9f9; color: #000000; font-size: 11px; line-height: .9;padding: 5px;text-align: right;">
				<span><?php echo $status->s_title ?></span>
			</td>
		</tr>
		<?php
		$count++;
	}
	?>
</table>
