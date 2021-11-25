<?php
/**
 * @version     2.0.0
 * @package     sellacious
 *
 * @copyright   Copyright (C) 2012-2020 Bhartiy Web Technologies. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Izhar Aazmi <info@bhartiy.com> - http://www.bhartiy.com
 */
defined('_JEXEC') or die;

use Joomla\Registry\Registry;
use Joomla\Utilities\ArrayHelper;

/** @var  Registry  $registry */
$registry = $this->registry;

$msgBlank = JText::_('COM_SELLACIOUS_PROFILE_VALUE_NOT_FOUND');

$data   = (array) $registry->get('custom_profile_data');
$groups = ArrayHelper::getColumn($data, 'group', 'group_id');
$sets   = array();

foreach ($data as $item)
{
	if ($item->group_id < 2)
	{
		continue;
	}

	$sets[(int) $item->group_id][] = $item;
}

foreach($sets as $gid => $group):
	echo JHtml::_('ctechBootstrap.addTab', 'profile_tabs_custom', ArrayHelper::getValue($groups, $gid), 'profile_tabs');
	?>
	<fieldset class="w100p users_profile_custom_info user-profile-info">
		<?php foreach ($group as $item): ?>
			<div class="ctech-form-group">
				<div class="ctech-col-form-label">
					<label><?php echo $item->label; ?></label>
				</div>
				<div class="ctech-col-form-value">
					<?php echo $item->html ?: $msgBlank; ?>
				</div>
				<div class="clearfix"></div>
			</div>
		<?php endforeach; ?>
	</fieldset>
<?php echo JHtml::_('ctechBootstrap.endTab');
endforeach; ?>
