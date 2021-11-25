<?php
/**
 * @version     2.0.0
 * @package     sellacious
 *
 * @copyright   Copyright (C) 2012-2020 Bhartiy Web Technologies. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Saurabh Sabharwal <info@bhartiy.com> - http://www.bhartiy.com
 */
defined('_JEXEC') or die;
/** @var  array $tplData */
/** @var  Joomla\Registry\Registry $registry */
/** @var  \SellaciousHelper $helper */

$registry     = $tplData;
$helper       = $this->helper;
$msgUndefined = JText::_('COM_SELLACIOUS_PROFILE_VALUE_NOT_FOUND');

if ($this->getShowOption('seller.title')): ?>
	<div class="ctech-form-group">
		<div class="ctech-col-form-label">
			<label><?php echo JText::_('COM_SELLACIOUS_PROFILE_FIELD_SELLER_NAME_LABEL'); ?></label>
		</div>
		<div class="ctech-col-form-value">
			<?php echo $registry->get('seller.title') ?: $msgUndefined; ?>
		</div>
		<div class="clearfix"></div>
	</div>
<?php endif; ?>
<?php if ($this->getShowOption('seller.store_name')): ?>
	<div class="ctech-form-group">
		<div class="ctech-col-form-label">
			<label><?php echo JText::_('COM_SELLACIOUS_PROFILE_FIELD_STORE_NAME_LABEL'); ?></label>
		</div>
		<div class="ctech-col-form-value">
			<?php echo $registry->get('seller.store_name') ?: $msgUndefined; ?>
		</div>
		<div class="clearfix"></div>
	</div>
<?php endif; ?>
<?php if ($this->getShowOption('seller.store_address')): ?>
	<div class="ctech-form-group">
		<div class="ctech-col-form-label">
			<label><?php echo JText::_('COM_SELLACIOUS_PROFILE_FIELD_STORE_ADDRESS_LABEL'); ?></label>
		</div>
		<div class="ctech-col-form-value">
			<?php echo $registry->get('seller.store_address') ?: $msgUndefined; ?>
		</div>
		<div class="clearfix"></div>
	</div>
<?php endif; ?>
<?php if ($this->getShowOption('seller.currency')): ?>
	<div class="ctech-form-group">
		<div class="ctech-col-form-label">
			<label><?php echo JText::_('COM_SELLACIOUS_PROFILE_FIELD_CURRENCY_LISTING_LABEL'); ?></label>
		</div>
		<div class="ctech-col-form-value">
			<?php echo $registry->get('seller.currency') ?: $msgUndefined; ?>
		</div>
		<div class="clearfix"></div>
	</div>
<?php endif; ?>
<?php if ($this->getShowOption('seller.store_location')): ?>
	<div class="ctech-form-group">
		<div class="ctech-col-form-label">
			<label><?php echo JText::_('COM_SELLACIOUS_PROFILE_FIELD_SELLER_STORE_LOCATION_LABEL'); ?></label>
		</div>
		<div class="ctech-col-form-value">
			<?php echo $registry->get('seller.store_location') ?: $msgUndefined; ?>
		</div>
		<div class="clearfix"></div>
	</div>
<?php endif; ?>
