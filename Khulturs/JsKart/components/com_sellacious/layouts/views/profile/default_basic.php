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

$msgUndefined = JText::_('COM_SELLACIOUS_PROFILE_VALUE_NOT_FOUND');

echo JHtml::_('ctechBootstrap.addTab', 'profile_tabs_basic', JText::_('COM_SELLACIOUS_PROFILE_FIELDSET_BASIC'), 'profile_tabs');
$avatar   = $this->helper->media->getImage('user.avatar', $registry->get('id'), true);

$customProfile = (array) $registry->get('custom_profile_data');
?>
<fieldset class="w100p users_profile_basic user-profile-info">
	<?php if ($this->getShowOption('profile.avatar')): ?>
		<div class="profile-avatar-container">
			<span class="profile-avatar" style="background-image: url('<?php echo $avatar; ?>')"></span>
		</div>
	<?php endif; ?>
	<div class="profile-basic-container">
		<?php if ($this->getShowOption('name')): ?>
		<div class="ctech-form-group">
			<div class="ctech-col-form-label">
				<label><?php echo JText::_('COM_SELLACIOUS_PROFILE_USER_FIELD_NAME_LABEL'); ?></label>
			</div>
			<div class="ctech-col-form-value">
				<?php echo $registry->get('name'); ?>
			</div>
			<div class="clearfix"></div>
		</div>
		<?php endif; ?>
		<div class="ctech-form-group">
			<div class="ctech-col-form-label">
				<label><?php echo JText::_('JGLOBAL_EMAIL'); ?></label>
			</div>
			<div class="ctech-col-form-value">
				<?php echo $registry->get('email'); ?>
			</div>
			<div class="clearfix"></div>
		</div>
		<?php if ($this->getShowOption('params.timezone')): ?>
			<div class="ctech-form-group">
				<div class="ctech-col-form-label">
					<label><?php echo JText::_('COM_SELLACIOUS_PROFILE_USER_FIELD_TIMEZONE_LABEL'); ?></label>
				</div>
				<div class="ctech-col-form-value">
					<?php echo $registry->get('params.timezone') ?: $msgUndefined; ?>
				</div>
				<div class="clearfix"></div>
			</div>
		<?php endif; ?>
		<?php if ($this->getShowOption('profile.mobile')): ?>
			<div class="ctech-form-group">
				<div class="ctech-col-form-label">
					<label><?php echo JText::_('COM_SELLACIOUS_PROFILE_FIELD_MOBILE_LABEL'); ?></label>
				</div>
				<div class="ctech-col-form-value">
					<?php echo $registry->get('profile.mobile') ?: $msgUndefined; ?>
				</div>
				<div class="clearfix"></div>
			</div>
		<?php endif; ?>
		<?php if ($this->getShowOption('profile.website')): ?>
			<div class="ctech-form-group">
				<div class="ctech-col-form-label">
					<label><?php echo JText::_('COM_SELLACIOUS_PROFILE_FIELD_WEBSITE_LABEL'); ?></label>
				</div>
				<div class="ctech-col-form-value">
					<?php echo $registry->get('profile.website') ?: $msgUndefined; ?>
				</div>
				<div class="clearfix"></div>
			</div>
		<?php endif; ?>

		<?php if ($this->helper->config->get('user_currency') && $this->getShowOption('profile.currency')): ?>
			<div class="ctech-form-group">
				<div class="ctech-col-form-label">
					<label><?php echo JText::_('COM_SELLACIOUS_PROFILE_FIELD_CURRENCY_LABEL'); ?></label>
				</div>
				<div class="ctech-col-form-value">
					<?php echo $registry->get('profile.currency') ?: $msgUndefined; ?>
				</div>
				<div class="clearfix"></div>
			</div>
		<?php endif; ?>
		<?php foreach ($customProfile as $key => $profileItem):
			if ($profileItem->group_id == 1): ?>
				<div class="ctech-form-group">
					<div class="ctech-col-form-label">
						<label><?php echo $profileItem->label; ?></label>
					</div>
					<div class="ctech-col-form-value">
						<?php echo $profileItem->html ?: $msgBlank; ?>
					</div>
					<div class="clearfix"></div>
				</div><?php
			endif;
		endforeach;?>
	</div>
</fieldset>
<?php echo JHtml::_('ctechBootstrap.endTab'); ?>
