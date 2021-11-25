<?php
/**
 * @version     2.0.0
 * @package     sellacious
 *
 * @copyright   Copyright (C) 2012-2020 Bhartiy Web Technologies. All rights reserved.
 * @license     SPL Sellacious Private License; see http://www.sellacious.com/spl.html
 * @author      Izhar Aazmi <info@bhartiy.com> - http://www.bhartiy.com
 */
// no direct access
defined('_JEXEC') or die;

use Sellacious\Config\ConfigHelper;

$config = ConfigHelper::getInstance('com_sellacious', 'core');
$ver    = $config->get('productmedia_versions');

/** @var  JLayoutFile  $displayData */
/** @var  stdClass     $displayData */
$media = $displayData->media;
$field = $displayData->field;
$linkM = JUri::root() . 'index.php?option=com_sellacious&task=productmedia.download&p=' . $field->code . '&file=%s' . ($ver ? '&version=%s' : '');
$linkS = JUri::root() . 'index.php?option=com_sellacious&task=productmedia.sample&p=' . $field->code . '&file=%s' . ($ver ? '&version=%s' : '');
$edit  = 'index.php?option=com_sellacious&view=productmedia&p=' . $field->code . '&id=' . (int) $media->id;

$mediaType  = $media->media_type == 'link' ? 'link' : 'upload';
$sampleType = $media->sample_type == 'link' ? 'link' : 'upload';
$group      = $media->files_group == '' ? 'files': $media->files_group;
$helper     = SellaciousHelper::getInstance();
$versioning = $helper->config->get('productmedia_versions');
?>
<div class="jff-productmedia-row <?php echo $media->state == 0 ? 'disabled-media': '' ?>" data-media-id="<?php echo (int) $media->id ?>">
	<?php if ($versioning): ?>
		<div class="jff-productmedia-column version">

			<label class="label jff-productmedia-version"><?php
				echo JText::_('COM_SELLACIOUS_PRODUCT_MEDIA_FIELD_VERSION_DISPLAY_LABEL') ?><?php echo $media->version ?></label>

	</div>
	<?php endif ?>
	<div class="jff-productmedia-column media-file">
		<div class="jff-productmedia-file">
			<?php
			$mc = $helper->media->getDownloadCount($media->id, isset($media->media->id) ? $media->media->id : 0, 'media');
			$sc = $helper->media->getDownloadCount($media->id, isset($media->sample->id) ? $media->sample->id : 0, 'sample');

			if ($mediaType === 'link' && strlen($media->media_url)): ?>

				<div class="jff-productmedia-media">
					<?php
					if (strlen($media->media_url) <= 40)
					{
						$text  = $media->media_url;
						$title = '';
					}
					else
					{
						$text  = substr($media->media_url, 0, 19) . '&hellip;' . substr($media->media_url, -18);
						$title = implode('<wbr>', str_split($media->media_url, 4));
					}
					?>
					<a class="jff-productmedia-hotlink hasTooltip btn-copy-code" data-placement="right"
					   title="<?php echo JText::_('COM_SELLACIOUS_PRODUCT_MEDIA_LABEL_HOTLINK_COPY') ?>"
					   data-text="<?php echo sprintf($linkM, $group, $media->version) ?>"> <i class="fa fa-copy"></i></a>&nbsp;
					<span class="hasTooltip" title="<?php echo htmlspecialchars($title) ?>" data-html="true"> <?php echo JText::_('COM_SELLACIOUS_PRODUCT_MEDIA_LABEL_PRODUCT_MEDIA_LINK') ?></span>
					<span class="hasTooltip pull-right" title="<?php echo JText::_('COM_SELLACIOUS_PRODUCT_MEDIA_LABEL_DOWNLOADS_COUNT') ?>"><i class="fa fa-download"></i> <?php echo $mc; ?></span>

				</div>

			<?php elseif ($mediaType === 'upload' && isset($media->media)): ?>

				<div class="jff-productmedia-media">
					<?php
					$name = !empty($media->media->original_name) ? $media->media->original_name : basename($media->media->path);

					if (strlen($name) <= 40)
					{
						$text  = $name;
						$title = '';
					}
					else
					{
						$text  = substr($name, 0, 19) . '&hellip;' . substr($name, - 18);
						$title = $name;
					}
					?>
					<a class="jff-productmedia-hotlink hasTooltip btn-copy-code" data-placement="right"
					   title="<?php echo JText::_('COM_SELLACIOUS_PRODUCT_MEDIA_LABEL_HOTLINK_COPY') ?>"
					   data-text="<?php echo sprintf($linkM, $group, $media->version) ?>"> <i class="fa fa-copy"></i></a>&nbsp;
					<span class="hasTooltip" title="<?php echo htmlspecialchars($title) ?>"><?php echo JText::_('COM_SELLACIOUS_PRODUCT_MEDIA_LABEL_PRODUCT_MEDIA') ?></span>
					<span class="hasTooltip pull-right" title="<?php echo JText::_('COM_SELLACIOUS_PRODUCT_MEDIA_LABEL_DOWNLOADS_COUNT') ?>"><i class="fa fa-download"></i> <?php echo $mc; ?></span>
				</div>

			<?php else: ?>

				<div class="jff-productmedia-media no-media">
					<i class="fa fa-times txt-color-red"></i>&nbsp;
					<span><?php echo JText::_('COM_SELLACIOUS_PRODUCT_MEDIA_LABEL_' . ($mediaType === 'link' ? 'NO_LINK' : 'NO_FILE')) ?></span>
				</div>

			<?php endif; ?>
		</div>
		<div class="jff-productmedia-file">
				<?php if ($sampleType === 'link' && strlen($media->sample_url)): ?>

					<div class="jff-productmedia-sample">
						<?php
						if (strlen($media->sample_url) <= 40)
						{
							$text  = $media->sample_url;
							$title = '';
						}
						else
						{
							$text  = substr($media->sample_url, 0, 19) . '&hellip;' . substr($media->sample_url, -18);
							$title = implode('<wbr>', str_split($media->sample_url, 4));
						}
						?>
						<a class="jff-productmedia-hotlink hasTooltip btn-copy-code" data-placement="right"
						   title="<?php echo JText::_('COM_SELLACIOUS_PRODUCT_MEDIA_LABEL_HOTLINK_COPY') ?>"
						   data-text="<?php echo sprintf($linkS, $group, $media->version) ?>"> <i class="fa fa-copy"></i></a>&nbsp;
						<span class="hasTooltip" title="<?php echo htmlspecialchars($title) ?>" data-html="true"> <?php echo JText::_('COM_SELLACIOUS_PRODUCT_MEDIA_LABEL_SAMPLE_MEDIA') ?>&nbsp;&nbsp;</span>
						<span class="hasTooltip pull-right" title="<?php echo JText::_('COM_SELLACIOUS_PRODUCT_MEDIA_LABEL_DOWNLOADS_COUNT') ?>"><i class="fa fa-download"></i> <?php echo $sc; ?></span>
					</div>

				<?php elseif ($sampleType === 'upload' && isset($media->sample)): ?>

					<div class="jff-productmedia-sample">
						<?php
						$name = !empty($media->sample->original_name) ? $media->sample->original_name : basename($media->sample->path);

						if (strlen($name) <= 40)
						{
							$text  = $name;
							$title = '';
						}
						else
						{
							$text  = substr($name, 0, 19) . '&hellip;' . substr($name, - 18);
							$title = $name;
						}
						?>
						<a class="jff-productmedia-hotlink hasTooltip btn-copy-code" data-placement="right"
						   title="<?php echo JText::_('COM_SELLACIOUS_PRODUCT_MEDIA_LABEL_HOTLINK_COPY') ?>"
						   data-text="<?php echo sprintf($linkS, $group, $media->version) ?>"> <i class="fa fa-copy"></i></a>&nbsp;
						<span class="hasTooltip" title="<?php echo htmlspecialchars($title) ?>"><?php echo JText::_('COM_SELLACIOUS_PRODUCT_MEDIA_LABEL_SAMPLE_MEDIA') ?>&nbsp;&nbsp;</span>
						<span class="hasTooltip pull-right" title="<?php echo JText::_('COM_SELLACIOUS_PRODUCT_MEDIA_LABEL_DOWNLOADS_COUNT') ?>"><i class="fa fa-download"></i> <?php echo $sc; ?></span>
					</div>

				<?php else: ?>

					<div class="jff-productmedia-sample no-media">
						<i class="fa fa-times txt-color-red"></i>&nbsp;
						<span><?php echo JText::_('COM_SELLACIOUS_PRODUCT_MEDIA_LABEL_' . ($sampleType === 'upload' ? 'NO_FILE' : 'NO_LINK')) ?></span>
					</div>

				<?php endif; ?>
		</div>
	</div>
	<div class="jff-productmedia-column media-info <?php echo $versioning ? 'media-info-small' : '' ?>">
		<div>
			<?php if ($media->hotlink == 2): ?>
				<label class="label jff-productmedia-state-hotlink hasTooltip pull-right active" title="<?php echo JText::_('COM_SELLACIOUS_PRODUCT_MEDIA_FIELD_HOTLINK_OPTION_SECURED') ?>"><i class="fa fa-lock"></i></label>
			<?php endif; ?>
			<?php if ($versioning): ?>
			<label class="label jff-productmedia-state-latest pull-right">
				<i title="<?php echo JText::_('COM_SELLACIOUS_PRODUCT_MEDIA_LABEL_LATEST') ?>" class="fa fa-star <?php echo $media->is_latest ? '' : 'hidden' ?>  hasTooltip"></i></label>
			<?php endif; ?>
		</div>
		<div class="pull-right">
			<div class="jff-productmedia-tags pull-right">
				<?php foreach (explode(',', $media->tags) as $tag): ?>
					<label class="label label-info jff-productmedia-tag pull-right"><?php echo $tag ?></label><wbr>
				<?php endforeach; ?>
			</div>
		</div>

		<?php
			$date = date_create($media->released);
			$date_small = date_format($date, 'y-m-d');
			$date = date_format($date, 'Y-m-d');
		?>
		<label class="jff-productmedia-released pull-right date-small"><span><?php echo $date_small; ?></span></label>
		<label class="jff-productmedia-released pull-right date-big"><span><?php echo $date; ?></span></label>
	</div>
	<div class="jff-productmedia-column media-actions">
		<a href="#" class="label jff-productmedia-state hasTooltip <?php
		echo $media->state ? 'active jff-productmedia-state-unpublish' : 'jff-productmedia-state-publish' ?>" title="<?php
		echo JText::_($media->state ? 'JPUBLISHED' : 'JUNPUBLISHED') ?>"><i
					class="fa <?php echo $media->state ? 'fa-eye' : 'fa-eye-slash' ?>"></i></a>
		<button type="button" class="btn btn-xs btn-primary jff-productmedia-edit"
				data-drawer-url="<?php echo htmlspecialchars($edit) ?>"><i class="fa fa-pencil"></i></button>
		<button type="button" class="btn btn-xs btn-danger jff-productmedia-remove"><i class="fa fa-times"></i></button>
	</div>

</div>
