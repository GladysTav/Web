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
$registry      = $this->registry;
$msgUndefined  = JText::_('COM_SELLACIOUS_PROFILE_VALUE_NOT_FOUND');
$addressFields = $this->helper->user->getAddressFields();

echo JHtml::_('ctechBootstrap.addTab', 'profile_tabs_address', JText::_('COM_SELLACIOUS_USER_FIELDSET_ADDRESSES'), 'profile_tabs')
?>
<fieldset class="w100p users_profile_address user-profile-info">
	<?php if (isset($addressFields['name']) && $addressFields['name']): ?>
	<div class="ctech-form-group">
		<div class="ctech-col-form-label">
			<label><?php echo JText::_('COM_SELLACIOUS_ADDRESS_FORM_FIELD_NAME_LABEL'); ?></label>
		</div>
		<div class="ctech-col-form-value">
			<?php echo $registry->get('address.name') ?: $msgUndefined; ?>
		</div>
		<div class="clearfix"></div>
	</div>
	<?php endif; ?>

	<?php if (isset($addressFields['company']) && $addressFields['company']): ?>
	<div class="ctech-form-group">
		<div class="ctech-col-form-label">
			<label><?php echo JText::_('COM_SELLACIOUS_ADDRESS_FORM_FIELD_COMPANY_LABEL'); ?></label>
		</div>
		<div class="ctech-col-form-value">
			<?php echo $registry->get('address.company') ?: $msgUndefined; ?>
		</div>
		<div class="clearfix"></div>
	</div>
	<?php endif; ?>

	<?php if (isset($addressFields['po_box']) && $addressFields['po_box']): ?>
	<div class="ctech-form-group">
		<div class="ctech-col-form-label">
			<label><?php echo JText::_('COM_SELLACIOUS_ADDRESS_FORM_FIELD_PO_BOX_LABEL'); ?></label>
		</div>
		<div class="ctech-col-form-value">
			<?php echo $registry->get('address.po_box') ?: $msgUndefined; ?>
		</div>
		<div class="clearfix"></div>
	</div>
	<?php endif; ?>

	<?php if (isset($addressFields['address']) && $addressFields['address']): ?>
	<div class="ctech-form-group">
		<div class="ctech-col-form-label">
			<label><?php echo JText::_('COM_SELLACIOUS_ADDRESS_FORM_FIELD_ADDRESS_LABEL'); ?></label>
		</div>
		<div class="ctech-col-form-value">
			<?php echo $registry->get('address.address') ?: $msgUndefined; ?>
		</div>
		<div class="clearfix"></div>
	</div>
	<?php endif; ?>

	<?php if (isset($addressFields['landmark']) && $addressFields['landmark']): ?>
	<div class="ctech-form-group">
		<div class="ctech-col-form-label">
			<label><?php echo JText::_('COM_SELLACIOUS_ADDRESS_FORM_FIELD_LANDMARK_LABEL'); ?></label>
		</div>
		<div class="ctech-col-form-value">
			<?php echo $registry->get('address.landmark') ?: $msgUndefined; ?>
		</div>
		<div class="clearfix"></div>
	</div>
	<?php endif; ?>

	<?php if (isset($addressFields['country']) && $addressFields['country']):
		$country = $registry->get('address.country');
		$country = is_numeric($country) ? $this->helper->location->getFieldValue($country, 'title') : $country; ?>
	<div class="ctech-form-group">
		<div class="ctech-col-form-label">
			<label><?php echo JText::_('COM_SELLACIOUS_ADDRESS_FORM_FIELD_COUNTRY_LABEL'); ?></label>
		</div>
		<div class="ctech-col-form-value">
			<?php echo $this->escape($country) ?: $msgUndefined; ?>
		</div>
		<div class="clearfix"></div>
	</div>
	<?php endif; ?>

	<?php if (isset($addressFields['state_loc']) && $addressFields['state_loc']):
		$state_loc = $registry->get('address.state_loc');
		$state_loc = is_numeric($state_loc) ? $this->helper->location->getFieldValue($state_loc, 'title') : $state_loc; ?>
	<div class="ctech-form-group">
		<div class="ctech-col-form-label">
			<label><?php echo JText::_('COM_SELLACIOUS_ADDRESS_FORM_FIELD_STATE_LOC_LABEL'); ?></label>
		</div>
		<div class="ctech-col-form-value">
			<?php echo htmlspecialchars($state_loc) ?: $msgUndefined; ?>
		</div>
		<div class="clearfix"></div>
	</div>
	<?php endif; ?>

	<?php if (isset($addressFields['district']) && $addressFields['district']):
		$district = $registry->get('address.district');
		$district = is_numeric($district) ? $this->helper->location->getFieldValue($district, 'title') : $district; ?>
	<div class="ctech-form-group">
		<div class="ctech-col-form-label">
			<label><?php echo JText::_('COM_SELLACIOUS_ADDRESS_FORM_FIELD_DISTRICT_LABEL'); ?></label>
		</div>
		<div class="ctech-col-form-value">
			<?php echo htmlspecialchars($district) ?: $msgUndefined; ?>
		</div>
		<div class="clearfix"></div>
	</div>
	<?php endif; ?>

	<?php if (isset($addressFields['zip']) && $addressFields['zip']): ?>
	<div class="ctech-form-group">
		<div class="ctech-col-form-label">
			<label><?php echo JText::_('COM_SELLACIOUS_ADDRESS_FORM_FIELD_ZIP_LABEL'); ?></label>
		</div>
		<div class="ctech-col-form-value">
			<?php echo $registry->get('address.zip') ?: $msgUndefined; ?>
		</div>
		<div class="clearfix"></div>
	</div>
	<?php endif; ?>

	<?php if (isset($addressFields['mobile']) && $addressFields['mobile']): ?>
	<div class="ctech-form-group">
		<div class="ctech-col-form-label">
			<label><?php echo JText::_('COM_SELLACIOUS_ADDRESS_FORM_FIELD_MOBILE_LABEL'); ?></label>
		</div>
		<div class="ctech-col-form-value">
			<?php echo $registry->get('address.mobile') ?: $msgUndefined; ?>
		</div>
		<div class="clearfix"></div>
	</div>
	<?php endif; ?>

	<?php if (isset($addressFields['residential']) && $addressFields['residential']): ?>
	<div class="ctech-form-group">
		<div class="ctech-col-form-label">
			<label><?php echo JText::_('COM_SELLACIOUS_ADDRESS_FORM_FIELD_RESIDENTIAL_LABEL'); ?></label>
		</div>
		<div class="ctech-col-form-value">
			<?php $res = $registry->get('address.residential');

			if ($res === null)
			{
				echo $msgUndefined;
			}
			else
			{
				echo $res ? JText::_('COM_SELLACIOUS_ADDRESS_FORM_FIELD_TYPE_OPTION_RESIDENTIAL')
					: JText::_('COM_SELLACIOUS_ADDRESS_FORM_FIELD_TYPE_OPTION_OFFICE');
			}
			?>
		</div>
		<div class="clearfix"></div>
	</div>
	<?php endif; ?>
</fieldset>
<?php echo JHtml::_('ctechBootstrap.endTab'); ?>
