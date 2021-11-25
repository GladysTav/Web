<?php
/**
 * @package   JD Simple Contact Form
 * @author    JoomDev https://www.joomdev.com
 * @copyright Copyright (C) 2009 - 2018 JoomDev.
 * @license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 or Later
 */
// no direct access
defined('_JEXEC') or die;
extract($displayData);
$buttonText = $params->get('submittext', 'JSUBMIT');
?>
<div class="col">
     <button type="submit" class="btn btn-primary"><?php echo JText::_($buttonText); ?></button>
</div>