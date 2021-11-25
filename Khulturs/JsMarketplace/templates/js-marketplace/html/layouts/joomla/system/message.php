<?php
/**
 * @package     Joomla.Site
 * @subpackage  Layout
 *
 * @copyright   Copyright (C) 2005 - 2015 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die;

/** @var  array  $displayData */
$msgList = $displayData['msgList'];
?>
<script type="joomla/message-json" id="system-message-json"><?php echo json_encode($msgList) ?></script>
<div id="system-message-container"></div>
