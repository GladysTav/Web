<?php
/**
 * @version     2.0.0
 * @package     Sellacious
 *
 * @copyright   Copyright (C) 2012-2020 Bhartiy Web Technologies. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Saurabh Sabharwal <info@bhartiy.com> - http://www.bhartiy.com
 */
defined('_JEXEC') or die;

use Sellacious\Template\TemplateVariable;

/** @var  array  $displayData  */
/** @var  TemplateVariable[]  $variables */

extract($displayData);
?>
<table class="table table-hover">
	<?php foreach ($variables as $variable): ?>
	<tr>
		<th style="min-width: 20px; width: 10%; white-space: nowrap;" class="template-variable" data-key="<?php echo htmlspecialchars($variable->name) ?>">
			<pre class="text-center w100p" style="padding: 1px; margin: 0;">%<?php echo strtoupper($variable->name) ?>%</pre>
		</th>
		<td><?php echo $variable->description ?></td>
	</tr>
	<?php endforeach; ?>
</table>
