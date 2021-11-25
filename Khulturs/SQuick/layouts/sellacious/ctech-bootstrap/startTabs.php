<?php
/**
 * @version     2.0.0
 * @package     Sellacious.ctech-bootstrap.startTabs
 * @subpackage  Layout
 *
 * @copyright   Copyright (C) 2005 - 2019 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Saurabh Sabharwal <info@bhartiy.com> - http://www.bhartiy.com
 */

defined('JPATH_BASE') or die;

/** @var array $displayData */
extract($displayData);

$id       = isset($displayData['id']) ? $displayData['id'] : '';
$type     = isset($displayData['type']) ? $displayData['type'] : 'tabs';
$vertical = isset($displayData['vertical']) ? $displayData['vertical'] : false;
?>
<?php if ($vertical): ?>
<div class="ctech-wrapper">
	<div class="ctech-row">
		<div class="ctech-col-sm-4">
			<?php endif; ?>
			<ul class="ctech-nav ctech-nav-<?php echo $type; ?>" id="<?php echo $id; ?>" role="tablist"></ul>
			<?php if ($vertical): ?>
		</div>
		<div class="ctech-col-sm-8">
			<?php endif; ?>
			<div class="ctech-tab-content" id="">
