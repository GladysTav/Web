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

if ($this->getShowOption('client.client_type')): ?>
	<div class="ctech-form-group">
		<div class="ctech-col-form-label">
			<label><?php echo JText::_('COM_SELLACIOUS_PROFILE_FIELD_CLIENT_TYPE_LABEL'); ?></label>
		</div>
		<div class="ctech-col-form-value">
			<?php
			$catTypes = $helper->client->getTypes();
			$catType  = $helper->core->getArrayField($catTypes, 'value', $registry->get('client.client_type', 'individual'), 'text');

			echo $catType ?: $msgUndefined;
			?>
		</div>
		<div class="clearfix"></div>
	</div>
<?php endif; ?>
<?php if ($this->getShowOption('client.business_name')): ?>
	<div class="ctech-form-group">
		<div class="ctech-col-form-label">
			<label><?php echo JText::_('COM_SELLACIOUS_PROFILE_FIELD_BUSINESS_NAME_LABEL'); ?></label>
		</div>
		<div class="ctech-col-form-value">
			<?php echo $registry->get('client.business_name') ?: $msgUndefined; ?>
		</div>
		<div class="clearfix"></div>
	</div>
<?php endif; ?>
<?php if ($this->getShowOption('client.org_reg_no')): ?>
	<div class="ctech-form-group">
		<div class="ctech-col-form-label">
			<label><?php echo JText::_('COM_SELLACIOUS_PROFILE_FIELD_ORG_REG_NO_LABEL'); ?></label>
		</div>
		<div class="ctech-col-form-value">
			<?php echo $registry->get('client.org_reg_no') ?: $msgUndefined; ?>
		</div>
		<div class="clearfix"></div>
	</div>
<?php endif; ?>
<?php if ($this->getShowOption('client.org_certificate')): ?>
	<div class="ctech-form-group">
		<div class="ctech-col-form-label">
			<label><?php echo JText::_('COM_SELLACIOUS_PROFILE_FIELD_ORG_CERTIFICATE_LABEL'); ?></label>
		</div>
		<div class="ctech-col-form-value">
			<?php
			$filter       = array(
				'list.select' => 'a.id, a.path, a.original_name, a.doc_type, a.doc_reference',
				'table_name'  => 'clients',
				'context'     => 'org_certificate',
				'record_id'   => $registry->get('client.id'),
				'state'       => 1,
			);
			$certificates = (array) $helper->media->loadObjectList($filter);
			$images       = array();
			$files        = array();

			foreach ($certificates as $certificate)
			{
				$imgUrl = $helper->media->getURL($certificate->path, false);

				if ($imgUrl)
				{
					if ($helper->media->isImage(JPATH_ROOT . '/' . $certificate->path))
					{
						$images[] = '<img src="' . $imgUrl . '"/>';
					}
					else
					{
						$dLink   = JRoute::_(JUri::base(true) . '/index.php?option=com_sellacious&task=media.download&id=' . $certificate->id);
						$files[] = '<a href="' . $dLink . '">' . $certificate->original_name . '</a>';
					}
				}
			}
			?>

			<?php if ($images || $files): ?>
				<?php if ($images): ?>
					<ul class="media-list media-list-image">
						<?php foreach ($images as $image): ?>
							<li><?php echo $image ?></li>
						<?php endforeach; ?>
					</ul>
					<div class="clearfix"></div>
				<?php endif; ?>
				<?php if ($files): ?>
					<ul class="media-list media-list-generic pull-left">
						<?php foreach ($files as $cFile): ?>
							<li><i class="fa fa-files-o"></i> <?php echo $cFile ?></li>
						<?php endforeach; ?>
					</ul>
				<?php endif; ?>
			<?php else: ?>
				<?php echo $msgUndefined; ?>
			<?php endif ?>
		</div>
		<div class="clearfix"></div>
	</div>
<?php endif; ?>
