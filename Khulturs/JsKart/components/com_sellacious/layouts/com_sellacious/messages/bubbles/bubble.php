<?php
/**
 * @version     2.0.0
 * @package     sellacious
 *
 * @copyright   Copyright (C) 2012-2020 Bhartiy Web Technologies. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Aditya Chakraborty <info@bhartiy.com> - http://www.bhartiy.com
 */
// no direct access
defined('_JEXEC') or die;

/** @var  stdClass  $displayData */
$me      = JFactory::getUser();
$helper  = SellaciousHelper::getInstance();
$message = $displayData['message'];

$owner     = $message->sender > 0 ? $message->sender : 0;
$ownerName = JFactory::getUser($owner)->get('name');
?>
<div class="message-data <?php echo ($message->sender == $me->id) ? 'ctech-text-right' : ''; ?>">
	<span class="message-data-name">
		<?php echo $ownerName; ?>
	</span>
	<span class="message-data-time ctech-text-secondary"><?php echo $helper->core->relativeDateTime($message->date_sent); ?></span>
</div>
<div class="message <?php echo ($message->sender == $me->id) ? 'ctech-bg-me my-message ctech-float-right' : 'ctech-bg-them'; ?>">
	<div class="message-body"><?php echo nl2br($message->body, false); ?></div>
</div>
