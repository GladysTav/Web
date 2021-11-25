<?php
/**
 * @version     2.0.0
 * @package     Sellacious.ctech-bootstrap.modal
 * @subpackage  Layout
 *
 * @copyright   Copyright (C) 2005 - 2019 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Saurabh Sabharwal <info@bhartiy.com> - http://www.bhartiy.com
 */

defined('JPATH_BASE') or die;

/** @var array $displayData */
extract($displayData);

$animation = isset($displayData['animation']) ? $displayData['animation'] : '';
$id        = isset($displayData['id']) ? $displayData['id'] : '';
$backdrop  = isset($displayData['backdrop']) ? $displayData['backdrop'] : '';
$classes   = isset($displayData['classes']) ? $displayData['classes'] : '';
$header    = isset($displayData['header']) ? $displayData['header'] : '';
$body      = isset($displayData['body']) ? $displayData['body'] : '';
$url       = isset($displayData['url']) ? $displayData['url'] : '';
$footer    = isset($displayData['footer']) ? $displayData['footer'] : '';
$showHead  = isset($displayData['showHeader']) ? $displayData['showHeader'] : true;
$iframe    = '';
if ($url)
{
	$iframe = '<div class="ctech-embed-responsive ctech-embed-responsive-16by9">';
	$iframe .= '<iframe class="ctech-embed-responsive-item" src="' . $url . '" width="800" height="600"></iframe>';
	$iframe .= '</div>';
}

?>
<div class="ctech-wrapper">
	<div class="ctech-modal <?php echo $animation; ?>" id="<?php echo $id; ?>" role="dialog" aria-hidden="true" <?php echo $backdrop; ?>>
		<div class="ctech-modal-dialog <?php echo $classes; ?>" role="document">
			<div class="ctech-modal-content">
				<?php if ($showHead): ?>
				<div class="ctech-modal-header">
					<h5 class="ctech-modal-title"><?php echo $header; ?></h5>
					<button type="button" class="ctech-close" data-dismiss="ctech-modal" aria-label="close">
						<span aria-hidden="true">&times;</span>
					</button>
				</div>
				<?php endif; ?>
				<div class="ctech-modal-body">
					<?php
					if ($body)
					{
						echo $body;
					}
					elseif ($url)
					{
						echo $iframe;
					}
					?>
				</div>
				<?php if ($footer): ?>
					<div class="ctech-modal-footer">
						<?php echo $footer; ?>
					</div>
				<?php endif; ?>
			</div>
		</div>
	</div>
</div>
