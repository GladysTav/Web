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

/** @var  \Joomla\Registry\Registry  $registry */
$registry = $this->registry;

$tabData = $this->loadTemplate('seller_fields', $registry);

if ($tabData):
	echo JHtml::_('ctechBootstrap.addTab', 'profile_tabs_seller', JText::_('COM_SELLACIOUS_PROFILE_FIELDSET_SELLER'), 'profile_tabs');
	?>

	<fieldset class="w100p users_profile_seller user-profile-info">
		<?php echo $tabData; ?>
	</fieldset>

	<?php echo JHtml::_('ctechBootstrap.endTab');
endif; ?>
