<?php
/**
 * @version     2.0.0
 * @package     Sellacious.ctech-bootstrap.addAccordionSlide
 * @subpackage  Layout
 *
 * @copyright   Copyright (C) 2005 - 2019 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Saurabh Sabharwal <info@bhartiy.com> - http://www.bhartiy.com
 */

defined('JPATH_BASE') or die;

/** @var array $displayData */
extract($displayData);

$id     = isset($displayData['id']) ? $displayData['id'] : '';
$parent = isset($displayData['parent']) ? $displayData['parent'] : '';
$label  = isset($displayData['label']) ? $displayData['label'] : '';

$show   = isset($displayData['active']) ? $displayData['active'] === $displayData['id'] ? 'ctech-show' : '' : '';

?>

<div class="ctech-card">
	<div class="ctech-card-header">
		<h2 class="ctech-mb-0">
			<button class="ctech-btn ctech-btn-link" type="button" data-toggle="ctech-collapse" data-target="#<?php echo $id; ?>">
				<?php echo $label; ?>
			</button>
		</h2>
	</div>
	<div id="<?php echo $id; ?>" class="ctech-collapse <?php echo $show; ?>" data-parent="#<?php echo $parent; ?>">
		<div class="ctech-card-body">

