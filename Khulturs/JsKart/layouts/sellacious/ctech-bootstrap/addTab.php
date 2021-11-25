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

$id      = isset($displayData['id']) ? $displayData['id'] : '';
$animate = isset($displayData['animate']) ? $displayData['animate'] : 'ctech-fade';
$active  = isset($displayData['active']) ? $displayData['active'] ? 'ctech-active ctech-show' : '' : '';
?>

<div class="ctech-tab-pane <?php echo $animate; ?> <?php echo $active; ?>" id="<?php echo $id; ?>" role="tabpanel" aria-labelledby="<?php echo $id; ?>-tab">
