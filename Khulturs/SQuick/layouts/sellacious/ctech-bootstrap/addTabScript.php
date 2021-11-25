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

$id    = isset($displayData['id']) ? $displayData['id'] : '';
$label = isset($displayData['label']) ? $displayData['label'] : '';
$parent = isset($displayData['parent']) ? $displayData['parent'] : '';
$active = isset($displayData['active']) ? $displayData['active'] == true ? 'ctech-active ctech-show' : '' : '';
$toggle = isset($displayData['type']) ? $displayData['type'] == 'ctech-pills' ? 'ctech-pill' : 'ctech-tab' : 'ctech-tab';

$li = '<li class="ctech-nav-item">
			<a class="ctech-nav-link ' . $active . '" id="' . $id . '-tab" href="#' . $id . '" data-toggle="' . $toggle . '" role="tab" aria-controls="' . $id . '">' . $label . '</a>
		</li>';

$script = 'jQuery(function($) {
		  $("#' . $parent . '").append(' . json_encode($li) . ');
		});';

echo $script;
